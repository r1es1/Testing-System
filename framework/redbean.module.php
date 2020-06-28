<?php

require dirname(__FILE__) . '/private/rb.php';

$settings = require BASE_DIR . '/settings.php';
R::setup( $settings['database']['connection_string'], $settings['database']['username'], $settings['database']['password'] );