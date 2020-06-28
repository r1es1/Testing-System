<?php

class App
{
  static $appname;

  public function __construct()
  {
    App::$appname = null;
  }

  public static function set($new_appname = null)
  {
    App::$appname = $new_appname;
  }

  public static function get()
  {
    return App::$appname;
  }

  static public function &settings($key = null)
  {
    static $settings = null;

    if( is_null($settings) )
    {
      $settings = require BASE_DIR . '/settings.php';
    }

    if( is_null($key) )
    {
      return $settings;
    }
    else {
      return $settings[$key];
    }
  }

}