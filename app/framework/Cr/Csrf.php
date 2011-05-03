<?php

require_once('Cr/Session.php');
require_once('Cr/Request.php');

class Cr_Csrf
{
	private $_timeout;
	private $_session;
	private $_token;
	
	public function __construct($timeout = 1800)
	{
		$this->_timeout = $timeout;
		$this->_session = new Cr_Session('_CSRF');
	}

	public function randomString($length = 32)
	{
		$res = '';
		for($i = 0; $i < $length; $i++)
			$res .= substr(str_shuffle(uniqid()), mt_rand(0, 12), 1);
		
		return $res;
	}
	
	private function _initializeToken()
	{
		if(empty($this->_token))
			$this->_token = $this->generateToken();
	}
	
	private function _generateHash()
	{
		$vars = $this->_session->getList();
		
		return sha1(implode('', $vars));
	}
	
	public function generateToken()
	{
		$this->_session
			->set('time', time())
			->set('salt', $this->randomString())
			->set('session_id', $this->_session->getId())
			->set('ip', Cr_Request::getIp());
			
		$hash = $this->_generateHash();
		
		return base64_encode($hash);
	}
	
	public function getFormInput()
	{
		$this->_initializeToken();
		return '<input type="hidden" name="_CSRF" value="' . $this->_token . '" />';
	}
	
	public function getMetaTag()
	{
		$this->_initializeToken();
		return '<meta name="_CSRF" content="' . $this->_token . '" />';
	}
	
	protected function _checkTime()
	{
		if(is_null($this->_timeout))
			return true;
		
		return ($_SERVER['REQUEST_TIME'] - $this->_session->time) < $this->_timeout;
	}
	
	public function checkToken()
	{
		if(isset($this->_session) && $this->_checkTime())
		{
			$token_exists = isset($_REQUEST['_CSRF']) || isset($_SERVER['HTTP_X_CSRF_TOKEN']);
			if($token_exists)
			{
				$token_hash = isset($_REQUEST['_CSRF']) ? $_REQUEST['_CSRF'] : $_SERVER['HTTP_X_CSRF_TOKEN'];
				$token_hash = base64_decode($token_hash);
				unset($_POST['_CSRF'], $_GET['_CSRF'], $_REQUEST['_CSRF']);
				
				return $token_hash === $this->_generateHash();
			}
		}
		
		return false;
	}
	
}