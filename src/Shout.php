<?php

/**
 * Class Shout
 * Animated Gif creator with large text
 */
class Shout
{
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

        $this->setting = Config::get('app.' . $setting );

        $this->base_color = new ImagickPixel( $this->setting['bg_color'] );
        $this->draw_color = new ImagickPixel( $this->setting['color'] );

        for ( $i = 0; $i < $layer_number; $i++ ) {
            $this->layers[$i] = new Imagick();

            $this->layers[$i]->newImage(
                $this->setting['width'],
                $this->setting['height'],
                $this->base_color );


        }
        
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

} 