<?php

return array(
    'debug' => true,

    'apps' => array(
        'tickets',
        'users',
        'admin',
        'api'
    ),

    'tickets' => array(
        'maximum_check_attempts' => 3,
        'repass_delay' => 600, // seconds
    ),

    'database' => array(
        'connection_string' => 'mysql:host=127.0.0.1;dbname=test',
        'name' => 'test',
        'host' => '127.0.0.1',
        'username' => 'root',
        'password' => ''
    )
);