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
    private $delay;

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

        $delay = $this->setting['delay'];

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

        if ( is_numeric($def)) {
            $definition = json_decode(
                file_get_contents($this->setting['font_base_dir'] . "/$def.def")
            );
        } else {
            try {
                $definition = json_decode($def);
            } catch (\Exception $e ) {
                $definition = json_decode(
                    file_get_contents($this->setting['font_base_dir'] . "/1.def"));
            }
        }

        $boundary = $definition->base->frame;
        $this->delay = $definition->base->delay;

        $frame_width = $boundary[1][0]-$boundary[0][0];
        $frame_height = $boundary[1][0]-$boundary[0][0];

        $font = $definition->base->font;
        $text_array = $this->divideString(
            $text, $font, $frame_width, $frame_height);

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
                    $drawer->annotation( $x - $dx, $y - $dy + $metrics['ascender'] + $height * $j, $text );
                    $layer->drawImage($drawer);

                }
                $this->layers[$i] = $layer;
            }
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
            $layer->setImageDelay($this->delay);
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

    private function isEnglish($text = null) {
        if ( preg_match("/^[a-zA-Z0-9\.\?\!\(\)\[\]\/\%\&\#]+$/", $text) ) {
            return true;
        } else {
            return false;
        }
    }

    private function tokenize( $text ) {
        $length = mb_strlen($text);
        $tokens = array("");
        $index = 0;

        for( $i = 0; $i < $length; $i++ ) {
            $sub_str = mb_substr( $text, $i, 1);
            if ( $tokens[$index] == "" || ( $this->isEnglish($tokens[$index])
                && $this->isEnglish($sub_str) ) ) {
                $tokens[$index] .= $sub_str;
            } else {
                $index++;
                $tokens[$index] = trim($sub_str);
            }
        }
        return $tokens;
    }


    private function divideString( $text, $font, $width_border, $height_border ) {

        $width_array = array();
        $height = 0;

        // Set up drawer for layer;

        $drawer = (new ImagickDraw());
        $drawer->setFont( $this->setting['font_base_dir']
            . '/' . $this->setting['font'][$font] );
        $drawer->setfontsize(50);

        // Divide string into tokens

        $tokens = $this->tokenize($text);

        // White Space width
        $metrics = $this->layers[0]
            ->queryFontMetrics($drawer, "A A");
        $width_space = $metrics['textWidth'];
        $metrics = $this->layers[0]
            ->queryFontMetrics($drawer, "AA");
        $width_space = $width_space - $metrics['textWidth'];

        $total_width = 0;

        foreach( $tokens as $i => $token) {
            $metrics = $this->layers[0]
                ->queryFontMetrics($drawer, $token); 
            $width_array[$i] = $metrics['textWidth'];

            $total_width += $metrics['textWidth'];

            if ( isset($tokens[$i-1])
                && $this->isEnglish($tokens[$i-1])
                && $this->isEnglish($token) )
                $total_width += $width_space;

            $height = max($height, $metrics['ascender']-$metrics['descender']);
        }

        $pre_evaluated_line_count = ceil(sqrt(
            $total_width / $height / $width_border * $height_border));
        $proper_width = $total_width / $pre_evaluated_line_count;

        $results = array("");
        $current_width = 0;
        $index = 0;

        foreach( $tokens as $i => $token) {
            if ( $current_width >= $proper_width ) {
                $index++;
                $results[$index] = $token;
                $current_width = $width_array[$i];
            } else if ( isset( $tokens[$i-1] )
                    && $this->isEnglish($tokens[$i-1])
                    && $this->isEnglish($token) ) {
                    $results[$index] .= " ";
                    $current_width += $width_space;
                    $results[$index] .= $token;
                    $current_width += $width_array[$i];
            } else {
                $current_width += $width_array[$i]; 
                $results[$index] .= $token;
            }
        }
        return $results;

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


                $height = ($metrics['ascender']-$metrics['descender']) * 1.03;
                $width = $metrics['textWidth'] * 1.1;
                $y = (int)(($this->setting['height'] -
                            $height * count($texts))/2);

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
        return $result;
    }
} 
