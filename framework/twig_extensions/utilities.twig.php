<?php
namespace Twig_Extensions;

class Utilities_Twig_Extension extends \Twig_Extension {

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('ceil', array($this, 'do_ceil')),
            new \Twig_SimpleFilter('normalize_stamp', array($this, 'do_normalize_stamp')),
            new \Twig_SimpleFilter('humanize_size', array($this, 'do_humanize_size')),
            new \Twig_SimpleFilter('or_zero', array($this, 'do_or_zero')),
        );
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('form_subcats', array($this, 'do_form_subcats')),
            new \Twig_SimpleFunction('build_crumbs', array($this, 'do_build_crumbs'))
        );
    }

    protected function build_crumbs_dig($cat_id, $categories)
    {
        if( !$cat_id )
        {
            return false;
        }

        foreach( $categories as $cat )
        {
            if( $cat['id'] == $cat_id )
            {
                return $cat;
            }
        }

        return false;
    }

    public function do_build_crumbs($cat_id, $categories)
    {
        $out = array();

        while( $crumb = $this->build_crumbs_dig($cat_id, $categories) )
        {
            $out[] = $crumb;
            $cat_id = $crumb['parent_id'];
        }

        $out = array_reverse($out);
        return $out;
    }

    public function do_or_zero($num)
    {
        if( $num > 0 )
            return $num;
        else
            return 0;
    }

    public function do_form_subcats($parent_id, $categories)
    {
        $out = array();

        foreach( $categories as $cat )
        {
            if( $cat['parent_id'] == $parent_id )
            {
                $out[] = $cat;
            }
        }

        return $out;
    }

    public function do_humanize_size($size, $in = 'kb', $precision = 0)
    {
        if( $in == 'kb' )
        {
            // kb to bytes
            if( $size >= 1024 )
            {
                // kb to mb
                $bytes = $size * 1024;
                $size = $size / 1024;

                if( $size >= 1024 )
                {
                    // mb to gb
                    $size = $size / 1024;
                    $bytes = $size * 1024 * 1024 * 1024;

                    if( $size >= 1024 )
                    {
                        // gb to tb
                    $size = $size / 1024;
                    $bytes = $size * 1024 * 1024 * 1024 * 1024;
                    }
                }
            }
        }

        if ($bytes >= 1073741824)
        {
            $bytes = number_format($bytes / 1073741824, $precision) . ' GB';
        }
        elseif ($bytes >= 1048576)
        {
            $bytes = number_format($bytes / 1048576, $precision) . ' MB';
        }
        elseif ($bytes >= 1024)
        {
            $bytes = number_format($bytes / 1024, $precision) . ' KB';
        }
        elseif ($size > 1)
        {
            $bytes = $size . ' байт';
        }
        elseif ($size == 1)
        {
            $bytes = '1 байт';
        }
        else
        {
            return false;
        }

        return $bytes;
    }

    public function do_ceil($value)
    {
        return ceil($value);
    }

    public function do_normalize_stamp($value)
    {
        $hours = sprintf('[%02d]', intval($value / 3600));
        $minutes = sprintf('[%02d]', intval($value % 3600) / 60);

        return $hours . ':' . sprintf('%02s', $minutes);
    }

    public function getName()
    {
        return 'howdy_utilities';
    }

}