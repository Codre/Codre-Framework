<?php
defined('INDEX') or die('Ошибка доступа');

/**
 * Класс ярдра freamwork'a
 */
 
 class Sys {

    public $config; // настройки приложения и ядра

    public function __construct(){}
    public function __clone(){}

    public function load() {       

        $this->config = $GLOBALS['config']; // Добавляем настройки приложения в ядро
        $this->DBTables = array(); // Создаём переменную для хранения списка баз данных

        /*
        * Добавляем в ядро основные библиотеки и авто загружаемые библиотеки
        */
        $autoload = $this->config['autoload_lib']; // получаем список загружаемых классов из настроек
        $autoload['security'] = 'class'; // класс проверки безопасности данных
        $autoload['input'] = 'class'; //класс обработки входящих данных        
        
        /*
        *  Загружаем автозагружаемые библиотеки
        */
        
        foreach($autoload as $name=>$type)
            $this->library($name, $type);        
        unset($autoload);

        /* Включение стандартных объектов */
        $this->vars =  new stdClass();
    }
    
    /**
     * Функция загрузки библиотек
     * @param string : $lib_name - имя библиотеки
     * @param string : $lib_type - тип библиотеки
     * @param mixed : $param    - Дополнительные параметры для переадчи в класс
     * @return object
     */
    
    public function library($lib_name, $lib_type = 'class', $param = ""){
        if (class_exists($lib_name) && $lib_type == 'class'){
            return SYS()->{$lib_name};
        }
        require_once(ex("core/lib/{$lib_name}.{$lib_type}"));
        if ($lib_type == 'class'){  
            $this->{$lib_name} = new $lib_name($param);
            return $this->{$lib_name};
        }    
    }
}
$_SERVER['SYS'] = new Sys();

/**
 * Функция обращения к ядру
 */
 
function &SYS(){
    return $_SERVER['SYS'];
}

SYS()->load();