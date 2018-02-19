<?php
defined('INDEX') or die('acsess error');
/**



 * Класс работы с базой данных 



 */







class db {







    public $_db; // Класс базы данных



    private $_prefix = ""; // Префикс таблиц



    



    private $where = null;



    private $select = "*";



    private $limit = null;



    private $group_by = null;



    private $order_by = null;



    private $join = null;







    private $_saveCache = false;



    private $__cache = array(); // Результаты проверки кэша    



    private $_result; // результат запроса



    private $_data = array(); // ассоциативный массив с полученными данными



    private $cfg = array();







    public $sql = array();







    /**



     * Функция подключения к mysql



     */







    public function __construct($db_cfg = null) {



        if (!$db_cfg)



            $db_cfg = get_config('db');



        $this->_prefix = $db_cfg['prefix'];







        $this->_db = @new mysqli($db_cfg['host'], $db_cfg['user'],



            $db_cfg['pass'], $db_cfg['table']);



        if ($this->_db->connect_errno) {



            debLog('lib_db', $this->_db->connect_errno . " " .$this->_db->connect_error);



            SysError("Подключение к MySQLi невозможно", "Ошибка базы данных");



        }



        $this->__cfg = $db_cfg;



        $this->set_charset();







        # Создаём папку для хранения кэша



        if (!isset($db_cfg['cache']) || $db_cfg['cache'])



            if (!ex('temp/system/cache/db', 0, '', 0))



            	@mkdir('temp'.DS.'system'.DS.'cache'.DS.'db', 0770);



            else



                $this->_saveCache = is_writable(ex('temp/system/cache/db', 0, '', 0));



        return;



   }



   



   public function __clone(){}



    







    /**



     * Экраниерование строки с учётом требований БД



     * @param str:$sql - строка для чистки



     * @return string



     */







    public function e($sql) {



        return $this->_db->real_escape_string($sql);



    }



    



    /**



     * Функция очистки переданных параметров



     * where, limit, join ...



     */



    



    public function clean_param(){



        $this->where = null;



        $this->join = null;



        $this->order_by = null;



        $this->group_by = null;



        $this->limit = null;



        $this->like = null;        



        $this->_data = array();



        $this->select = "*";



        return $this;



    }



    



    /**



     * Функция установки кодировки БД



     * @param $charset - кодировка



     */



    



    public function set_charset($charset = "utf8"){



        $this->_db->query("SET character_set_results='{$charset}'");



        $this->_db->query("SET NAMES '{$charset}'");



    }   



	



	/**



     * Метод записи кэша запроса



     * @param string : $table  - имя таблицы



     * @param string : $query  - запрос



     * @param array  : $result - результат запроса



     * @return boolean 



     */



    



    private function __setCache($table, $query, $result){



        if (!$result || !$this->_saveCache) return false;



        $table = md5($table.join('', $this->__cfg));



        if (!ex('temp/system/cache/db/'.$table, 0, 0, 0))



            @mkdir(BASE_DIR.'temp/system/cache/db/'.$table);        



        return @file_put_contents(BASE_DIR."temp/system/cache/db/{$table}/".md5($query), serialize($result));



    } 



    



    /**



     * Метод проверки существования кэша запроса



     * @param string : $table  - имя таблицы



     * @param string : $query  - запрос



     * @return string



     */



     



    private function __issetCache($table, $query){



        if (!$this->_saveCache) return false;



        $tableDir = md5($table.join('', $this->__cfg));



        $file = ex("temp/system/cache/db/{$tableDir}/".md5($query), 1, 0, 0);    



        if (!$file) return false;



        $db_config = $this->__cfg;



        $inf = $this->query("SHOW TABLE STATUS FROM `{$db_config['table']}` WHERE NAME = '{$this->_prefix}{$table}'", false);         



        $time = filemtime($file);



        if (!$inf or !$time or empty($this->_data[0]['Update_time'])){ $this->delCache($table); return false; }



        $t1 = explode(' ', $this->_data[0]['Update_time']);



        $t1[0] = explode('-', $t1[0]);



        $t1[1] = explode(':', $t1[1]);



        $time2 = mktime($t1[1][0], $t1[1][1], $t1[1][2], $t1[0][1], $t1[0][2], $t1[0][0]);



        if ($time <= $time2){ $this->delCache($table); return false; }



        return $file;



    }



 	



