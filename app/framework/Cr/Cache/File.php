<?php

class Cr_Cache_File extends Cr_Cache_Abstract
{
	private $extension = '.cache';
	private $path;
	private $tags_file;
	protected $tags_key = '--metadata';
	
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
		
		if(!empty($options['tags']))
		{
			$filename_tags = $this->getFileName($name . $this->tags_key);

			$metadata = array(
				'expires' => time() + $time,
				'name' => $name,
				'tags' => $options['tags']
			);
			
			$this->writeToFile($metadata, $filename_tags);
		}
	}
	
	public function get($name, $tags = false)
	{
		$filename = $this->getFileName($name);
		
		if(!file_exists($filename))
		{
			return false;
		}
		
		$file = fopen($filename, 'r');
		if(!$file)
		{
			return false;
		}
		flock($file, LOCK_SH);
		
		$data = file_get_contents($filename);
		
		flock($file, LOCK_UN);
		fclose($file);
	
		$data = @unserialize($data);
		
		if($data === false || ($data && isset($data['expires']) && time() > $data['expires']))
		{
			if($tags && isset($data['name']))
				$this->delete($data['name']);
			
			$this->_deleteFile($filename);
			
			return false;
		}
		
		if($tags)
			return !empty($data['tags']) ? $data : false;
		
		return !empty($data['content']) ? $data['content'] : true;
	}
	
	public function delete($name)
	{
		$filename = $this->getFileName($name);
		
		if(file_exists($filename) && !$this->_deleteFile($filename))
		{
			throw new Exception('Could not delete cache file.');
		}
	}

	public function deleteByTag($tag)
	{	
		if(!is_dir($this->path))
			return false;
		
		$metadata_files = $this->_getCacheFiles(true);
		
		foreach($metadata_files as $file)
		{
			if(is_file($file))
			{
				$info = pathinfo($file);
				
				$name = $info['filename'];
				
				$metadata = $this->get($name, true);
				
				if($metadata === false || in_array($tag, $metadata['tags']))
				{
					$this->delete($metadata['name']);
					
					$this->_deleteFile($file);
				}
			}
		}
	}
	
	protected function _getCacheFiles($tags = false)
	{
		$pattern = $this->path . '*';
		
		if($tags)
			$pattern .= $this->tags_key;
		
		return @glob($pattern . $this->extension);
	}
	
	protected function writeToFile($data, $filename)
	{
		$file = fopen($filename, 'w');
		
		if(!$file)
		{
			throw new Exception('Could not open or create cache file.');
		}
	
		flock($file, LOCK_EX);
		
		fseek($file, 0);
    	ftruncate($file, 0);
		
		$data = serialize($data);
		
		if(fwrite($file, $data) === false)
		{
			throw new Exception('Could not write to cache file.');
		}
		
		flock($file, LOCK_UN);
		fclose($file);
	}
	
	protected function _deleteFile($filename)
	{
		if(!file_exists($filename))
			return true;
		
		$file = fopen($filename, 'w');
		flock($file, LOCK_UN);
		fclose($file);
		return unlink($filename);
	}
	
	protected function getFileName($name)
	{
		return $this->path . $name . $this->extension;
	}
	
	public function cleanCache()
	{
		$files = $this->_getCacheFiles();
		
		foreach($files as $file)
		{
			$info = pathinfo($file);
			$this->get($info['filename']);
		}
	}
}