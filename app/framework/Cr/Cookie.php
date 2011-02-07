<?php

class Cr_Cookie
{

	private $_prefix;
	private $_default_time;
	private $_default_path;
	
	public function __construct($prefix, $default_time = null, $default_path = '/')
	{
		$this->_prefix = $prefix;
		$this->_default_time = $default_time;
		$this->_default_path = $default_path;
	}
	
	public function set($key, $value, $time = null, $path = '/')
	{
		$seconds = $this->_getTimeDelta(is_array($time) ? $time : $this->_default_time);
		setcookie($this->_prefix . '[' .  $key . ']', $value, time() + $seconds, $path);
	}
	
	public function __set($key, $value)
	{
		$seconds = $this->_getTimeDelta();
		setcookie($this->_prefix . '[' .  $key . ']', $value, time() + $seconds, $this->_default_path);
	}
	
	public function __get($key)
	{
		return isset($_COOKIE[$this->_prefix][$key]) ? $_COOKIE[$this->_prefix][$key] : null;
	}
	
	public function __isset($key)
	{
		return isset($_COOKIE[$this->_prefix][$key]);
	}
	
	public function __unset($key)
	{
		$this->set($key, null, 0);
	}
	
	public function getList()
	{
		return isset($_COOKIE[$this->_prefix]) ? $_COOKIE[$this->_prefix] : false;
	}
	
	public function clear()
	{
		$list = $this->getList();
		if($list)
		{
			foreach($list as $name => $value)
				unset($this->$name);
		}
	}

	private function _getTimeDelta($time = null)
	{
		$time = empty($time) ? $this->_default_time : $time;
		
		if(is_array($time))
		{
			$type = key($time);
			$time = $time[$type];
			switch($type)
			{
				case 'seconds':
					$time = $time;
					break;
				case 'minutes':
					$time = $time * 60;
					break;
				case 'hours':
					$time = $time * 3600;
					break;
				case 'days':
					$time = $time * 86400;
					break;
				case 'weeks':
					$time = $time * 604800;
					break;
				case 'months':
					$time = $time * 2592000;
					break;
				case 'years':
					$time = $time * 31536000;
					break;
				default:
					break;
			}
		}
		
		return $time;
	}

}