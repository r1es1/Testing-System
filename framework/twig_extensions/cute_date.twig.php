<?php
namespace Twig_Extensions;

class Cute_date_Twig_Extension extends \Twig_Extension {

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('cute_date', array($this, 'do_cute_date'))
        );
    }

    public function do_cute_date($date, $today_str, $yesterday_str)
    {
        $today = date('d.m.Y', time());
        $yesterday = date('d.m.Y', time() - 86400);
        $dbDate = date('d.m.Y', $date);
        $dbTime = date('G:i', $date);

        switch ($dbDate)
        {
          case $today : $output = $today_str . $dbTime; break;
          case $yesterday : $output = $yesterday_str . $dbTime; break;
          default : $output = $dbDate;
        }
        return $output;
    }

    public function getName()
    {
        return 'howdy_cute_date';
    }

}