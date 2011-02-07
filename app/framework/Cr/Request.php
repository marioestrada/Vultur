<?php

/*
	Class: Cr_Request
	Manages and access HTTP request information.
*/
class Cr_Request
{

	/* 
		Function: getAppRoute
			Return the current internal (redirect or real) route for the application to work with.
			
		Returns:
			*String* containing internal route.			
	*/
	static function getAppRoute()
	{
		return isset($GLOBALS['CR']['APP_URL']) ? $GLOBALS['CR']['APP_URL'] :
					isset($_SERVER['REDIRECT_URL']) && !empty($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] :
						$_SERVER['REQUEST_URI'];
	}
	
	/* 
		Function: getRoute
			Return the current route for the application to work with.
			
		Returns:
			*String* containing route.			
	*/
	static function getRoute()
	{
		if(isset($GLOBALS['CR']['route']))
			return $GLOBALS['CR']['route'];
		
		$cr_config = cr_config();
		$url_path = self::getAppRoute();
		$qmark_position = strpos($url_path, '?');
		
		$route = $qmark_position === false ? $url_path : substr($url_path, 0, $qmark_position);
		
		$GLOBALS['CR']['route'] = substr_replace($route, '', 0, strlen($cr_config['app_path']));
		
		return $GLOBALS['CR']['route'];
	}
	
	/* 
		Function: getUrl 
			Return the current _URL_.
			
		Returns:
			*String* containing the current _URL_.
	*/
	static function getUrl()
	{
		return self::getBaseUrl() . self::getAppRoute();
	}
	
	/* 
		Function: getInternalUrl 
			Return the internal/redirected _URL_.
			
		Returns:
			*String* containing the internal/redirected _URL_.
	*/
	static function getInternalUrl()
	{
		return self::getBaseUrl() . '/' . self::getRoute();
	}
	
	/* 
		Function: getAppUrl 
			Return the current internal _URL_.
			
		Returns:
			*String* containing the current internal _URL_.
	*/	
	static function getAppUrl() 
	{
		$cr_config = cr_config();
		
		return self::getBaseUrl() . $cr_config['app_path'];
	}
	
	static function getBaseUrl()
	{
		$url = isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on" ? 'https://' : 'http://';
		
		if($_SERVER["SERVER_PORT"] != "80")
		{
			$url .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"];
		}else{
			$url .= $_SERVER["SERVER_NAME"];
		}
		
		return $url;
	}

	/* 
		Function: getListFromUrl 
			Return an *array* with variables from the _URL_ request.
			
		Returns:
			*Array* containing a list of all the extra parameters on the _URL_.
			
		See Also:
			<getVarsFromUrl>
	*/
	static function getListFromUrl()
	{	
		$vars = array();
		
		$cr_route = self::getRoute();
		
		$parts = explode('/', $cr_route);
		
		if(!empty($parts[2]))
		{
			unset($parts[0], $parts[1]);
			$parts = array_values($parts);
			
			foreach($parts as $value)
			{
				if(!empty($value))
					$vars[] = $value;
			}
		}
		
		return $vars;
	}

	/* 
		Function: getVarsFromUrl 
			Return an *array* with a key-value pair formed from the _URL_ request.
			
		Returns:
			*Array* containing a key-value pair formed from the _URL_ request.
			
		See Also:
			<getVarsUrl>
	*/
	static function getVarsFromUrl()
	{	
		$vars = array();
		
		$cr_route = self::getRoute();
		
		$parts = explode('/', $cr_route);
		
		if(!empty($parts[2]))
		{
			unset($parts[0], $parts[1]);
			$parts = array_values($parts);
			
			$total = count($parts);
			for($i = 0; $i < $total; $i += 2)
			{
				if(!empty($parts[$i]))
					$vars[$parts[$i]] = isset($parts[$i + 1]) ? addslashes($parts[$i + 1]) : null;
			}
		}
		
		return $vars;
	}
	
	static function getVars()
	{
		return self::getVarsFromUrl();
	}
	
