<?php

/**
 * Стартовая страница движка framework's
 */

/*
* Константа для проверки запущен ли код через index файл
* константа хранящая время старта framework 
* константа сокращение сепаратор директории
* константа корневой директории
*/

define("INDEX", true);
define("CODRE_START", microtime());
define('DS', DIRECTORY_SEPARATOR); 
define("BASE_DIR", dirname(__FILE__).DS, true);

/*
* Загрузка настроек CMS
*/

if (file_exists(BASE_DIR.'core/config.php')) 
    require_once (BASE_DIR.'core/config.php');
else 
    exit("Error: not find file config!");


/*
* Устанавливаем уровень вывода ошибок
*/

if ($config['development'])
    error_reporting(E_ALL);
else
    error_reporting(0);
ini_set('display_errors', (int)$config['development']);


/*
* Устанавливаем кодировку
*/

header("Content-type: text/html; charset=" . $config['charset']);
mb_internal_encoding($config['charset']);

/*
* Запуск ядра системы
*/

if (file_exists(BASE_DIR.'core'.DS.'core.php'))
    require_once (BASE_DIR.'core'.DS.'core.php');
else
    exit("Error: core upload error!");

/*
* Загружаем приложение
*/

if (defined("CORE_MODE")) exit();

require_once (ex("application/start"));
SYS()->application = new application();
if (method_exists(SYS()->application, 'start'))
	SYS()->application->start();
exit();
