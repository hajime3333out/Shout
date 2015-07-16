<?php

class Config
{
    private static $data;

    public static function get( $key, $default = null ) {

        $key_array = preg_split('/\./', $key );
        if ( !isset($key_array[0]) ) return null;

        if ( !isset(self::$data[$key_array[0]])) {
            self::$data[$key_array[0]] =
                include __APP__ . '/config/' . $key_array[0] . '.php';
            if ( self::$data[$key_array[0]] == false ) {
                self::$data[$key_array[0]] = null;
            }
        }

        $return = self::$data;

        foreach($key_array as $k) {
            if ( !isset($return[$k]) ) return $default;
            $return = $return[$k];
        }
        return $return;
    }
}