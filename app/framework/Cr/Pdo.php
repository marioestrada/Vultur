<?php 

class Cr_Pdo extends PDO
{
	public $type;
	public function __construct($type_config, $path = '', $username = '', $password = '', $driver_options = array()) {
        if(!is_object($type_config))
        {
        	$this->type = $type_config;
        	$dsn = $type_config . ':' . $path;
        	$driver_options = !empty($driver_options) ? $driver_options : array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
        	
        }else if(get_class($type_config) == 'Cr_DbConfig'){
        	
        	if($type_config->type == 'sqlite')
        		$dsn = $type_config->type . ':' . $type_config->path;
        	else
        		$dsn = $type_config->type . ':host=' . $type_config->path . ';dbname=' . $type_config->name;
        	
        	$this->type = $type_config->type;
        	
        	$username = $type_config->username;
        	$password = $type_config->password;
        	$driver_options = !is_null($type_config->extra) ? $type_config->extra : array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
        	
        }else{
        	throw new Exception('No valid configuration was provided.');
        }
        
        parent::__construct($dsn, $username, $password, $driver_options);
    }
}