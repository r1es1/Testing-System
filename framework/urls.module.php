<?php

function url($pattern, $view, $alias = false)
{
    return array(
        'pattern' => $pattern,
        'view' => $view,
        'alias' => $alias);
}

function convert_url($pattern)
{
    $rgp = array();
    $rgp[] = '#^';

    if( $pattern == '/{language}*' )
    {
        $rgp[] = '.*';
    } else
    {
        $rgp[] = preg_replace_callback('#\{[A-z0-9]+\}#', function($match) {
        return '([^/]+?)';
    }, $pattern);
    }

    if( substr($pattern, -1) == '/' )
    {
        $rgp[] = '*$#i';
    } else
    {
        $rgp[] = '/*$#i';
    }

    return join('', $rgp);
}

/**
 * Twig Urls Bridge Emulator.
 */
class Urls {

    static $placeholders = array();
    static $arguments = array();
    static $arguments_keys = array();

    static public function get($arguments, $ignore_hooks = false)
    {
        Urls::$placeholders = $arguments;

        // Define alias & app name
        if( isset($arguments['app']) )
        {
            $app_name = $arguments['app'];
            unset($arguments['app']);
        }

        if( isset($arguments['alias']) )
        {
            $alias_name = $arguments['alias'];
            unset($arguments['alias']);
        }

        if( isset($arguments['method']) )
        {
            $method_name = $arguments['method'];
            unset($arguments['method']);
        }

        // Get iterable urls, based on alias type
        $iterable_urls = array();
        if( isset($app_name) )
        {
            $iterable_urls = require APPS_DIR . '/' . strtolower($app_name) . '/urls.php';
        } else
        {
            $settings = require BASE_DIR . '/settings.php';

            foreach( $settings['apps'] as $app )
            {
                $iterable_urls = array_merge($iterable_urls, require APPS_DIR . '/' . strtolower($app) . '/urls.php');
            }
        }

        // Return alias
        foreach( $iterable_urls as $iu )
        {
            if( isset($alias_name) )
            {
                if( $alias_name == $iu['alias'] )
                {
                    return Urls::convert_url($iu['pattern'], $ignore_hooks);
                }
            } else if( isset($method_name) )
            {
                if( $method_name == $iu['view'] )
                {
                    return Urls::convert_url($iu['pattern'], $ignore_hooks);
                }
            } else
            {
                return false;
            }
        }

    }

    static public function convert_url($pattern, $ignore_hooks = false)
    {   
        $url = preg_replace_callback('#\{[A-z0-9]+\}#', array('Urls', 'convert_url_callback'), $pattern);
        if( !$ignore_hooks )
        {
            Hooks::apply('urls', $url);
        }
        return $url;
    }

    static public function convert_url_callback($match)
    {
        $key = str_replace('{', '', str_replace('}', '', $match['0']));
        $value = Urls::$placeholders[$key];
        unset(Urls::$placeholders[$key]);
        return $value;
    }

    static public function create_url_callback($match)
    {
        Urls::$arguments[str_replace('{', '', str_replace('}', '', $match['0']))] = array_shift(Urls::$arguments_keys);
        return '';
    }

    static public function create_arguments($pattern, $matches)
    {
        Urls::$arguments = array();
        Urls::$arguments_keys = array_slice($matches, 1);
        preg_replace_callback('#\{[A-z0-9]+\}#', array('Urls', 'create_url_callback'), $pattern);
        return Urls::$arguments;
    }

}