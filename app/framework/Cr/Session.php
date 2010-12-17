<?php

class Cr_Session
{

	private $name;
	
	public function __construct($name)
	{
		$this->name = $name;
		$this->start();
	}
	
	static public function start()
	{
		if(!session_id())
		{
			session_start();
		}	
	}
	
	public function __set($key, $value)
	{
		$_SESSION[$this->name][$key] = $value;
	}
	
	public function __get($key)
	{
		if(isset($_SESSION[$this->name][$key]))
			return $_SESSION[$this->name][$key];
			
		return null;
	}
	
	public function __isset($key)
	{
		return isset($_SESSION[$this->name][$key]);
	}
	
	public function __unset($key)
	{
		unset($_SESSION[$this->name][$key]);
	}
	
	public function clear()
	{
		if(isset($_SESSION[$this->name]))
		{
			foreach($_SESSION[$this->name] as $key -> $item)
			{
				unset($_SESSION[$this->name][$key]);
			};
			unset($_SESSION[$this->name]);
		}
	}

}