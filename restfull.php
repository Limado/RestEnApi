<?php
/**
 * Fecha Creacion: 23/08/2013
 * Fecha Modificacion: 26/08/2013
 * @author Emiliano Noli <noliemiliano@gmail.com>
 * @package GTE_Restfull_Api
 */
class restfull{
    private $service_name;
    private $requested_method = null;
    private $functions = array();
    private $function_names = array();
    private $parameters = array();
    private  $json_error = true;    
    function __construct($name){ 
        if (! $name) { die('Debe especificar un nombre para el servicio.'); }
        $this->service_name = $name;
    }
    
    /**
     * inicia el servicio y ejecuta
     */
    public function service_start(){
        if ($this->posible_injection()) $this->error("Algunos de los valores enviados son inv&aacute;lidos &oacute; potencialmente da&ntilde;inos para el sistema.",3);
        if (isset($_SERVER['REQUEST_METHOD'])) {
			$this->requested_method = $_SERVER['REQUEST_METHOD'];
		} 
        if(!isset($_REQUEST['function'])){ $this->print_home();}
        else
        {
            $fname = $_REQUEST['function'];
           
            if (in_array($fname,$this->function_names)){
                 $function = $this->functions[$fname];
               if($this->requested_method != $function->method() && $function->method() != "REQUEST")
                   {
                   print restfull_error::method_call_error($function->method(), $this->requested_method);
                   die();
                   //$this->error("La funci&oacute;n solo acepta el metodo {$function->method()} y se ha llamado con el metodo {$this->requested_method}.",1);
                   }
               $this->prepare_parameters($function);
            } else 
                {
                print restfull_error::invalid_function_error($fname);
                die();
                //$this->error("Funcion '{$fname}' inexistente",0);
                }
           call_user_func_array($fname, $this->parameters);
        }
    }
    /**
     * Imprime el error en el formato especificado para el servicio (Plano o Json) y corta la ejecucion.
     * @param string $messaje
     * @param mixed $codigo
     */
    public function error(){
        if ($this->json_error)
        {
            $error= array("error"=>"si","codigo"=>$codigo,"descripcion"=>$messaje);
            print json_encode($error);
        } 
        else 
        {
            print $messaje;
        }
        die();
    }
    /**
     * imprime pantalla con informacion de los metodos
     */
    private function print_home()
    {
        print "<html><body style='background-color:#ccc;'>";
        print "<h2  style='display:block; background-color:#045FB4; height:30px; border:solid 2px #095FB9; color:#FAFAFA;' >$this->service_name</h2>";
        print "<div style='margin:auto;width:650px;border-top:solid 1px #888; border-right:solid 1px #888; border-bottom:solid 2px #666; border-left:solid 2px #666; background-color:#BDBDBD;'>";
        print '<label>El par&aacute;metro default para indicar que funci&oacute;n se quiere ejecutar es "function", en todos los casos el sistema es Case Sensitive.</label>';
        foreach($this->functions as $name => $function){
            print '<label style="padding:5px;display:block; background-color:#6E6E6E;">
                <a href="javascript:Collapser(\''.$name.'\');" style="margin:15px 0px 15px 15px; color:black;font-weight:bold">'.$name.'</a></label>';
            print "<ul class='funcion' style='display:none' id='$name'>";
            if ($function->method() == "REQUEST" ? $metodo = "GET/POST" : $metodo = $function->method());
            print "<li>M&eacute;todo: {$metodo} </li>";
            print "<li>Par&aacute;metros:<ul>";
            // PARAMETROS OBLIGATORIOS
            foreach($function->parameters() as $pname => $ptype)
            {
             print "<li>$pname => $ptype</li>";
            }
            print "</ul></li>";
            // PARAMETROS OPCIONALES
            print "<li>Par&aacute;metros opcionales:<ul>";
            foreach($function->op_parameters() as $pname => $ptype)
            {
             print "<li>$pname => $ptype</li>";
            }
            print "</ul></li>";
            print "<li>Devoluci&oacute;n: {$function->return_description}</li>";
            print "<li>Definici&oacute;n: {$function->description}</li>";
            print "</ul>";
        }
        print "</div></body>";
         print "<script>
                        function Collapser(id) 
                        { 
                          uls = document.getElementsByTagName('ul');
                          for (var i=0;i<uls.length;i++)
                          {
                          if(uls[i].className=='funcion')
                                uls[i].style.display='none';
                          }
                          var x = document.getElementById(id); 
                          x.style.display = (x.style.display == \"\") ? 'none' : \"\"; 
                        }
                   </script>";
         print "</html>";
    }
     /**
     * Setea el valor de cada parametro obtenido por GET o POST 
     * Si hay parametros demas no los tiene en cuenta, si faltan parametros devuelve error.
     * @param restfull_function $function
     * @return array
     */
    public function prepare_parameters($function){
       // $function = new restfull_function();
        $param_received =count($_REQUEST)-1; //-1 parametro function
        $function_param =count($function->parameters());
         if ($param_received < $function_param)
             {
             print restfull_error::paramater_count_error($function->name(), $function_param, $param_received, $_REQUEST);
             die();
             //$this->error("La funci&oacute;n {$function->name()} necesita {$function_param} par&aacute;metros y se enviaron {$param_received} .",2);
             }
             foreach ($_REQUEST as $name => $value) {
                 if(!array_key_exists($name, $function->parameters()) && $name != "function" && !array_key_exists($name, $function->op_parameters()))
                 {
                     print restfull_error::invalid_parameter_error($name, $function->name());
                     die();
                     //$this->error("{$name} no es un parametro v&aacute;lido para la funci&oacute;n {$function->name()}.",3);       
                 }
             }     
        //Valido que los parametros tengan el formato correspondiente. 
        foreach ($function->parameters() as $name => $type) 
            {
                if(isset($_REQUEST[$name])){
                  $value = $_REQUEST[$name];
                  if( $this->validate_parameter($value,$type) )
                    {
                    $this->parameters[$name] = $value; }
                  else { 
                    print restfull_error::invalid_parameter_type_error($name, $type);
                    die();
                    }
                }
            }
            // Si no llegaron todos los parametros. 
            // Valido nuevamente, por si llego la cantidad necesaria pero con nombres distintos.
            $param_added = count($this->parameters);
            if($param_added < $function_param) {
                 print restfull_error::paramater_count_error($function->name(), $function_param, $param_added, $this->parameters);   
                 die();
                //$this->error("No se recibieron todos los parametros necesarios para ejecutar la funcion {$function->name()}.",4);
        }
        // Ingreso los parametros Opcionales si los hubiese.
        foreach ($function->op_parameters() as $name => $type) 
            {
                if(isset($_REQUEST[$name])){
                  $value = $_REQUEST[$name];
                  if( $this->validate_parameter($value,$type) )
                    {
                        $this->parameters[$name] = $value; 
                    }
                  else {
                        print restfull_error::invalid_parameter_type_error($name, $type);
                        die();
                    }
                }
            }
    }
    /**
     *  Valida que el parametro tenga el tipo especificado para la funcion. Int/String/Array/bool
     * @param string $value
     * @param string $type
     * @return boolean
     */
    public function validate_parameter($value,$type){
        $ret = true;
            switch($type){
                case "array": $ret = is_array($value);
                    break;
                case "int": $ret = is_numeric($value);
                    break;
                case "string": $ret = is_string($value);
                    break;
                case "bool": $ret = is_bool($value);
                    break;
                default: $ret = false;
                    break;
            }
        return $ret;
    }
    /**
     * Registra una funcion en la api, si esta mal configurada devuelve el error correspondiente.
     * @param restfull_function $function
     */
    public function register_function($function)
    {
        if($function->is_valid() == false) 
          {
            die($function->message());
          }
        else 
          {
            $this->functions[$function->name()] = $function;
            $this->function_names[] = $function->name();
          }
    }
    private function posible_injection(){
        foreach($_REQUEST as $key => $value){
            if($value!=""){
                if(strpos($value,";") > -1) return true;
                if(strpos($value,"--") > -1) return true;
                if(strpos($value,"//") > -1) return true;
                if(strpos($value,"');") > -1) return true;
                if(strpos($value,'");') > -1) return true;
                if(strpos($value,");") > -1) return true;
                if(strpos($value,")") > -1) return true;
                if(strpos($value,"(") > -1) return true;
                if(strpos($value,"/*") > -1) return true;
                if(strpos($value,"*/") > -1) return true;
                if(strpos(strtolower($value),"xp_") > -1) return true;
                if(strpos(strtolower($value),"call ") > -1) return true;
                if(strpos(strtolower($value)," table") > -1) return true;
                if(strpos(strtolower($value)," column") > -1) return true;
                if(strpos(strtolower($value)," field") > -1) return true;
                if(strpos(strtolower($value),"drop ") > -1) return true;
                if(strpos(strtolower($value),"truncate ") > -1) return true;
                if(strpos(strtolower($value),"delete ") > -1) return true;
                if(strpos(strtolower($value),"update ") > -1) return true;
                if(strpos(strtolower($value),"create ") > -1) return true;
                if(strpos(strtolower($value),"alert") > -1) return true;
                if(strpos(strtolower($value),"insert ") > -1) return true;
                if(strpos(strtolower($value),"insert into") > -1) return true;
                if(strpos(strtolower($value),"select ") > -1) return true;
                if(strpos($value,"*") > -1) return true;
            }
        }
        return false;
    }
}
/**
 * Fecha Creacion: 23/08/2013
 * Fecha Modificacion: 23/08/2013
 * @author Emiliano Noli <noliemiliano@gmail.com>
 * @package GTE_Restfull_Api
 */
class restfull_function {
    /**
     * Nombre del metodo.
     * @var type 
     */
    private $name;
    /**
     * Descripcion del metodo.
     * @var string
     */
    public $description;
    /**
     * Descripcion de la devolucion del metodo.
     * @var string 
     */
    public $return_description;
    private $is_valid = true;
    private $message = "";
    /**
     * metodo 0-POST | 1-GET | 2-REQUEST
     * @var string
     */
    private $method;
    private $valid_method= array("POST","GET","REQUEST");
    /**
     * array que define los parametros y sus tipos array($parameter_name => $parameter_tipo);
     * @var array 
     */
    private $parameters = array();
    /**
     * array que define los parametros opcionales y sus tipos array($parameter_name => $parameter_tipo);
     * @var array 
     */
    private $op_parameters = array();
     
/**
 * @param string $name
 * @param string $method default REQUEST
 * @param array $parameters array($parameter_name => $parameter_tipo, n => n);
 * @param string $return_description
 * @param string $description
 */
    public function __construct($name,$method="REQUEST",$parameters=null,$return_description=null,$description=null){
        $this->name($name);
        $this->method($method);
        $this->parameters($parameters);
        $this->return_description = $return_description;
        $this->description = $description;
    }
    /**
     * Obtiene o establece el nombre del metodo.
     * @param string $name
     * @return 
     */
    public function name($name = null){
        if(is_null($name)) { return $this->name; }
        if(!is_string($name) || is_int(substr($name, 0, 1)) || $name == "")
        {
            $this->set_message_and_value_state(false, "Nombre de funci&oacute;n inv&aacute;lido.");
        }
        else{ $this->name = $name; }
    }
    /**
     * Establece los parametros y sus tipos.
     * Ej: array($parameter_name => $parameter_tipo, n => n);
     * @param array $parameters
     */
    public function parameters($parameters = null){
        if(is_null($parameters)){ return $this->parameters; }
        if(!is_array($parameters))
          {
            $this->set_message_and_value_state(false,"Formato de par&aacute;metros inv&aacute;lidos.");
          }
         $this->parameters = $parameters;
    }
    /**
     * Establece los parametros opcionales y sus tipos.
     * Ej: array($parameter_name => $parameter_tipo, n => n);
     * @param array $parameters
     */
    public function op_parameters($parameters = null){
        if(is_null($parameters)){ return $this->op_parameters; }
        if(!is_array($parameters))
          {
            $this->set_message_and_value_state(false,"Formato de par&aacute;metros inv&aacute;lidos.");
          }
         $this->op_parameters = $parameters;
    }
    /**
     * Obtiene o establece el metodo por el cual tomara los parametros.
     * 0-POST | 1-GET | 2-REQUEST
     * @param mixed $method
     * @return type
     */
    public function method($method = null){
        if(is_null($method)){return $this->method;}
        if (is_int($method)){ $this->method = $this->valid_method[$method];}
        if(in_array( strtoupper($method), $this->valid_method)) 
        {
            $this->method = strtoupper($method);
        }
    }
    /**
     * Devuleve mensaje de error si lo hubiera.
     * @return string
     */
    public function message(){
        return "Funcion: {$this->name}<br/>{$this->message}";
    }
    /**
     * Indica si la funcion es valida o tiene algun error.
     * @return bool
     */
    public function is_valid(){ return $this->is_valid; }
 
