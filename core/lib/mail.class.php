<?php

defined('INDEX') or die('acsess error');

/**
 * Класс отправки e-mail сообщений
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once 'phpmailer/src/Exception.php';
require_once 'phpmailer/src/PHPMailer.php';
require_once 'phpmailer/src/SMTP.php';

class mail{	

	private $__lib;
	public $charset = 'utf-8';
	public $fromName = 'noreply';
	public $fromEmail = 'noreply@domain.name';
	private $html_translation_table = array('Ў'=>'&iexcl;','ў'=>'&cent;','Ј'=>'&pound;','¤'=>'&curren;','Ґ'=>'&yen;','¦'=>'&brvbar;','§'=>'&sect;','©'=>'&copy;','Є'=>'&ordf;','«'=>'&laquo;','“'=>'&ldquo;','”'=>'&rdquo;','„'=>'&bdquo;','‘'=>'&lsquo;','’'=>'&rsquo;','¬'=>'&not;','­'=>'&shy;','®'=>'&reg;','Ї'=>'&macr;','°'=>'&deg;','±'=>'&plusmn;','І'=>'&sup2;','і'=>'&sup3;','ґ'=>'&acute;','µ'=>'&micro;','¶'=>'&para;','·'=>'&middot;','є'=>'&ordm;','»'=>'&raquo;','ј'=>'&frac14;','Ѕ'=>'&frac12;','ѕ'=>'&frac34;','ї'=>'&iquest;','"'=>'&quot;','\''=>'&#39;','<'=>'&lt;','>'=>'&gt;','&'=>'&amp;','—'=>'&mdash;','–'=>'&ndash;');
	private $html_translation_table_utf8 = array('РЋ'=>'&iexcl;','Сћ'=>'&cent;','Р€'=>'&pound;','В¤'=>'&curren;','Тђ'=>'&yen;','В¦'=>'&brvbar;','В§'=>'&sect;','В©'=>'&copy;','Р„'=>'&ordf;','В«'=>'&laquo;','вЂњ'=>'&ldquo;','вЂќ'=>'&rdquo;','вЂћ'=>'&bdquo;','вЂ'=>'&lsquo;','вЂ™'=>'&rsquo;','В¬'=>'&not;','В­'=>'&shy;','В®'=>'&reg;','Р‡'=>'&macr;','В°'=>'&deg;','В±'=>'&plusmn;','Р†'=>'&sup2;','С–'=>'&sup3;','Т‘'=>'&acute;','Вµ'=>'&micro;','В¶'=>'&para;','В·'=>'&middot;','С”'=>'&ordm;','В»'=>'&raquo;','С'=>'&frac14;','Р…'=>'&frac12;','С•'=>'&frac34;','С—'=>'&iquest;','"'=>'&quot;','\''=>'&#39;','<'=>'&lt;','>'=>'&gt;','&'=>'&amp;','вЂ”'=>'&mdash;','вЂ“'=>'&ndash;');


	public function __construct(){
		$this->fromEmail = 'noreply@'.str_replace(array('http://', 'https://'), '', BASE_URL);
		return $this->init();		
	}	

	public function init(){
		$this->__lib = new phpmailer();
		$this->__lib->CharSet = $this->charset;
		$this->__lib->From = $this->fromEmail;
		$this->__lib->FromName = $this->fromName;
		return $this->__lib;
	}

	public function lib(){
		return $this->__lib;
	}

	public function send($email, $subj, $message, $html = true) {
		$lib = $this->init();
		$lib->Subject = $subj;
		$lib->Body = $message;
		$lib->IsHTML($html);
		if (is_array($email))
			foreach ($email as $e)
				$lib->addAddress($e);
		else
			$lib->addAddress($email);
		return $lib->send();
	}

}