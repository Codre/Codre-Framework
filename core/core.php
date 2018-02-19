<?php
defined('INDEX') or die('Access error');

/**
 * Файл ярдра framework'a
 */
 
/*
* Проверяем деррикторию temp
*/

if (!is_dir(BASE_DIR."temp"))
	mkdir(BASE_DIR."temp", 0775);
if (!is_dir(BASE_DIR."temp".DS."system"))
	mkdir(BASE_DIR."temp".DS."system", 0770);
if (!is_dir(BASE_DIR."temp".DS."system".DS."cache"))
	mkdir(BASE_DIR."temp".DS."system".DS."cache", 0770);
if (!is_dir(BASE_DIR."temp".DS."system".DS."logs"))
	mkdir(BASE_DIR."temp".DS."system".DS."logs", 0770);
if (!is_dir(BASE_DIR."temp".DS."system".DS."update"))
	mkdir(BASE_DIR."temp".DS."system".DS."update", 0770);
if (!is_dir(BASE_DIR."temp".DS."upload"))
	mkdir(BASE_DIR."temp".DS."upload", 0775);
	
if (!is_writable(BASE_DIR."temp")) exit("Set the permissions 775 OR 777 on the folder <b>temp</b>");

/*
 * Определяем адрес сайта
 */ 

$url = '';
$default_port = 80;
if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS']=='on')) {
    $url .= 'https://';
    $default_port = 443;
}else 
    $url .= 'http://';
$url .= $_SERVER['SERVER_NAME'];
if ($_SERVER['SERVER_PORT'] != $default_port)
	$url .= ':'.$_SERVER['SERVER_PORT'];
define('BASE_URL', $url.$config['sub_folder'], true);
unset($url); unset($default_port);
 
/*
* Загрузка глобальных функций
*/

if (file_exists(BASE_DIR . 'core'.DS.'common.php'))
	require_once (BASE_DIR . 'core'.DS.'common.php');
else
	exit('Error: file "common" upload error');


/*
* Загрузка версий
*/

$info = new SimpleXMLElement(file_get_contents(ex('core/versions', 1, 'xml', 'sys')));
if (!$info || empty($info->framework->version))
	SysError("Файл версий не найден.");
foreach ($info as $name=>$obj)
	define('CODRE_'.$name.'_VERSION', $obj->version, true);

/*
* Старт сессии
*/

session_cache_expire($config['sess_time']*60);
ini_set("session.gc_maxlifetime", $config['sess_time']*60);
session_start();

/*
* Объявляем класс ядра
*/

require_once (ex('core/sys.class'));