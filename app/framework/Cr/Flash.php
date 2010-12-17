<?php 
require_once('Cr/Session.php');

class Cr_Flash
{
	static function start()
	{
		Cr_Session::start();
	}

	static function set($name, $value)
	{
		$_SESSION['cr_flash'][$name] = array(
			'value' => $value,
			'retain' => true
		);
	}
	
	static function get($name = '')
	{
		if(empty($name))
		{
			$value = isset($_SESSION['cr_flash']) ? $_SESSION['cr_flash'] : false;
		}else{
			$value = isset($_SESSION['cr_flash'][$name]) ? $_SESSION['cr_flash'][$name]['value'] : false;
		}
		return $value;
	}
	
	static function retain($name)
	{
		if(!empty($name))
		{
			if(isset($_SESSION['cr_flash'][$name]['retain']))
			{
				$_SESSION['cr_flash'][$name]['retain'] = true;
				return true;
			}
		}
		return false;
	}
	
	static function clear()
	{
		if(isset($_SESSION['cr_flash']))
		{
			foreach($_SESSION['cr_flash'] as $name => $value)
			{
				if(!$_SESSION['cr_flash'][$name]['retain'])
					unset($_SESSION['cr_flash'][$name]);
				else
					$_SESSION['cr_flash'][$name]['retain'] = false;
			}
			if(empty($_SESSION['cr_flash']))
				unset($_SESSION['cr_flash']);
		}
	}

}