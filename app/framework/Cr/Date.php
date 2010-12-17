<?php

class Cr_Date
{
	private $date_type, $locale;
	private $months_en = array('January', 'February', 'March', 'April', 'May', 'June', 
					  'July', 'August', 'September', 'October', 'November', 'December');
	private $months_es = array('Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 
					  'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');

	public function __construct($date_type, $locale = null)
	{
		$this->date_type = $date_type;
		$this->locale = $locale;
	}
	
	public function now()
	{
		switch($this->date_type)
		{
		case 'timestamp':
			return time();
			break;
		case 'mysql':
			return date('Y-m-d H:i:s');
			break;
		}
	}
	
	public function fromIso($date)
	{
		switch($this->date_type)
		{
		case 'timestamp':
			return $this->isoDateToTimestamp($date);
			break;
		case 'mysql':
			return $this->isoDateToMysql($date);
			break;
		}
	}
	
	public function toIso($date)
	{
		return $this->toCustom($date, 'd/m/Y');
	}
	
	public function toString($date, $time = false)
	{
		$res = '';
		switch($this->date_type)
		{
		case 'timestamp':
			$res = $this->timestampToString($date, $time);
			break;
		case 'mysql':
			$res = $this->mysqlDateToString($date, $time); 
			break;
		}
		$res = ($this->locale != null) ? $this->toLocale($res) : $res;
		return $res;
	}
	
	public function toYears($date)
	{
		switch($this->date_type)
		{
		case 'timestamp':
			return $this->timestampToYears($date);
			break;
		case 'mysql':
			return $this->mysqlDateToYears($date); 
			break;
		}
	}
	
	public function toCustom($date, $format)
	{
		if(empty($date))
			return;
		switch($this->date_type)
		{
		case 'timestamp':
			return date($format, $date);
			break;
		case 'mysql':
			return date($format, strtotime($date)); 
			break;
		}
	}
	
	public function getTime($date)
	{
		switch($this->date_type)
		{
		case 'timestamp':
			return $this->timestampToTime($date);
			break;
		case 'mysql':
			return $this->mysqlDateToTime($date); 
			break;
		}
	}
	
	public function fromTimestamp($timestamp)
	{
		switch($this->date_type)
		{
		case 'timestamp':
			return $this->timestampToDate($timestamp);
			break;
		case 'mysql':
			return $this->timestampToDate($date, 'Y-m-d'); 
			break;
		} 
	}
	
	private function timestampToDate($timestamp, $format = 'd/m/Y')
	{
		return is_numeric($timestamp) ? date($format, $timestamp) : null;
	}
	
	private function timestampToTime($timestamp)
	{
		return is_numeric($timestamp) ? date('H:i') : null;
	}
	
	private function isoDateToTimestamp($date, $time = false)
	{
		$parts = explode('/', $date);
		
		$time_hour = $time === false ? 0 : $time['hour'];
		$time_minutes = $time === false ? 0 : $time['minutes'];
		
		return !empty($parts[2]) ? 
			mktime($time_hour, $time_minutes, 0, $parts[1], $parts[0], $parts[2]) : null;
	}
	
	private function timestampToString($timestamp, $time = false, $force_format = '')
	{					  
		$format = $time ? 'j F, Y - h:i A' : 'j F, Y';
		$format = $force_format === '' ? $format : $force_format;
		
		return is_numeric($timestamp) ? date($format, $timestamp) : null;
	}
	
	private function timestampToYears($time)
	{
		return floor((time() - $time) / 31556926);
	}
	
	private function mysqlDateToString($date, $hora = false)
	{
		return $this->timestampToString(strtotime($date), $hora);
	}
	
	private function mysqlDateToIso($date)
	{
		if(!empty($date))
			return $this->timestampToDate(strtotime($date));
		return null;
	}
	
	private function mysqlDateToYears($date)
	{
		if(!empty($date))
			return $this->timestampToYears(strtotime($date));
		return null;
	}
	
	private function mysqlDateToTime($date)
	{
		if(!empty($date))
			return $this->timestampToTime(strtotime($date));
		return null;
	}
	
	private function isoDateToMysql($date)
	{
		if(!empty($date))
		{
			$date_elements = explode('/', $date);
			$date_elements = array_reverse($date_elements);
			$date = implode('-', $date_elements);
			
			return $date;
		}
		return null;
	}
	
	public function toLocale($string)
	{
		$locale = 'months_' . $this->locale;
		return str_replace($this->months_en, $this->$locale, $string);
	}

}