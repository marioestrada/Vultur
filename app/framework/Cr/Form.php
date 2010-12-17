<?php

class Cr_Form
{
	private $options = array(
		'action' => '',
		'enctype' => 'application/x-www-form-urlencoded',
		'method' => 'post',
		'attributes' => array()
	);
	private $html_output = array(
		'text' => '<input type="text" name="{field}" id="{id}" value="{value}" {options} />',
		'other' => '<input type="{kind}" name="{field}" id="{id}" value="{value}" {options} />',
		'password' => '<input type="password" name="{field}" id="{id}" value="{value}" {options} />',
		'textarea' => '<textarea name="{field}" id="{id}" {options}></textarea>',
		'select' => '<select name="{field}" id="{id}" {options}>{values}</select>',
		'option' => '<option value="{value}">{label}</option>',
		'submit' => '<input type="submit" value="{label}"></input>',
		'label' => '<label for="{id}">{label}</label>',
		'br' => "<br />\n",
		'form' => '<form action="{action}" method="{method}" enctype="{enctype}" {options}>{content}</form>'
	);
	private $default_options = array(
		'label' => 'before',
		'label_colon' => true,
		'break' => true,
		'attributes' => array()
	);
	private $html = false;
	private $items = array();
	private $values = array();
	
	public function __construct($options)
	{
		$this->options = array_merge($this->options, $options);
	}
	
	public function add($kind, $field, $label, $arg_options = '')
	{
		$options = $this->default_options;
		if(in_array($kind, array('label', 'submit')))
			$options['label'] = false;
		elseif(in_array($kind, array('radio', 'checkbox')))
			$options['label'] = 'after';
		
		if(in_array($kind, array('radio', 'checkbox', 'submit')))
			$options['label_colon'] = false;
		
		if(is_array($arg_options))
			$options = array_merge($options, $arg_options);
				
		$this->items[] = array('label' => $label, 'field' => $field, 'kind' => $kind, 'options' => $options);
		
		$this->html = false;
		
		return $this;
	}
	
	public function addValues($values)
	{
		$this->html = false;
		$this->values = $values;
		
		return $this;
	}	
	
	public function __toString()
	{
		if(is_string($this->html))
			return $this->html;
		
		$form_output = str_replace(
			array('{action}', '{method}', '{enctype}', '{options}'), 
			array($this->options['action'], $this->options['method'], $this->options['enctype'], $this->htmlAttributes($this->options['attributes'])), 
			$this->html_output['form']
		);
		
		$output = '';
		
		foreach($this->items as $item)
		{
			$options =& $item['options']; 
			if($options['label'] == 'before' || $options['label'] === true)
				$output .= $this->htmlItem($item, 'label');
			
			$output .= $this->htmlItem($item);
				
			if($options['label'] == 'after')
				$output .= $this->htmlItem($item, 'label');
				
			if($options['break'])
				$output .= $this->html_output['br'];
			
		}
		
		$this->html = $form_output = str_replace('{content}', "\n" . $output, $form_output);
		
		return $this->html;
	}
	
	private function htmlItem($item, $kind = '')
	{
		$kind = empty($kind) ? $item['kind'] : $kind;
		
		$options =& $item['options'];
		
		$output = $this->html_output[$kind];
		$output = str_replace('{value}', isset($this->values[$item['field']]) ? $this->values[$item['field']] : '', $output);
		$output = str_replace('{field}', $item['field'], $output);
		$output = str_replace('{label}', $item['label'] . ($item['options']['label_colon'] ? ':' : ''), $output);
		$output = str_replace('{id}', isset($options['attributes']['id']) ? $options['id']  : $item['field'], $output);
		
		unset($options['attributes']['id']);
		$output = str_replace('{options}', $this->htmlAttributes($options['attributes']), $output);
				
		return $output;
	}
	
	private function htmlAttributes($attributes)
	{
		$output = '';
		foreach($attributes as $attr => $value)
		{
			$output .= $attr . '="' . $value . '" ';
		}
		return $output;
	}
	
}