 	/**



 	 * Метод получения данных из кэша



 	 * @param string : $table  - имя таблицы



	 * @param string : $query  - запрос



	 * @return array



	 */



	 



 	private function __getCache($table, $query){



        if (!$this->_saveCache) return false;



 		$file = $this->__issetCache($table, $query);



        if (isset($this->__cache[$file])) return $this->__cache[$file];



 		if ($file){



 			$this->__cache[$file] = unserialize(file_get_contents($file));



            return $this->__cache[$file];



        }



		return false;



 	}



    



    /**



     * Метод удаления кэша таблицы



     * @param string : $table - имя таблицы



     * @return boolean



     */



     



    public function delCache($table){



        $table = md5($table.join('', $this->__cfg)); 



    	$arr = glob(BASE_DIR.DS."temp".DS."system".DS."cache".DS."db".DS.$table.DS."*");



    	if (!$arr)



    		return false;



   		foreach ($arr as $a)



   			@unlink($a);



        @rmdir(BASE_DIR.DS."temp".DS."system".DS."cache".DS."db".DS.$table);



		return true;



    }







    /**



     * Функция выполнения запроса к БД



     * @param str:$sql - запрос



     * @param bool:$clean - очищать ли переданные параметры



     * @return boolean or object



     */



     



    public function query($sql, $clean = true) {        



        $this->free_result();    



        $sql = str_replace('#___', $this->_prefix, $sql);    



        $this->sql[] = $sql;



        if ($clean) 



            $this->clean_param();



        else



            $this->_data = array();











        $this->_result = $this->_db->query($sql);        



        if ($this->_result === false){



            debLog('lib_db', $this->_db->errno . " " .$this->_db->error . " sql: {$sql}");



            return false;



        }       



        if(is_object($this->_result) || is_resource($this->_result)){



            while ($row = $this->_result->fetch_assoc())



                $this->_data[] = $row;



            if (count($this->_data) == 0)



                return false;



            else



                return $this;            



        }elseif($this->_result)



            return $this; 



        else 



            return false;



    }







    /**



    * Функция возвращает результат предыдущего запроса



    * @return mixed



    */







    public function getData(){



        return $this->_data;



    }



    



    /**



     * Получение количества записий в БД при текущих параметрах выборки



     * @param str : $table - имя таблицы



     * @param str : $pref - сокращенное имя таблицы в запросе



     * @param boolean : $clear - Очистить параметры запроса



     * @return int



     */



    



    public function count($table, $pref = "", $clear = false){        



        $select = $this->select;
        $order_by = $this->order_by;



        $this->select = "COUNT(*) num";
        $this->order_by = '';



        $param = $this->get($table, $pref, true, $clear);



        $this->select = $select;
        $this->order_by = $order_by;



        if (!isset($param['num']))



            return 0;



        else



            return $param['num'];



    }



    



    /**



     * Функция создания sql dump`a таблицы



     * Обработка таблицы



     * @param object : $data - записи таблицы



     * @param string : $table - имя таблицы



     * @param integer : $num_filds - колличество ячеек



     * @return string



     */



    



    private function _creat_dump($data, $table, $num_fields){



        $return = "";



        $return.= 'INSERT INTO '.$table.' VALUES(';



        for($j=0; $j<$num_fields; $j++){            



            $row = current($data);



            $row = addslashes($row);



            $row =  str_replace("\n","\\n",$row);



            if (isset($row))



                $return.= '"'.$row.'"' ; 



            else 



                $return.= '""';                 



            if ($j<($num_fields-1))



                $return.= ',';



            next($data);



        }



        $return.= ")\n";



        return $return;



    }



    



