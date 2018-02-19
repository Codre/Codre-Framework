<?php
defined('INDEX') or die('acsess error');

/**
 * Класс работы с файлами
 */

class file{
       
    /**
     * Функция записи данных в файл
     * @param string :$path - путь к файлу
     * @param string :$data - строка для записи в файл
     * @param string :$mode - модификатор для фала(подробнее в инструкции по fopen)
     * @return boolean
     */
 
    public function write($path, $data, $mode = 'a+'){
        $path = BASE_DIR."/".$path;
        if (!$fp = @fopen($path, $mode)){
            debLog('lib_file', "Не удалось открыть файл {$path}");
            return FALSE;
        }
        flock($fp, LOCK_EX);
        fwrite($fp, $data);
        flock($fp, LOCK_UN);
        fclose($fp);
        return TRUE;
    }
    
    /**
     * Функция загрузки содержимого файла
     * @param string : $path - путь к файлу
     * @return string
     */
     
     public function get_content($path){
        $path = BASE_DIR.$path;
        if (!file_exists($path)){
            debLog('lib_file', "Не удалось найти файл {$path}");
            return false;
        }
        return file_get_contents($path);
     }   
     
     /**
     * Функция получения информации о файле
     * @param string : $path - путь к файлу
     * @param maxid  : $info - массив или строка разделённая ',' получаемых данных
     * Возможные варианты:
     * 'name' - имя файла
     * 'size' - размер файла
     * 'date' - время последнего изменения файла
     * 'readable' - доступен ли файл для чтения
     * 'writable' - доступен ли файл для записи
     * 'fileperms' - права на файл
     * @return string
     */
     
     public function get_info($path, $info = array('name', 'size', 'date')){
        $file = BASE_DIR.$path;
        if (!file_exists($file)){
            debLog('lib_file', "Не удалось найти файл {$file}");
            return FALSE;
        }
        if (is_string($info))
            $info = explode(',', $info);
        foreach ($info as $key)
        {
            switch ($key)
            {
                case 'name':
                    $fileinfo['name'] = substr(strrchr($file, "/"), 1);
                    break;
                case 'size':
                    $size = (filesize($file)/1024);
                    if ($size>1024)
                        $size = round($size/1024, 1)." мб";
                    else
                        $size = round($size, 2)." кб";
                    $fileinfo['size'] = $size;
                    break;
                    break;
                case 'date':
                    $fileinfo['date'] = filemtime($file);
                    break;
                case 'readable':
                    $fileinfo['readable'] = is_readable($file);
                    break;
                case 'writable':
                    $fileinfo['writable'] = is_writable($file);
                    break;
                case 'fileperms':
                    $fileinfo['fileperms'] = fileperms($file);
                    break;
            }
        }
        return $fileinfo;
    }

    /**
    * Нормалисует массив типа Files для передачи в функции
    * @param array : $files - массив вида $_FILES
    * @return array
    */

    public function normalizeFiles($files){
        $return = array();
        foreach ($files as $k => $v) {
            $key = 0;
            $p = $v;
            if (is_array($v))
                foreach ($v as $key => $p)
                    $return[$key][$k] = $p;
            else
                $return[$key][$k] = $v;
        }
        return $return;
    }

    /**
     * Функция загрузки файлов на сервер
     * @param array  : $files    - массив параметров для загрузки на сервер
     * @param string : $dir      - дерриктория в которую загружать файл
     * @param array  : $ext      - массив разрешённых расширений
     * @param string : $filename - имя файла для сохранения (будет добавляться порядковый номер)
     * @return array
     */

    public function loadFiles($files, $dir="", $ext=array(), $filename = false){
        $return = array();
        foreach ($files as $k => $v) {
            if ($v['type'] == 'link')
                $return[$k] = $this->loadFileUrl($v['url'], $dir, $ext, ($filename?$filename.$k:false));
            elseif ($v['error']){
                debLog('lib_file', 'Не удалось загрузить файл "'.$v['name'].'": '.$this->__codeToMessage($v['error']));
                continue;
            }else
                $return[$k] = $this->loadFile($v, $dir, $ext, ($filename?$filename.$k:false));
        }
        return $return;
    }
     
