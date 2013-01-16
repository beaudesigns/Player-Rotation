<?php

// Define the constants we need to get going.
define('NL', "\r\n");
define('DS', DIRECTORY_SEPARATOR);
define('PATH_ROOT', dirname(__FILE__));
define('CONTROLLER_DIR', PATH_ROOT . DS . 'controllers' . DS);
define('CONTROLLER_EXT', '.php');
define('VIEW_DIR', PATH_ROOT . DS . 'views' . DS);
define('VIEW_EXT', '.html');

// Require all of our necessary libraries.
require_once(PATH_ROOT . DS . 'classes' . DS . 'core' . DS . 'core.php');
require_once(PATH_ROOT . DS . 'classes' . DS . 'core' . DS . 'controller.php');
require_once(PATH_ROOT . DS . 'classes' . DS . 'core' . DS . 'user.php');
require_once(PATH_ROOT . DS . 'classes' . DS . 'core' . DS . 'database.php');

// Start our session
if(!isset($_SESSION))
{
	session_start();
}

// Set up the Application specific settings.
$Configuration['Application']['Title'] = 'Twinkies Player Rotation';
$Configuration['Application']['Locale'] = 'en-US';
$Configuration['Application']['Controller.Default'] = 'index';
$Configuration['Application']['Date.Format'] = 'm/d/Y';
$Configuration['Application']['DateTime.Format'] = 'm/d/Y h:i a';

// Instantiate needed classes
$CORE = new Core($Configuration);