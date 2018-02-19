<?php
defined('INDEX') or die('acsess error');
/**
 * Файл глобальных функций
 */

/**
* Функция проверки существования файлов
* @param str:$file - имя файла без расширения или папки 
* @param bool: $is_file - проверять файл(TRUE) или папка (FALSE)
* @param str:$ext - расширение файла (если пустое то без расширения)
* @param bool:$setErr - eсли 'sys' после вывода ошибки скрипт будет оствновлен, 
*                       если true будет вывод ошибки без остановки скрипта
*                       если false вернёть true или false без вывода ошибки
* @return mixed возвращает путь или false
*/
    
function ex($name, $is_file = true, $ext = "php", $setErr=true){
    if (DS != '/') $name = str_replace('/', DS, $name);
    $ext = (!$ext || !$is_file)?'':'.'.$ext;
    $name .= $ext;
    $path = BASE_DIR.DS.$name;
    if ($is_file && !is_file($path)){
    	if ((String)$setErr == 'sys')
        	SysError('Файл '.$name.' не найден'); // выводим системную ошибку
         elseif ($setErr)
         	echo GetError('Файл '.$name.' не найден');  // выводим ошибку             
     	return false; // возвращаем false                        
    }elseif (!$is_file && !is_dir($path)){
    	if ((String)$setErr == 'sys')
        	SysError('Каталог '.$name.' не найден!'); // выводим системную ошибку
     	elseif ($setErr)
        	echo GetError('Каталог '.$name.' не найден!'); // выводим ошибку 
      	return false; // возвращаем false      
    }
    return $path; // возвращаем путь
}

/**
 * Функция записи текста в лог файл
 * @param string : $name - имя лог файла
 * @param string : $text - Текст для добавления в лог файл
 * @param integer: $line - номер строки
 * @param string : $file - имя файла
 * @return boolean
 */
 
function set_log($name, $text, $line, $file){
    $path = BASE_DIR."temp".DS."system".DS."logs".DS.$name.".log";
    $text .= " Строка: {$line} Файл: {$file} Дата: ".date("m.d.Y H:i:s")."|\n";
    $fp = @fopen($path, 'a');
    if (!$fp)
        return FALSE;
    flock($fp, LOCK_EX);        
    fwrite($fp, $text);
    flock($fp, LOCK_UN);
    fclose($fp); 
    return true; 
}

/**
 * Функция записи текста в лог файл
 * @param string : $name - имя лог файла
 * @param string : $text - Текст для добавления в лог файл
 * @param integer: $limit - Максимальная длинна лог файла (строк)
 * @return boolean
 */
 
function debLog($name, $text, $limit = 10000){   
    $path = BASE_DIR."temp".DS."system".DS."logs".DS.$name.".log";
    $file = array();
    if (file_exists($path))
        $file = file($path);


    $file[] = $text." Дата: ".date("m.d.Y H:i:s")."|\n"; 
    $deb = @debug_backtrace();
    if ($deb)
        foreach ($deb as $v)
            $file[] = "\t {$v['file']}:{$v['line']}\n";

    while (count($file) > $limit)
        array_shift($file);

    return file_put_contents($path, join('', $file));
}

/**
 * Функция установки статуса
 * @param string : $status - статус
 * @return boolean
 */

function HeaderStatus($status){
	if (headers_sent())
		return false;
			
	 $http_status = array(
		200 => 'OK',
		301 => 'Moved Permanently',
		302 => 'Found',
		303 => 'See Other',
		401 => 'Unauthorized',
		403 => 'Forbidden',
		404 => 'Not Found',
		500 => 'Internal Server Error',
		502 => 'Bad Gateway',
		504 => 'Gateway Timeout',
	);
	
	if (isset($http_status[$status]))
		$status = $status.' '.$http_status[$status];	
	$protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : "HTTP/1.1";
	if (substr(php_sapi_name(), 0, 3) == 'cgi') {
		header('Status: '.$status, true);
	} else {
		header($protocol.' '.$status);
	}
	return true;
}

/**
* Функция вывода системной ошибки
* @param str:$title - заголовок ошибки
* @param str:$text - текст ошибки
* @param string :$status - текст ошибки
*/
     
function SysError($text, $title="Системная ошибка", $status = '500'){
	if ($status)
		HeaderStatus($status);
    if (file_exists(BASE_DIR."template/errors/syserror.php")){
        include BASE_DIR."template/errors/syserror.php";
    }else
        echo "<b>{$title}</b>: {$text}";
    exit();
}


/**
* Функция вывода ошибки без остановки скрипта
* @param str:$text - Текст обшибки
* @param str:$title - заголовок ошибки
*/
    