    /**
     * Функция загрузки файлов на сервер
     * @param array   : $file     - массив параметров для загрузки на сервер
     * @param string  : $dir      - дерриктория в которую загружать файл
     * @param array   : $ext      - массив разрешённых расширений
     * @param string  : $filename - имя файла для сохранения
     * @param integer : $num      - счётчик для уникального названия (ставиться автоматически)
     * @return string
     */
    
    public function loadFile($file, $dir="", $ext=array(), $filename = false, $num = 0){   
        if ($file['error']){
            debLog('lib_file', 'Не удалось загрузить файл "'.$file['name'].'": '.$this->__codeToMessage($file['error']));      
            return false;
        }
        $tt = explode('.', $file['name']);

        $file_ext = trim(strtolower(end($tt)));         

        if ($ext){            

            if (array_search($file_ext, $ext) === false){

                debLog('lib_file', 'Не верное расширение файла "'.$file['name'].'"');

                return false;

            }

        }



        SYS()->library('lang');

        if (!$filename)

            $name = str_replace(".".$file_ext, '', $file['name']).($num?"-{$num}":'');

        else

            $name = $filename.($num?"-{$num}":'');  

        if (ex($dir.SYS()->lang->RuToEn($name), 1, $file_ext, 0))

            return $this->loadFile($file, $dir, $ext, $filename, ++$num);

        $name .= ".{$file_ext}";

              

        if (move_uploaded_file($file['tmp_name'], BASE_DIR.$dir.$name)) 

            return $name;

        else{

            debLog('lib_file', "Не удалось загрузить файл {$file['name']} в {$dir}{$name}");

            return false;  

        }     

    }



    /**

        * Функция загрузки файла на сервер по ссылке

        * @param array  : $url      - ссылка на файл

        * @param string : $temp     - дерриктория в которую загружать файл

        * @param array  : $ext      - массив разрешённых расширений

        * @param string : $filename - имя файла для сохранения

        * @param integer : $num      - счётчик для уникального названия (ставиться автоматически)

        * @return string

     */



    public function loadFileUrl($url, $dir="", $ext=array(), $filename = false,  $num = 0){    

        $tt = explode('.', $url);

        $file_ext = trim(strtolower(end($tt)));         

        if (!$file_ext || strlen($file_ext) > 5){

            debLog('lib_file', 'Не верное расширение файла "'.$url.'"');

            return false;

        }

        if ($ext){            

            if (array_search($file_ext, $ext) === false){

                debLog('lib_file', 'Не верное расширение файла "'.$url.'"');

                return false;

            }

        }

        SYS()->library('lang');

        if (!$filename){

            $tt = explode('/', $url);

            $name = str_replace(".".$file_ext, '', trim(strtolower(end($tt)))).($num?"-{$num}":'');

        }else

            $name = $filename.($num?"-{$num}":'');  

        if (ex($dir.SYS()->lang->RuToEn($name), 1, $file_ext, 0))

            $this->loadFileUrl($url, $dir, $ext, $filename, ++$num);

        $name .= ".{$file_ext}";



        $file = file_get_contents($url);

        if (!$file){

            debLog('lib_file', "Не удалось открыть файл {$url}");

            return false;  

        }



        if (file_put_contents(BASE_DIR.$dir.$name, $file))

            return $name;

        else{

            debLog('lib_file', "Не удалось загрузить файл {$url} в {$dir}{$name}");

            return false;  

        } 

    }



    private function __codeToMessage($code){ 

        switch ($code) { 

            case UPLOAD_ERR_INI_SIZE: 

                $message = "Размер принятого файла превысил максимально допустимый размер, который задан директивой upload_max_filesize конфигурационного файла php.ini"; 

                break; 

            case UPLOAD_ERR_FORM_SIZE: 

                $message = "Размер загружаемого файла превысил значение MAX_FILE_SIZE, указанное в HTML-форме"; 

                break; 

            case UPLOAD_ERR_PARTIAL: 

                $message = "Загружаемый файл был получен только частично"; 

                break; 

            case UPLOAD_ERR_NO_FILE: 

                $message = "Файл не был загружен"; 

                break; 

            case UPLOAD_ERR_NO_TMP_DIR: 

                $message = "Отсутствует временная папка"; 

                break; 

            case UPLOAD_ERR_CANT_WRITE: 

                $message = "Не удалось записать файл на диск"; 

                break; 

            case UPLOAD_ERR_EXTENSION: 

                $message = "PHP-расширение остановило загрузку файла"; 

                break; 



            default: 

                $message = "Не известная ошибка"; 

                break; 

        } 

        return $message; 

    } 

    

