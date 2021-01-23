<?php

/**
 *
 * @author Emiliano Noli <noliemiliano@gmail.com>
 * @package GTE_Renapi_Api
 */

namespace Limado\RestEnApi;

use Limado\RestEnApi\ApiLogger;
use Limado\RestEnApi\RenapiError;
use Limado\RestEnApi\RenapiFunction;
use Limado\RestEnApi\Tools;
use \exception;
use \Firebase\JWT\JWT;

//require 'utils.php';
class Renapi
{
    private $service_name;
    private $requested_method = null;
    private $functions = array();
    private $function_names = array();
    private $parameters = array();
    private $parameters_received_from_request_uri = array();
    private $parameters_from_uri = false;
    private $json_error = true;
    public $model = "";
    private $sendResponse;
    private $debug = false;
    private $logger;
    private $authentication;

    /**
     * Instancia Renapi.
     * Setea el nombre y los actions y models dispoible.
     *
     * @param json $config
     * @param string $name
     */
    public function __construct($config, $debug = false, $name = "Renapi api server")
    {

        $this->debug = $debug;
        $this->logger = ApiLogger::getInstance();
        $this->logger->setPath(realpath(dirname(__FILE__, 5)) . '/logs/');

        $this->debug("fn: Renapi->__construct: {$name} init. Debug mode = {$debug}.");
        $this->service_name = $name;
        $this->baseUri = $config->uri;
        $this->debug("fn: Renapi->__construct: Base uri: {$config->uri}.");
        $this->sendResponse = array($this, 'sendResponseFunction');
        $this->authentication = (isset($config->authentication) ? $config->authentication : false);
        $this->setRequestedModelAndFunction();
        $this->setActions($config);

    }

    /**
     * inicia el servicio y ejecuta
     *
     * @return void
     */
    public function start()
    {
        $this->debug("fn: Renapi->start called.");

        if (!isset($_SERVER['REQUEST_METHOD'])) {
            $this->error("fn: Renapi->start. No method found on request (GET/POST/PUT/DELETE)");
            RenapiError::genericError("No method found on request (GET/POST/PUT/DELETE)");
        }
        if (in_array($this->requested_function, $this->function_names)) {
            $function = $this->functions[$this->requested_function];

            if ($this->requested_method != $function->method()) {
                $this->error("fn: Renapi->start method call error. Requested: {$this->requested_method} function accepts: {$function->method()} .");
                RenapiError::methodCallError($function, $this->requested_method, $this);
            }
            /**
             * Valido si el método reuqiere autenticacion por token.
             * y valido la authenticacion por jwt
             */
            // $authToken = false;
            if ($this->functions[$this->requested_function]->authentication()) {
                $authentication = $this->getHeader($this->authentication->header);
                if (!$authentication) {
                    $this->error("fn: Renapi->start authentication required but not received. Function: {$this->requested_function}");
                    RenapiError::authenticationRequired($function);
                } else {
                    $authToken = str_replace("Bearer ", "", $authentication);
                    try {
                        // Access is granted.
                        $decoded = JWT::decode($authToken, $this->authentication->secret, array('HS256'));
                    } catch (Exception $e) {
                        // Access is denied.
                        RenapiError::authenticationFailed($e);
                    }
                }
            }
            $parameters = $function->parameters();

            //$this->debug("fn: Renapi->start. Content-Type: " . $contentType);

            $this->prepareParameters($function);

            $contentType = $this->getHeader('Content-Type');
            if ($contentType == "application/json") {
                $this->debug("fn: Renapi->start. Content-Type: application/json");
                $this->prepareJsonParameter($function);
            }

        } else {
            $this->debug("fn: Renapi->start. InvalidFunction error : " . $this->requested_function);
            RenapiError::invalidFunctionError($this->requested_function);
        }
        /**
         * Si el metodo requiere autenticacion y llego en el header, lo envio como parametro antes del sendResponse.
         * La funcion recibirá ($params, $token, $sendResponse)
         */
        // if ($authToken) {
        //     if ($this->authentication->type == "jwt") {
        //         $authToken = str_replace("Bearer ", "", $authToken);
        //     }
        //     $this->parameters["token"] = $authToken;
        // }
        $this->parameters["sendResponse"] = $this->sendResponse;
        try {
            call_user_func_array($this->requested_function, $this->parameters);
        } catch (exception $e) {
            $this->error($e);
            RenapiError::genericError($e->message);
        }
    }

