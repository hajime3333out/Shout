<?php

use Framework\Route;
use Framework\Response;
use App\Shout;
use \Framework\Input;

mb_internal_encoding('UTF-8');

Route::get('/image', function(){
    $font = Input::get("f", 1);
    $string = Input::get('q', '你好');
    return (new Response())
        ->setContentType('image/gif')
        ->setContent(
            (new Shout())
                ->drawString($string, $font )
                ->finalise() );
});

Route::get('display', function(){
$string = Input::get('q', null);
if ( ! $string ) $string = "%E6%83%B3%E7%9D%A1%E4%BA%86";
else $string = urlencode($string);

$html = <<<EOT
<body style='text-align: left;'>
<div>
<form method=get action="display">
<input type="text" name="q" size=30 />
<input type="submit" value="go" />
</form>
</div>
<div style='text-align: center; display:table-cell; background-position:center middle; vertical-align: middle; width:240px; height: 320px; background-image:url(http://d3gbrb95pfitbz.cloudfront.net/message_photo/1436241183_5447329.jpeg); margin:0px; padding:0px;background-size: cover;'>
EOT;

$html .= "<img width=240px src='image?f=1&q=$string' /></div></body>";
    return (new Response())->setContentType('text/html')->setContent($html);
});

require 'route_editor.php';

Route::get('/', function(){
    return (new Response())
        ->setContentType('image/gif')
        ->setContent(Shout::emptyImage());
});
