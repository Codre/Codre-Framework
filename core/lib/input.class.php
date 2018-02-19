<?php
defined('INDEX') or die('acsess error');
/**
 * Класс обработки входящих данных
 */
 
class input{
    
    public $get = array(); // массив get переменных
    public $post = array(); // массив post переменных
    public $uri; // массив uri сегментов
    public $uri_string; // строка без get
    public $url_string; // строка с get
    

    /**
     * Функция обработки строки get данных
     */  
     
    public function __construct(){
        $this->url_string = $_SERVER['REQUEST_URI'];
        $this->post = $_POST;
        $this->uri_string = (strpos($this->url_string, '?')!==false?substr($this->url_string, 0, strpos($this->url_string, '?')):$this->url_string);

        $params = explode('/', $this->uri_string);
        foreach ($params as $param) {
            if (!$param) continue;
            $gg = explode('=', $param, 2);
            if (count($gg) == 2)
                $this->get[$gg[0]] = $gg[1];
            else     
                $this->uri[] = xss_clean(trim($gg[0]));            
        }   
        $this->get = array_merge($this->get, $_GET);
    }

    /**
     * 
     * Функция получения get данных
     * @param string  : $var - имя get переменной
     * @param boolean : $xss - фильтр данных
     * @return string
     */
    
    public function get($var, $xss=false){  
        if (isset($this->get[$var])){
            $get = $this->get[$var];
            if ($xss || get_config('xss_get'))
                $get = xss_clean($get);
            if ($get){
                $this->get[$var] = $get;
                return $get;
            }else{
                $this->get[$var] = null;
                return false;
            }                
        }else
            return false;        
    }
    
    /**
     * 
     * Функция получения post данных
     * @param string  : $var - имя post переменной
     * @param boolean : $xss - фильтр данных
     * @return string 
     */
    
    public function post($var, $xss = false){
        if ($xss && isset($this->post[$var]))
            $this->post[$var]= xss_clean($this->post[$var]);
        return isset($this->post[$var])?$this->post[$var]:false;
    }
    
    /**
     * 
     * Функция получения post или get данных
     * @param string  : $var - имя переменной
     * @param boolean : $xss - фильтр данных
     * @return string 
     */
    
    public function request($var, $xss = false){
        return $this->post($var)?$this->post($var, $xss):$this->get($var, $xss);
    }
    
    /**
     * Функция получения реального ip пользователя
     * @return string
     */
    
    public function GetIp(){
        if (!empty($_SERVER['HTTP_CLIENT_IP'])){
            $ip=$_SERVER['HTTP_CLIENT_IP'];
        }elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
            $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
        }else{
            $ip=$_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    
    /**
     * 
     * Функция получения Server данных
     * @param  string : $var - имя server переменной
     * @return string
     * 
     */
    
    public function server($var){
        if (isset($_SERVER[strtoupper($var)]))
            return $_SERVER[strtoupper($var)];
        elseif (isset($_SERVER[$var]))
            return $_SERVER[$var];
        return false;
    } 
        
    /**
     * 
     * Функция проверки существование сегмента uri
     * @param string : $var - сегмент
     * @return boolean
     *  
     */
    
    public function uri($var){
        if (!is_array($this->uri)) return false;
        return array_search($var, $this->uri)!==false?true:false;
    }

    /**
    * Функция получение сегмента uri
    * @param integer : $num - порядковый номер элемента в url (начало с 0)
    * @return string or false
    */

    public function GetUri($num){
        if (!is_array($this->uri)) return false;
        return !empty($this->uri[aint($num)])?$this->uri[aint($num)]:false;
    }
}

function input(){
    return SYS()->library('input');
}