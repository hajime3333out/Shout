<?php

class Router
{

    private static $me;

    private $parameters;
    private $path;
    private $is_called;

    private function __construct( ) {
        $this->is_called = false;
        $request_uri = preg_split( "/\?/", $_SERVER['REQUEST_URI'], 2 );
        if ( isset($request_uri[1]) ) {
            parse_str($request_uri, $this->parameters);
        } else {
            $this->parameters = array();
        }
        $requestUri = preg_split( "/\//", $request_uri[0] );

        $scriptName = preg_split( "/\//", $_SERVER['SCRIPT_NAME'] );


        foreach ($scriptName as $key => $value) {
            if ($value == $requestUri[$key]){
                unset($requestUri[$key]) ;
            }
        }
        $this->path = array_values($requestUri);

    }

    private static function getInstance( ) {
        if ( self::$me == null ) {
            self::$me = new Router();
        }
        return self::$me;
    }

    static function action($path, $function) {

        $me = self::getInstance();
        if ( $me->is_called == true ) return false;

        $path_array = preg_split("/\//", $path);
        foreach( $path_array as $key => $path_element ) {
            if ( strlen(trim($path_element)) == 0 ) {
                unset($path_array[$key]);
            }
        }
        $path_array = array_values($path_array);

        foreach( $path_array as $key => $path_element ) {
            if ( preg_match("/^\:(.*)$/", $path_element, $match) ) {
                $me->parameters[$match[1]] = $me->path[$key];
            } else {
                if ( $me->path[$key] != $path_element ) {
                    return false;
                }
            }
        }

        if ( is_callable( $function ) ) {
            $function();
            $me->is_called = true;
            return true;
        }

        $function_array = preg_split("/@/", $function );
        if ( !isset($function_array[1])) return false;

        $class_name = $function_array[0];
        $method_name = $function_array[1];

        $class_name::$method_name();
        $me->is_called = true;

        return true;
    }

}