<?php

class SessionManager {
    
    public function __construct() {
        //empty one
    }

    static public function store($key, $value) {
        $_SESSION[$key]=$value;
        return true;
    }

    static public function stored($key) {
        return isset($_SESSION[$key]);
    }

    static public function read($key) {
        return $_SESSION[$key];
    }

    static public function remove($key) {
        unset($_SESSION[$key]);
    }

}