    /**

     * Функция прапорционального изменения размера изображения

     * @param string  : $input   - Путь к исходному файлу

     * @param string  : $output  - Путь для сохранения сжатого файла

     * @param integer : $width   - Ширина сжатого изображения

     * @param integer : $height   - Высота сжатого изображения

     * @param boolean : $ration  - Включает равномерное уменьшение

     * @param boolean : $precent - Использовать ширину и высоту сжатия как проценты или как пиксели    

     * @return boolean

     */

    

    

    public function img_resize($input, $output, $width = 0, $height = 0, $ration = false, $percent = false) {

        list($w_i, $h_i, $type) = @getimagesize(BASE_DIR.$input); 

        if (!$w_i || !$h_i) {

            debLog('lib_file', "Не удалось получить размер файла {$input}");

            return false;

        }

        if (!$width) $width = $w_i;

        if (!$height) $height = $h_i;

        $types = array(

            IMAGETYPE_GIF => 'gif',

            IMAGETYPE_JPEG => 'jpeg',

            IMAGETYPE_PNG => 'png',

        );

        $ext = (isset($types[$type])?$types[$type]:false);

        if ($ext) {

            $func = 'imagecreatefrom'.$ext;

            $img = $func(BASE_DIR.$input);

        } else {

            debLog('lib_file', "Некорректный формат файла {$input}");

            return false;

        }      



        if ($width > $w_i) $width = $w_i;

        if ($height > $h_i) $height = $h_i;

        if ($percent) {

            $width *= $w_i / 100;

            $height *= $h_i / 100;

        }

        if (!$height) $height = $width/($w_i/$h_i);

        if (!$width) $width = $height/($h_i/$w_i);

        if ($ration){

            $x_ratio = $width / $w_i;

            $y_ratio = $height / $h_i;

        

            $ratio       = min($x_ratio, $y_ratio);

            $use_x_ratio = ($x_ratio == $ratio);

        

            $width   = $use_x_ratio  ? $width  : floor($w_i * $ratio);

            $height  = !$use_x_ratio ? $height : floor($h_i * $ratio);    

        }

        $img_o = imagecreatetruecolor($width, $height);

        if ($ext == 'png'){

            imageAlphaBlending($img_o, false);

            imageSaveAlpha($img_o, true);

        }

        imagecopyresampled($img_o, $img, 0, 0, 0, 0, $width, $height, $w_i, $h_i);

        if ($ext == 'jpg' or $ext == 'jpeg') {

            $return = imagejpeg($img_o,BASE_DIR.$output,100);

        } else {

            $func = 'image'.$ext;

            $return = $func($img_o,BASE_DIR.$output);

        }



        unset($img);

        unset($img_o);

        return $return;

    }

    

    /**

     * Функция обрезки изображения

     * @param string  : $input   - Путь к исходному файлу

     * @param string  : $output  - Путь для сохранения сжатого файла

     * @param mixed   : $crop    - если true то обрезает до ровного квадрата иначе если массив обрезает на колличество указанных едениц

     * @param boolean : $precent - Использовать ширину и высоту сжатия как проценты или как пиксели

     * @return boolean

     */

    

