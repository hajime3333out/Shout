<?php

use Framework\Route;
use Framework\Response;
use App\Shout;
use \Framework\Input;

mb_internal_encoding('UTF-8');

Route::get('/hello', function(){
    $font = Input::get("font", 1);
    $string = Input::get('q', '你好');
    return (new Response())
        ->setContentType('image/gif')
        ->setContent(
            (new Shout())
                ->drawString($string, $font )
                ->finalise() );
});

Route::get('/', function(){
    return (new Response())
        ->setContentType('image/gif')
        ->setContent(Shout::emptyImage());
});