	/* 
		Function: getVarsUrl
			Returns the _URI PATH_ formed with the current _URL_ variables.
			
		Returns:
			*String* with the _URI PATH_ with the proper _URL_ variables.
			
		See Also:
			<getVarsFromUrl>
	*/
	static function getVarsUrl()
	{
		$vars = self::getVarsFromUrl();
		
		$url = '';
		foreach($vars as $i => $valor)
		{
			$url = $i . '/' . $valor . '/';
		}
		return $url;
	}
	
	/* 
		Function:  getController
			Return the current requested _controller_.
			
		Returns:
			*String* with the name of the _controller_.
			
		See Also:
			<getAction>
	*/
	static function getController()
	{
		$cr_route = self::getRoute();
		
		$parts = explode('/', $cr_route);
		
		$controller = isset($parts[0]) && !empty($parts[0]) ? $parts[0] : 'index';
		
		return $controller;
	}
	
	/* 
		Function: getAction 
			Returns the current requested _action_.
		
		Parameters:
			include_extension - *Optional* *Boolean* Determines if it should include the _action extension_. 
			
		Returns:
			*String* with the name of the _action_.
			
		See Also:
			<getController>
	*/
	static function getAction($include_extension = false)
	{
		$cr_route = self::getRoute();
		
		$parts = explode('/', $cr_route);
		$action = isset($parts[1]) && !empty($parts[1]) ? $parts[1] : 'index';
		
		if(!$include_extension)
		{
			$parts = explode('.', $action);
			$action = $parts[0];
		}
		
		return $action;
	}
	
	static function getActionExtension()
	{
		return strtolower(substr(strrchr(self::getAction(true),"."), 1));
	}
	
	/*
		Function: isAjax
		Checks if the request was made using Ajax.
		
		Returns:
			A *boolean* representing the result.
			
		See Also:
			<isJson>, <isXml>
	*/
	static function isAjax()
	{
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
	}
	
	/*
		Function: isJson
		Checks if the request was made with '.json' extension.
		
		Returns:
			A *boolean* representing the result.
		
		See Also:
			<isXml>, <isAjax>
	*/
	static function isJson()
	{
		return self::getActionExtension() === 'json';
	}
	
	/*
		Function: isXml
		Checks if the request was made with '.xml' extension.
		
		Returns:
			A *boolean* representing the result.
		
		See Also:
			<isJson>, <isAjax>
	*/
	static function isXml()
	{
		return self::getActionExtension() === 'xml';
	}
	
	/*
		Function: isPost
		Checks if the request was made using _POST_.
		
		Returns:
			A *boolean* representing the result.
		
		See Also:
			<isGet>, <isPut>, <isDelete>
	*/
	static function isPost()
	{
		return $_SERVER['REQUEST_METHOD'] === 'POST';
	}
	
	/*
		Function: isGet
		Checks if the request was made using _GET_.
		
		Returns:
			A *boolean* representing the result.
		
		See Also:
			<isPost>, <isPut>, <isDelete>
	*/
	static function isGet()
	{
		return $_SERVER['REQUEST_METHOD'] === 'GET';
	}
	
	/*
		Function: isPut
		Checks if the request was made using _PUT_.
		
		Returns:
			A *boolean* representing the result.
		
		See Also:
			<isPost>, <isGet>, <isDelete>
	*/
	static function isPut()
	{
		return $_SERVER['REQUEST_METHOD'] === 'PUT';
	}
	
	/*
		Function: isDelete
		Checks if the request was made using _DELETE_.
		
		Returns:
			A *boolean* representing the result.
		
		See Also:
			<isPost>, <isPut>, <isGet>
	*/
	static function isDelete()
	{
		return $_SERVER['REQUEST_METHOD'] === 'DELETE';
	}
	
	/*
		Function: getReferer
		Get the url referer.
		
		Returns:
			A *string* with the referer _URL_ or *false* if there's no referer.
	*/
	static function getReferer()
	{
		return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : false;
	}
	
	static function getIp()
	{
		return isset($_SERVER["HTTP_X_FORWARDED_FOR"]) ? $_SERVER["HTTP_X_FORWARDED_FOR"] : $_SERVER["REMOTE_ADDR"];
	}
	
}