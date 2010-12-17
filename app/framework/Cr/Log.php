<?php

class Cr_Log
{
	static function firebug($var, $prefix = '')
	{
		require_once('FirePHPCore/fb.php');
		fb($var, $prefix);
	}
}