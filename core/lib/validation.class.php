<?php
defined('INDEX') or die('acsess error');

 /**

  * Класс проверки коректности вводимых данных

 */

 

 class validation{

    

    private $label;

    private $filed;

    private $post;

    private $start_tag = '<div class="validation-error">';

    private $end_tag = "</div>";

    private $valStart_tag = "<p>";

    private $valEnd_tag = "</p>";

    private $process = true;

    private $error = array();





    /**

    * Проверка массива данных

    * @param mixed : $params - параметры проверки, либо имя таблицы в SYS()->DBTables

    * @return array or false

    */



    public function check($params){

        if (is_string($params))

            if (!isset(SYS()->DBTables[$params])) 

                return false;

            else

                $params = SYS()->DBTables[$params];

        $arr = array();

        foreach ($params as $k => $v) {

            if (empty($v['valid']) || empty($v['label'])) continue;

            $arr[$k] = $this->set_valid($k, $v['label'], $v['valid']);           

        }

        if (!$this->result()) return false;

        return empty($arr)?false:$arr;

    }



    /**

     * Текущее состояние проверки

     * @return boolean

     */

    

    public function result(){

        if (!$this->process)

            SYS()->session->set_flashdata('validation_error', $this->get_error());        

        return $this->process;

    }

    

    /**

     * Функция получения текст ошибки

     * @param str:$err - тип ошибки

     * @return string or false

     */

     

    private function _lang($err){        

        $lang = array(

            'maxlen' => "Поле %s содержит слишком много символов",

            'minlen' => "Поле %s содержит слишком мало символов",

            'required' => "Поле %s обязательно для заполнения",

            'uniq_db' => "Текст в поле %s уже содержится в базе",    

            'isset_db' => "Текст в поле %s не содержится в базе", 

            'phone_ru' => "Содержимое поля %s не является российским телефонным номером", 

            'email' => "Содержимое поля %s не является e-mail адресом",   

            'name' => "Содержимое поля %s может состоять только из латинских или руcских букв", 

            'name_ru' => "Содержимое поля %s может состоять только из руcских букв", 

            'name_en' => "Содержимое поля %s может состоять только из латинских букв", 

            'alpha_numeric' => "Содрежимое поля %s может состоять только из цифр и латинских букв",

        );

        return (isset($lang[$err]))?$lang[$err]:false;

    }



    /**

    * Проверка одного значения

    * @param mixed  : $value - значение

    * @param string : $valid - параметры валидации

    * @return boolean

    */



    public function valid($value, $valid){

        $this->post = $value;

        $valid = explode('/', $valid);

        foreach ($valid as $v){

            $param = explode("{", $v);

            if (count($param) > 1)

                $param = substr($param[1], 0, mb_strlen($param[1])-1);

            else

                $param = "";            

            $v = preg_replace("/{.+}/", '', $v);

            if (!$this->{"_".$v}($param)){

                $this->_set_error($v); 

                return false;

            }

        }

        return true;

    }



    /**

    * Получить текущее обрабатываемое значение

    * @return string

    */



    public function value(){

        return $this->post;

    }



    /**

     * Установка параметров для проверки

     * @param str:$filed - имя поля формы

     * @param str:$label - Описания поля для вывода в ошибки

     * @param str:$valid - правила валидации

     * @return string or false

     */     

     

    public function set_valid($filed, $label, $valid){

        $this->label = $label;

        $this->filed = $filed;

        if (!$this->valid(SYS()->input->post($filed), $valid))

            $this->process = false;

        $_POST[$filed] = SYS()->input->post[$filed] = $this->post;

        SYS()->template->assign('validation_'.$filed, $this->post, true);

        if ($this->process)

            return $this->post;

        else

            return false;

    }

    

    /**

     * Функция установки оформительных тегов сообщиния об ошибки

     * @param str:$start - начальный тег блока сообщения

     * @param str:$end - конечный тег блока собщения

     * @param str:$valStart - начальный тег каждой строки сообщения

     * @param str:$valEnd - конечный тег каждой строки сообщения

     */

     

    public function set_tags($start = '<div class="validation-error">', $end = '</div>', $valStart = "<p>", $valEnd = "</p>"){

        $this->start_tag = $start;

        $this->end_tag =$end;

        $this->valStart_tag = $valStart;

        $this->valEnd_tag = $valEnd;

    }

    

    /**

     * Функция добавления своей ошибки валидации

     * @param str:$filed - имя ошибки 

     * @param str:$text - текст ошибки

     */

    

    public function set_error($filed, $text){

        $this->process = false;

        $this->error[$filed] = $text;  

    }

    

    /**

     * Функция получения текста ошибки

     * @param str:$filed - получить текст определённого поля

     * @param bool:$clear - очистить сессию ошибок

     * @return string

     */

    

    public function get_error($filed = null, $clear = false){

        if (!$this->error && $filed) $res = '';

        elseif (!$this->error && session()->keep_flashdata('validation_error'))

            $res = session()->flashdata('validation_error');

        elseif (!$this->error)

            return '';

        elseif ($filed){

            $res = isset($this->error[$filed])?$this->error[$filed]:'';                    

        }else{

            $error = $this->start_tag;            

            foreach($this->error as $err){

                $error .= $this->valStart_tag.$err.$this->valEnd_tag;

            }

            $error .= $this->end_tag;

            $res = $error;

        }  

        if ($clear)

            session()->flashdata('validation_error');

        return $res;        

    }





    /**

    * Функция применения произвольной функции (php)

    * @param strin : $param - название функции 

    */



    private function __($param){    

        if (function_exists($param))

            $this->post = $param($this->post);        

        return true;

    }





    /**

    * Функция подкготовки html кода

    */



    private function _html(){

        $this->post = SYS()->security->text_clean($this->post);

        return true;

    }







    /**

    * Функция очистка данных для БД

    * @param strin : $param - название функции

    */



    private function _db_clean(){

        $this->post = SYS()->security->db_clean($this->post);

        return true;

    }

    

    /**

     * Функция проверки максимальной длинны строки

     * @param string : $param[0] - Разрешённая длинна

     * @param boolean : $param[1] - обрезать ли строку

     * @return boolean

     */

    

    private function _maxlen($param){

        $param = explode("|", $param);

        if (isset($param[1]) && $param[1] == "true" && mb_strlen($this->post)>$param[0])

            $this->post = substr($this->post, 0, $param[0]-1);        

        return mb_strlen($this->post)<=$param[0]?true:false;

    } 

    

    /**

     * Функция проверки минимальной длинны строки

     * @param string : $param - минимальная длинна

     * @return boolean

     */

    

    private function _minlen($param){

        return mb_strlen($this->post)>=$param?true:false;

    } 

    

    /**

     * Функция проверки валидности e-mail адреса

     * @return boolean

     */

    

    private function _email(){

        return preg_match("/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{1,12}$/ix", $this->post);

    }  

    

    /**

     * Функция проверки состоит ли поле только из русских буков

     * @return boolean

     */

     

    private function _name_ru(){

        return (preg_match("/^[".chr(0x7F)."-".chr(0xff)."\s_\-]+$/", $this->post))?TRUE:FALSE;       

    }

    

    

    /**

     * Функция проверки состоит ли поле только из латинских букв

     * @return boolean

     */

     

    private function _name_en(){

        return (!preg_match("/[^A-Za-z0-9\s_\-]/", $this->post))?TRUE:FALSE;        

    }

    

    /**

     * Функция проверки состоит ли поле только из латинских или русских буков

     * @return boolean

     */

     

    private function _name(){

        return (preg_match("/^([A-ZА-Яa-яa-z0-9\s_\-])+$/i", $this->post))?TRUE:FALSE;        

    }

    

    /**

     * Функция проверки на наличе только английских букв и цифр

     * @return boolean

     */

     

     private function _alpha_numeric(){

        return ( ! preg_match("/^([a-z0-9])+$/i", $this->post)) ? FALSE : TRUE;

     }

    

    

    /**

     * Функция проверки на уникальность записи в базе данных

     * @param str: $param - Имя таблицы|поля|текущее значение в md5

     * @return boolean

     */

    

    private function _uniq_db($param){

        if (!$this->post) return true;

        $param = explode('|', $param);

        if (empty($param[0]) || empty($param[1]) || 

                empty(SYS()->DBTables[$param[0]][$param[1]]))

            return false;

        if (!empty($param[2])){

            if ($param[2] ==  md5($this->post))

                return true;            

        }

        SYS()->db->select($param[1]);

        SYS()->db->where(array($param[1] => $this->post));

        return SYS()->db->get($param[0], '', true)?false:true;

    }  



    /**

     * Функция проверки существует ли переменная в базе данных

     * @param str: $param - Имя таблицы|поле

     * @return boolean

     */



    private function _isset_db($param){

        $param = explode('|', $param);

        if (empty($param[0]) || empty($param[1]) || 

                empty(SYS()->DBTables[$param[0]][$param[1]]))

            return false;

        SYS()->db->select($param[1]);

        SYS()->db->where(array($param[1] => $this->post));

        return SYS()->db->get($param[0], '', true)?true:false;

    }

    

    /**

     * Функция проверки поля на заполненость

     * @return boolean

     */

    

    private function _required(){

        return (bool)trim($this->post);

    }   

    



    /**

     * Функция проверки валидность телефонного номера

     * @return boolean

     */

     

     private function _phone_ru(){

        if (!$this->post) return true;

        return ( ! preg_match("/^((8|\+7)[\- ]?)?(\(?\d{3}\)?[\- ]?)?[\d\- ]{7,10}$/i", $this->post)) ? FALSE : TRUE;

     }





    

    /**

     * Функция установки ошибки

     * @param str:$v - имя типа ошибки

     */

     

    private function _set_error($v){

        $label = explode('|', $this->label);

        if (count($label)>1){

            $str = $this->_lang($v);

            foreach($label as $l){

                $str = printf($str, $l);

            }

            $this->error[] = $str;

        }else{

            ob_start();

                printf($this->_lang($v), $this->label); 

            $this->error[$this->filed] =  ob_get_clean();

        }        

    }

    

}



function validation(){

    return SYS()->library('validation');

}