<?php
namespace Twig_Extensions;

class Urls_Twig_Extension extends \Twig_Extension {

    protected
        $placeholders;

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('url', array($this, 'url'))
        );
    }

    public function url($alias)
    {
        $this->placeholders = array_slice(func_get_args(), 1);
        $alias = explode('.', $alias);

        // Brake alias
        if( count($alias) > 1 )
        {
            $app_name = array_shift($alias);
            $alias_name = join('.', $alias);
        } else
        {
            $alias_name = join('.', $alias);
        }

        // Get iterable urls, based on alias type
        if( isset($app_name) )
        {
            $iterable_urls = require APPS_DIR . '/' . strtolower($app_name) . '/urls.php';
        } else
        {
            $settings = require BASE_DIR . '/settings.php';

            foreach( $settings['apps'] as $app )
            {
                $iterable_urls = require APPS_DIR . '/' . strtolower($app) . '/urls.php';
            }
        }

        // Return alias
        foreach( $iterable_urls as $iu )
        {
            if( $alias_name == $iu['alias'] )
            {
                return $this->convert_url($iu['pattern']);
            }
        }

    }

    public function convert_url($pattern)
    {
        return preg_replace_callback('#\{[A-z0-9]+\}#', array($this, 'convert_url_callback'), $pattern);
    }

    public function convert_url_callback($match)
    {
        return array_shift($this->placeholders);
    }

    public function getName()
    {
        return 'howdy_urls';
    }

}