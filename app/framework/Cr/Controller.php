<?php
require_once('Cr/View.php');
require_once('Cr/Request.php');

abstract class Cr_Controller
{
	protected $action;
	protected $controller;
	protected $view;
	protected $call_view = true;
	protected $use_layout = true;
	protected $layout_name;
	protected $clear_flash = true;
	protected $_action_response = null;
	
	public function __construct($action, $layout_name = 'main')
	{
		$this->view = new Cr_View();
		$this->controller = get_class($this);
		
		$this->action = $action;
		$this->layout_name = $layout_name;
		
		if(Cr_Request::isAjax())
		{
			$this->use_layout = false;
		}
		
		$this->init();
		
		try
		{
			if(method_exists($this, $action) || method_exists($this, '__call'))
			{
				$this->_action_response = $this->$action();
			}else{
				throw new Exception("Action '{$action}' does not exists for controller '{$this->controller}'.");
			}
		}catch(Exception $e){
			$this->showError('index', $e);
		}
	}
	
	protected function init(){}
	
	protected function gotoController($controller, $action = "", $params = "", $code = 302)
	{	
		$param_url = "";
		if(is_array($params))
		{
			foreach($params as $indice => $valor)
			{
				$param_url .= $indice . '/' . $valor . '/';
			}	
		}
		$action = empty($action) ? '' : $action . '/';
		
		$url = Cr_Request::getAppUrl();
		$this->gotoUrl("{$url}{$controller}/{$action}{$param_url}", $code);
	}
	
	protected function gotoUrl($url, $code = 302, $code_message = '')
	{
		$this->call_view = false;
		$this->clear_flash = false;
				
		switch($code)
		{
			case 300:
				$code_message = 'Multiple Choices';
				break;
			case 301:
				$code_message = 'Moved Permanently';
				break;
			case 302:
				$code_message = 'Found';
				break;
			case 303:
				$code_message = 'See Other';
				break;
			case 304:
				$code_message = 'Not Modified';
				break;
			case 305:
				$code_message = 'Use Proxy';
				break;
			case 307:
				$code_message = 'Temporary Redirect';
				break;
		}
		
		Cr_Flash::clear();
		
		$server_protocol = $_SERVER['SERVER_PROTOCOL'];
		
		header("{$server_protocol} {$code} {$code_message}");
		header('Location: ' . $url);
		exit();
	}
	
	protected function setLayout($name)
	{
		$this->layout_name = $name;
	}
	
	public function showView($view = '', $call_view = false)
	{
		$this->call_view = $call_view;
		
		if(Cr_Request::isJson())
		{
			if(!is_null($this->_action_response))
				$this->respondJson($this->_action_response);
			else
				throw new Exception('No response for JSON request.');
			return;
		}else if($this->_action_response !== null){
			echo $this->_action_response;
			return;
		}
		
		$controller_dir = strtolower(str_replace('Controller', '', $this->controller));
		$view = empty($view) ? str_replace('Action', '', $this->action) : $view;
		
		$this->view->show($controller_dir . '/' . $view, $this->use_layout, $this->layout_name);
	}
	
	public function showError($view = 'index', $e = null)
	{
		$this->call_view = false;
		Cr_Base::dispatchError($view, $e);
	}
	
	protected function respondJson($data, $wrap_start = '', $wrap_end = '')
	{
		$this->call_view = false;
		
		header('Cache-Control: no-cache, must-revalidate');
		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		if(empty($wrap_start) && empty($wrap_end))
			header('Content-type: application/json');
		
		echo $wrap_start;
		echo json_encode($data);
		echo $wrap_end;
	}
	
	public function callView()
	{
		return $this->call_view;
	}
	
	public function clearFlash()
	{
		return $this->clear_flash;
	}
	
}