<?php
require_once('Cr/Controller.php');

class ErrorController extends Cr_Controller
{
	public function indexAction()
	{
		$this->view->exception = isset($GLOBALS['Cr_Exception']) ? $GLOBALS['Cr_Exception'] : null;
	}
}