    /**



     * Функция возвращения sql dump`a таблыц



     * @param $table - имя таблцы



     * @return string



     */



    



    public function get_dump($table){



        $return = "";



        $this->query("SELECT * FROM {$table}");



        $dump = $this->_data;



        $num_fields = mysqli_num_fields($this->_result);



        $return.= 'DROP TABLE IF EXISTS '.$table;



        $this->query('SHOW CREATE TABLE '.$table);



        $return.= "\n\n".end($this->_data[0])."\n\n";



        foreach ($dump as $d)



            $return .= $this->_creat_dump($d, $table, $num_fields);        



        $return.="\n\n\n";



        return $return;



    }



    



    /**



     * Функция создания резервной копии базы данных



     * @return string - код бэкапа



     */



    



    public function dump(){



        $this->query('SHOW TABLES');



        $return = "";



        foreach ($this->_data as $d)



            $return .= $this->get_dump(current($d));



        return $return; 



    }







    /**



    * Функция проверки целостности базы данных



    * @param mixed : $table - имя таблицы или массив таблиц с параметрами



    * @param array  : $param - параметры полей:



    *                                   `type` - int, bool, text, ddefault: varchar



    *                                   `null` - default: not null



    *                                   `ai` - auto_incriment



    *                                   `default` - default value



    * @return boolean



    */







    public function install($table, $param = null){



        if (is_array($table)){



            foreach ($table as $t => $p)



                $this->install($t, $p);



            return;        



        }elseif (!$param) return false;







        $ex = $this->exists($table);        



        if ($ex)



            $q = "ALTER TABLE  `{$this->_prefix}{$table}` ";



        else



            $q = "CREATE TABLE `{$this->_prefix}{$table}` (";



        $a = '';



        $key = '';



        foreach ($param as $k => $v) {            



            if ($ex && !$this->exists_field($table, $k)){



                if ($a) $a .= ', ';



                $a .= "ADD";



                $a .= $this->normalize_install($k, $v);



            }elseif(!$ex){



                if ($a) $a .= ', ';



                if (!empty($v['ai'])) $key = $k;



                $a .= $this->normalize_install($k, $v);



            }



        }



        if (!$a or !$ex && !$key)



            return false;



        $q .= $a;



        if (!$ex)



            $q .= ", PRIMARY KEY (  `{$key}` ))";        



        return $this->query($q);



    }







    /**



    * Функция преобразования масива параметров в строку для установки



    * @param string : $key   - имя поля



    * @param array  : $value - массив параметров



    * @return string



    */







    private function normalize_install($key, $value){        



        $str = "`{$key}`";



        switch (@$value['type']) {



            case 'int': 



                $lng = !empty($value['length'])?$value['length']:'11';



                $str .= " INT({$lng})"; 



            break;



            case 'bool': $str .= " BOOL"; break;



            case 'text': $str .= " TEXT"; break;



            case 'decimal': 



                $lng = !empty($value['length'])?$value['length']:'2';



                $str .= "DECIMAL(10,{$lng})";



            break;



            case 'varchar': 



                $lng = !empty($value['length'])?$value['length']:'255';



                $str .= " VARCHAR({$lng})";



            break;



            default: $str .= " VARCHAR(255)"; break;



        }







        if (empty($value['null'])) $str .= " NOT NULL";



        if (isset($value['default'])) $str .= " DEFAULT  '{$value['default']}'";



        if (!empty($value['ai'])) $str .= " AUTO_INCREMENT";



        



        return " {$str} ";



    }







    /**



     * Функция получения id последней добавленой записи



     */







    public function insert_id() {



        return $this->_db->insert_id;



    }







    /**



     * Функция добавления where в запрос



     * @param mixed : $where - массив или строка для добавления в запрос



     * если $where является массивом то условием будет "="



     * @param str : $type - оператор котрым будут объединятся условия 



     */