    private function set_message_and_value_state($valid, $message){
        if($this->is_valid == true) { $this->is_valid = $valid; }
        $this->message .= "{$message}<br/>";
       // die($this->message);
    }
}

/******************************/
class restfull_error{

    public static function invalid_function_error($name)
    {
        $code = 0;
        $message = "Funcion '{$name}' inexistente";
        $error= array("error" => "si", "codigo" => $code, "descripcion" => $message);
        return json_encode($error);
    }
    public static function method_call_error($function_method, $called_method)
    {
        $code = 1;
        $message = "La funci&oacute;n solo acepta el metodo {$function_method} y se ha llamado con el metodo {$called_method}.";
        $error= array("error" => "si", "codigo" => $code, "descripcion" => $message);
        return json_encode($error);
    }
    public static function paramater_count_error($fname, $function_parameters_count, $received_parameters, $parameters)
    {
        $code = 2;
        $message = "La funci&oacute;n {$fname} necesita {$function_parameters_count} par&aacute;metros y se enviaron {$received_parameters}.";
        $message .= " Parametros recibidos:  ";
        foreach($parameters as $name => $type){
            if($name!="function")
            { $message .= " - " . $name; }
        }
        $error= array("error" => "si", "codigo" => $code, "descripcion" => $message);
        return json_encode($error);
    }
    
    public static function invalid_parameter_error($parameter_name, $function_name)
    {
        $code = 3;
        $message = "{$parameter_name} no es un parametro v&aacute;lido para la funci&oacute;n {$function_name}.";
        $error= array("error" => "si", "codigo" => $code, "descripcion" => $message);
        return json_encode($error);
    }
    public static function invalid_parameter_type_error($name, $type)
    {
        $code = 4;
        $message = "El par&aacute;metro {$name} no tiene el formato especificado {$type}";
        $error= array("error" => "si", "codigo" => $code, "descripcion" => $message);
        return json_encode($error);
    }
}