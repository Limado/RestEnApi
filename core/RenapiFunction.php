<?php

namespace Limado\RestEnApi;
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
       foreach ($parameters as $param) {
           $this->parameters[$param->name] = $param->type;
       }

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