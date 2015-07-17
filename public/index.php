<?php

define('__APP__', __DIR__ . '/..');

function __autoload($class_name) {
    include __APP__ . "/src/" . $class_name . '.php';
}


require_once( __APP__ . "/src/" . route.php);