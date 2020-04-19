<?php
header('Content-Type: text/html; charset=iso-8859-1');
//header("Access-Control-Allow-Origin:*");
//error_reporting(E_ALL);
ini_set("display_errors", 1);

ob_start(); //output buffering
require \core\renapi.php;
include("apiFunctions.php");
/** How to configure Renapi server and functions */
$server = new Renapi();

/** JSON from body definition, if header Content-Type = application/json is present */

$parametros = array("body" => "json");
$description = "This function receives parameters from the body.";
$returnDescription = "Json";
$function = new RenapiFunction("user_post", "POST",$parametros,$returnDescription,$description);
$server->registerFunction($function);

$parametros = array("id" => "int");
$description = "This function receives parameters from the body.";
$returnDescription = "Json";
$function = new RenapiFunction("user_get", "GET",$parametros,$returnDescription,$description);
$server->registerFunction($function);

$parametros = array("id" => "int");
$description = "This function receives parameters from the body.";
$returnDescription = "Json";
$function = new RenapiFunction("user_delete", "GET",$parametros,$returnDescription,$description);
$server->registerFunction($function);

/** JSON from body definition, if header Content-Type = application/json is present */

$parametros = array("body" => "json");
$description = "This function receives parameters from the body.";
$returnDescription = "Json";
$function = new RenapiFunction("user_put", "PUT",$parametros,$returnDescription,$description);
$server->registerFunction($function);
/***************************************************************/
$server->start();
/***************************************************************
****************************************************************/
ob_end_flush();