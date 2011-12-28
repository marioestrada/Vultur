<?php
require_once('Abstract.php');

class Cr_Cache_File extends Cr_Cache_Abstract
{
	private $extension = '.cache';
	private $path;
	private $tags_file;
	
	public function __construct($options, $default_time = null)
	{
		parent::__construct($options, $default_time);
		$this->path = $options['path'];
		$this->extension = isset($options['extension']) ? $options['extension'] : $this->extension;
	}
	
	public function set($name, $value, $options = null)
	{
		$filename = $this->getFileName($name);
		$time = isset($options['time']) ? $this->getTimeDelta($options['time']) : $this->getTimeDelta();
		
		$data = array(
			'expires' => time() + $time, 
			'content' => $value
		);
		$this->writeToFile($data, $filename);
		
		if(isset($options['tags']))
		{
			$this->addToTags($name, $options['tags']);
		}
	}
	
	public function addToTags($name, $tags)
	{
		$this->_openTagsFile();
		
		$filename = $this->getFileName($this->tags_key);
		$tags_array = $this->_addToTags($name, $tags);
		
		$this->writeToFile(array('content' => $tags_array), $filename, true);
		
		$this->_closeTagsFile();
	}
	
	private function _openTagsFile()
	{
		if(isset($this->tags_file))
			return;
		
		$filename = $this->getFileName($this->tags_key);
		$this->tags_file = fopen($filename, 'c+');
		
		if(!$this->tags_file)
		{
			throw new Exception('Could not open or create cache file.');
		}

		flock($this->tags_file, LOCK_EX);
	}
	
	private function _closeTagsFile()
	{
		if(isset($this->tags_file))
		{
			flock($this->tags_file, LOCK_UN);
			fclose($this->tags_file);
			unset($this->tags_file);
		}
	}
	
	protected function writeToFile($data, $filename, $tags = false)
	{
		if(!$tags)
		{
			$file = fopen($filename, 'w');
		}else{
			$file = & $this->tags_file;
		}
		
		if(!$file)
		{
			throw new Exception('Could not open or create cache file.');
		}
		
		if(!$tags)
		{
			flock($file, LOCK_EX);
		}
		
		fseek($file, 0);
    	ftruncate($file, 0);
		
		$data = serialize($data);
		
		if(fwrite($file, $data) === false)
		{
			throw new Exception('Could not write to cache file.');
		}
		
		if(!$tags)
		{
			flock($file, LOCK_UN);
			fclose($file);
		}
	}
	
	public function get($name, $tags = false)
	{
		$filename = $this->getFileName($name);
		
		if(!file_exists($filename))
		{
			return false;
		}
		
		if(!$tags)
		{	
			$file = fopen($filename, 'r');
			if(!$file)
			{
				return false;
			}
			flock($file, LOCK_SH);
		}else{
			$file = & $this->tags_file;
		}

		$data = @fread($file, filesize($filename));
		
		if(!$tags)
		{
			flock($file, LOCK_UN);
			fclose($file);
		}
		
		$data = @unserialize($data);
		
		if(!$data || ($data && isset($data['expires']) && time() > $data['expires']))
		{
			if(!$tags)
				unlink($filename);
			return false;
		}
		
		return $data['content'];
	}
	
	public function delete($name)
	{
		$filename = $this->getFileName($name);
		
		if(file_exists($filename) && !unlink($filename))
		{
			throw new Exception('Could not delete cache file.');
		}
	}
	
	public function deleteByTag($tag)
	{
		$this->_openTagsFile();
		
		$filename = $this->getFileName($this->tags_key);
		$tags_array = $this->_deleteByTag($tag);
		
		$this->writeToFile(array('content' => $tags_array), $filename, true);
		
		$this->_closeTagsFile();
	}
	
	protected function getFileName($name)
	{
		return $this->path . $name . $this->extension;
	}

}