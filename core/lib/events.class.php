<?php
defined('INDEX') or die('acsess error');

/**
 * Класс работы с событиями
 */
 
class events{
    
    private $events = array();
    
    public function __construct(){}
    public function __clone(){}

    /**
    * Вызов события 
    * @param string : $event  - название события
    * @param array  : $params - Список параметров передаваемый в функции
    * @return array - список результатов выполнения функций
    */
    
    final public function load($event, $params = array()){
        if (empty($this->events[$event])) return false;
        $return = array();
        foreach ($this->events[$event] as $ev){
            switch ($ev['type']){ 
                case 'func':
                    $params['return_data'] = $return;
                    $return[$ev['name']] = call_user_func_array($ev['name'], $params); 
                break;
                
                case 'class':                    
                    if (!method_exists($ev['name'], $ev['method'])) continue;
                    $params['return_data'] = $return;
                    $return[$ev['name'].".".$ev['method']] = call_user_func_array(array(new $ev['name'](), $ev['method']), $params);
                break;
            
                default : continue;
            }
        }
		
		return $return;
    }
    
    /**
     * Установка функции в качестве события
     * @param string : $func - имя функции
     * @param string : $name - имя события (по умолчанию будет присвоено имя функции)
     * @return boolean
     */ 
     
    final public function setFunc($func, $name = ''){
        if (!function_exists($func)) return false;
        if (!$name) $name = $func;
       if (!isset($this->events[$name]))
            $this->events[$name] = array(array('name' => $func, 'type' => 'func'));
        else
            $this->events[$name][] = array('name' => $func, 'type' => 'func');
        return true;
    } 
    
    /**
     * Установка методов класса в качестве событий
     * @param string : $class  - имя класса
     * @param strimg : $method - имя метода (если не передоно то будут загружены все методы класса)
     * @param string : $name   - имя события (по умолчанию будет присвоено имя метода)
     * @return boolean
     */ 
    
    final public function setClass($class, $method = null, $name = null){
        if (!class_exists($class)) return false;
        if (!$method){
            foreach (get_class_methods($class) as $m)
                $this->setClass($class, $m, $name);
            return true;
        }
        if (!$name) $name = $method;
        if (!isset($this->events[$name]))
            $this->events[$name] = array(array('name' => $class, 'method' => $method, 'type' => 'class'));
        else
            $this->events[$name][] = array('name' => $class, 'method' => $method, 'type' => 'class');
        return true;
    }    
}

$_SERVER['CL_EVENTS'] = new events(); 

/**
 * Функция для быстрого вызова события
 * @param string : $event  - название события
 * @param mixed  : $params - параметры передоваемые в событие
 * Если не передан $event вернёт объект класса events 
 */
function events($event = null, $params = array()){
    if (!$event)
        return $_SERVER['CL_EVENTS'];
    else
        return events()->load($event, $params);    
}

# Загружаем события из папки application/events
if ($d = ex('application/events/', 0, '', 0)){
    $f = glob($d."*.events.php");
    foreach ($f as $v)
        require_once $v;
}