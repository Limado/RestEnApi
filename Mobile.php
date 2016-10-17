<?php
header('Content-Type: text/html; charset=iso-8859-1');
header("Access-Control-Allow-Origin:*");

ob_start(); //output buffering
include_once('../class/Db.php');
include_once('../class/Json.php');
include ("../class/restfull/restfull.php");
include_once ("./entity/include_entities.php");
include("Mobile_Functions.php");

/*
 * SenialesXAlineacion($idAlineacion)
 * LogInUsuario($email, $clave)
 * RegistroUsuario($nombre, $apellido, $email, $clave)
 * MultimediaPersona($idPersona)
 * MultimediaPrograma($idPrograma)
 * ProgramacionPersona($idPersona,$idAlineacion)
 * ProgramacionSenial($idAlineacion,$idSenial,$horas)
 */
$server = new restfull("ReporTV App Mobile");


//getUserByEmailOnly($email)
$parametros = array("email" => "string", "idcliente" => "int");
//$parametros = array("email" => "string");
$definicion = "Login de usuario solo con email";
$function = new restfull_function("getUserByEmailOnly", "REQUEST",$parametros,"Json",$definicion);

$server->register_function($function);

//ProgramacionSenial($idAlineacion,$idSenial,$horas)
$parametros = array("idAlineacion" => "int", "idSenial" => "int", "horas" => "int");
$definicion = "Toda la programacion correspondiente a las horas pedidas de la senial correspondiente a idSenial.";
$function = new restfull_function("ProgramacionSenial", "REQUEST",$parametros,"Json",$definicion);
$server->register_function($function);

//ProgramacionSenialDesdeHasta($idAlineacion,$idSenial,$hDesde,$hHasta)
$parametros = array("idAlineacion" => "int", "idSenial" => "int", "desde" => "int", "hasta" => "int");
$definicion = "Toda la programacion correspondiente a la franja horaria para la senial correspondiente a idSenial.<br> Desde y Hasta se deben expresar en horas.";
$function = new restfull_function("ProgramacionSenialDesdeHasta", "REQUEST",$parametros,"Json",$definicion);
$server->register_function($function);

//ProgramacionPersona($idPersona,$idAlineacion)
$parametros = array("idPersona" => "int", "idAlineacion" => "int");
$definicion = "Toda la programacion del actor o director para la alineacion correspondiente a idAlineacion";
$function = new restfull_function("ProgramacionPersona", "REQUEST",$parametros,"Json",$definicion);
$server->register_function($function);

//ProgramacionPrograma($idPrograma,$idAlineacion)
$parametros = array("idPrograma" => "int", "idAlineacion" => "int");
$definicion = "Toda la programacion del programa para la alineacion correspondiente a idAlineacion";
$function = new restfull_function("ProgramacionPrograma", "REQUEST",$parametros,"Json",$definicion);
$server->register_function($function);

//MultimediaPrograma($idPrograma)
$parametros = array("idPrograma" => "int");
$definicion = "Imagenes correspondientes al actor o director";
$function = new restfull_function("MultimediaPrograma", "REQUEST",$parametros,"Json",$definicion);
$server->register_function($function);

//MultimediaPersona($idPersona)
$parametros = array("idPersona" => "int");
$definicion = "Imagenes correspondientes al programa";
$function = new restfull_function("MultimediaPersona", "REQUEST",$parametros,"Json",$definicion);
$server->register_function($function);

//SenialesXAlineacion($idAlineacion)
$parametros = array("idAlineacion" => "int");
$definicion = "Se&ntilde;ales correspondientes a la alineaci&oacute;n";
$function = new restfull_function("SenialesXAlineacion", "REQUEST",$parametros,"Json",$definicion);
$function->op_parameters(array("idCategoria" => "int"));
$server->register_function($function);

//LogInUsuario($email, $clave)
$parametros = array("email" => "string", "clave" => "string");
$definicion = "Login de usuario";
$function = new restfull_function("LogInUsuario", "REQUEST",$parametros,"Json",$definicion);
$server->register_function($function);

//RegistroUsuario($nombre, $apellido, $email, $clave)
$parametros = array("nombre" => "string", "apellido"=>"string", "email" => "string", "clave" => "string");
$definicion = "Registro de nuevo usuario";
$function = new restfull_function("RegistroUsuario", "REQUEST",$parametros,"Json",$definicion);
$function->op_parameters(array("cliente" => "int"));
$server->register_function($function);

