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
    function __construct( $setting = 'default' ) {

        $this->setting = Config::get('image.' . $setting );

        $this->base_color = new ImagickPixel( $this->setting['bg_color'] );
        $this->draw_color = new ImagickPixel( $this->setting['color'] );

        $layer = new Imagick();
        $layer->newimage(
            $this->setting['width'],
            $this->setting['height'],
            $this->base_color
        );
        $layer->setimageformat('gif');

        $this->layers = array( $layer );

    }

    function drawString( $text, $def = 1 ) {

        $text_array = $this->divideString($text);
        $definition = json_decode(
            file_get_contents($this->setting['font_base_dir'] . "/$def.def")
        );

        $font = $definition->base->font;
        list($size, $max_width, $height, $y)
            = $this->getProperFontSize($text_array, $font);

        try {
            foreach( $definition->layers as $i => $commands ) {
		$commands = get_object_vars($commands);
                $layer = new Imagick();
                $layer->newimage(
                    $this->setting['width'],
                    $this->setting['height'],
                    $this->base_color
                );
                $layer->setimageformat('gif');

                $drawer = (new ImagickDraw());
                $drawer->setFont(  $this->setting['font_base_dir']
                    . '/' . $this->setting['font'][$font]);

                $drawer->setfillcolor($this->draw_color);
                $drawer->setfontsize($size);
                $dx = 0; $dy = 0;
                foreach($commands as $command => $value ) {
                    switch ( $command ) {
                        case 'color':
                            $drawer->setFillColor(
                                new ImagickPixel($value)
                            );
                            break;
                        case 'scale':
                            $drawer->setFontSize( (int)($value * $size)  );
                            break;
                        case "rotate":
                            $drawer->rotate($value[0]);
                            $cos = cos(deg2rad($value[0])); $sin = sin(deg2rad($value[0]));
                            $rx = $value[1] * $this->setting['width']; $ry = $value[2] * $this->setting['height'];
                            $dx = $rx - $cos * $rx - $sin * $ry;
                            $dy = $ry + $sin * $rx - $cos * $ry;
                            break;
                        case 'stroke-color':
                            $drawer->setStrokeColor(new ImagickPixel($value));
                            break;
                        case 'stroke-width':
                            $drawer->setStrokeWidth( $value );
                            break;

                    }
                }

                foreach( $text_array as $j => $text ) {
                    $metrics = $layer->queryFontMetrics($drawer, $text);

                    $width = $metrics['textWidth'];
                    $x = (int)(($this->setting['width'] - $width)/2 - $metrics['boundingBox']['x1']);

                    //print_r(array('width'=>$width, 'x'=>$x, 'y'=>$y));

                    $drawer->annotation( $x - $dx, $y - $dy + $height * $j, $text );
                    $layer->drawImage($drawer);

                }
                $this->layers[$i] = $layer;
            }
//header('Content-type: image/gif'); echo $this->layers[$i]->getImagesBlob();die;
            return $this;
        } catch ( Exception $e ) {
            print_r($e->getTrace()); die;
        }
    }

    /**
     * @return Binary of image; good to print
     */

    function finalise( ) {
        $output = new Imagick();
        $output->setFormat("gif");
//        $output->setImageDispose(3);
        foreach( $this->layers as $layer ) {
            $layer->setImageDelay($this->setting['delay']);
            $layer->setImageDispose(3);
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

    private function getProperFontSize( $texts, $font ) {

        $this_size = $this->setting['width'];
        $this_height = 0;
        $this_width = 0;
        $this_y = 0;
        $drawer = (new ImagickDraw());

        $drawer->setFont( $this->setting['font_base_dir']
            . '/' . $this->setting['font'][$font]);

        $drawer->setfillcolor($this->draw_color);

        foreach( $texts as $text ) {
            for ( $size = $this_size;
                $size > 20;
                $size = (int) ($size * 0.9 ) ) {

                $drawer->setfontsize($size);

                $metrics = $this->layers[0]
                    ->queryFontMetrics($drawer, $text);


                $height = $metrics['textHeight'] * 1.03;
                $width = $metrics['textWidth'] * 1.1;
                $y = (int)(($this->setting['width'] -
                            $height * count($texts))/2) + $metrics['ascender'];

                if ( $width < $this->setting['width'] && $y > 0 ) {
                    $this_size = min($this_size, $size);
                    $this_height = max($height, $this_height);
                    $this_width = max( $width, $this_width);
                    $this_y = $y;// + $metrics['acsender'];
                    break;
                }
            }
        }

        $result =  array( $this_size, (int)$this_width, (int)$this_height, (int)$this_y );
print_r($result); die;
        return $result;
    }
} 
