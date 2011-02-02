<?php
require_once('Cr/Flash.php');
require_once('Cr/Request.php');

/* 
	Class: Cr_Base
	Base class for dispatching the proper _Controllers_ and _Actions_.
*/
class Cr_Base
{
	private $_routes = array();
	private $_routes_paths = array();
	private $_routes_vars = array();
	private $_routes_handlers = array();
	private $_routes_count = 0;
	
	public function __construct($routes = null)
	{
		$this->_routes = $routes;
		
		$this->_saveRoutes();
		
		return $this;
	}
	
	private function _saveRoutes()
	{
		if(is_array($this->_routes))
		{
			foreach($this->_routes as $path => $handler)
			{
				$this->addRoute($path, $handler);
			}
		}
	}
	
	public function addRoute($path, $handler)
	{	
		$this->_routes_handlers[] = $handler;
		
		$path_prepared = preg_replace("/\/$/", "/?", $path);
		$path_prepared = str_replace('/', '\/', $path_prepared) . '$';
		$this->_routes_paths[] = preg_replace("/:([a-z]+[a-z\_\-0-9]*)/i", "([^\/]*)", $path_prepared);
		preg_match_all("/:([a-z]+[a-z\_\-0-9]*)/i", $path, $matches);
		
		if(!empty($matches))
		{
			$matches = $matches[1];
			$max = count($matches);
			$this->_routes_vars[$this->_routes_count] = '';
			for($j = 0; $j < $max; $j++)
			{
				$this->_routes_vars[$this->_routes_count] .= $matches[$j] . '/{' . ($j + 1) . '}/';		
			}
		}
		
		$this->_routes_count++;
		return $this;
	}
	
	private function _matchRoute()
	{
		$url = Cr_Request::getRoute();
		
		foreach($this->_routes_paths as $i => $path)
		{
			preg_match("/{$path}/", $url, $matches);
			if(!empty($matches))
			{
				$tokens = $tokens_replace = array();
				foreach($matches as $j => $value)
				{
					$tokens[] = "{{$j}}";
					$tokens_replace[] = $value;
				}    
				$redirect_url = $this->_routes_handlers[$i] . str_replace($tokens, $tokens_replace, $this->_routes_vars[$i]);
								
				$GLOBALS['CR']['route'] = $GLOBALS['CR']['APP_URL'] = $redirect_url;
				
				break;
			}
		}
	}
	
	public function start()
	{
		$this->_matchRoute();
		$this->dispatch();
	}
	
	/* 
		Function: dispatch 
			Dispatches the proper _Controller_ and _Action_.
	*/
	static public function dispatch()
	{
		$controller = ucwords(Cr_Request::getController()) . 'Controller';
		$action = Cr_Request::GetAction() . 'Action';
		
		$clase = str_replace('_', '/', $controller) . '.php';
		
		try
		{
			if(!is_file('app/controllers/' . $clase))
			{
				throw new Exception("Controller '$clase' does not exist.");
			}
			
			require_once('app/controllers/' . $clase);
			
			$control = new $controller($action);
			if($control->callView())
			{
				$control->showView();
			}
			$clear_flash = $control->clearFlash();
			unset($control);
			
		}catch(Exception $e){
			self::dispatchError('index', $e);
		}
		
		if(isset($clear_flash) && $clear_flash)
			Cr_Flash::clear();
	}
	
	public function __destruct()
	{
		
	}
	
	/* 
		Function: dispatchError 
			Dispatches to the error _Controller_ when needed.
	*/
	static function dispatchError($action = 'index', $e = null)
	{
		if(!empty($e)) 
			$GLOBALS['Cr_Exception'] = $e;
		
		require_once('app/controllers/ErrorController.php');
		$action = $action . 'Action';
		
		$control = new ErrorController($action);
		if($control->callView())
		{
			$control->showView();
		}
		unset($control);
	}
}