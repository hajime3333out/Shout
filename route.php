<?php

use Framework\Route;

Route::action('GET', '/welcome', function(){
    echo "welcome";
});

Route::action('GET', '/hello', 'SampleController@hello');
