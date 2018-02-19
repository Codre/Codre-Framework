<?php
function smarty_modifier_filesize($size)
{
	$size = aint($size);
	$ed = 'bytes';
	if ($size > 1024) {
		$ed = 'Kbytes';
		$size = $size / 1024;
	}
	if ($size > 1024) {
		$ed = 'Mbytes';
		$size = $size / 1024;
	}
	$size = str_replace('.', ',', $size);
	if (strpos($size, ',') !== false)
		$size = substr($size, 0, strpos($size, ',') + 3);
	return $size."&nbsp;".$ed;
}

?>