function GetError($text, $title="Ошибка"){
     if (file_exists(BASE_DIR."template/errors/geterror.php")){
        include BASE_DIR."template/errors/geterror.php";
    }else
        echo "<b>{$title}</b>: {$text}";
}

/**
 * Функция получения конфигураций
 * @return boolean or var
 */
 
function get_config($name){
    global $config;
    if (isset($config[$name]))
        return $config[$name];
    else
        return false;
}

/**
 * Функция получения url адреса данной страницы
 * @return string
 */
 
function this_url(){
    return BASE_URL.$_SERVER['REQUEST_URI'];
}

/**
 * Функция обновления страницы
 * @param int:$time - время через которое необходимо обновить страницу
 * @param str:$url - адрес на котрый необходимо произвести переадресацию после истечения времени обновления 
 */
 
function refresh($time = 0, $url = null){
    HeaderStatus(301);
    if ($url)
        header("Refresh: {$time}; url=".BASE_URL.$url);
    else
        header("Refresh: {$time}");
    return;
}

/**
* xss фильтрация с вырезанием тегов
* @param str:$input - строка для фильтрации
* @return string
*/
    
function xss_clean($input){
    $input = htmlentities($input, ENT_QUOTES, get_config('charset'));   
    if(get_magic_quotes_gpc ())        
        $input = stripslashes ($input);        
    $input = strip_tags($input);    
    $input=str_replace ("\n"," ", $input);
    $input=str_replace ("\r","", $input);   
    return $input;
}

/**
 * Функция переадресации
 * @param str:$url - адрес для переадресации
 * если адрес отсутствует то обновляет текущую страницу
 */
 
function redirect($url=null){
    if ($url == 'referer')
        header("Location: ".(input()->server('http_referer')?input()->server('http_referer'):BASE_URL));
    elseif ($url && (strpos($url, 'http://') !== false || strpos($url, 'https://') !== false))
        header("Location: ".$url);
    elseif ($url)
        header("Location: ".BASE_URL.$url);
    else
        header("Location: ".this_url());
    exit();
}

/**
 * Функция нормализации microtime
 * @param значение microtime()
 * @return float
 */
 
function getmicrotime($mic = null){
    if ($mic == null)
        $mic = microtime();
    list($usec, $sec) = explode(" ", $mic); 
    return ((float)$usec + (float)$sec); 
} 

/**
 * Функция проверяет выполняется ли скрипт ajax'сом
 * @return boolean
 */

function isAjax(){
	if (isset($_REQUEST['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') || isset($_SERVER['HTTP_AJAX']))
		return true;
	else
		return false;
}

/**
 * Функция возвращения json ответа на ajax
 * @param mixed : $data   - параметр для команды json_encode
 * @param mixed : $return - если скрипт выполнен без ajax то функция вернёт это значение
 */ 
 
function ajaxMessage($data, $return = false){
	if (!isAjax())
		return $return;
	if (!headers_sent())
		header('Content-type: application/json');
	exit(json_encode($data));		
}


/**
* Преобразовывает в положительное число, и проверяет существует ли переданная строка
* @param string : $str - Строка для преобразования
* @return integer or false
*/

function aint($str){
    if (empty($str)) return 0;
    return abs((int)$str);
}

/**
* Функция рекурсивного удаления дериктории
* @param string  : $dir   - путь к дериктории
* @param boolean : $clear - удалять исходный каталог или очистить 
*/

function rrmdir($dir, $clear = false) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object); else unlink($dir."/".$object);
            }
        }
        reset($objects);
        if (!$clear)
            rmdir($dir);
    }
}


/**
* Функция вывода информации в js console
* @param mixed  : $var  - переменная для вывода
* @param string : $type - тип вывода 'php' - будет передан результат работы функции var_export или 'json' (по умолчанию) - переменная будет преобразованная в json
*/

function console($var, $type = 'json'){
    if ($type == 'php'){
        echo '<script type="text/javascript">console.log('.json_encode(var_export($var, true)).')</script>';
        return;
    }
    echo '<script type="text/javascript">console.log('.json_encode($var).')</script>';
}

/**
* Функция ставит ключами элементов массива значение ключевого параметра
* @param array  : $array - массив для обработки
* @param string : $key   - Ключь, значение которого будет присвоено
* @return array
*/

function distinct($array, $key = null){
    if (!is_array($array) || !$array) return null;
    if (!$key) $key = key(current($array));
    $result = array();
    foreach ($array as $v)
        $result[$v[$key]] = $v;
    return $result;        
}