    public function img_crop($input, $output, $crop = true,$percent = false) {

        list($w_i, $h_i, $type) = @getimagesize(BASE_DIR.$input); 

        if (!$w_i || !$h_i) {

            debLog('lib_file', "Не удалось получить размер файла {$input}");

            return false;

        }

        $types = array(

            IMAGETYPE_GIF => 'gif',

            IMAGETYPE_JPEG => 'jpeg',

            IMAGETYPE_PNG => 'png',

        );

        $ext = (isset($types[$type])?$types[$type]:false);

        if ($ext) {

            $func = 'imagecreatefrom'.$ext;

            $img = $func(BASE_DIR.$input);

        } else {

            debLog('lib_file', "Некорректный формат файла {$input}");

            return false;

        }



        if (!is_array($crop)) {

            $min = $w_i;

            if ($w_i > $h_i) $min = $h_i;

            $w_o = $h_o = $min;

            $x_o =$y_o = 0;

        } else {

            list($x_o, $y_o, $w_o, $h_o) = $crop;

            if ($percent) {

                $w_o *= $w_i / 100;

                $h_o *= $h_i / 100;

                $x_o *= $w_i / 100;

                $y_o *= $h_i / 100;

           }

            if ($w_o < 0) $w_o += $w_i;

                $w_o -= $x_o;

            if ($h_o < 0) $h_o += $h_i;

                $h_o -= $y_o;

            if ($w_i < $w_o) $w_o =  $w_i;

            if ($h_i < $h_o) $h_o =  $h_i;



       }



       $img_o = imagecreatetruecolor($w_o, $h_o);



        if ($ext == 'png'){

            imageAlphaBlending($img_o, false);

            imageSaveAlpha($img_o, true);

        }

        imagecopyresampled($img_o, $img, 0, 0, $x_o, $y_o, $w_o, $h_o, $w_o, $h_o);



       if ($type == 2) {

          $return = imagejpeg($img_o,BASE_DIR.$output,100);

       } else {

            $func = 'image'.$ext;

            $return =  $func($img_o,BASE_DIR.$output);

       }

       unset($img);

       unset($img_o);

       return $return;

    }





    /**

    * Функция добавления вотермарка к изображению

    * @param string  : $input    - Путь к исходному файлу

    * @param string  : $output   - Путь для сохранения сжатого файла

    * @param string  : $mark     - Путь для watermark'a

    * @param int     : $precent  - процен сколько будет занимать вотермакр от изображения

    * @param array   : $position - позиция вотермарка

    * @return boolean

    */



    public function setWatermark($input, $output, $mark, $precent = 30, $position = array('bottom' => 10, 'right' => 10)){

        list($w_i, $h_i, $type) = getimagesize(BASE_DIR.$input); 

        if (!$w_i || !$h_i) {

            debLog('lib_file', "Не удалось получить размер файла {$input}");

            return false;

        }

        list($mw_i, $mh_i, $mtype) = getimagesize(BASE_DIR.$mark); 

        if (!$mw_i || !$mh_i) {

            debLog('lib_file', "Не удалось получить размер файла {$mark}");

            return false;

        }



        $types = array(

            IMAGETYPE_GIF => 'gif',

            IMAGETYPE_JPEG => 'jpeg',

            IMAGETYPE_PNG => 'png',

        );

        $ext = (isset($types[$type])?$types[$type]:false);

        if ($ext) {

            $func = 'imagecreatefrom'.$ext;

            $img = $func(BASE_DIR.$input);

        } else {

            debLog('lib_file', "Некорректный формат файла {$input}");

            return false;

        }



        $mext = $types[$mtype]; 

        if ($mext) {

            $mfunc = 'imagecreatefrom'.$mext;

            $mark_img = $mfunc(BASE_DIR.$mark);

        } else {

            debLog('lib_file', "Некорректный формат файла {$mark}");

            return false;

        }



        $mark_w = $w_i/100*$precent;

        $mark_h = $h_i/100*$precent;

        

        $x_ratio = $mark_w / $mw_i;

        $y_ratio = $mark_h / $mh_i;

        

        $ratio       = min($x_ratio, $y_ratio);

        $use_x_ratio = ($x_ratio == $ratio);

        

        $mark_w   = $use_x_ratio  ? $mark_w  : floor($mw_i * $ratio);

        $mark_h = !$use_x_ratio ? $mark_h : floor($mh_i * $ratio);           



        if (isset($position['bottom']))

            $marge_top = $h_i - $mark_h - $position['bottom'];

        elseif (isset($position['top']))

            $marge_top = $position['top'];

        else

            $marge_top = 0;

        

        if (isset($position['right']))

            $marge_left = $w_i - $mark_w - $position['right'];

        elseif (isset($position['left']))

            $marge_left = $position['left'];

        else

            $marge_left = 0;



        if ($ext == 'png'){

            imageAlphaBlending($img, false);

            imageSaveAlpha($img, true);

        } 

        if ($mext == 'png'){

            imageAlphaBlending($mark_img , false);

            imageSaveAlpha($mark_img , true);

        }

        imagecopyresampled($img, $mark_img, $marge_left , $marge_top, 0, 0, $mark_w, $mark_h, $mw_i, $mh_i);



        $func = 'image'.$ext;

        $return = $func($img,BASE_DIR.$output);



        unset($img);

        unset($mark_img);

        return $return;

    }



   

