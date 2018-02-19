<?php 
defined('INDEX') or die('acsess error');

/**
 * Описание: Библиотека работы с пользователями
 */

class users {

    private $__user = false;
    private $__cache = array();

    private $__table = array(
        'users' => array(
            'id' => array(
                'ai' => 1,
                'type' => 'int',
            ),
            'login' => array(
                'valid' => 'required/_{xss_clean}/_{strtolower}/uniq_db{users|login}',
                'label' => 'Логин',
            ),
            'email' => array(
                'valid' => 'required/email/_{strtolower}/uniq_db{users|email}',
                'label' => 'E-mail',
             ),
            'password' => array(
                'valid' => 'required',
                'label' => 'Пароль',
            ),
            'GID' => array(
                'label' => 'Права',
                'valid' => '_{aint}/maxlen{3}',
                'type' => 'int',
                'default' => 0,
                ),
            'salt' => array(),
            'group' => array(
                'type' => 'int',
                'default' => 0,
                'label' => 'Группа',
                'valid' => '_{aint}',
            ),
            "create" => array(),
            "active" => array(
                "type" => "int",
                "default" => 0,
            ),
        ), 
    );


    /**
     * Проверка авторизован ли пользователь
     * @return boolean
     */

    public function __construct() {

        # обнуляем пользователя
        $this->__user = false;
        tpl()->assignByRef('user', $this->__user);

        # Добавляем базу
        if (isset(SYS()->DBTables['users']))
            SYS()->DBTables['users'] = array_merge(SYS()->DBTables['users'], $this->__table['users']);
        else
            SYS()->DBTables['users'] = $this->__table['users'];

        if (!empty($_COOKIE['user_class_saveUser'])){
            $res = @explode('|', base64_decode($_COOKIE['user_class_saveUser']));            
            if (count($res) == 3 && $res[0] > time()){  
                $user = $this->getUser(aint($res[1]));              
                if ($user && $res[2] == $this->hash($user['id'], $user['password'], $user['salt']))
                    return $this->setUser($user['id'], true);                
            }   
        }

        # Собираем параметры из сессии
        $param = array('id','hash');
        $session = array();
        foreach ($param as $p)
            if (!$session[$p] = session()->get($p)) return false; 
            // если нет одного из параметров возвращаем false

        # Получаем пользователя из базы если нужно
        if (!$user = $this->getUser($session['id'])) return false;

        # Сверяем Hash
        if ($this->hash($user['id'], $user['password'], $user['salt']) != $session['hash']) return false;

        # Устанавливаем пользователя
        return $this->setUser($user['id']);
    }


    /**
     * Метод залогинивания пользователя
     * @param integer : $id - id пользователя
     * @param boolean : $remember - запоминать ли пользователя
     * @return boolean
     */

    public function setUser($id, $remember = false) {

        # Получаем пользователя из базы если нужно
        if (!$user = $this->getUser($id)) return false;

        # Ставим новую сессию
        session()->set(array(
            'id' => $user['id'],
            'hash' => $this->hash($user['id'], $user['password'], $user['salt']),
            ));

        # Устанавливаем пользователя
        $this->__user = $user;

        if ($remember)
            $this->saveUser();

        return true;
    }

    /**
     * Метод авторизации пользователя
     * @param string  : $login    - Логин пользователя
     * @param string  : $pass     - Пароль пользователя
     * @param boolean : $remember - запоминать ли пользователя
     * @return boolean
     */

    public function validUser($login, $pass, $remember = false) {
        # Проверяем входные параметры
        $xlogin = db()->e(strtolower(xss_clean($login)));
        $xpass = xss_clean($pass);
        if ($xpass != $pass or !$login or !$pass) return false;

        # Ищем пользователя в базе
        db()->select('id, password, active, salt');
        db()->where(array('login' => $xlogin, 'email' => $xlogin), 'OR');
        $user = db()->get('users', '', true);
        if (!$user || !$user['active']) return false;


        # Проверяем пароль
        if ($user['password'] != $this->hash($user['id'], $xpass, $user['salt'])) return false;        

        # Устанавливаем пользователя
        return $this->setUser($user['id'], $remember);
    }

    /**
    * Метод сохранения авторизации текущего пользователя пользователя
    * @param int : $time - на сколько минут сохранять (по умолчанию : 10080 - 7 дней)
    * @return boolean
    */

