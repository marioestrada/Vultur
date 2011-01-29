<?php

require_once('Abstract.php');
require_once('Rediska.php');
require_once('Rediska/Key.php');

class Cr_Cache_Redis extends Cr_Cache_Abstract
{
	private $server;
	
	public function __construct($options, $default_time = null)
	{
		parent::__construct($options, $default_time);
		$this->server = new Rediska($options);
	}
	
	
	public function set($name, $value, $time = null)
	{
		$key = $this->getKey($name, $time);
		$key->setValue($value);
	}
	
	public function addToTags($name, $tags)
	{
		$key = $this->getKey($this->tags_key);
		$tags_array = $this->_addToTags($name, $tags);
		
		$key->setValue($tags_array);
	}
	
	public function deleteByTag($tag)
	{
		$key = $this->getKey($this->tags_key);
		$tags_array = $this->_deleteByTag($tag);
		
		$key->setValue($tags_array);
	}
	
	public function get($name)
	{
		$key = $this->getKey($name);
		$res = $key->getValue();
		return is_null($res) ? false : $res;
	}
	
	public function delete($name)
	{
		$key = $this->getKey($name);
		$key->delete();
	}
	
	public function getKey($name, $time = null)
	{
		if(empty($time))
			$key = new Rediska_Key($name);
		else
			$key = new Rediska_Key($name, $this->getTimeDelta($time));
		
		$key->setRediska($this->server);
		
		return $key;
	}

}