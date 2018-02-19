<?php
function smarty_modifier_number_format($text, $afterpoint = 0){

	$text = str_replace(' ', '&nbsp;', number_format($text, $afterpoint, ',', ' '));
	return $text;
}

?>