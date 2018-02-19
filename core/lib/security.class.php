<?php
defined('INDEX') or die('acsess error');
/**
 * Класс фильтрации и проверки безопасности данных
 */

class security{
    
    
    /**
     * Фильтрация строрки без вырезки тегов
     * @param str:$input - строка для фильтрации
     * @return string
     */
     
     function text_clean($input){
        $input = htmlentities($input, ENT_QUOTES, get_config('charset'));   
        if(get_magic_quotes_gpc ())        
            $input = stripslashes ($input);        
        return $input;
    }
    
    /**
     * Фильтрация данных перед добавлением в базу данных
     * @param string : $input - строка для фильтрации
     * @return string
     */
     
     function db_clean($input){
        // удаляем лишние пробелы
        $input = preg_replace("% +%", " ", $input);
        // удаляем начальные и конечные пробелы
        $input = trim($input);
        // удаляем одинарные кавычки
        $input = str_replace("'", "", $input);
        // заменяем двойные кавычки на &quot;
        $input = str_replace("\"", "&quot;", $input);
        return $input;
     }
}
?>