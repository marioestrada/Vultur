<?php

class Cr_Loader{
	
	protected $_root_dir = '.';
	
	public function __construct($root_dir = 'app/framework/')
	{
		$this->_root_dir = $root_dir;

		set_include_path($root_dir . PATH_SEPARATOR . get_include_path());
		
		spl_autoload_register(array(__CLASS__, 'load'));
	}
	
	public function load($class_name)
	{
		if(class_exists($class_name, false) || interface_exists($class_name, false))
		{
			return true;
		}
		
		try{
			$filename = str_replace('_', '/', $class_name) . '.php';
			require_once($filename);
		}catch(Exception $e){
		}
		
		return class_exists($class_name, false) || interface_exists($class_name, false);
	}
}