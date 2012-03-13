<?php

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

function fb_log($var)
{
	$cr_config = cr_config();
	
	if($cr_config['debug'])
	{
		Cr_Log::firebug($var);
	}
}