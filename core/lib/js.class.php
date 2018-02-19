<?php
defined('INDEX') or die('acsess error');
/**
 * Библиотека работы с JavaScripts
 */

class js{


    public function __construct(){
        $this->set_script("var base = '".get_config('sub_folder')."/';", false);
    }
        
    /**
     * Функция загрузки js файлов
     * @param string  : $file    - путь к файлу js
     * @param boolean : $scripts - Устанавливать скрипт в head(FALSE) или scripts(TRUE)
     * @param string  : $type    - тип скрипта
     * @return boolean
     */
    
    public function load($file, $scripts = true, $type="text/javascript"){
        if (file_exists(BASE_DIR.current(explode('?', $file)))){
            $cod = '<script type="'.$type.'" src="'.get_config('sub_folder').'/'.$file.'"></script>';
            if ($scripts)
                SYS()->template->set_scripts($cod);
            else
                SYS()->template->set_headers($cod);
            return true;
        }
        return false;
    }

    /**
    * Метод подключает скрипт js/system/common.js
    * @return boolean
    */

    public function common(){
        static $isset = false;
        if (!$isset)
            $isset = $this->load('js/system/common.js?'.CODRE_FRAMEWORK_VERSION); 
        return $isset;
    }

    /**
    * Метод подключает скрипты из папки js/system/plugins/
    * @param mixed : $name - строка или массив имён плагинов из папки js/system/plugins/, без расширения .js
    * @return array - история результатов подключения скриптов или резльтат подключения, если передана строка
    */

    public function plugin($name){
        static $isset = array();

        if (is_array($name)) 
            foreach($name as $n)
                $this->plugin($n);
        elseif (empty($isset[$name])){
            $isset[$name] = $this->load('js/system/plugins/'.$name.'.js?'.CODRE_FRAMEWORK_VERSION); 
            return $isset[$name];        
        }
        return $isset;
    }

    /**
    * Метод подключает скрипты из папки js/application/
    * @param mixed : $name - строка или массив имён плагинов из папки js/application/, без расширения .js
    * @return array - история результатов подключения скриптов или резльтат подключения, если передана строка
    */

    public function application($name){
        static $isset = array();

        if (is_array($name)) 
            foreach($name as $n)
                $this->application($n);
        elseif (empty($isset[$name])){
            $time = file_exists(BASE_DIR.'js/application/'.$name.'.js')?filemtime(BASE_DIR.'js/application/'.$name.'.js'):1;
            $isset[$name] = $this->load('js/application/'.$name.'.js?'.(get_config('development')?time():$time)); 
            return $isset[$name];        
        }
        return $isset;
    }

    
    /**
     * Функция установки скрипта в код шаблона
     * @param string  : $cod     - js код
     * @param boolean : $scripts - Устанавливать скрипт в head или scripts
     * @param string  : $type    - тип скрипта
     * @return boolean
     */
    
    public function set_script($cod, $scripts=true, $type="text/javascript"){
        if (!$cod)
            return false;
        $js = '<script type="'.$type.'">'; 
        $js .= $cod;
        $js .= '</script>';
        if ($scripts)
            SYS()->template->set_scripts($js);
        else
            SYS()->template->set_headers($js);
        return true;
    }
    
}
?>