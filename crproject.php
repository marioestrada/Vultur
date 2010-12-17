<?php

if(isset($argv[1]))
{
	$dir_name = dirname(__FILE__) . '/' . $argv[1];
}else{
	die('A directory name was not specified.');
}

chdir('../');

function supercopy($src, $dst)
{ 
    $dir = opendir($src);

	try
	{
    	if(!file_exists($dst))
		{
			mkdir($dst);
		}

	    while(($file = readdir($dir)) !== false)
		{ 
	        if (( $file != '.' ) && ( $file != '..' )) { 
	            if ( is_dir($src . '/' . $file) ) { 
	                supercopy($src . '/' . $file,$dst . '/' . $file); 
	            } 
	            else { 
	                copy($src . '/' . $file,$dst . '/' . $file); 
	            } 
	        } 
	    }
	}catch(Exception $e){
		die('ERROR: Could not copy all files. ' . $e->getMessage());
	}
	
    closedir($dir);

	return true;
}

if(supercopy(dirname(__FILE__) . '/cr_resources', $dir_name))
	echo "Directory: '{$dir_name}' created succesfully.\n";