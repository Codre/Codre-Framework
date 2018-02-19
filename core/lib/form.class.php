<?php

defined('INDEX') or die('acsess error');



/**

 * Класс для построения и обработки форм

 */



class form{



	public function __construct(){}

    public function __clone(){}



    /**

    * Метод обрабатывает дополнительные параметры типа поля

    * @param array : $field - параметры поля

    * @return array

    */



    public function checkTypeParams($field){

        if (!empty($field['format'])){

            $format = explode('|', $field['format']);

            foreach ($format as $f){

                switch ($f) {

                    case 'json':

                        $field['value'] = @json_decode(html_entity_decode($field['value']), true);

                    break;

                    case 'serialize':

                        $field['value'] = @unserialize($field['value']);

                    break;

                }

            }

        }



        if (!strpos($field['type'], '{'))

            return $field;



        $params = explode('{', $field['type']);

        $params = substr($params[1], 0, strlen($params[1])-1);

        $params = explode('|', $params);

        $field['type'] = preg_replace("/{.+}/", '', $field['type']);



        switch ($field['type']) {

            case 'dblist':

                if (count($params) < 3) 

                    return false;

                $field['listType'] = (isset($params[3])?$params[3]:'select');

                if (isset($params[4]))

                    db()->where($params[4]);

                $field['listItems'] = db()->select("`{$params[1]}`, `{$params[2]}`")->get($params[0]);

                if (!$field['listItems'])

                    return false;

            break;



            case 'list':

                if (!is_array($params)) 

                    return false;

                $field['listType'] = (isset($params[1])?$params[1]:'select');

                $temp = explode(';', $params[0]);

                if (!$temp || !is_array($temp)) 

                    return false;

                $field['listItems'] = array();

                foreach ($temp as $t){

                    $tt = explode(':', $t);

                    if (!$tt || !is_array($tt)) continue;

                    $field['listItems'][$tt[0]] = $tt[1];

                }

                if (!$field['listItems'])

                    return false;

            break;

            

            default:

            break;

        }

        return $field;

    }



    /**

    * Метод для создания поля по пользовательскому шаблону

    * @param array  : $field - параметры поля

    * @param string : $attr - дополнительные атрибудты

    * @param string : $value - значение

    * @param string : $tpl - путь к шаблону в папке template/ (по умолчанию template/lib/form/$type)

    * @return string

    */



    public function get_field($field, $tpl = ''){

        if ($field === false) 

            return false;

        if (!$tpl)

            $tpl = "lib/form/{$field['type']}";

        $tplPath = ex("template/".$tpl, 1, 'html', 0, 0);

        if (!$tplPath)

            return $this->getDefault_field($field);



        return tpl('formParams', $field)->parser($tpl);

    }



    /**

    * Метод для создания поля по шаблону по умолчанию

    * @param array  : $field - параметры поля

    * @param string : $attr - дополнительные атрибудты

    * @param string : $value - значение

    * @return string

    */



    public function getDefault_field($field, $attr = '', $value = ''){

        $path = ex('core/lib/form/', false, '', false, false);

        if(!$path){

            debLog('lib_from', 'Папка с шаблонами не найдена');

            return '';

        }

        if ($field === false) return false;

        $file = ex('core/lib/form/'.$field['type'], 1, 'html', 0, 0);

        if (!$file) return '';

        $tplDir = tpl()->getTemplateDir();

        tpl()->setTemplateDir($path);

        $html = tpl('formParams', $field)->parser($field['type']);

        tpl()->setTemplateDir($tplDir);

        return $html;

    }





    /**

    * Метод создания формы

    * @param mixed  : $params - массив параметров или имя таблицы из SYS()->DBTables

    * @param mixed  : $attr   - массив атрибутов для каждого типа или строка - атрибут для всех типов

    * @param string : $id     - id редактируемой записи

    * @param mixed  : $folder - каталог с шаблонами полей

    * @return string

    */



    public function build($params, $attr = '', $id = 0, $folder = ''){

        $table = '';

    	if (is_string($params))

    		if (isset(SYS()->DBTables[$params])){

    			$table = $params;

    			if ($id)

    				$entry = db()->where(array('id' => $id))->get($table, '', 1);

    			$params = SYS()->DBTables[$params];

    		}else

    			return '';

    	$allAttr = '';

    	if (is_array($attr) && isset($attr['all']))

    		$allAttr = $attr['all'];

    	elseif (is_string($attr))

    		$allAttr = $attr;



    	$html = '';

    	foreach ($params as $k=>$p){           

            $data = $this->genField(($table?$table:$p), $k, (isset($attr[$k])?$attr[$k]:$allAttr), $id);

            if (!$data)

                continue;

    		$html .= $this->get_field($data, ($folder?$folder.$p['form']:''));

    	}

    	return $html;

    }



