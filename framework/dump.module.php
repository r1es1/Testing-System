<?php

function dump($what, $exit = false)
{
  echo '<pre style="background-color: black;color: #2DE91B;">';
  print_r($what);
  echo '</pre>';

  if( $exit )
  {
    exit;
  }
}