//ProgramaDetalle($IdPrograma, $IdCapitulo, $IdAlineacion,$IdPautado)
$parametros = array("IdPrograma" => "int", "IdCapitulo"=>"int", "IdAlineacion" => "int", "IdPautado" => "int");
//$parametros = null;
$definicion = "Detalles del programa, actores, directores, imagenes y emisiones.";
$function = new restfull_function("ProgramaDetalle", "REQUEST",$parametros,"Json",$definicion);
$server->register_function($function);

//ProgramaDetallePorId
$parametros = array("IdPrograma" => "int", "IdAlineacion" => "int");
$definicion = "Detalles del programa, actores, directores, emisiones.";
$function = new restfull_function("ProgramaDetallePorId", "REQUEST", $parametros, "Json", $definicion);
$server->register_function($function);

//finderProgramaDetalle($IdPrograma, $IdCapitulo, $IdAlineacion,$IdPautado)
$parametros = array("IdPrograma" => "int", "IdCapitulo"=>"int", "IdAlineacion" => "int", "IdPautado" => "int");
//$parametros = null;
$definicion = "Detalles del programa, actores, directores, imagenes y emisiones.";
$function = new restfull_function("finderProgramaDetalle", "REQUEST",$parametros,"Json",$definicion);
$server->register_function($function);

//AlineacionXPais($idPais)
$parametros = array("idPais" => "int");
//$parametros = null;
$definicion = "Devuelve todas las alineaciones y su nombre para el pais pedido.";
$function = new restfull_function("AlineacionXPais", "REQUEST",$parametros,"Json",$definicion);
$server->register_function($function);

//function Paises()
$parametros = null;
$definicion = "Devuelve todos los paises con alguna alineacion activa.";
$function = new restfull_function("Paises", "REQUEST",$parametros,"Json",$definicion);
$server->register_function($function);

//function Destacados()
$parametros = array("idAlineacion" => "int");
$definicion = "Devuelve todos los destacados para la alineacion pedida.";
$function = new restfull_function("Destacados", "REQUEST",$parametros,"Json",$definicion);
$function->op_parameters(array("idCategoria" => "int"));
$server->register_function($function);

//function Busqueda()
$parametros = array("idAlineacion" => "int", "idSenial" => "int", "titulo" => "string"
                    ,"persona" => "string","idCategoria" => "int","idGenero" => "int");
$definicion = "Devuelve las ocurrencias de 7 dias para los parametros enviados.";
$function = new restfull_function("Busqueda", "REQUEST",$parametros,"Json",$definicion);
$server->register_function($function);


//function AgregarFavorito()
$parametros = array("idPrograma" => "int", "token" => "string");
$definicion = "Inserta un favorito al listado del usuario";
$function = new restfull_function("AgregarFavorito", "REQUEST",$parametros,"Json",$definicion);
$server->register_function($function);

//function EliminarFavorito()
$parametros = array("idPrograma" => "int", "token" => "string");
$definicion = "Elimina un favorito del listado del usuario";
$function = new restfull_function("EliminarFavorito", "REQUEST",$parametros,"Json",$definicion);
$server->register_function($function);

//function ListarFavoritos()
$parametros = array("token" => "string");
$definicion = "Lista los programas favoritos del usuario.";
$function = new restfull_function("ListarFavoritos", "REQUEST",$parametros,"Json",$definicion);
$server->register_function($function);

//function AgregarAlarma()
$parametros = array("token" => "string", "idPautado" => "int", "idAlineacion" => "int", "cantMinAntes" => "int");
$definicion = "Agrega un recordatorio.";
$function = new restfull_function("AgregarAlarma", "REQUEST",$parametros,"Json",$definicion);
$server->register_function($function);

//function EliminarAlarma()
$parametros = array("token" => "string", "idAlarma" => "int");
$definicion = "Eliminar un recordatorio.";
$function = new restfull_function("EliminarAlarma", "REQUEST",$parametros,"Json",$definicion);
$server->register_function($function);

//function ListarAlarmas()
$parametros = array("token" => "string");
$definicion = "Lista todas las alarmas del usuario.";
$function = new restfull_function("ListarAlarmas", "REQUEST",$parametros,"Json",$definicion);
$server->register_function($function);

