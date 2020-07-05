<?php

/**
 *
 * @author Emiliano Noli <noliemiliano@gmail.com>
 * @package GTE_Renapi_Api
 */

namespace RestEnApi;

require 'utils.php';
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
        if ($this->posibleInjection()) {
            $this->error("fn: Renapi->start. Posible injection error.");
            print RenapiError::injectionError();
        }

        if (!isset($_SERVER['REQUEST_METHOD'])) {
            $this->error("fn: Renapi->start. No se encontro ningun metodo en la llamada (GET/POST/PUT/DELETE)");
            print RenapiError::genericError("No se encontro ningun metodo en la llamada (GET/POST/PUT/DELETE)");
            die();
        }
        if (in_array($this->requested_function, $this->function_names)) {
            $function = $this->functions[$this->requested_function];

            if ($this->requested_method != $function->method()) {
                $this->error("fn: Renapi->start method call error. Requested: {$this->requested_method} function accepts: {$function->method()} .");
                print RenapiError::methodCallError($function, $this->requested_method);
                die();
            }
            /**
             * Valido si el método reuqiere autenticacion por token.
             */
            $authToken = false;
            if ($this->functions[$this->requested_function]->authentication()) {
                $authentication = $this->getHeader('Authentication');
                if (!$authentication) {
                    $this->error("fn: Renapi->start authentication required but not received. Function: {$this->requested_function}");
                    print RenapiError::authenticationRequired($function);
                    die();
                } else {
                    $authToken = $authentication;
                }
            }
            $parameters = $function->parameters();
            $contentType = $this->getHeader('Content-Type');
            if ($contentType == "application/json") {
                $this->debug("fn: Renapi->start. Content-Type: application/json");
                $this->prepareJsonParameter($function);
            } else {
                $this->debug("fn: Renapi->start. Content-Type: " . $contentType);
                $this->prepareParameters($function);
            }
        } else {
            $this->debug("fn: Renapi->start. InvalidFunction error : " . $this->requested_function);
            print RenapiError::invalidFunctionError($this->requested_function);
            die();
        }
        /**
         * Si el metodo requiere autenticacion y llego en el header, lo envio como parametro antes del sendResponse.
         */
        if ($authToken) {
            $this->parameters["token"] = $authToken;
        }
        $this->parameters["sendResponse"] = $this->sendResponse;
        call_user_func_array($this->requested_function, $this->parameters);
    }
    /**
     * Callable from user defined functions. User defined function last paramater
     *  if $die == true, stops script execution
     * @param [object] $json
     * @param [bool] $die
     * @return void
     */
    public function sendResponseFunction($json, $die = true)
    {
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
        );
        $this->debug($debugMessage);
    }

    /**
     * Search for an specific header and returns its value or false.
     *
     * @param [string] $h
     * @return string
     */
    private function getHeader($h)
    {
        $reqHeaders = Tools::getallheaders();
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
    private function error()
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
        $this->debug(["fn: Renapi->prepareJsonParameter.", "Received body." . $entityBody]);
        if ($entityBody == "") {
            $this->parameters[] = '';
        } elseif ($this->validateParameter($entityBody, 'json')) {
            $this->parameters[] = $entityBody;
        } else {
            $this->error("fn: Renapi->prepareJsonParameter. Invalid parameter type. Name: {$name} type: {$type}");
            print RenapiError::invalidParameterTypeError($name, $type);
            die();
        }
    }
    /**
     * Setea el valor de cada parametro obtenido por GET o POST
     * Si hay parametros demas no los tiene en cuenta, si faltan parametros devuelve error.
     * @param RenapiFunction $function
     * @return array
     */
    public function prepareParameters($function)
    {

        // Methods PUT, PATCH y DELETE tambien vienen por el body

        if (in_array($this->requested_method, ["PUT", "DELETE"])) {
            /**
             *  Revisar, ya que si el body es un form data, no estoy tomandolo bien.
             */
            $bodyContent = file_get_contents('php://input');
            $param_received = 0;
        } else {
            $param_received = (count($_REQUEST) == 0 ? count($this->parameters_received_from_request_uri) : count($_REQUEST));
            $_PARAMS = (count($_REQUEST) == 0 ? $this->parameters_received_from_request_uri : $_REQUEST);
            $this->debug(["fn: prepareParameters. Params received: ", $_PARAMS]);
        }

        $function_param_count = count($function->parameters());
        if ($param_received < $function_param_count) {
            $this->error("fn: Renapi->prepareParameters. Parameter count error. Function: " . $function->name() . " expected: {$function_param_count}, received: {$param_received}");
            print RenapiError::paramaterCountError($function->name(), $function_param_count, $param_received, $_PARAMS);
            die();
        }
        if ($this->parameters_from_uri) {
            /** Parameters from REQUEST_URI */
            $this->validateParameterByKey($function->parameters(), $_PARAMS);
            $this->validateParameterByKey($function->optionalParameters(), $_PARAMS);
        } else if ($function->parameters() != []) {
            /** Parameters from GET/POST siempre y cuando la funcion tenga definidos que parametros espera.
             * Si acepta cualquier entrada, se define en api.config.json params: [].
             */
            foreach ($_PARAMS as $name => $value) {
                if (!array_key_exists($name, $function->parameters()) && !array_key_exists($name, $function->optionalParameters())) {
                    $this->error("fn: Renapi->prepareParameters. Invalid parameter. Function: " . $function->name() . " parameter name: {$name}");
                    print RenapiError::invalidParameterError($name, $function->name());
                    die();
                }
            }
            //Valido que los parametros tengan el formato correspondiente.
            $this->validateParameterByName($function->parameters(), $_PARAMS);
            // Si no llegaron todos los parametros.
            // Valido nuevamente, por si llego la cantidad necesaria pero con nombres distintos.
            $param_added = count($this->parameters);
            if ($param_added < $function_param_count) {
                $this->error("fn: Renapi->prepareParameters. Parameter count error. Function: " . $function->name() . " expected: {$function_param_count}, received: {$param_added}");
                print RenapiError::paramaterCountError($function->name(), $function_param_count, $param_added, $this->parameters);
                die();
            }
            // Ingreso los parametros Opcionales si los hubiese.
            $this->validateParameterByName($function->optionalParameters(), $_PARAMS);
        } else {
            /**
             * $function->parameters() == []
             * Si la funcion espera un array indefinido de datos, se los paso asi tal cual llegan.
             */
            $this->parameters["request"] = $_PARAMS;
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
                    print RenapiError::invalidParameterTypeError($name, $type);
                    die();
                }
            }
        }
    }
    /**
     * Valida los paramentros revibidos por GET/POST con los valores definidos en la función, segun el nombre del parametro.
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
                print RenapiError::invalidParameterTypeError($name, $type);
                die();
            }
            $i++;
        }

    }

    /**
     *  Valida que el parametro tenga el tipo especificado para la funcion. Int/String/Array/bool
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
            case "long":$ret = filter_var($value, FILTER_VALIDATE_LONG);
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
    /**
     * Detects posible query injection
     * It's too old, i'm not sure about this
     *
     * @return void
     */
    private function posibleInjection()
    {
        foreach ($_REQUEST as $key => $value) {
            if ($value != "") {
                if (strpos($value, ";") > -1) {
                    return true;
                }

                if (strpos($value, "--") > -1) {
                    return true;
                }

                if (strpos($value, "//") > -1) {
                    return true;
                }

                if (strpos($value, "');") > -1) {
                    return true;
                }

                if (strpos($value, '");') > -1) {
                    return true;
                }

                if (strpos($value, ");") > -1) {
                    return true;
                }

                if (strpos($value, ")") > -1) {
                    return true;
                }

                if (strpos($value, "(") > -1) {
                    return true;
                }

                if (strpos($value, "/*") > -1) {
                    return true;
                }

                if (strpos($value, "*/") > -1) {
                    return true;
                }

                if (strpos(strtolower($value), "xp_") > -1) {
                    return true;
                }

                if (strpos(strtolower($value), "call ") > -1) {
                    return true;
                }

                if (strpos(strtolower($value), " table") > -1) {
                    return true;
                }

                if (strpos(strtolower($value), " column") > -1) {
                    return true;
                }

                if (strpos(strtolower($value), " field") > -1) {
                    return true;
                }

                if (strpos(strtolower($value), "drop ") > -1) {
                    return true;
                }

                if (strpos(strtolower($value), "truncate ") > -1) {
                    return true;
                }

                if (strpos(strtolower($value), "delete ") > -1) {
                    return true;
                }

                if (strpos(strtolower($value), "update ") > -1) {
                    return true;
                }

                if (strpos(strtolower($value), "create ") > -1) {
                    return true;
                }

                if (strpos(strtolower($value), "alert") > -1) {
                    return true;
                }

                if (strpos(strtolower($value), "insert ") > -1) {
                    return true;
                }

                if (strpos(strtolower($value), "insert into") > -1) {
                    return true;
                }

                if (strpos(strtolower($value), "select ") > -1) {
                    return true;
                }

                if (strpos($value, "*") > -1) {
                    return true;
                }

            }
        }
        return false;
    }

}

