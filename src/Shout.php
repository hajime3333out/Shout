<?php

/**
 * Class Shout
 * Animated Gif creator with large text
 */
class Shout
{
    private static $me;

    private $output;

    function __construct( ) {
        $output = new Imagick();
        $output->setformat('gif');
        
    }



} 