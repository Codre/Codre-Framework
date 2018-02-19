<?php

function smarty_function_imgsize($params, $smarty){    
    SYS()->library('file');
    return SYS()->file->imgsize($params['src'], isset($params['width'])?$params['width']:0, isset($params['height'])?$params['height']:0, isset($params['crop']));
}
?>