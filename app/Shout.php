<?php

namespace App;

use Framework\Config;
use Imagick;
use ImagickPixel;
use ImagickDraw;

class Shout {
    private $layers;

    private $base_color;
    private $draw_color;

    private $setting;

    /**
     * Constructor
     * Specify the number of layers
     * Setting will be imported from /config/app.php
     *
     * @param int $layer_number
     * @param string $setting
     */
    function __construct( $layer_number = 2, $setting = 'default' ) {

        $this->setting = Config::get('image.' . $setting );

        $this->base_color = new ImagickPixel( $this->setting['bg_color'] );
        $this->draw_color = new ImagickPixel( $this->setting['color'] );

        for ( $i = 0; $i < $layer_number; $i++ ) {
            $this->layers[$i] = new Imagick();

            $this->layers[$i]->newImage(
                $this->setting['width'],
                $this->setting['height'],
                $this->base_color );
            $this->layers[$i]->setImageFormat('gif');
        }
    }

    function drawSample( $letters ) {

        for ( $i = 0; $i < count($this->layers) && $i < count($letters); $i++ ) {
            $drawer = (new ImagickDraw());
            $drawer->setfontsize(50);
            $drawer->setFont( __APP__ . "/fonts/1new.ttf");
            $drawer->setfillcolor($this->draw_color);
            $metrics = $this->layers[$i]
                ->queryFontMetrics($drawer, $letters[$i]);
            $drawer->annotation( 0, $metrics['ascender'], $letters[$i] );
            $this->layers[$i]->drawImage($drawer);

        }
        return $this;
    }

    /**
     * @return Binary of image; good to print
     */

    function finalise( ) {
        $output = new Imagick();
        $output->setFormat("gif");

        foreach( $this->layers as $layer ) {
            $layer->setImageDelay($this->setting['delay']);
            $output->addImage($layer);
        }
        return $output->getImagesBlob();
    }

    /**
     *
     */
    static function emptyImage() {
        $output = new Imagick();
        $output->setFormat("gif");
        $output->newImage(
            Config::get('image.default.width'),
            Config::get('image.default.height'),
            new ImagickPixel(Config::get('image.default.bg_color')));

        return $output->getimagesblob();

    }
} 
