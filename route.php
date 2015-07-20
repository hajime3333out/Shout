<?php

use Framework\Route;
use Framework\Response;
use App\Shout;


mb_internal_encoding('UTF-8');

Route::get('/hello', function(){
    return (new Response())
        ->setContentType('image/gif')
        ->setContent(
            (new Shout())
                ->drawSample(array("你","好"))
                ->finalise() );
});

Route::get('/', function(){
    return (new Response())
        ->setContentType('image/gif')
        ->setContent(Shout::emptyImage());
});