    public function getAuthentication()
    {
        return $this->authentication;
    }
    /**
     * Callable from user defined functions. User defined function last paramater
     *  if $die == true, stops script execution
     * @param [object] $json
     * @param [bool] $die
     * @return void
     */
    public function sendResponseFunction($json, $code = 200, $die = true)
    {
        http_response_code($code);
        print json_encode($json);
        if ($die) {
            die();
        }
    }

    /**
     * Gets the requested function from RQUEST_URI
     *
     * @return void
     */
    private function setRequestedModelAndFunction()
    {

        $script_name = str_replace('.php', '/', $_SERVER['SCRIPT_NAME']);
        $this->requested_method = $_SERVER['REQUEST_METHOD'];

        $method = strtolower($this->requested_method);
        $rURI = (isset($_SERVER['REDIRECT_URL']) ? $_SERVER['REDIRECT_URL'] : $_SERVER['REQUEST_URI']);
        // $rURI .= (isset($_SERVER['REDIRECT_QUERY_STRING']) ? $_SERVER['REDIRECT_QUERY_STRING'] : "");
        $debugMessage = array("fn: Renapi->setRequestedModelAndFunction:",
            '_SERVER["SCRIPT_NAME"] => ' . $script_name,
            '_SERVER["REQUEST_METHOD"] => ' . $method,
            '_SERVER["REDIRECT_URL/REQUEST_URI"] => ' . $rURI,
        );
        $this->debug($debugMessage);

        $arr = explode($script_name, $rURI);
        if (count($arr) > 1) {
            //Modificar para volver a usar el home -> asi no está funcionando.
            //uri= http://ip/api/model/function/getParameter
            //$arr[0] = model, $arr[1] = function, $arr[2] = parameter (GET/DELETE Only)

            $arr = explode('/', $arr[1]);
            $this->model = "api";
            $fname = "describe";
            if (count($arr) > 0) {
                $this->model = $arr[0] == "" ? "api" : $arr[0];
            }
            if (count($arr) > 1) {
                $fname = $arr[1] == "" ? "describe" : $arr[1];
            }
            if (count($arr) > 2) {
                $this->parameters_received_from_request_uri = explode('/', $arr[2]);
                $this->parameters_from_uri = true;
            }
        } else {
            $fname = "describe";
        }
        $this->requested_function = $fname;

        $debugMessage = array("fn: Renapi->setRequestedModelAndFunction:",
            'requested_function => ' . $fname,
            'parameters_received_from_request_uri => ' . implode(" | ", $this->parameters_received_from_request_uri),
            'parameters_from_uri => ' . $this->parameters_from_uri,
            'requested_function => ' . $this->requested_method,
        );

        $this->debug($debugMessage);

        /**
         * Si me envía un OPTIONS solo quiere saber los headers y methods disponibles.
         */
        if (strtolower($this->requested_method) == "options") {
            http_response_code(200);
            header('Content-Type: text/html; charset=utf-8');
            header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept, ' . $this->getAuthentication()->header);
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Methods: OPTIONS, GET, POST, PUT, PATCH, DELETE, HEAD");
            die();
        }
    }

    /**
     * Search for an specific header and returns its value or false.
     *
     * @param [string] $h
     * @return string
     */
    private function getHeader($h)
    {
        if (function_exists("getallheaders")) {
            $reqHeaders = getallheaders();
        } else {
            $reqHeaders = Tools::getallheaders();
        }

        $this->debug($reqHeaders);

        foreach ($reqHeaders as $header => $value) {
            if (strtolower($header) == strtolower($h)) {
                return $value;
            }
        }
        return false;
    }
    /**
     * Loguea un error.
     * @param string $messaje
     * @param mixed $codigo
     * @return void
     */
    private function error($message)
    {
        $this->logger->write($message, "ERROR");
    }
    /**
     * Loguea informacion util, si esta habilitado el debug
     *
     * @param [type] $message
     * @return void
     */
    private function info($message)
    {
        $this->debug($message, "INFO");
    }
    /**
     * Loguea advertencias, si esta habilitado el debug
     *
     * @param [type] $message
     * @return void
     */
    private function warning($message)
    {
        $this->debug($message, "WARNING");
    }
    /**
     * Loguea todo el recorrido del llamado, si esta habilitado el debug
     *
     * @param [type] $message
     * @return void
     */
    private function debug($messages, $level = "DEBUG")
    {
        if ($this->debug) {
            if (!is_array($messages)) {
                $messages = array($messages);
            }
            $message = "";
            foreach ($messages as $msg) {
                $msg = (is_array($msg) ? var_export($msg, true) : $msg);
                $message .= $msg . PHP_EOL;
            }
            $this->logger->write($message, $level);
        }
    }
    /**
     * Si el header Content-Type:application/json está presente, toma el json del body y lo envia a la función definida.
     * @param RenapiFunction $function
     * @return array
     */

