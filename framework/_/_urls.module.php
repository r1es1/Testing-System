<?php

function url($pattern, $view, $alias)
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

    $rgp[] = preg_replace_callback('#\{[A-z0-9]+\}#', function($match) {
        return '(.+?)';
    }, $pattern);

    $rgp[] = '/*$#i';
    return join('', $rgp);
}