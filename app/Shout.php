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
     * Setting will be imported from /config/image.php
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

    function drawString( $text, $font = 0 ) {

        $text_array = $this->divideString($text);

        for ( $i = 0; $i < count($this->layers) && $i < count($text_array); $i++ ) {

            $drawer = (new ImagickDraw());
            $drawer->setFont( __APP__ . $this->setting['font_base_dir']
                . '/' . $this->setting['font'][$font]);
            $drawer->setfillcolor($this->draw_color);

            list($size, $max_width, $height, $y) =
                $this->getProperFontSize($text_array, $drawer);

            $drawer->setfontsize($size);

print_r(array("size"=>$size, 'text'=>$text_array));die;

            foreach( $text_array as $i => $text ) {
                $metrics = $this->layers[0]
                    ->queryFontMetrics($drawer, $text);

                $width = $metrics['x2']-$metrics['x1'];
                $x = (int)(($this->setting['width'] - $width)/2 - $metrics['x1']);

                $drawer->annotation( 0, $y + $height * $i, $text );
                $this->layers[$i]->drawImage($drawer);
            }


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

    private function divideString( $text ) {
        $return = array();
        if ( mb_strlen($text) > 0 ) {
            $k = ceil(sqrt(mb_strlen($text)));
            for ($i = 0; $i < mb_strlen($text); $i+=$k ) {
                $return[(int)($i/$k)] = mb_substr($text, $i, $k);
            }
        }
        return $return;
    }

    private function getProperFontSize( $texts, ImagickDraw $drawer ) {

        $this_size = $this->setting['width'];
        $this_height = 0;
        $this_width = 0;
        $this_y = 0;

        foreach( $texts as $text ) {

            for ( $size = $this_size;
                $size > 20;
                $size = (int) ($size * 0.9 ) ) {

                $drawer->setfontsize($size);

                $metrics = $this->layers[0]
                    ->queryFontMetrics($drawer, $text);

                $height = ($metrics['y2']-$metrics['y1']) * 1.1;
                $width = $metrics['x2']-$metrics['x1'];
                $y = (int)(($this->setting['width'] -
                            $height * count($texts))/2) - $metrics['ascender'];

                if ( $width < $this->setting['width'] * 0.90 && $y > 0 ) {
                    $this_size = $size;
                    $this_height = $height;
                    $this_width = $width;
                    $this_y = $y;
                    break;
                }
            }
        }

        return array(
            'size' => $this_size,
            'width'=>$this_width,
            'height'=> $this_height,
            'y' => $this_y
        );
    }
} 
