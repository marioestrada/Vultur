<?php

class Cr_Ini
{
	static function parse($file, $load_sections = false, $section = null)
	{
		$res = parse_ini_file($file, $load_sections);
		return $load_sections && !empty($section) ? $res[$section] : $res;
	}
}