    public function prepareJsonParameter($function)
    {

        $entityBody = file_get_contents('php://input');
        $this->debug(["fn: Renapi->prepareJsonParameter.", "Received body: " . $entityBody]);
        /*
        Si el body viene vacío, a la función no le llegan parametros (lógico)
        if ($entityBody == "") {
        $this->parameters[] = '';
        } elseif */
        if ($entityBody != "") {
            if ($this->validateParameter($entityBody, 'json')) {
                $this->parameters[] = json_decode($entityBody);
            } else {
                $this->error("fn: Renapi->prepareJsonParameter. Invalid parameter type.");
                RenapiError::invalidParameterTypeError("entityBody", "empty");
            }
        }
    }
    /**
     * Setea el valor de cada parametro obtenido por GET (querystring/url) o POST(formdata)
     * Si hay parametros demas no los tiene en cuenta, si faltan parametros devuelve error.
     * @param RenapiFunction $function
     * @return array
     */
    public function prepareParameters($function)
    {

        $param_received = (count($_REQUEST) == 0 ? count($this->parameters_received_from_request_uri) : count($_REQUEST));
        $_PARAMS = (count($_REQUEST) == 0 ? $this->parameters_received_from_request_uri : $_REQUEST);
        $this->debug(["fn: prepareParameters. Params received: ", $_PARAMS]);

        $function_param_count = count($function->parameters());
        if ($param_received < $function_param_count) {
            $this->error("fn: Renapi->prepareParameters. Parameter count error. Function: " . $function->name() . " expected: {$function_param_count}, received: {$param_received}");
            RenapiError::paramaterCountError($function->name(), $function_param_count, $param_received, $_PARAMS);
        }
        if ($this->parameters_from_uri) {
            /**
             *  Parameters from REQUEST_URI http://ip/api/model/function/{param1}/{param2}/{paramN}
             **/
            $this->validateParameterByKey($function->parameters(), $_PARAMS);
            $this->validateParameterByKey($function->optionalParameters(), $_PARAMS);
        } else if ($function->parameters() != []) {
            /**
             * Parameters from GET/POST siempre y cuando la funcion tenga definidos que parametros espera.
             * Si acepta cualquier entrada, se define en api.config.json params: [].
             */
            foreach ($_PARAMS as $name => $value) {
                if (!array_key_exists($name, $function->parameters()) && !array_key_exists($name, $function->optionalParameters())) {
                    $this->error("fn: Renapi->prepareParameters. Invalid parameter. Function: " . $function->name() . " parameter name: {$name}");
                    RenapiError::invalidParameterError($name, $function->name());
                }
            }
            //Valido que los parametros tengan el formato correspondiente.
            $this->validateParameterByName($function->parameters(), $_PARAMS);
            // Si no llegaron todos los parametros.
            // Valido nuevamente, por si llego la cantidad necesaria pero con nombres distintos.
            $param_added = count($this->parameters);
            if ($param_added < $function_param_count) {
                $this->error("fn: Renapi->prepareParameters. Parameter count error. Function: " . $function->name() . " expected: {$function_param_count}, received: {$param_added}");
                RenapiError::paramaterCountError($function->name(), $function_param_count, $param_added, $this->parameters);
            }
            // Ingreso los parametros Opcionales si los hubiese.
            $this->validateParameterByName($function->optionalParameters(), $_PARAMS);
        } else {
            /**
             * $function->parameters() == []
             * Si la funcion espera un array indefinido de datos, se los paso asi tal cual llegan.
             */
            //$this->parameters["request"] = $_PARAMS;
        }
    }
    /**
     * Valida los paramentros revibidos por GET/POST con los valores definidos en la función, segun el nombre del parametro.
     * @param array $functionParams
     * @param array $requestValues
     */
    private function validateParameterByName($functionParams, $requestValues)
    {
        foreach ($functionParams as $name => $type) {
            if (isset($requestValues[$name])) {
                $value = $requestValues[$name];
                if ($this->validateParameter($value, $type)) {
                    $this->parameters[$name] = $value;
                } else {
                    $this->error("fn: Renapi->validateParameterByName. Invalid parameter type. Name: {$name} type: {$type}");
                    RenapiError::invalidParameterTypeError($name, $type);
                }
            }
        }
    }
    /**
     * Valida los paramentros recibidos por GET/POST con los valores definidos en la función, segun el nombre del parametro.
     * @param array $functionParams
     * @param array $requestValues
     */
    private function validateParameterByKey($functionParams, $requestValues)
    {
        $i = 0;
        foreach ($functionParams as $name => $type) {
            $value = $requestValues[$i];
            if ($this->validateParameter($value, $type)) {
                $this->parameters[$i] = $value;
            } else {
                $this->error("fn: Renapi->validateParameterByKey. Invalid parameter type. Name: {$name} type: {$type}");
                RenapiError::invalidParameterTypeError($name, $type);
            }
            $i++;
        }

    }