    /**

    * Метод создания поля

    * @param mixed  : $params - массив параметров или имя таблицы из SYS()->DBTables

    * @param string : $attr   - атрибуты для поля

    * @param string : $id     - id редактируемой записи

    * @param mixed  : $folder - каталог с шаблонам поля

    * @return string

    */





    public function buildField($params, $field, $attr = '', $id = 0, $folder = ''){

        $data = $this->genField($params, $field, $attr, $id);

        if (!$data)

            return '';

        return $this->get_field($data, ($folder?$folder.$data['type']:''));

    }



    /**

    * Метод генерирует массив информации для генерации поля

    * @param mixed  : $params - массив параметров или имя таблицы из SYS()->DBTables

    * @param string : $field  - имя поля

    * @param string : $attr   - атрибуты для поля

    * @param string : $id     - id редактируемой записи

    * @return array

    */



    public function genField($params, $field, $attr = '', $id = 0){

        $table = '';

        if (is_string($params))

            if (isset(SYS()->DBTables[$params][$field])){

                $table = $params;

                if ($id)

                    $value = @current(db()->select("`{$field}`")->where(array('id' => $id))->get($table, '', 1));

                $params = SYS()->DBTables[$params][$field];

            }else

                return;

            if (!isset($params['form']) || !isset($params['label']) || !isset($params['valid']))

                return;



            if (isset($params['valid']) && strpos($params['valid'], 'required') !== false)

                $attr .= ' required';

            if (isset($params['placeholder']))

                $attr .= ' placeholder="'.$params['placeholder'].'"';

            if (!isset($value))

                $value = '';



            return $this->checkTypeParams(array(

                'table' => $table,

                'type' => $params['form'],

                'label' => $params['label'],

                'name' => $field,

                'id' => $id,

                'attr' => $attr,

                'value' => $value,

                'multiple' => !empty($params['multiple']),

                'required' => (isset($params['valid']) && strpos($params['valid'], 'required') !== false),

                'placeholder' => (isset($params['placeholder'])?$params['placeholder']:''),

                'format' => (isset($params['format'])?$params['format']:'')

            ));



    }





    /**

    * Метод добавления/обновления записи с валидацией формы

    * @param string  : $table - имя таблицы

    * @param integer : $id    - id записи

    * @return boolean

    */



    public function save($table, $id = 0){

    	if (!isset(SYS()->DBTables[$table]))

    		return false;

        $id = aint($id);

        if (!input()->post)

            return null;

        $images = array();

        $tParams = SYS()->DBTables[$table];

        foreach (SYS()->DBTables[$table] as $k=>$v){

            if (!isset($v['form']) || !isset($v['label']) || !isset($v['valid'])){

                unset($tParams[$k]);

                continue;

            }

            switch ($v['form']) {

                case 'image':

                    input()->post[$k] = SYS()->library('file')->input($k, $table, array('jpg', 'jpeg', 'gif', 'png'), $table, $id);  

                    if (input()->post[$k])

                        $images[] = input()->post[$k];

                    else

                        unset($tParams[$k]);

                break;

                case 'date':

                    input()->post[$k] = @strtotime(input()->post[$k]);

                break;

            }

            if (!empty($v['format'])){

                $format = explode('|', $v['format']);

                foreach ($format as $f){

                    switch ($f) {

                        case 'json':

                             input()->post[$k] = @json_encode(input()->post[$k]);

                        break;
                        case 'serialize':
                             input()->post[$k] = @serialize(input()->post[$k]);
                        break;

                    }

                }

            }  

            if (!empty($v['multiple']) && is_array(input()->post[$k]))

                input()->post[$k] = join(',', input()->post[$k]);         

             

        }

        $data = SYS()->library('validation')->check($tParams);

        if (!validation()->result()){

            if ($images)

                foreach ($images as $i)

                    @unlink(BASE_DIR."temp/upload/".$table."/".$i);

            return false;

        }

        if ($id)

            return db()->update($table, $data, array('id' => $id));        

        return db()->insert($table, $data);

    }



}