    public function saveUser($time = 10080){        
        if (!$this->get('id')) return false;
        $hash = $this->hash($this->get('id'), $this->get('password'), $this->get('salt'));
        return setcookie('user_class_saveUser', base64_encode((time()+($time*60))."|".$this->get('id')."|".$hash), time()+($time*60), '/');
    }

    /**
     * Метод завершения сессии пользователя
     * @redirect /
     */

    public function logout() {
        session()->delete(array('logined','hash'));
        setcookie('user_class_saveUser', '', time()-3600, '/');
    }


    /**
     * Метод получения данных пользователя из базы
     * @param array : $id - id пользователя
     * @return array or false
     */

    public function getUser($id) {
        $id = aint($id);
        if (isset($this->__cache[$id])) return $this->__cache[$id];
        db()->where(array('id' => $id));
        $this->__cache[$id] = db()->get('users', '', true);
        return $this->__cache[$id];        
    }

    /**
     * Метод добавления нового пользователя
     * @param array : $user - параметры нового пользователя
     * @return id or false
     */

    public function addUser($user) {
        if (empty(SYS()->DBTables['users']) || empty($user['email']) || empty($user['password'])) return false;
        if (empty($user['login'])) $user['login'] = $this->generateLogin($user['email']);
        SYS()->library('validation');
        input()->post = array_merge(input()->post, $user);
        $user =validation()->check(SYS()->DBTables['users']);
        if (!$user) return false;
        $user['salt'] = sha1($user['email'] . md5($user['password'] . mt_rand()));
        $user['create'] = time();
        if (!db()->insert('users', $user)) return false;
        $id = db()->insert_id();     
        $this->updateUser($id, array('password' => $user['password']));
        return $id;
    }

    /**
     * Метод обновления данных пользователя
     * @param int : $id - id пользователя
     * @param array : $entry - параметры пользователя
     * @return boolean
     */

    public function updateUser($id, $entry) {
        if (empty($entry) || !is_array($entry)) return false;
		if (!$user = $this->getUser($id)) return false;
        SYS()->library('validation');
        foreach ($entry as $k => $v)
            if (empty(SYS()->DBTables['users'][$k]) || (isset(SYS()->DBTables['users'][$k]['valid']) && !validation()->valid($v, SYS()->DBTables['users'][$k]['valid']))) unset($entry[$k]);		
        if (isset($entry['password'])){
            $old = $entry['password'];
			$entry['password'] = $this->hash($user['id'], $entry['password'], $user['salt']);		
        }
        if (!db()->update('users', $entry, array('id' => $id))) return false;
        $this->__cache[$user['id']] = array_merge($user, $entry);
        return true;
    }

    /**
     * Метод удаления данных пользователя
     * @param int : $id - id пользователя
     * @return boolean
     */

    public function deleteUser($id) {
        $id = abs((int)$id);
        if (!$id) return false;
        return SYS()->db->delete('users', array('id' => $id));
    }

    /**
     * @param string : $param - необходимый параметр для получения ("*" - все параметры)
     * @return string or false
     */

    public function get($param = '*') {
        if (!$this->__user) return false;
        if ($param == '*') return $this->__user;
        return isset($this->__user[$param]) ? $this->__user[$param] : false;
    }

    /**
    * Метод генерирует логин пользователя из e-maila
    * @param string : $email - для генерации логина
    * @return string
    */

    public function generateLogin($email, $n=0){
        $login = explode('@', $email);
        $login = current($login);
        if ($n)
            $login .= $n;
        db()->select('login');
        db()->where(array('login' => $login));
        if (!db()->get('users', '', true)) return $login;
        return $this->generateLogin($email, ++$n);
    }

    /**
     * Функция создания кеша
     * @param integer : $id   - user id
     * @param string  : $pass - user password
     * @param string  : $salt - строка для генерации хэша
     * @return string
     */

    public function hash($id, $pass, $salt) {        
        $hash = md5($id . $pass . $salt);
        return $this->getHash($id, $hash, $salt);
    }

    public function getHash($id, $hash, $salt){
        $iterations = ceil(sqrt($id));
        for ($i = 1; $i <= $iterations{0}; $i++) $hash = md5($hash . BASE_URL . $salt);
        return $hash;
    }
}

function users(){
    return SYS()->library('users');
}