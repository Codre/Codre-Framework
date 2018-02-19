<?php
defined('INDEX') or die('acsess error');
/**
 * Класс работы с сессией
 */
class session{
    
    public function __construct(){
        $this->set('class_session_test', 'test', 1);
        if ($this->get('class_session_test') !== 'test'){
            set_log('lib_session', 'Не удалось загрузить/сохранить сессию', __line__, __file__);
            SysError('Ошибка работы сессии, возможно в вашем браузере отлючены cookie!');
            return false;
        }
        $this->delete('class_session_test');
        if ($this->get('class_session_test') == 'test'){
            set_log('lib_session', 'Не удалось удалить сессию', __line__, __file__);
            SysError('Ошибка работы сессии!');
            return false;
        }          
    }
    
    /**
     * Функция получения данных куки
     * @param str:$name - имя куки
     * @return string or false
     */
    
    public function get($name){
        if ($name && isset($_SESSION[$name]))
            return $_SESSION[$name];
        else
            return false;
    }
    
    /**
     * Функция установки куки
     * @param mixed:$data - массив с данными для добавления в куки или строка названия куки
     * @param string:$value - строка данных добавляемых в куку
     */
    
    public function set($data = array(), $value = null){
        if (is_array($data)){
            foreach($data as $k=>$v)
                $this->set($k, $v);            
        }elseif($data && $value)
            $_SESSION[$data] = $value;         
    }
    
    /**
     * Функция удаления куки
     * @param mixed:$name - имя удаляемой(ых) кук(и)
     */
    
    public function delete($name){
        if (is_array($name)){
            foreach ($name as $n){
                $this->delete($n);
            }
            return;
        }
        unset($_SESSION[$name]);
    }

    /**
    * Функция установки временной куки
    * @param mixed:$data - массив с данными для добавления в куки или строка названия куки
    * @param string:$value - строка данных добавляемых в куку
    */

    public function set_flashdata($data = array(), $value = null) {
        if (is_array($data)){
            foreach($data as $k=>$v)
                $this->set_flashdata($k, $v);            
        }elseif($data && $value)
            $this->set("flash_".$data, $value);          
    }

    /**
    * Функция получения времменной куки
    * @param string : $name - имя временной куки
    * @return string or false
    */

    public function flashdata($name){
        $name = "flash_".$name;
        if ($str = $this->get($name)){
            $this->delete($name);
            return $str;
        }
        return false;
    }

    /**
    * Функция получения времменной куки с сохранением её
    * @param string : $name - имя временной куки
    * @return string or false
    */

    public function keep_flashdata($name){
        $name = "flash_".$name;
        if ($str = $this->get($name))
            return $str;    
        return false;    
    }
}

function session(){
    return SYS()->library('session');
}