    public function where($where, $type = "AND") {



        if ($type != "AND")



            $type = "OR";



        if (is_array($where)) {



            foreach ($where as $k => $v) {



                $k = explode(".", $k);



                if (count($k) > 1)



                    $k = $k[0] . ".`" . $k[1] . "`";



                else



                    $k = "`" . $k[0] . "`";



                if (!$this->where) {



                    $this->where .= "{$k} = '{$v}'";



                } else {



                    $this->where .= " {$type} {$k} = '{$v}'";



                }



            }



        } else {



            if (!$this->where) {



                $this->where .= $where;



            } else {



                $this->where .= " {$type} {$where}";



            }



        }



        return $this;



    }







    /**



     * Функция установки параметра select



     * @param str:$select - параметр select



     */







    public function select($select) {



        $this->select = $select;



        return $this;



    }







    /**



     * Функция установки параметра limit



     * @param int:$start - начальное значение



     * @param int:$rows - количество выбираемых строк



     */







    public function limit($start, $rows = null) {



        if ($rows)



            $this->limit = "{$start}, {$rows}";



        else



            $this->limit = "{$start}";



        return $this;



    }







     /**



     * Метод генерирует данные для навигации и устанавливает лимит для запроас



     * @param string  : $table - таблица из которой нужно получить список



     * @param string  : $pref  - префикс таблицы (для сложных запросов)



     * @param integer : $limit - по сколько записей выводить



     * @return object



     */







    public function navigation($table, $pref = '', $limit = 20){



        $n = array(



            'page' => aint(input()->get('page'))?aint(input()->get('page')):1,



            'all' => $this->count($table, $pref),



            'limit' => aint($limit)?aint($limit):20,



            'url' => input()->url_string,



        );



        $n['url'] = str_replace(array("page={$n['page']}/", "page={$n['page']}"), '', $n['url']);



        if (strpos($n['url'], '?') !== false && $n['url'][strlen($n['url'])-1] != '&')



            $n['url'] .= "&";



        if (strpos($n['url'], '?') === false && $n['url'][strlen($n['url'])-1] != '/')



            $n['url'] .= '/';



        $n['pages'] = ($n['all']?range(1, ceil($n['all']/$n['limit'])):array());



        tpl('navigation', $n);



        $n['page']--;



        return $this->limit($n['page']*$n['limit'], $n['limit']);



    }











    /**



     * Функция установки параметра join



     * @param str:$type - тип join



     * @param str:$table - таблица для объединения



     * @param str:$on - параметры объединения



     * @param str:$pref - сокращенное имя таблицы в запросе 



     */







    public function join($type, $table, $on = "", $pref = "") {



        if (!$this->join)



            $this->join = " {$type} `{$this->_prefix}{$table}` {$pref}";



        else



            $this->join .= " {$type} `{$this->_prefix}{$table}` {$pref}";



        if ($on)



            $this->join .= " ON ({$on})";



        return $this;



    }







    /**



     * Функция установки параметра group_by



     * @param str:$param - значение group by



     */







    public function group_by($param) {



        $this->group_by = "{$param}";



        return $this;



    }







    /**



     * Функция установки параметра order_by



     * @param str:$param - имя поля по которому сортировать



     * @param bool:$DESC - Вкл/выкл сортировку типа DESC



     */







    public function order_by($param, $DESC = false) {



        $this->order_by = "{$param}";



        if ($DESC)



            $this->order_by .= " DESC";



        return $this;



    }







    /**



     * Функция выборки данных из базы



     * @param str : $table - имя таблицы



     * @param str : $pref - сокращенное имя таблицы в запросе



     * @param bool : $one - вернуть первую строку результата



     * @param bool : $clean - очищать ли параметры после выполнения запроса



     * @return array or false Возвращает ассоцитивный массив



     */







