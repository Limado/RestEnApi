<?php

namespace Limado\RestEnApi;
/**
 * @author Emiliano Noli <noliemiliano@gmail.com>
 * @package GTE_Renapi_Api
 */
class RenapiError
{

    public static function invalidFunctionError($name)
    {
        $code = 0;
        $message = "Function '{$name}' does not exists.";
        $error = array("error" => true, "code" => $code, "description" => $message);
        //return json_encode($error);
        http_response_code(400);
        print json_encode($error);
        die();
    }
    public static function methodCallError($function, $called_method, $server)
    {
        //else {
        $method = $function->method();
        $name = $function->name();
        $code = 1;
        $message = "Function '{$name}' can only be called by {$method} and was called by {$called_method}.";
        $error = array("error" => true, "code" => $code, "message" => $message);
        http_response_code(400);
        print json_encode($error);
        die();
        //}
    }
    public static function paramaterCountError($fname, $function_parameters_count, $received_parameters, $parameters)
    {
        $code = 2;
        $message = "The function {$fname} expects {$function_parameters_count} parameters but received {$received_parameters}.";
        if ($received_parameters > 0) {
            $message .= " Parameters received:  ";
            foreach ($parameters as $name => $type) {
                if ($name != "function") {$message .= " - " . $name;}
            }
        }
        $error = array("error" => true, "code" => $code, "message" => $message);
        http_response_code(400);
        print json_encode($error);
        die();
    }

    public static function invalidParameterError($parameter_name, $function_name)
    {
        $code = 3;
        $message = "{$parameter_name} is not a valid parameter for {$function_name}.";
        $error = array("error" => true, "code" => $code, "message" => $message);
        http_response_code(400);
        print json_encode($error);
        die();
    }
    public static function invalidParameterTypeError($name, $type)
    {
        /** Si el parametro llega por query string, en $type llega el type, sino llega un objeto (name:"", type:"") */
        if (is_object($type)) {
            $name = $type->name;
            $type = $type->type;
        }
        $code = 4;
        $message = "Parameter {$name} has an invalid type. Expected type: {$type}";
        $error = array("error" => true, "code" => $code, "message" => $message);
        http_response_code(400);
        print json_encode($error);
        die();
    }
    /**
     * Deprecated
     */
    public static function injectionError()
    {
        $code = 5;
        $message = "Received values may be potentially dangerous to the system.";
        $error = array("error" => true, "code" => $code, "message" => $message);
        http_response_code(500);
        print json_encode($error);
        die();
    }

    public static function genericError($message = "Undefined error")
    {
        $code = 6;
        $error = array("error" => true, "code" => $code, "message" => $message);
        http_response_code(500);
        print json_encode($error);
        die();

    }
    public static function authenticationRequired($function, $message = "Auhtentication required.")
    {
        $code = 7;
        $error = array("error" => true, "code" => $code, "message" => $message);
        http_response_code(401);
        print json_encode($error);
        die();
    }

    public static function authenticationFailed($exception)
    {
        $code = 8;
        $error = array("error" => true, "code" => $code, "message" => $exception->getMessage());
        http_response_code(401);
        print json_encode($error);
        die();
    }
}