    /**
     *  Valida que el parametro tenga el tipo especificado para la funcion. Int/String/Array/bool/json
     * @param string $value
     * @param string $type
     * @return boolean
     */
    public function validateParameter($value, $type)
    {
        /** Si el parametro llega por query string, en $type llega el type, sino llega un objeto {name:"", type:""} */
        if (is_object($type)) {$type = $type->type;}
        $ret = true;
        switch (strtolower($type)) {
            case "array":$ret = is_array($value);
                break;
            case "int":$ret = filter_var($value, FILTER_VALIDATE_INT);
                break;
            case "integer":$ret = filter_var($value, FILTER_VALIDATE_INT);
                break;
            case "numeric":$ret = is_numeric($value);
                break;
            case "long":$ret = filter_var($value, FILTER_VALIDATE_INT);
                break;
            case "double":$ret = is_double($value);
                break;
            case "float":$ret = filter_var($value, FILTER_VALIDATE_FLOAT);
                break;
            case "string":$ret = is_string($value);
                break;
            case "bool":$ret = filter_var($value, FILTER_VALIDATE_BOOL);
                break;
            case "email":$ret = filter_var($value, FILTER_VALIDATE_EMAIL);
                break;
            case "ip":$ret = filter_var($value, FILTER_VALIDATE_IP);
                break;
            case "mac":$ret = filter_var($value, FILTER_VALIDATE_MAC);
                break;
            case "url":$ret = filter_var($value, FILTER_VALIDATE_URL);
                break;
            case "json":json_decode($value);
                $ret = (json_last_error() === JSON_ERROR_NONE ? true : false);
                break;
            default:$ret = false;
                break;
        }
        return $ret;
    }
    /**
     * Recibe el config.json con los models y actions definidas y registra las funciones al server.
     *
     * @param [object] $config
     * @return void
     */
    private function setActions($config)
    {
        // $parameters = null;
        // $description = "This function returns json with api describe methods";
        // $returnDescription = "Json";
        // $function = new RenapiFunction("describe", "GET",$parameters,$returnDescription,$description);
        // $server->registerFunction($function);
        $debugMessage = array("fn: Renapi->setActions:");

        foreach ($config->models as $model) {

            if ($this->model == $model->name) {
                foreach ($model->actions as $action) {

                    $parameters = isset($action->params) ? $action->params : null;
                    $authentication = isset($action->authentication) ? $action->authentication : false;
                    $description = isset($action->description) ? $action->description : null;
                    $returnDescription = isset($action->returnDescription) ? $action->returnDescription : null;
                    $debugMessage[] = "Registering {$model->name}->{$action->name} method {$action->method}";
                    $function = new RenapiFunction($action->name, $action->method, $parameters, $authentication, $description, $returnDescription);
                    $this->registerFunction($function);
                }
            }
        }
        //$this->debug($debugMessage);
    }
    /**
     * Registra una funcion en la api, si esta mal configurada devuelve el error correspondiente.
     * @param RenapiFunction $function
     */
    public function registerFunction($function)
    {
        if ($function->isValid() == false) {
            $debugMessage = array("fn: Renapi->registerFunction:");
            $debugMessage[] = $function->message();
            $this->debug($debugMessage);
            die($function->message());
        } else {
            $this->functions[$function->name()] = $function;
            $this->function_names[] = $function->name();
        }
    }

}
