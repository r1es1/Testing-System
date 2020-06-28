<?php

class Import {

  static public function app($name, $what = 'controller')
  {
    require_once APPS_DIR . '/' . $name . '/' . $what . '.php';
  }

  static public function interface($app_name, $version, $interface)
  {
    $path = APPS_DIR . '/' . $app_name . '/interfaces/v' . $version . '/' . strtolower($interface) . '.interface.php';

    if( file_exists($path) )
    {
      require_once $path;
      return true;
    } else
    {
      return false;
    }
  }

      static public function private_module($module_name)
    {
        require dirname(__FILE__) . '/private/' . $module_name . '.php';
    }
  
  static public function interface_exists($app_name, $version, $interface)
  {
    $path = APPS_DIR . '/' . $app_name . '/interfaces/v' . $version . '/' . $interface . '.interface.php';

    return file_exists($path);
  }

  static public function model($app_name)
  {
    $path = APPS_DIR . '/' . $app_name . '/model.php';

    if( file_exists($path) )
    {
      require_once $path;
      return true;
    } else
    {
      return false;
    }
  }

}