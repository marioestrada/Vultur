<?php 

class Cr_File
{
	static function getMime($file_extension)
	{
		$mime = array(
			"pdf" => "application/pdf",
			"txt" => "text/plain",
			"html" => "text/html",
			"htm" => "text/html",
			"exe" => "application/octet-stream",
			"zip" => "application/zip",
			"doc" => "application/msword",
			"docx" => "application/msword",
			"xls" => "application/vnd.ms-excel",
			"xlsx" => "application/vnd.ms-excel",
			"ppt" => "application/vnd.ms-powerpoint",
			"pptx" => "application/vnd.ms-powerpoint",
			"gif" => "image/gif",
			"png" => "image/png",
			"jpeg"=> "image/jpg",
			"jpg" =>  "image/jpg",
			"php" => "text/plain"
		);
		
		return isset($mime[$file_extension]) ? $mime[$file_extension] : "application/octet-stream";
	}
	
	static function getFileExtension($filename)
	{
		return strtolower(substr(strrchr($filename,"."),1));
	}
	
	static function isWebImage($filename)
	{
		return in_array(self::getFileExtension($filename), array('png', 'gif', 'jpg', 'jpeg'));
	}
	
}