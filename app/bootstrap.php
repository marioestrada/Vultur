<?php

set_include_path('app/framework/' . PATH_SEPARATOR . get_include_path());

require_once('Cr/Ini.php');

function cr_config($part = '')
{
	if(!isset($GLOBALS['CR']['config']) && $part == '')
	{
		$GLOBALS['CR']['config'] = Cr_Ini::parse('app/config/config.ini', true);
	}
	
	if($part != '' && isset($GLOBALS['CR']['config'][$part]))
	{
		return $GLOBALS['CR']['config'][$part];
	}
	
	return $GLOBALS['CR']['config'];
}

function __autoload($class_name)
{
	require_once str_replace('_', '/', $class_name) . '.php';
}

function fb_log($var)
{
	$cr_config = cr_config();
	
	if($cr_config['debug'])
	{
		Cr_Log::firebug($var);
	}
}