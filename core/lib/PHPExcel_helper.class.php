<?php
defined('INDEX') or die('acsess error');
/**
 * Класс-помошник для работы Phpexcel
 */

class PHPExcel_helper {

	private $__xls = null;
	private $__file = null; 
	public $maxCell = 0;

  	public function __construct() {
  		SYS()->library('PHPExcel');
  		ini_set('memory_limit', '256M');
		ini_set('max_execution_time', 0);
		set_time_limit(0);
		ignore_user_abort(true);
  		$cacheMethod = PHPExcel_CachedObjectStorageFactory:: cache_to_phpTemp;
		$cacheSettings = array( 'memoryCacheSize' => '8MB');
		PHPExcel_Settings::setCacheStorageMethod($cacheMethod, $cacheSettings);
  		PHPExcel_Settings::setLocale("ru");
  	}

	public function load($file) {	
		$this->__file = $file;
		$objReader = PHPExcel_IOFactory::createReader(PHPExcel_IOFactory::identify($this->__file));
		$objReader->setReadDataOnly(true);
		$this->__xls = $objReader->load($file);
		return $this;
	}

	public function getData($sheet = 0) {
		$this->__xls->setActiveSheetIndex($sheet);
		$aSheet = $this->__xls->getActiveSheet();
		$data = array();
		foreach($aSheet->getRowIterator() as $row){
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false);			
			$item=array();
			$kempty = 0;
			$count = 0;
		    foreach($cellIterator as $cell){
		    	$v = $cell->getCalculatedValue();
		    	$count++;
		    	if (!$v)
					$kempty++;
				
				array_push($item, $v);
		    } 
		    if ($count-$kempty < 4)
		    	continue;
		    if ($count > $this->maxCell)
		    	$this->maxCell = $count;
		    array_push($data, $item);
		}
		return $data;
	}

	public function disconect(){
		$this->__xls->disconnectWorksheets();
   		unset($this->__xls);
	}

}