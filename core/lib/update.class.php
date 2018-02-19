<?php
defined('INDEX') or die('acsess error');
/**
 * Класс для обновления cmf
 */

class update {

	const tmp_ERROR = "lib_update"; // имя log файла
	const server = "http://server.codre.ru/"; // Сервер с обновлениями

	private $info = false; // информация о продукте
	private $type = 'framework'; // тип системы
	private $history = array(); // История
	private $links = array(); // ссылки на обновления


	private $status = array( //статусы сервера
		200 => 'Обновление не требуется',
  		201 => 'Передан список обновлений',
   		401 => 'Ошибка доступа (не все параметры переданны)',
  		500 => 'Неизвестная ошибка (сообщите в тех поддержку)',
	);

	public function __construct($type = 'framework') {
		$this->type = $type;
		if (!$file = ex('core/versions', 1, 'xml', false))
			return set_log(self::tmp_ERROR, 'Файл с версиями не найден', __line__, __file__);
		$xml = file_get_contents($file);
		$this->info = new SimpleXMLElement($xml);
		if (empty($this->info) || empty($this->info->{$type}->version)){
			$this->info = false;
			return set_log(self::tmp_ERROR, 'Файл версий продукта повреждён', __line__, __file__);
		}
		$this->info = $this->info->{$type};
		$this->history[] = 'Файл версий загружен';
		return true;
	}

	/**
	* Полчуить расшифровку статуса и запись в историю
	* @param integer : $cod - Код статуса
	* @return string
	*/ 

	private function setStatus($cod){
		if (empty($this->status[$cod])) return false;
		$this->history[] = $this->status[$cod];
		return $this->status[$cod];
	}

	/**
	* Получить историю
	* @param boolean : $all - Всё историю (true) или последнее сообщение (false)
	* @return mixed
	*/

	public function getHistory($all = true){
		if (empty($this->history)) return false;
		return $all?$this->history:end($this->history);
	}

	/**
	* Провека обновлений
	* @return boolean
	*/

	public function check(){
		if (!$this->info) return false;		
		$req = array('type' => $this->type);
		list($req['c'], $req['m'], $req['f']) = explode('.', $this->info->version);
		$request = self::server."?status=1000&params=".base64_encode(serialize($req));
		$this->history[] = "Запрос отправлен";
	    $status = @json_decode(file_get_contents(($request)));
	    if (empty($status) or empty($status->cod)){
	    	$this->history[] = 'Нет ответа';
	    	return false;
	    }
	    $this->setStatus($status->cod);
	    if (isset($status->links))
	    	$this->links = $status->links;
	    return ($status->cod == 201);
	}

	/** 
	* Обновление системы
	* @return boolean
	*/

	public function start(){
		if (empty($this->links)) return false;	
		if (!$folder = ex('temp/system/update/', 0, 0, false)){
			set_log(self::tmp_ERROR, 'Папка для распаковки обновлений не найдена', __line__, __file__);
			return false;
		}
		if (!is_writable($folder)){
			set_log(self::tmp_ERROR, 'Папка для распаковки обновлений не доступна для записи', __line__, __file__);
			return false;
		}
		foreach ($this->links as $ver=>$l){
			if (!is_dir($folder.DS.$ver))
				mkdir($folder.DS.$ver);
			file_put_contents($folder.DS.$ver.DS.'update.zip', file_get_contents($l.'update'));
			file_put_contents($folder.DS.$ver.DS.'inf.xml', file_get_contents($l.'inf'));
		}
	    return $this->install();
	}

	public function checkWritable($dirs = array('core', 'temp'), $pre = '/'){
		$status = true;
		if ($pre == '/')
			if (!is_writable(BASE_DIR.'/index.php')){
				$status = false;
				$this->history[] = "Ошибка: Файл /index.php не доступен для записи!";
			}		
		foreach ($dirs as $k => $dir) {
			$list = scandir(BASE_DIR.$pre.$dir.'/');
			foreach ($list as $l){
				if (in_array($l, array('.', '..'))) continue;
				if (is_dir(BASE_DIR.$pre.$dir.'/'.$l.'/')) 
					if (!$this->checkWritable(array($l), $pre.$dir."/"))
						$status = false;
				if (!is_writable(BASE_DIR.$pre.$dir.'/'.$l)){
					$status = false;
					$this->history[] = "Ошибка: Файл ".$pre.$dir.'/'.$l." не доступен для записи!";
				}
			}
		}
		return $status;
	}

	/** 
	* Установка обновлений
	* @return boolean
	*/

	public function install(){
		if (!$this->info) return false;
		if (!$folder = ex('temp/system/update/', 0, 0, false)){
			set_log(self::tmp_ERROR, 'Папка для распаковки обновлений не найдена', __line__, __file__);
			return false;
		}
		if (!$this->checkWritable()){
			set_log(self::tmp_ERROR, 'Нет доступа к системным файлам', __line__, __file__);			
			return false;
		}
		$dirs = scandir($folder);
		$dir = array();
		foreach ($dirs as $f) {
			if ($f == '.' || $f == '..' || !is_dir($folder.$f)) continue;
			$dir[$f] = $f;
		}
		natcasesort($dir);
		$zip = new ZipArchive;
		foreach ($dir as $f) {			
			$this->history[] = "Установка версии {$this->type} {$f}";
			$xml = new SimpleXMLElement(file_get_contents($folder.$f."/inf.xml"));
			if ($zip->open($folder.$f."/update.zip") !== TRUE) {
				$this->history[] = "Не удалось открыть архив с обновлением {$this->type} {$f}";
				return false;
			}
			if (!empty($xml->remove->file)){
				foreach ($xml->remove->file as $r){
					$this->history[] = "Файл {$r} удалён";
					@unlink(BASE_DIR.$r);
				}
			}
			if (!empty($xml->remove->folder)){
				foreach ($xml->remove->folder as $r){
					$this->history[] = "Каталог {$r} удалён";
					rrmdir(BASE_DIR.$r);
				}
			}			
			$zip->extractTo(BASE_DIR);
			$zip->close();
			rrmdir($folder.$f);

			$file = ex('core/versions', 1, 'xml', 0);
			$vxml =  new SimpleXMLElement(file_get_contents($file));
	 		$vxml->{$this->type}->version = str_replace('_', '.', $f);
	 		file_put_contents($file, $vxml->asXML());

			$this->history[] = "Обновление до {$this->type} {$f} установлено";
			if (!empty($xml->reboot)){
				$this->history[] = 'Требуется перезагрузка скрипта';
				return false;
			}
		}
		$this->history[] = 'Все обновления установлены';
		return true;
	}

}