<?php

namespace App\Controller;
use Framework\Config;
use Framework\Response;
use Framework\Input;

class SampleController {
    function hello( ) {
        return (new Response())
            ->setContent(Config::get('app.greeting') . Input::get('name', 'guest'))
            ->setContentType('text/plain')
            ->setCode(200);
    }
}