/**
 * @author Emiliano Noli <noliemiliano@gmail.com>
 * @package GTE_Renapi_Api
 */
class RenapiFunction
{
    /**
     * Nombre del metodo.
     * @var string
     */
    private $name;
    /**
     * Descripcion del metodo.
     * @var string
     */
    private $authentication;
    /**
     * Define si el methodo requiere autenticacion por token en el header.
     * @var bool
     */
    public $description;
    /**
     * Descripcion de la devolucion del metodo.
     * @var string
     */
    public $return_description;
    private $isValid = true;
    private $message = "";
    /**
     * metodo * 0-GET | 1-POST | 2-PUT | 3-DELETE | 4-PATCH
     * @var string
     */
    private $method;
    private $valid_method = array("GET", "POST", "PUT", "DELETE", "PATCH");
    /**
     * array que define los parametros y sus tipos array($parameter_name => $parameter_tipo);
     * @var array
     */
    private $parameters = array();
    /**
     * array que define los parametros opcionales y sus tipos array($parameter_name => $parameter_tipo);
     * @var array
     */
    private $optionalParameters = array();

/**
 * @param string $name
 * @param string $method default GET
 * @param array $parameters array($parameter_name => $parameter_tipo, n => n);
 * @param string $return_description -> tipo de retorno. (text,json, etc)
 * @param string $description
 */
    public function __construct($name, $method = "GET", $parameters = null, $authentication, $description = null, $return_description = null)
    {
        $this->name($name);
        $this->method($method);
        $this->parameters($parameters);
        $this->return_description = $return_description;
        $this->description = $description;
        $this->authentication = $authentication;
    }
    /**
     * Obtiene o establece el nombre del metodo.
     * @param string $name
     * @return
     */
    public function name($name = null)
    {
        if (is_null($name)) {return $this->name;}
        if (!is_string($name) || is_int(substr($name, 0, 1)) || $name == "") {
            $this->setMessageAndValueState(false, "Invalid function name");
        } else { $this->name = $name;}
    }
    /**
     * Devuelve ó establece los parametros y sus tipos.
     *
     * Ej: array($parameter_name => $parameter_tipo, n => n);
     * @param array $parameters
     */
    public function parameters($parameters = null)
    {
        if (is_null($parameters)) {return $this->parameters;}
        if (!is_array($parameters)) {
            $this->setMessageAndValueState(false, "Invalid parameters types");
        }
        $this->parameters = $parameters;
    }
    /**
     * Establece los parametros opcionales y sus tipos.
     * Ej: array($parameter_name => $parameter_tipo, n => n);
     * @param array $parameters
     */
    public function optionalParameters($parameters = null)
    {
        if (is_null($parameters)) {return $this->optionalParameters;}
        if (!is_array($parameters)) {
            $this->setMessageAndValueState(false, "Invalid optional parameters types");
        }
        $this->optionalParameters = $parameters;
    }
    /**
     * Obtiene o establece el metodo por el cual tomara los parametros.
     * 0-GET | 1-POST | 2-PUT | 3-DELETE | 4-PATCH
     * @param mixed $method
     * @return type
     */
    public function method($method = null)
    {
        if (is_null($method)) {return $this->method;}
        if (is_int($method)) {$this->method = $this->valid_method[$method];}
        if (in_array(strtoupper($method), $this->valid_method)) {
            $this->method = strtoupper($method);
        }
    }
    /**
     * Obtiene los Http Verbs validos
     * 0-GET | 1-POST | 2-PUT | 3-DELETE | 4-PATCH
     * @return array
     */
    public function validMethods()
    {
        return $this->valid_method;
    }
    /**
     * Devuleve mensaje de error si lo hubiera.
     * @return string
     */
    public function message()
    {
        return "Funcion: {$this->name}<br/>{$this->message}";
    }
    /**
     * Indica si la funcion es valida o tiene algun error.
     * @return bool
     */
    public function isValid()
    {
        return $this->isValid;
    }

