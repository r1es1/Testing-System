<?php

session_start();

define('IS_DEV', true);
if( IS_DEV )
{
    // dev
    error_reporting(E_ERROR | E_WARNING | E_PARSE);
    chdir('C:/xampp\htdocs/');
} else
{
    // production
    error_reporting(0);
    chdir('/home/user/domain.net/www');
}

define('BASE_DIR', getcwd());
define('APPS_DIR', BASE_DIR . '/apps');
define('FRAMEWORK_DIR', BASE_DIR . '/framework');
define('STATIC_DIR', BASE_DIR . '/static');

// Start the fun
require './framework/starter.php';

if( !defined('BLOCK_RENDER') )
{
	// Render
	$app = new HowdyEngine( require('settings.php') );
}