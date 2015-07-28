<?php

$strokeColor = "#ffffff";
$fillColor = "#0000ff"; 
$backgroundColor = "#000000";
$fillModifiedColor = "#00ffff";

$x1 = 100; $x2 = 400;
$y1 = 230; $y2 = 280;

    $draw = new \ImagickDraw();
    $draw->setStrokeColor($strokeColor);
    $draw->setStrokeOpacity(1);
    $draw->setFillColor($fillColor);
    $draw->rectangle($x1, $y1, $x2, $y2);
    $draw->setFillColor($fillModifiedColor);
    $draw->rotate(25);
    list($rx1, $ry1) = get($x1, $y1, 25);
    list( $rx2, $ry2) = get($x2, $y2, 25);

    $draw->rectangle($rx1, $ry1, $rx2, $ry2);
 
    $image = new \Imagick();
    $image->newImage(500, 500, $backgroundColor);
    $image->setImageFormat("png");
    $image->drawImage($draw);
 
    header("Content-Type: image/png");
    echo $image->getImageBlob();

function get($x, $y, $r) {
        $rx = $x - 250 + cos(deg2rad($r)) * 250 + sin(deg2rad($r)) * 250;
        $ry = $y - 250 + cos(deg2rad($r)) * 250 - sin(deg2rad($r)) * 250;

	return array($rx, $ry);
}