    /**

    * Функция создания миниатюр для изображений

    * @param string  : $input    - Путь к исходному изображению

    * @param int     : $width    - Максимальная ширина

    * @param int     : $height   - Максимальная высота

    * @param boolean : $crop     - Обризать ли изображение

    * @return string - путь к новому изображению

    */



    public function imgsize($input, $width = 0, $height = 0, $crop = false){

        list($w_i, $h_i, $type) = getimagesize(BASE_DIR.$input); 

        if (!$w_i || !$h_i) {

            debLog('lib_file', "Не удалось получить размер файла {$input}");

            return $input;

        }



        # если исходное изображение меньше рамок возвращаем его

        if ($w_i <= $width && $h_i <= $height)

            return $i_input;



        # смотрим если папка кэш в темпе

        $dir  = explode('/', $input);

        $filename = array_pop($dir);        

        $dir = BASE_DIR."temp/imgsize/";

        if (!is_dir($dir))

            mkdir($dir);       

        # создаём по первым буква имени файла 

        $dir = BASE_DIR."temp/imgsize/".mb_substr($filename, 0, 3).'/';

        if (!is_dir($dir))

            mkdir($dir);



        if (!is_writable($dir)){

            debLog('lib_file', "Не могу писать в папку {$dir}");

            return $input;

        }



        # смотрим если уже файл нужного размера

        $filename = $width."_".$height."_".(int)$crop."-".$filename;

        $res_url = str_replace(BASE_DIR, get_config('sub_folder')."/", $dir).$filename;

        if (is_file($dir.$filename))

            return $res_url;



        if ($crop){

            $ww = $hh = 0;

            $w_r = $width/$w_i;

            $h_r = $height/$h_i;

            if ($w_r >= $h_r)

               $ww = $width;                

            else

                $hh = $height;  

            $this->img_resize($input, str_replace(BASE_DIR, "/", $dir).$filename, $ww, $hh, true);

            $this->img_crop(str_replace(BASE_DIR, "/", $dir).$filename, str_replace(BASE_DIR, "/", $dir).$filename, $crop = array(0, 0, $width, $height));

        }else

            $this->img_resize($input, str_replace(BASE_DIR, "/", $dir).$filename, $width, $height, true);



        return $res_url;   

    }



    /**

    * Метод ждёт передачи $_FILES[$field] или $_POST[$field."_url"] и загружает выбранный файл на сервер в указанную директорию upload

    * @param string  : $field    - Имя поля

    * @param string  : $dir      - Имя папки в папке temp/upload/ (без последнего слеша)

    * @param array   : $ext      - Доступные расширения для загрузки

    * @param string  : $table    - Название таблицы (нужно для удаления файла)

    * @param int     : $id       - ID записи (нужно для удаления файла)

    * @return string - путь к загруженному файлу

    */



    public function input($field, $dir, $ext = array('jpg', 'jpeg', 'gif', 'png'), $table = '', $id = 0){

        $tdir = "/temp/upload/".$dir."/";

        if (!ex($tdir, 0, '', false))

            @mkdir(BASE_DIR.$tdir);

        $tdir .= date('my')."/";

        $rdir = date('my')."/";

        if (!ex($tdir, 0, '', false))

            @mkdir(BASE_DIR.$tdir);

        ex($tdir, 0, '', 'sys');



        if (!empty($_FILES[$field]['tmp_name'])){

            $file = $this->loadFile($_FILES[$field], $tdir, $ext);

            if (!$file) return '';

        }elseif (!empty($_POST[$field."_url"])){

            $file = $this->loadFileUrl($_POST[$field."_url"], $tdir, $ext);

            if (!$file) return '';

        }else

            return '';



        $file = $rdir.$file;



        if (!$table || !$id)

            return $file;

        $res = db()->select("`{$field}`")->where(array('id' => $id))->get($table, '', 1);

        if (empty($res[$field])) return $file;

        @unlink(BASE_DIR."/temp/upload/{$dir}/{$res[$field]}");

        return $file;

    }



}



function SFile(){

    return SYS()->library('file');

}