//function loginConTwitter()
$parametros = array("oauth_token" => "string", "oauth_token_secret" => "string");
$definicion = "Registra o loguea al usuario mediante twitter.";
$function = new restfull_function("loginConTwitter", "REQUEST",$parametros,"Json",$definicion);
$function->op_parameters(array("cliente" => "int"));
$server->register_function($function);

//function loginConFacebook()
$parametros = array("token" => "string");
$definicion = "Registra o loguea al usuario mediante facebook.";
$function = new restfull_function("loginConFacebook", "REQUEST",$parametros,"Json",$definicion);
$function->op_parameters(array("cliente" => "int"));
$server->register_function($function);

//function actualizarPerfilUsuario()
$parametros = array("token" => "string", "nombre" => "string", "apellido" => "string", "clave" => "string");
$definicion = "Actualiza el perfil delusuario";
$function = new restfull_function("actualizarPerfilUsuario", "REQUEST",$parametros,"Json",$definicion);
$server->register_function($function);

//function obtenerCategoriasDeSeniales()
$parametros = null;
$definicion = "Obtiene el listado de las Categorias de se&ntilde;ales";
$function = new restfull_function("obtenerCategoriasSeniales", "REQUEST",$parametros,"Json",$definicion);
$server->register_function($function);

//function obtenerCategoriasProgramas()
$parametros = null;
$definicion = "Obtiene el listado de las Categorias de programas";
$function = new restfull_function("obtenerCategoriasProgramas", "REQUEST",$parametros,"Json",$definicion);
$server->register_function($function);

//function obtenerGennerosXCategoria()
$parametros = array("idCategoria" => "int");
$definicion = "Obtiene el listado de los G&eacute;neros de una categor&iacute;a";
$function = new restfull_function("obtenerGennerosXCategoria", "REQUEST",$parametros,"Json",$definicion);
$server->register_function($function);

//function alineacionXOrigen()
$parametros =  array("idCliente" => "int", "latitud" => "string", "longitud" => "string");
$definicion = "Obtiene la/s alineacion/es para las coordenadas y idCliente recibidos";
$function = new restfull_function("alineacionXOrigen", "REQUEST",$parametros,"Json",$definicion);
$server->register_function($function);

//function alineacionXCliente()
$parametros =  array("idCliente" => "int");
$definicion = "Obtiene la/s alineacion/es para idCliente recibidos";
$function = new restfull_function("alineacionXCliente", "REQUEST",$parametros,"Json",$definicion);
$server->register_function($function);

//function grillaXAlineacion()
$parametros =  array("idAlineacion" => "int", "hora" => "string","idCategoria" => "int");
$definicion = "Obtiene el programa actual y siguiente para cada señal de la alineación. Formato hora '2015-04-15 11:56:07'";
$function = new restfull_function("grillaXAlineacion", "REQUEST",$parametros,"Json",$definicion);
$function->op_parameters(array("cantProgramas" => "int"));
$server->register_function($function);

//function finderGrillaXAlineacion()
$parametros =  array("idAlineacion" => "int", "hora" => "string","idCategoria" => "int");
$definicion = "Obtiene el programa actual y siguiente para cada señal de la alineación. Formato hora '2015-04-15 11:56:07'";
$function = new restfull_function("finderGrillaXAlineacion", "REQUEST",$parametros,"Json",$definicion);
$function->op_parameters(array("cantProgramas" => "int"));
$server->register_function($function);


//function temporadasXPrograma()
$parametros =  array("idPrograma" => "int");
$definicion = "Obtiene listado de temporadas y sus capitulos para el programa pedido.";
$function = new restfull_function("temporadasXPrograma", "REQUEST",$parametros,"Json",$definicion);
$server->register_function($function);


//function capituloDetalle()
$parametros =  array("idCapitulo" => "int");
$definicion = "Obtiene el detalle del capitulo.";
$function = new restfull_function("capituloDetalle", "REQUEST",$parametros,"Json",$definicion);
$server->register_function($function);

//function programasRelacionados()
$parametros =  array("idAlineacion"=>"int", "idCategoria" => "int","idGenero" => "int");
$definicion = "Obtiene un listado de programas de la categoria y genero pedidos.";
$function = new restfull_function("programasRelacionados", "REQUEST",$parametros,"Json",$definicion);
$server->register_function($function);
/***************************************************************/
$server->service_start();
/***************************************************************
****************************************************************/
ob_end_flush();