    public function get($table, $pref = "", $one = false, $clean = true) {     







        $sql = "SELECT " . $this->select . " FROM `{$this->_prefix}{$table}` {$pref}";



        if ($this->join)



            $sql .= $this->join;



        if ($this->where)



            $sql .= " WHERE " . $this->where;



        if ($this->group_by)



            $sql .= " GROUP BY " . $this->group_by;



        if ($this->order_by)



            $sql .= " ORDER BY " . $this->order_by;



        if ($this->limit)



            $sql .= " LIMIT " . $this->limit;        



        if ($this->__issetCache($table, $sql)){            



        	$result = $this->__getCache($table, $sql);



       		if ($clean)



        		$this->clean_param();



        }else{            



        	$res = $this->query($sql, $clean);



        	if (!$res)



            	return false;



        	$result = $this->_data;



        	$this->__setCache($table, $sql, $result);		  	



    	}        



        if($one)



            return $result[0];



        else



            return $result;



    }    







    /**



     * Функция удаления записей из базы данных



     * @param str:$table - Название талицы



     * @param mixed:$where - массив или строка параметра where 



     * @return boolean



     */







    public function delete($table, $where = array()) {        



        $this->where($where);



        $sql = "DELETE FROM {$this->_prefix}{$table} WHERE " . $this->where;



        return $this->query($sql);



    }







    /**



     * Функция обновления записей в таблицу



     * @param str:$table - Название талицы



     * @param array:$item - Массив данных для обновления



     * @param mixed:$where - массив или строка параметра where 



     * @return boolean



     */







    public function update($table, $items, $where=array()) {



        $this->where($where);



        $query = '';



        foreach ($items as $k => $v) {



            $query .= "`{$k}` = '{$v}', ";



        }



        $query = substr($query, 0, strlen($query) - 2);



        $sql = "UPDATE `{$this->_prefix}{$table}` SET {$query} WHERE " . $this->where;



        $res = $this->query($sql);



        if ($res)



            $this->delCache($table);



        return $res;



    }







    /**



     * Функция добавления записей в таблицу



     * @param str:$table - Название талицы



     * @param array:$insert - Массив данных для вставки



     * @return boolean



     */







    public function insert($table, $insert) {



        $key = '';



        $val = '';



        foreach ($insert as $k => $v) {



            $key .= "`{$k}`, ";



            $val .= "'{$v}', ";



        }



        $key = substr($key, 0, strlen($key) - 2);



        $val = substr($val, 0, strlen($val) - 2);



        $sql = "INSERT INTO `{$this->_prefix}{$table}` ({$key}) VALUES ({$val})";



        $res = $this->query($sql);



        if ($res)



            $this->delCache($table);



        return $res;



    }







    /**



     * 



     * Функция проверки сеществует ли таблица



     * @param str:$table - имя таблицы



     * @return boolean



     * 



     */







    public function exists($table) {



        return $this->query("SHOW TABLES LIKE  '{$this->_prefix}{$table}'");



    }







    /**



     * 



     * Функция проверки сеществует поле в таблице



     * @param str:$table - имя таблицы



     * @param str:$field - имя поля



     * @return boolean



     * 



     */







    public function exists_field($table, $field) {



        $this->query("SELECT `{$field}` FROM `{$this->_prefix}{$table}` WHERE 0");



        return $this->_result===false?false:true;



    }







    /**



     * Функция проверкм существования записи в таблице



     * @param str:$table - Название таблицы



     * @param str:$field - название поля



     * @param str:$val - значение поля



     * @return boolean



     */







    public function exists_val($table, $field, $val) {



        return $this->query("SELECT `{$field}` FROM `{$this->_prefix}{$table}` WHERE `{$field}` = '{$val}'");



    }







    



    /**



     * Функция очистки результата запроса



     */



    



    public function free_result(){



        if (is_object($this->_result))



            $this->_result->free();



    }



    



    /**



     * Функция закрытия соеденеия с базой данных



     */







    function __destruct() {



        if (!$this->_db)



            return false;



        $this->free_result();



        @$this->_db->close();



        unset($this->db);



        return;       



    }



}











function db(){



    return SYS()->library('db');



}