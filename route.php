<?php

use Framework\Route;
use Framework\Response;
use App\Shout;
use \Framework\Input;

mb_internal_encoding('UTF-8');

Route::get('/image', function(){
    $style = Input::get("style", 1);
    $string = Input::get('q', '你好');
    return (new Response())
        ->setContentType('image/gif')
        ->setContent(
            (new Shout())
                ->drawString($string, $style )
                ->finalise() );
});


Route::get('display', function(){
$string = Input::get('q', null);
if ( ! $string ) $string = "%E6%83%B3%E7%9D%A1%E4%BA%86";
else $string = urlencode($string);

    $default = <<<EOF
{
	"base":{
		"font":5,
		"frame":[[0,0],[1,1]],
		"delay":50
	},
	"layers":[
		{
			"scale":0.95,
			"rotate": [-3, 0.5, 0.5],
                        "stroke-width": 1, "stroke-color":"#ff0000",
			"color":"#ffffff"
		},
		{
			"scale":1,
                        "rotate": [3, 0.5, 0.5],
                        "stroke-width": 1, "stroke-color":"#ff0000",
			"color":"#ffffff"
		}
	]
}
EOF;


    $style = Input::get('style', $default);

$html = <<<EOT
<body style='text-align: left;'>
<table><tr>
<td>
<div>
<form method=get action="display">

<textarea name="style" cols="60" rows="10">
$style
</textarea>
<br /><br />
<input type="text" name="q" size=30 /><br />
<input type="submit" value="go" />
</form>
</div>
</td>
<td>
<div style='text-align: center; display:table-cell; background-position:center middle; vertical-align: middle; width:240px; height: 320px; background-image:url(http://d3gbrb95pfitbz.cloudfront.net/message_photo/1436241183_5447329.jpeg); margin:0px; padding:0px;background-size: cover;'>

EOT;

    $style = urlencode($style);
$html .= "<img width=240px src='image?style=$style&q=$string' /></div></td></tr></table></body>";
    return (new Response())->setContentType('text/html')->setContent($html);
});

require 'route_editor.php';

Route::get('/', function(){
    return (new Response())
        ->setContentType('image/gif')
        ->setContent(Shout::emptyImage());
});
