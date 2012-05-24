<?php

if(isset($argv[1]))
{
	$dir_name = './' . $argv[1];
}else{
	die('A directory name was not specified.');
}

function supercopy($source, $destination)
{ 
    $dir = opendir($source);

	try
	{
    	if(!file_exists($destination))
		{
			mkdir($destination);
		}

		$ignored = array(
			'.',
			'..',
			'.git',
			'README.markdown',
			'test',
			'app/framework/simpletest'
		);

	    while(($file = readdir($dir)) !== false)
		{ 
	        if (!in_array($file, $ignored))
			{
	            if(is_dir($source . '/' . $file)){ 
					supercopy($source . '/' . $file, $destination . '/' . $file); 
	            }else{ 
					copy($source . '/' . $file, $destination . '/' . $file); 
	            }
	        }
	    }
	}catch(Exception $e){
		die('ERROR: Could not copy all files. ' . $e->getMessage());
	}
	
    closedir($dir);

	return true;
}

if(supercopy(dirname(__FILE__), $dir_name))
	echo "Directory: '{$dir_name}' created succesfully.\n";