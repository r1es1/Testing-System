<?php
namespace Twig_Extensions;

class Declension_Twig_Extension extends \Twig_Extension {

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('declension', array($this, 'do_declension'))
        );
    }

    public function do_declension($number, $decs, $ignore_number = false)
    {
      $cases = array (2, 0, 1, 1, 1, 2);

      if( $ignore_number )
      {
        return $decs[ ($number%100>4 && $number%100<20)? 2: $cases[min($number%10, 5)] ];
      } else 
      {
        return $number.' '.$decs[ ($number%100>4 && $number%100<20)? 2: $cases[min($number%10, 5)] ];
      }
    }

    public function getName()
    {
        return 'howdy_declension';
    }

}