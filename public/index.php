<?php

define('__APP__', __DIR__ . '/..');

require_once( __APP__ . "/config/app.php" );

function __autoload($class_name) {
    include __APP__ . "/src/" . $class_name . '.php';
}



Router::action('GET', '/hello', function(){
    echo "heeh";
});
