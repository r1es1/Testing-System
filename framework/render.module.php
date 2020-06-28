<?php

function render($view, $vars = array()) {

  if(defined("API_MODE")) return $vars;

  // Create loader
  $loader = new Twig_Loader_Filesystem( TEMPLATES_DIR );

  // Extend paths
  $loader->addPath( TEMPLATES_DIR . '/' . ACTIVE_APP );

  // Create environment
  $twig = new Twig_Environment($loader, array(
    // 'cache' => TWIG_CACHE_DIR,
  ));

  // Connect twig extensions
  $extensions = glob( TWIG_EXTENSIONS_DIR . '/*.twig.php' );
  foreach( $extensions as $extension ) {
      require $extension;
      $extension_class_name = '\\Twig_Extensions\\' . ucfirst(strtolower(str_replace('.twig.php', '', basename($extension)))) . '_Twig_Extension';
      $twig->addExtension( new $extension_class_name() );
  }

  $vars['app']['settings'] = require BASE_DIR . '/settings.php';
  $vars['is_admin'] = isset($_SESSION['admin']) && $_SESSION['admin'];
  $vars['user'] = \Users\get();

  // Render template
  $template = $twig->loadTemplate( $view . '.html' );
  $template->display( $vars );

}

function render_url($view, $vars = array()) {
  return array(
    'view' => $view,
    'vars' => $vars);
}

function redirect($where)
{
  header('Location: ' . $where);
  exit();
}