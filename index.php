<?php

require_once('app/framework/Cr/Loader.php');
$loader = new Cr_Loader();

require('app/bootstrap.php');

Cr_Base::dispatch();