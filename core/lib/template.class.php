<?php
defined('INDEX') or die('acsess error');
/**
 * Классс шаблонизатора
 */
 
define('SMARTY_DIR', BASE_DIR.DS."core".DS."lib".DS."smarty".DS);
require_once(SMARTY_DIR.'Smarty.class.php');

class template extends Smarty {        
        
    public function __construct(){
      	parent::__construct();    	
      	
      	if (!is_dir(BASE_DIR."temp".DS."system".DS."cache".DS."smarty"))
  			    mkdir(BASE_DIR."temp".DS."system".DS."cache".DS."smarty", 0770);
    		if (!is_dir(BASE_DIR."temp".DS."system".DS."smarty_c"))
    		    mkdir(BASE_DIR."temp".DS."system".DS."smarty_c", 0770);	
    				
    		$this->setCompileDir(BASE_DIR."temp".DS."system".DS."smarty_c");
    		$this->setConfigDir(SMARTY_DIR."configs".DS);
    		$this->setCacheDir(BASE_DIR."temp".DS."system".DS."cache".DS."smarty".DS);       
    		$this->addPluginsDir(SMARTY_DIR.'usrplugins');
    		$this->set_folder();
    		$this->assignByRef('SYS', SYS());
    		$this->assign(array(
      			'base' => get_config('sub_folder').'/',
      			'base_url' => BASE_URL,
      			'charset' => get_config('charset'),			
    		));
    }   
     
     /**
      * Функция установки параметров head
      * @param string:$headers - параметр head
      */
      
     public function set_headers($string){
       	$tabs = '';
       	if ($this->getTemplateVars('headers'))
         		$tabs = "\n\t";
        $this->assign('headers', $this->getTemplateVars('headers').$tabs.$string);
     }
     
     /**
      * Функция установки параметров scripts
      * @param string : $string - параметр scripts
      */
      
     public function set_scripts($string){
       	$tabs = '';
       	if ($this->getTemplateVars('scripts'))
       	  	$tabs = "\n";
        $this->assign('scripts', $this->getTemplateVars('scripts').$tabs.$string);
     }
	 
	  /**
      * Функция установки папки шаблона
      * @param string : $folder - имя папки
      * @return boolean
      */
      
    public function set_folder($folder = ''){
    		if($path = ex('template/'.$folder, false, '', false, false)){
      			$this->setTemplateDir($path);
      			$this->assign('base_template', get_config('sub_folder')."/template/".$folder.'/');
      			return true;
    		}
        set_log('lib_template', 'Папка с шаблонами "'.$folder.'" не найдена', __LINE__, __FILE__);
		    return false;
	  }   

	  /**
      * Функция установки тега link в header
      * @param string : $href  - путь к файлу
      * @param strign : $type  - MIME тип подключаемого файла
      * @param string : $rel   - параметр атрибута rel
      * @param string : $media - параметр атрибута media
      */
      
    public function set_link($href, $rel = '', $type = "text/css", $media = 'all'){
       	if (ex(current(explode('?', $href)), 1, 0, 0, 0))
     	    	$this->set_headers('<link href="'.get_config('sub_folder').'/'.$href.'" type="'.$type.'" rel="'.$rel.'" media="'.$media.'" />');
    }
	
  	/**
  	 * Функция загрузки шаблона
  	 * @param string : $template - имя шаблона  
  	 * @return HTML
  	 */
	 
   	public function parser($template = "template"){
   		 return $this->fetch($template.".html");
   	} 
}

function tpl(){
    return SYS()->library('template');
}