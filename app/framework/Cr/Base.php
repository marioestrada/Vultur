<?php
require_once('Cr/Flash.php');
require_once('Cr/Request.php');

/* 
	Class: Cr_Base
	Base class for dispatching the proper _Controllers_ and _Actions_.
*/
class Cr_Base
{
	/* 
		Function: dispatch 
			Dispatches the proper _Controller_ and _Action_.
	*/
	static public function dispatch()
	{
		Cr_Flash::start();
		
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