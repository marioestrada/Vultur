<?php

abstract class Cr_Cache_Abstract
{
	protected $options;
	protected $default_time;
	protected $tags_key = '_tags';
	
	public function __construct($options, $default_time = null)
	{
		$this->options = $options;
		$this->default_time = $default_time;
	}
	
	
	abstract public function set($name, $value, $options = null);
	
	abstract public function get($name);
	
	abstract public function addToTags($name, $tags);
	
	protected function _addToTags($name, $tags)
	{
		if(!is_array($tags) && !is_string($tags))
			throw new Exception('Tags have to be defined as arrays or a string with comma separated values.');
		
		if(is_string($tags))
		{
			$tags = explode(',', $tags);
			foreach($tags as $i => $tag)
			{
				$tags[$i] = trim($tag);
			}
		}
		
		$tags_array = $this->get($this->tags_key, true);
		$tags_array = $tags_array ? $tags_array : array();
		
		foreach($tags as $tag)
		{
			if(!is_string($tag))
				throw new Exception('Each individual tag needs to be a string.');
			
			$tags_array[$tag][] = $name;
			$tags_array[$tag] = array_unique($tags_array[$tag]);
		}
		
		return $tags_array;
	}
	
	abstract public function delete($name);
	
	abstract public function deleteByTag($tag);
	
	protected function _deleteByTag($tag)
	{
		$tags_array = $this->get($this->tags_key, true);
		if($tags_array && isset($tags_array[$tag]))
		{
			foreach($tags_array[$tag] as $i => $name)
			{
				$this->delete($name);
				unset($tags_array[$tag][$i]);
			}
		}
		
		return $tags_array;
	}
	
	protected function getTimeDelta($time = null)
	{
		$time = empty($time) ? $this->default_time : $time;
		
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