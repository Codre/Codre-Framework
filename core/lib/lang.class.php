<?php
defined('INDEX') or die('acsess error');
/**
 * Класс методов языков
 */

class lang{

	public function __construct(){}

	/**
	* Функция склонения фразы в зависимости от числа
	* @param integer : $number - число для склонения
	* @param string  : $var1   - сколение если один предмет
	* @param string  : $var2_4 - склонение если от двух до четырёх предметов
	* @param string  : $var5_0 - сколнение если пять предметов
	* @return string
	**/

	public function Sklon($number, $var1, $var2_4, $var5_0) {
		$number .= "";
		$last1 = substr($number, -1)-0;
		$last2 = substr($number, -2)-0;
		if ($last1 == 0 || $last1 >= 5 || ($last2 >= 10 && $last2 <= 19))
			return $var5_0;
		elseif ($last1 >=2 && $last1 <= 4)
			return $var2_4;
		elseif ($last1 == 1)
			return $var1;
		else
			return $var5_0;
	}

	/**
	 * Функция транслита
	 * @param string : $string - строка для транслита
	 * @return string
	 */

	public function RuToEn($string){
	    $iso9_table = array(
	            'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G', 'Ѓ' => 'G`',
	            'Ґ' => 'G`', 'Д' => 'D', 'Е' => 'E', 'Ё' => 'YO', 'Є' => 'YE',
	            'Ж' => 'ZH', 'З' => 'Z', 'Ѕ' => 'Z', 'И' => 'I', 'Й' => 'Y',
	            'Ј' => 'J', 'І' => 'I', 'Ї' => 'YI', 'К' => 'K', 'Ќ' => 'K',
	            'Л' => 'L', 'Љ' => 'L', 'М' => 'M', 'Н' => 'N', 'Њ' => 'N',
	            'О' => 'O', 'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T',
	            'У' => 'U', 'Ў' => 'U', 'Ф' => 'F', 'Х' => 'H', 'Ц' => 'TS',
	            'Ч' => 'CH', 'Џ' => 'DH', 'Ш' => 'SH', 'Щ' => 'SHH', 'Ъ' => '``',
	            'Ы' => 'YI', 'Ь' => '`', 'Э' => 'E`', 'Ю' => 'YU', 'Я' => 'YA',
	            'а' => 'a', 'б' => 'b', 'в' => 'v', 'г' => 'g', 'ѓ' => 'g',
	            'ґ' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo', 'є' => 'ye',
	            'ж' => 'zh', 'з' => 'z', 'ѕ' => 'z', 'и' => 'i', 'й' => 'y',
	            'ј' => 'j', 'і' => 'i', 'ї' => 'yi', 'к' => 'k', 'ќ' => 'k',
	            'л' => 'l', 'љ' => 'l', 'м' => 'm', 'н' => 'n', 'њ' => 'n',
	            'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's', 'т' => 't',
	            'у' => 'u', 'ў' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'ts',
	            'ч' => 'ch', 'џ' => 'dh', 'ш' => 'sh', 'щ' => 'shh', 'ь' => '',
	            'ы' => 'yi', 'ъ' => "'", 'э' => 'e`', 'ю' => 'yu', 'я' => 'ya', ' '=> '_'
	    );	     
	    $string = strtr($string, $iso9_table);
	    $string = preg_replace("/[^A-Za-z0-9`'_\-\.]/", '-', $string);    
	    return $string;
	}

	/**
	* Метод перевода месяца на русский язык
	* @param $input - строка с названием месяца на английском (пример date('F'))
	* @param $rod   - Выводить в родительном падеже
	* @param $upone - Перевести в верхний регистр первый символ
	* @return string
	*/

