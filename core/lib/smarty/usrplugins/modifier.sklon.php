<?php
function smarty_modifier_sklon($number, $var1, $var2_4, $var5_0 = null) {
	SYS()->library('lang');
	if (!$var5_0) $var5_0 = $var2_4;
	return SYS()->lang->Sklon($number, $var1, $var2_4, $var5_0);
}

?>