<?php

class Cr_View
{
	private $view_content;
	private $cr_config;
	public $template_extension = '.php';

	public function __construct()
	{
		$this->cr_config = cr_config();
		if(isset($this->cr_config['template_extension']))
			$this->template_extension = $this->cr_config['template_extension'];
	}
	
	public function show($view_path, $use_layout = true, $layout = 'main')
	{
		
		$this->view_content = $this->getView($view_path);
		if($use_layout)
		{
			include "app/layout/{$layout}{$this->template_extension}";
		}else{
			echo $this->view_content;
		}
	}
	
	public function getView($view_path)
	{
		ob_start();
		include "app/views/{$view_path}{$this->template_extension}";
		$res = ob_get_contents();
		ob_end_clean();
		
		return $res;
	}
	
	public function __set($name, $value)
	{
		$this->$name = $value;
	}
	
	public function showValueOr($data, $other = "-")
	{
		echo !empty($data) ? $data : $other;
	}
	
	public function escape($data)
	{
		echo htmlentities(stripslashes($data));
	}
	
	public function escapeUrl($data)
	{
		echo urlencode($data);
	}
	
	public function embedScripts($scripts, $scripts_url = '', $file_ext = '')
	{	
		foreach($scripts as $script)
		{
			if(is_string($script))
				echo '<script src="', $scripts_url, $script, $file_ext, '"></script>';
			elseif(is_array($script))
				echo '<script src="', $script[0], '"></script>';
		}
	}
	
	public function createOptions($fields, $selected = null)
	{
		foreach($fields as $value => $string)
		{
			$attribute = !is_null($selected) && $value == $selected ? 'selected="selected"' : '';
			echo '<option value="', $value, '" ', $attribute, '>', $string, '</option>';
		}
	}
	
}