    public function authentication()
    {
        return $this->authentication;
    }
    private function setMessageAndValueState($valid, $message)
    {
        if ($this->isValid == true) {$this->isValid = $valid;}
        $this->message .= "{$message}<br/>";
        // die($this->message);
    }

}

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
        return json_encode($error);
    }
    public static function methodCallError($function, $called_method)
    {
        $method = $function->method();
        $name = $function->name();
        $code = 1;
        $message = "Function '{$name}' can only be called by {$method} and was called by {$called_method}.";
        $error = array("error" => true, "code" => $code, "description" => $message);
        return json_encode($error);
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
        $error = array("error" => true, "code" => $code, "description" => $message);
        return json_encode($error);
    }

    public static function invalidParameterError($parameter_name, $function_name)
    {
        $code = 3;
        $message = "{$parameter_name} is not a valid parameter for {$function_name}.";
        $error = array("error" => true, "code" => $code, "description" => $message);
        return json_encode($error);
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
        $error = array("error" => true, "code" => $code, "description" => $message);
        return json_encode($error);
    }

    public static function injectionError()
    {
        $code = 5;
        $message = "Received values may be potentially dangerous to the system.";
        $error = array("error" => true, "code" => $code, "description" => $message);
        return json_encode($error);
    }

    public static function genericError($message = "Undefined error")
    {
        $code = 6;
        $error = array("error" => true, "code" => $code, "description" => $message);
        return json_encode($error);
    }
    public static function authenticationRequired($function, $message = "Auhtentication required.")
    {
        $code = 7;
        $error = array("error" => true, "code" => $code, "description" => $message);
        return json_encode($error);
    }
}
