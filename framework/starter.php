<?php

// Require system classes
require( FRAMEWORK_DIR . '/core/system/base_controller.php' );
require( FRAMEWORK_DIR . '/core/system/controller.php' );

// Require composer
if( file_exists(FRAMEWORK_DIR . '/core/composer/vendor/autoload.php') ) {
    require FRAMEWORK_DIR . '/core/composer/vendor/autoload.php';
}

// Few constants
define( 'TWIG_CACHE_DIR', FRAMEWORK_DIR . '/cache' );
define( 'TEMPLATES_DIR', BASE_DIR . '/templates' );
define( 'MODULES_DIR', FRAMEWORK_DIR . '/modules' );
define( 'TWIG_EXTENSIONS_DIR', MODULES_DIR . '/twig_extensions' );

// Connect modules
$modules = glob( MODULES_DIR . '/*.module.php' );
foreach( $modules as $module ) {
    require $module;
}

// Connect functions
$settings = require( BASE_DIR . '/settings.php' );
$apps = $settings['apps'];
foreach( $apps as $app ) {
  if( file_exists( APPS_DIR . '/' . $app . '/functions.php' ))
  {
    require APPS_DIR . '/' . $app . '/functions.php';
  }
}

// Start the fun
require( FRAMEWORK_DIR . '/core/bootstrap.php' );