	public function MonthName($input, $rod = false, $upone = false) {
		$new = strtolower($input);
		$search = array (
				"january",	"february",	"march",
				"april",		"may",			"june",
				"july",			"august",		"september",
				"october",	"november",	"december"
				);

		$replace1 = array (
				"январь",		"февраль",	"март",
				"апрель",		"май",			"июнь",
				"июль",			"август",		"сентябрь",
				"октябрь",	"ноябрь",		"декабрь"
				);

		$replace2 = array (
				"января",		"февраля",	"марта",
				"апреля",		"мая",			"июня",
				"июля",			"августа",	"сентября",
				"октября",	"ноября",		"декабря"
				);


		if ($rod) $replace = $replace2;
			else $replace = $replace1;

		$res = str_replace($search, $replace, $new);
		if ($upone)
			$res[0] = strtoupper($res[0]);		

		return $res;
	}

	/**
	* Метод перевода дня недели на русский язык
	* @param $input - строка с названием дня на английском (пример date('l'))
	* @return string
	*/

	public function ruDayName($input){
		$search = array(
			'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'
		);

		$replace = array(
			'Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье'
		);

		return str_replace($search, $replace, $input);
	}


	/**
 	* Возвращает сумму прописью
 	* @author runcore
 	* @uses morph(...)
 	*/
	
	public function num2str($num, $cop = true) {
		$nul='ноль';
		$ten=array(
			array('','один','два','три','четыре','пять','шесть','семь', 'восемь','девять'),
			array('','одна','две','три','четыре','пять','шесть','семь', 'восемь','девять'),
		);
		$a20=array('десять','одиннадцать','двенадцать','тринадцать','четырнадцать' ,'пятнадцать','шестнадцать','семнадцать','восемнадцать','девятнадцать');
		$tens=array(2=>'двадцать','тридцать','сорок','пятьдесят','шестьдесят','семьдесят' ,'восемьдесят','девяносто');
		$hundred=array('','сто','двести','триста','четыреста','пятьсот','шестьсот', 'семьсот','восемьсот','девятьсот');
		$unit=array( // Units
			array('копейка' ,'копейки' ,'копеек',	 1),
			array('рубль'   ,'рубля'   ,'рублей'    ,0),
			array('тысяча'  ,'тысячи'  ,'тысяч'     ,1),
			array('миллион' ,'миллиона','миллионов' ,0),
			array('миллиард','милиарда','миллиардов',0),
		);
		//
		list($rub) = explode('.',sprintf("%015.2f", floatval($num)));
		$kop = round(($num-$rub)*100);
		$out = array();
		if (intval($rub)>0) {
			foreach(str_split($rub,3) as $uk=>$v) { // by 3 symbols
				if (!intval($v)) continue;
				$uk = sizeof($unit)-$uk-1; // unit key
				$gender = $unit[$uk][3];
				list($i1,$i2,$i3) = array_map('intval',str_split($v,1));
				// mega-logic
				$out[] = $hundred[$i1]; # 1xx-9xx
				if ($i2>1) $out[]= $tens[$i2].' '.$ten[$gender][$i3]; # 20-99
				else $out[]= $i2>0 ? $a20[$i3] : $ten[$gender][$i3]; # 10-19 | 1-9
				// units without rub & kop
				if ($uk>1) $out[]= $this->morph($v,$unit[$uk][0],$unit[$uk][1],$unit[$uk][2]);
			} //foreach
		}
		else $out[] = $nul;
		if (!$cop)
			return trim(preg_replace('/ {2,}/', ' ', join(' ',$out)));
		$out[] = $this->morph(intval($rub), $unit[1][0],$unit[1][1],$unit[1][2]); // rub
		$out[] = $kop.' '.$this->morph($kop,$unit[0][0],$unit[0][1],$unit[0][2]); // kop
		return trim(preg_replace('/ {2,}/', ' ', join(' ',$out)));
	}

	/**
	 * Склоняем словоформу
	 * @author runcore
	 */
	public function morph($n, $f1, $f2, $f5) {
		$n = abs(intval($n)) % 100;
		if ($n>10 && $n<20) return $f5;
		$n = $n % 10;
		if ($n>1 && $n<5) return $f2;
		if ($n==1) return $f1;
		return $f5;
	}

}