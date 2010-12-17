<?php 

class Cr_DbConfig
{
	public $username, $password, $type, $path, $extra, $name;
	
	public function __construct($file, $context)
	{
		$contents = Cr_Ini::parse($file, true);
		if(isset($contents[$context]))
		{
			foreach($contents[$context] as $i => $value)
			{
				$this->$i = $value;
			}
		}else{
			throw new Exception("The context ({$context}) was not found in the configuration file ({$file}).");
		}
	}

	public function getUrl()
	{
		return "{$this->type}://{$this->username}:{$this->password}@{$this->path}/{$this->name}";
	}
	
}