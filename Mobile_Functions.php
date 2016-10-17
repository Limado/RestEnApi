<?php

function getUserByEmailOnly($email, $idcliente = 0){
    $db = new DB(Config::DB_HOST, Config::DB_USER, Config::DB_PASS);
    $db->selectDb(Config::DB_REPORTV_APP);
    $db->name = "webService_mobile";
    $db->ShowError(true);
    $dt_ws = new data_table();

    $db->run_queryi("CALL Sp_Get_Usuario_By_Email('{$email}', {$idcliente})");
    $dt_ws = $db->get_data_table(0);
    if($dt_ws == null || $dt_ws->row_count() < 1  )
    {       
        print error::email_incorrecto();
        return;
    }
    else {
        $user = new usuario($dt_ws->get_associative_row(0));
            $resp = '{"error":"no","resultado":' . $user->getJsonObj() . '}';
            print $resp;
    }
}


function actualizarPerfilUsuario($token, $nombre, $apellido, $clave){
    $db = new DB(Config::DB_HOST, Config::DB_USER, Config::DB_PASS);
    $user = getUserByToken($db, $token);
  
    if( $user != false)
    {
        $db->selectDb( Config::DB_REPORTV_APP);
        $dt_ws = new data_table();
        $db->run_queryi("CALL Sp_Actualizar_Perfil({$user->id},'{$nombre}','{$apellido}','{$clave}');");
        $dt_ws = $db->get_data_table(0);
        if($dt_ws->get_item(0, 0) == "OK" )
        { print '{"error":"no","resultado":"Perfil actualizado."}'; }
        else
        {
            print error::error_general();
            return;
        }
    } 
}

function ListarAlarmas($token)
{
    $db = new DB(Config::DB_HOST, Config::DB_USER, Config::DB_PASS);
    $user = getUserByToken($db, $token);
    if( $user != false)
    {
        $db->selectDb( Config::DB_REPORTV_APP );
        $dt_ws = new data_table();
        $db->run_queryi("CALL Sp_Listar_Alarmas({$user->id});");
        $dt_ws = $db->get_data_table(0);
        if(!tiene_datos($dt_ws)){ return;}
        $resultado = "";
        for ($i=0; $i < $dt_ws->row_count(); $i++) {
        
            $alarma = new alarma($dt_ws->get_associative_row($i));
            $resultado .= $alarma->getJsonObj() .",";
        }
        /* QUITO LA ULTIMA COMA */
        $resultado = substr($resultado, 0, strlen($resultado) - 1) ;
        print '{"error":"no","resultado":['.$resultado.']}';
        return;
    }
}

function EliminarAlarma($token, $idAlarma)
{
    $db = new DB(Config::DB_HOST, Config::DB_USER, Config::DB_PASS);
    $user = getUserByToken($db, $token);
    if( $user != false)
    {
        $db->selectDb( Config::DB_REPORTV_APP );
        $dt_ws = new data_table();
        $db->run_queryi("CALL Sp_Eliminar_Alarma({$idAlarma});");
        $ret= array("error"=>"no","resultado"=>"Alarma eliminada.");
        print json_encode($ret);
        return;
    }
}

function AgregarAlarma($token, $idPautado, $idAlineacion, $cantMinAntes)
{
    $db = new DB(Config::DB_HOST, Config::DB_USER, Config::DB_PASS);
    $user = getUserByToken($db, $token);
    if( $user != false)
    {
        $db->selectDb( Config::DB_REPORTV_APP );
        $dt_ws = new data_table();
        $db->run_queryi("CALL Sp_Agregar_Alarma({$user->id},{$idPautado},{$idAlineacion},{$cantMinAntes});");
        $ret= array("error"=>"no","resultado"=>"Alarma agregada.");
        print json_encode($ret);
        return;
    }
}
/****************************************************/
/****************************************************/
function ListarFavoritos($token)
{
    $db = new DB(Config::DB_HOST, Config::DB_USER, Config::DB_PASS);
    $user = getUserByToken($db, $token);
    $resultado = '';
    if( $user != false)
    {
        $db->selectDb( Config::DB_SCHEMA );
        $dt_ws = new data_table();
        $db->run_queryi("CALL Sp_mobile_listar_favoritos({$user->id});");
        $dt_ws = $db->get_data_table(0);
        if(!tiene_datos($dt_ws)) return;
        for ($i=0; $i < $dt_ws->row_count(); $i++) {
        
            $datos_programa = array("idPrograma" =>$dt_ws->get_item('idPrograma',$i)
                                    ,"sinopsis" =>$dt_ws->get_item('sinopsis',$i)
                                    ,"genero" => new entidad_base( array("id" => $dt_ws->get_item('idGenero',$i), "nombre" => $dt_ws->get_item('genero',$i), "tipo" => "Genero"))
                                    ,"categoria" => new entidad_base( array("id" => $dt_ws->get_item('idCategoria',$i), "nombre" => $dt_ws->get_item('categoria',$i), "tipo" => "Categoria"))
                                    ,"pais" => new entidad_base( array("id" => $dt_ws->get_item('idPais',$i), "nombre" => $dt_ws->get_item('nombrePais',$i), "tipo" => "Pais"))
                                    ,"titulo" => $dt_ws->get_item('titulo',$i)
                                    ,"tituloOriginal" => $dt_ws->get_item('tituloOriginal',$i)
                                    ,"anio" => $dt_ws->get_item('anio',$i)
                                    ,"parentalRating" =>$dt_ws->get_item('parentalRating',$i));
            $prg = new programa($datos_programa);
            $prg->getImagenes($db);
            $prg->getCreditos($db);
            $resultado .= $prg->getJsonObj() .",";
        }
        /* QUITO LA ULTIMA COMA */
        $resultado = substr($resultado, 0, strlen($resultado) - 1) ;
        print '{"error":"no","resultado":['.$resultado.']}';
        return;
    } 
}
function AgregarFavorito($idPrograma, $token)
{
    $db = new DB(Config::DB_HOST, Config::DB_USER, Config::DB_PASS);
    $user = getUserByToken($db, $token);
    if( $user != false)
    {
        $db->selectDb( Config::DB_REPORTV_APP );
        $dt_ws = new data_table();
        $db->run_queryi("CALL Sp_Agregar_Favorito({$idPrograma},{$user->id});");
        $ret= array("error"=>"no","resultado"=>"Favorito agregado.");
        print json_encode($ret);
        return;
    }
}
function EliminarFavorito($idPrograma, $token)
{
    $db = new DB(Config::DB_HOST, Config::DB_USER, Config::DB_PASS);
    $user = getUserByToken($db, $token);
    if( $user != false)
    {
        $db->selectDb( Config::DB_REPORTV_APP );
        $dt_ws = new data_table();
        $db->run_queryi("CALL Sp_Eliminar_Favorito({$idPrograma},{$user->id});");
        $ret= array("error"=>"no","resultado"=>"Favorito eliminado.");
        print json_encode($ret);
        return;
    }
}
function Busqueda($idAlineacion, $idSenial, $titulo, $persona, $idCategoria, $idGenero)
{
    if( ($titulo == '' || strlen($titulo) < 4) && ($persona == '' || strlen($persona) < 4)  && $idCategoria < 1 && $idGenero < 1)
        {
        print '{"error":"si","codigo":"5","descripcion":"Debe especificar algun parametro de b&uacute;queda. T&iacute;tulo y persona deben contener al menos 4 carcateres."}';
        die();
        }
    //Lucho, 28/09/2016 rompia el sql si mando comilla simple
    $titulo = str_replace('\'','\'\'',$titulo);
    
    $db = new DB(Config::DB_HOST, Config::DB_USER, Config::DB_PASS);
    $db->selectDb(Config::DB_SCHEMA);
    $db->name = "webService_mobile";
    $db->ShowError(true);
    $dt_ws = new data_table();
    $db->run_queryi("CALL sp_mobile_busqueda({$idAlineacion},{$idSenial},'{$titulo}','{$persona}',{$idCategoria},{$idGenero});");
    $dt_ws = $db->get_data_table(0);
    if(!tiene_datos($dt_ws)) return;
    $resultado="";
    $listProgramas = array();
  for($i=0; $i<$dt_ws->row_count(); $i++)
  {
      $row = $dt_ws->get_associative_row($i);
      $pautado = new pautado($row);
      $pautado->senial = new senial(array("idSenial" => $row['idSenial']
                                            ,"nombre" => $row['senialAlternativa']
                                            ,"logo" => $row['logo']
                                            ,"canal" => $row['canal']
                                            ,"abreviatura" => $row['abreviatura']));
      if(isset($row['idPersona'])){
        $datos = array('idPersona' => $row['idPersona'],'nombre' => $row['nombrePersona']
                      ,'fechaNacimiento' => null, 'fechaFallecimiento' => null
                      ,'idPais' => null, 'nombrePais' => null);
            switch($row['tipoPersona'])
            {
                case 'Actor':
                      $pautado->programa->actores[] = new persona($datos);
                    break;
                case 'Director':
                      $pautado->programa->directores[] = new persona($datos);
                    break;
            }
      }
     if(!in_array($pautado->programa->idPrograma, $listProgramas))
     {$resultado .= $pautado->getJsonObj() . ",";}
      $listProgramas[] = $pautado->programa->idPrograma;
  }
    /* QUITO LA ULTIMA COMA */
  $resultado = substr($resultado, 0, strlen($resultado) - 1) ;
  print '{"error":"no","resultado":['.$resultado.']}';
}
/*****************************************************************
 * 
****************************************************************/
function Destacados($idAlineacion, $idCategoria = 0) 
{
    $db = new DB(Config::DB_HOST, Config::DB_USER, Config::DB_PASS);
    $db->selectDb(Config::DB_SCHEMA);
    $db->name = "webService_mobile";
    $db->ShowError(true);
    $dt_ws = new data_table();
    $db->run_queryi("CALL sp_mobile_destacados('{$idAlineacion}','{$idCategoria}');");
    $dt_ws = $db->get_data_table(0);
    if(!tiene_datos($dt_ws)){ return;}
    $resultado="";
    $listProgramas = array();
  for($i=0; $i<$dt_ws->row_count(); $i++)
  {
      $row = $dt_ws->get_associative_row($i);
      $pautado = new pautado($row);
      $pautado->senial = new senial(array("idSenial" => $row['idSenial']
                                            ,"nombre" => $row['senialAlternativa']
                                            ,"logo" => $row['logo']
                                            ,"canal" => $row['canal']
                                            ,"abreviatura" => $row['abreviatura']));
     /* 
      if(!in_array($pautado->programa->idPrograma, $listProgramas))
      {$resultado .= $pautado->getJsonObj() . ",";}
      * 
      */
      $resultado .= $pautado->getJsonObj() . ",";
      $listProgramas[] = $pautado->programa->idPrograma;
  }
  /* QUITO LA ULTIMA COMA */
  $resultado = substr($resultado, 0, strlen($resultado) - 1) ;
  print '{"error":"no","resultado":['.$resultado.']}';
} 
function AlineacionXPais($idPais) 
{
    $db = new DB(Config::DB_HOST, Config::DB_USER, Config::DB_PASS);
    $db->selectDb(Config::DB_SCHEMA);
    $db->name = "webService_mobile";
    $db->ShowError(true);
    $dt_ws = new data_table();
    $db->run_queryi("CALL sp_mobile_alineacion_x_pais('{$idPais}');");
    $dt_ws = $db->get_data_table(0);
    if(!tiene_datos($dt_ws)) return;
    print data_table_to_json($dt_ws);
}
function Paises() 
{
    $db = new DB(Config::DB_HOST, Config::DB_USER, Config::DB_PASS);
    $db->selectDb(Config::DB_SCHEMA);
    $db->name = "webService_mobile";
    $db->ShowError(true);
    $dt_ws = new data_table();
    $db->run_queryi("CALL sp_mobile_paises();");
    $dt_ws = $db->get_data_table(0);
    if(!tiene_datos($dt_ws)) return;
    print data_table_to_json($dt_ws);
}
/******************/
function obtenerCategoriasSeniales() 
{
    $db = new DB(Config::DB_HOST, Config::DB_USER, Config::DB_PASS);
    $db->selectDb(Config::DB_SCHEMA);
    $db->name = "webService_mobile";
    $db->ShowError(true);
    $dt_ws = new data_table();
    $db->run_queryi("CALL sp_mobile_categoria_senial();");
    $dt_ws = $db->get_data_table(0);
    if(!tiene_datos($dt_ws)) return;
    print data_table_to_json($dt_ws);
}
/******************/
function obtenerCategoriasProgramas() 
{
    $db = new DB(Config::DB_HOST, Config::DB_USER, Config::DB_PASS);
    $db->selectDb(Config::DB_SCHEMA);
    $db->name = "webService_mobile";
    $db->ShowError(true);
    $dt_ws = new data_table();
    $db->run_queryi("CALL sp_mobile_categorias_programas();");
    $dt_ws = $db->get_data_table(0);
    if(!tiene_datos($dt_ws)) return;
    print data_table_to_json($dt_ws);
}
/******************/
function ProgramaDetalle($IdPrograma, $IdCapitulo, $IdAlineacion,$IdPautado) 
{
    $db = new DB(Config::DB_HOST, Config::DB_USER, Config::DB_PASS);
    $db->selectDb(Config::DB_SCHEMA);
    $db->name = "webService_mobile";
    $db->ShowError(true);
    
    $db->run_queryi("CALL sp_mobile_programa_detalle({$IdPrograma},{$IdCapitulo},{$IdAlineacion},{$IdPautado});");
       
        $dt_pautado = $db->get_data_table(0);
        $dt_actores = $db->get_data_table(1);
        $dt_directores = $db->get_data_table(2);
        $dt_emisiones = $db->get_data_table(3);
        $dt_imagenes = $db->get_data_table(4);
        if(!tiene_datos($dt_pautado)) {return;}
        $resultado="";
  
  for($i=0; $i<$dt_pautado->row_count(); $i++)
  {
      $row = $dt_pautado->get_associative_row($i);
      $pautado = new pautado($row);
      $pautado->senial = new senial(array("idSenial" => $row['idSenial']
                                            ,"nombre" => $row['senialAlternativa']
                                            ,"logo" => $row['logo']
                                            ,"canal" => $row['canal']
                                            ,"abreviatura" => $row['abreviatura']));
      
      //Actores   
      if($dt_actores != null && $dt_actores->row_count() > 0 ){
        for($i=0; $i<$dt_actores->row_count(); $i++)
        {
            $row = $dt_actores->get_associative_row($i);
            $actor = new persona($row);
            $pautado->programa->actores[] = $actor;
        }
      }
      //Directores
      if($dt_directores != null && $dt_directores->row_count() > 0 ){
        for($i=0; $i<$dt_directores->row_count(); $i++)
        {
            $row = $dt_directores->get_associative_row($i);
            $director = new persona($row);
            $pautado->programa->directores[] = $director;
        }
     }
     //Emisiones
     if($dt_emisiones != null && $dt_emisiones->row_count() > 0 ){
        for($i=0; $i<$dt_emisiones->row_count(); $i++)
         {
             $row = $dt_emisiones->get_associative_row($i);
             $emisiones = new emision($row);
             $pautado->programa->emisiones[] = $emisiones;
         }
     }
     //Imagenes
     if($dt_imagenes != null && $dt_imagenes->row_count() > 0 ){
        for($i=0; $i<$dt_imagenes->row_count(); $i++)
         {
             $row = $dt_imagenes->get_associative_row($i);
             $imagenes = new imagen($row);
             $pautado->programa->imagenes[] = $imagenes;
         }
     }

      $resultado .= $pautado->getJsonObj() . ",";
  }
  
  /* QUITO LA ULTIMA COMA */
  $resultado = substr($resultado, 0, strlen($resultado) - 1) ;
  print '{"error":"no","resultado":['.$resultado.']}';
}
/***********************/
function ProgramaDetallePorId($IdPrograma, $IdAlineacion) 
{
    $db = new DB(Config::DB_HOST, Config::DB_USER, Config::DB_PASS);
    $db->selectDb(Config::DB_SCHEMA);
    $db->name = "webService_mobile";
    $db->ShowError(true);
    
        $db->run_queryi("CALL Sp_mobile_programa_detalle_por_id({$IdPrograma},{$IdAlineacion});");
        $dt_programa = $db->get_data_table(0);
        $dt_actores = $db->get_data_table(1);
        $dt_directores = $db->get_data_table(2);        
        $dt_emisiones = $db->get_data_table(3);
        $dt_imagenes = $db->get_data_table(4);
        if(!tiene_datos($dt_programa)) {return;}
        $resultado="";
        
    if($dt_programa != null && $dt_programa->row_count() > 0)
    {
        $datos_programa = array("idPrograma" =>$dt_programa->get_item('idPrograma',$i)
                                    ,"sinopsis" =>$dt_programa->get_item('sinopsis',$i)
                                    ,"genero" => new entidad_base( array("id" => $dt_programa->get_item('idGenero',$i), "nombre" => $dt_programa->get_item('genero',$i), "tipo" => "Genero"))
                                    ,"categoria" => new entidad_base( array("id" => $dt_programa->get_item('idCategoria',$i), "nombre" => $dt_programa->get_item('categoria',$i), "tipo" => "Categoria"))
                                    ,"pais" => new entidad_base( array("id" => $dt_programa->get_item('idPais',$i), "nombre" => $dt_programa->get_item('nombrePais',$i), "tipo" => "Pais"))
                                    ,"titulo" => $dt_programa->get_item('titulo',$i)
                                    ,"tituloOriginal" => $dt_programa->get_item('tituloOriginal',$i)
                                    ,"anio" => $dt_programa->get_item('anio',$i)
                                    ,"parentalRating" =>$dt_programa->get_item('parentalRating',$i));
            $prg = new programa($datos_programa);
            
        
        if($dt_actores != null && $dt_actores->row_count() > 0 )
        {
            for($i=0; $i<$dt_actores->row_count(); $i++)
            {
                $row = $dt_actores->get_associative_row($i);
                $actor = new persona($row);
                $prg->actores[] = $actor;
            }
        }
        
        if($dt_directores != null && $dt_directores->row_count() > 0)
        {
            for($i=0; $i<$dt_directores->row_count(); $i++)
            {
                $row = $dt_directores->get_associative_row($i);
                $director = new persona($row);
                $prg->directores[] = $director;                
            }
        }
        
         //Emisiones
        if($dt_emisiones != null && $dt_emisiones->row_count() > 0 )
        {
            for($i=0; $i<$dt_emisiones->row_count(); $i++)
             {
                 $row = $dt_emisiones->get_associative_row($i);
                 $emisiones = new emision($row);
                 $prg->emisiones[] = $emisiones;
             }
         }
         
         if($dt_imagenes != null && $dt_imagenes->row_count() > 0)
         {
             for($i=0; $i<$dt_imagenes->row_count(); $i++)
             {
                 $row = $dt_imagenes->get_associative_row($i);
                 $imagenes = new imagen ($row);
                 $prg->imagenes[] = $imagenes;
             }
         }         
        $resultado .= $prg->getJsonObj() .",";
    }    
    /* QUITO LA ULTIMA COMA */
    $resultado = substr($resultado, 0, strlen($resultado) - 1) ;
    print '{"error":"no","resultado":['.$resultado.']}';
    return;
}   
/***********************/
function SenialesXAlineacion($idAlineacion, $idCategoria = 0) 
{
    $db = new DB(Config::DB_HOST, Config::DB_USER, Config::DB_PASS);
    $db->selectDb(Config::DB_SCHEMA);
    $db->name = "webService_mobile";
    $db->ShowError(true);
    $dt_ws = new data_table();
    $db->run_queryi("CALL sp_mobile_seniales_x_alineacion('{$idAlineacion}','{$idCategoria}');");
    $dt_ws = $db->get_data_table(0);
    if(!tiene_datos($dt_ws)) return;
      $resultado="";
  for($i=0; $i<$dt_ws->row_count(); $i++)
  {
      $row = $dt_ws->get_associative_row($i);
      $senial = new senial(array("idSenial" => $row['idSenial']
                                            ,"nombre" => $row['senialAlternativa']
                                            ,"logo" => $row['logo']
                                            ,"logoSmall" => $row['logoSmall']
                                            ,"canal" => $row['canal']
                                            ,"abreviatura" => $row['abreviatura']));
      $resultado .= $senial->getJsonObj() . ",";
  }
  /* QUITO LA ULTIMA COMA */
  $resultado = substr($resultado, 0, strlen($resultado) - 1) ;
  print '{"error":"no","resultado":['.$resultado.']}';
}

function LogInUsuario($email, $clave) 
{
    $db = new DB(Config::DB_HOST, Config::DB_USER, Config::DB_PASS);
    $db->selectDb(Config::DB_REPORTV_APP);
    $db->name = "webService_mobile";
    $db->ShowError(true);
    $dt_ws = new data_table();
    $db->run_queryi("CALL Sp_Get_Usuario('{$email}','{$clave}');");
    $dt_ws = $db->get_data_table(0);
    
    if($dt_ws == null || $dt_ws->row_count() < 1  )
        {
            print error::usuario_incorrecto();
            return;
        }
       
    $user = new usuario($dt_ws->get_associative_row(0));
    $token = sha1(microtime(). Config::ENCRYPT_STRING);
    $user->token = $token;
    $db->run_query("INSERT INTO tbl_usuario_token VALUES ({$dt_ws->get_item("idUsuario", 0)},'{$token}','AppRtv',NOW(),NOW());");
    $resp = '{"error":"no","resultado":' . $user->getJsonObj() . '}';
    print $resp;
 //print data_table_to_json($dt_ws);
}

function RegistroUsuario($nombre, $apellido, $email, $clave, $cliente = 0, $socialId = 0, $social='') 
{
    $db = new DB(Config::DB_HOST, Config::DB_USER, Config::DB_PASS);
    $db->selectDb(Config::DB_REPORTV_APP);
    $db->name = "webService_mobile";
    $db->ShowError(true);
    $dt_ws = new data_table();
    $nombreUsuario = $email;
    switch ($social) {
        case "twitter":
                $twitterId = $socialId;
                $facebookId = 0;
                $email = "";
            break;
        case "facebook":
                $facebookId = $socialId;
                $twitterId = 0;
            break;
        default:
            $twitterId = 0;
            $facebookId = 0;
            break;
    }
    $db->run_queryi("CALL Sp_Insert_Usuario('{$nombreUsuario}','{$clave}','{$nombre}','{$apellido}','{$email}',{$facebookId},{$twitterId},{$cliente});");
    $dt_ws = $db->get_data_table(0);
    
    if(!tiene_datos($dt_ws)) {return;}
    if($dt_ws->get_item(0, 0) == false){
         $resp = error::usuario_ya_existe();
         //$resp = "CALL Sp_Insert_Usuario('{$nombreUsuario}','{$clave}','{$nombre}','{$apellido}','{$email}',{$facebookId},{$twitterId},{$cliente});";
    }
    else{
        $token = sha1(microtime()."Hola Chiche");
        $user = new usuario ($dt_ws->get_associative_row(0));
        $db->run_queryi("INSERT INTO tbl_usuario_token VALUES ({$dt_ws->get_item("idUsuario", 0)},'{$token}','AppRtv',NOW(),NOW());");
        $user->token = $token;
        $resp = '{"error":"no","resultado":' . $user->getJsonObj() . '}';
        mailBienvenida( $email, $nombre . $apellido );
    }
    print $resp;
}

function MultimediaPersona($idPersona) 
{
    $db = new DB(Config::DB_HOST, Config::DB_USER, Config::DB_PASS);
    $db->selectDb(Config::DB_SCHEMA);
    $db->name = "webService_mobile";
    $db->ShowError(true);
    $dt_ws = new data_table();
    $db->run_queryi("call sp_mobile_multimedia_persona({$idPersona});");
    //$db_ws->run_queryi("SELECT id_senial,senial,logo FROM tbl_seniales");
    $dt_ws = $db->get_data_table(0);
    if(!tiene_datos($dt_ws)) {return;}
    print data_table_to_json($dt_ws);
}

function MultimediaPrograma($idPrograma) 
{
    $db = new DB(Config::DB_HOST, Config::DB_USER, Config::DB_PASS);
    $db->selectDb(Config::DB_SCHEMA);
    $db->name = "webService_mobile";
    $db->ShowError(true);
    $dt_ws = new data_table();
    $db->run_queryi("call sp_mobile_multimedia_programa({$idPrograma});");
    $dt_ws = $db->get_data_table(0);
    if(!tiene_datos($dt_ws)) return;
    print data_table_to_json($dt_ws);
}
/**
 * @param type $idPersona
 * @param type $idAlineacion
 * @return type
 */
function ProgramacionPersona($idPersona,$idAlineacion) 
{
    $db = new DB(Config::DB_HOST, Config::DB_USER, Config::DB_PASS);
    $db->selectDb(Config::DB_SCHEMA);
    $db->name = "webService_mobile";
    $db->ShowError(true);
    $dt_ws = new data_table();
    $db->run_queryi("call sp_mobile_programacion_persona({$idPersona},{$idAlineacion});");
    $dt_ws = $db->get_data_table(0);
    if(!tiene_datos($dt_ws)){ return; }
    $resultado="";
        for($i=0; $i<$dt_ws->row_count(); $i++)
        {
            $row = $dt_ws->get_associative_row($i);
            $pautado = new pautado($row);
            $pautado->senial = new senial(array("idSenial" => $row['idSenial']
                                                  ,"nombre" => $row['senialAlternativa']
                                                  ,"logo" => $row['logo']
                                                  ,"canal" => $row['canal']
                                                  ,"abreviatura" => $row['abreviatura']));
            $resultado .= $pautado->getJsonObj() . ",";
        }
  /* QUITO LA ULTIMA COMA */
  $resultado = substr($resultado, 0, strlen($resultado) - 1) ;
  print '{"error":"no","resultado":['.$resultado.']}';
}
/**
 * @param type $idAlineacion
 * @param type $idSenial
 * @param type $horas
 * @return type
 */
function ProgramacionSenial($idAlineacion,$idSenial,$horas=10) 
{
    $horas = 24;
    $db = new DB(Config::DB_HOST, Config::DB_USER, Config::DB_PASS);
    $db->selectDb(Config::DB_SCHEMA);
    $db->name = "webService_mobile";
    $db->ShowError(true);
    $dt_ws = new data_table();
    //$a = "CALL sp_mobile_pautado_x_senial_alineacion({$idAlineacion}, {$idSenial},  ADDDATE(NOW(),INTERVAL -30 MINUTE),ADDDATE(NOW(),INTERVAL {$horas} HOUR));";
    $db->run_queryi("CALL sp_mobile_pautado_x_senial_alineacion({$idAlineacion}, {$idSenial}, NOW(),ADDDATE(NOW(),INTERVAL {$horas} HOUR));");
    $dt_ws = $db->get_data_table(0);
    
   if(!tiene_datos($dt_ws)) return;
   
  $resultado="";
  for($i=0; $i<$dt_ws->row_count(); $i++)
  {
      $array = $dt_ws->get_associative_row($i);
      $pautado = new pautado($array);
      $pautado->senial = new senial($array);
      $resultado .= $pautado->getJsonObj() . ",";
  }
  /* QUITO LA ULTIMA COMA */
  $resultado = substr($resultado, 0, strlen($resultado) - 1) ;
  print '{"error":"no","resultado":['.$resultado.']}';
}
/**
 * @param type $idAlineacion
 * @param type $idSenial
 * @param type $horas
 * @return type
 */
function ProgramacionSenialLucho($idAlineacion,$idSenial,$horas=10) 
{
    $horas = 24;
    $db = new DB(Config::DB_HOST, Config::DB_USER, Config::DB_PASS);
    $db->selectDb(Config::DB_SCHEMA);
    $db->name = "webService_mobile";
    $db->ShowError(true);
    $dt_ws = new data_table();
    $dt_ws2 = new data_table();
    //$a = "CALL sp_mobile_pautado_x_senial_alineacion({$idAlineacion}, {$idSenial},  ADDDATE(NOW(),INTERVAL -30 MINUTE),ADDDATE(NOW(),INTERVAL {$horas} HOUR));";
    $db->run_queryi("CALL sp_mobile_pautado_x_senial_alineacion_con_emisiones({$idAlineacion}, {$idSenial}, NOW(),ADDDATE(NOW(),INTERVAL {$horas} HOUR));");
    $dt_ws = $db->get_data_table(0);
    $dt_ws2 = $db->get_data_table(1);

  $resultado="";
  for($i=0; $i<$dt_ws->row_count(); $i++)
  {
      $cantidad = 1;
      $array = $dt_ws->get_associative_row($i);
      $pautado = new pautado($array);
      $pautado->senial = new senial($array);
      //Emisiones
           for($j=0; $j<$dt_ws2->row_count(); $j++)
            {
                $row = $dt_ws2->get_associative_row($j);
                if ($pautado->programa->idPrograma == $row["idPrograma"] && strtotime($row["fechaInicio"]) > strtotime($pautado->fechaInicio) && $cantidad < 5)
                {
                    $cantidad++;
                    $eArray["duracion"] = $row["duracion"];
                    $eArray["fechaFin"] = $row["fechaFin"];
                    $eArray["fechaInicio"] = $row["fechaInicio"];
                    $eArray["idPautado"] = $row["idPautado"];
                    $eArray["idSenial"] = $array["idSenial"];
                    $eArray["abreviatura"] = $array["abreviatura"];
                    $eArray["senialAlternativa"] = $array["nombre"];
                    $eArray["logo"] = $array["logo"];
                    $eArray["categoria"] = $array["categoriaSenial"];
                    $eArray["canal"] = $array["canal"];
                    $eArray["titulo"] = $pautado->programa->titulo;
                    $pautado->programa->emisiones[] = new emision($eArray);
                }
            }
      $resultado .= $pautado->getJsonObj() . ",";
  }

  
  /* QUITO LA ULTIMA COMA */
  $resultado = substr($resultado, 0, strlen($resultado) - 1) ;
  print '{"error":"no","resultado":['.$resultado.']}';
}

/**
 * @param type $idAlineacion
 * @param type $idSenial
 * @param type $hDesde
 * @param type $hHasta
 * @return type
 */
function ProgramacionSenialDesdeHasta($idAlineacion,$idSenial,$hDesde,$hHasta) 
{
    $db = new DB(Config::DB_HOST, Config::DB_USER, Config::DB_PASS);
    $db->selectDb(Config::DB_SCHEMA);
    $db->name = "webService_mobile";
    $db->ShowError(true);
    $dt_ws = new data_table();
    //$a = "CALL sp_mobile_pautado_x_senial_alineacion({$idAlineacion}, {$idSenial},  ADDDATE(NOW(),INTERVAL -30 MINUTE),ADDDATE(NOW(),INTERVAL {$horas} HOUR));";
    $db->run_queryi("CALL sp_mobile_pautado_x_senial_alineacion({$idAlineacion}, {$idSenial}, ADDDATE(NOW(),INTERVAL -{$hDesde} HOUR),ADDDATE(NOW(),INTERVAL {$hHasta} HOUR));");
    $dt_ws = $db->get_data_table(0);
    
   if(!tiene_datos($dt_ws)) return;
   
  $resultado="";
  for($i=0; $i<$dt_ws->row_count(); $i++)
  {
      $array = $dt_ws->get_associative_row($i);
      $pautado = new pautado($array);
      $pautado->senial = new senial($array);
      $resultado .= $pautado->getJsonObj() . ",";
  }
  /* QUITO LA ULTIMA COMA */
  $resultado = substr($resultado, 0, strlen($resultado) - 1) ;
  print '{"error":"no","resultado":['.$resultado.']}';
}
//FACEBOOK
/**
 * Login o registro con facebook
 * @param string $token
 */
function loginConFacebook($token, $cliente = 0){

   //mailBienvenida($para, $token);
    
    $url = "https://graph.facebook.com/me?fields=id,name,email,first_name,last_name&access_token={$token}";
    
    $json = file_get_contents($url);

    $fb_user = json_decode($json);
    if (isset($fb_user->id)){//SI EXISTE UN USUARIO LOGUEADO DE FACEBOOK -> TOKEN VALIDO
        $user = getUserBySocialId($fb_user->id,"facebook");
        if(!$user) {//SI EL USUARIO NO ESTA REGISTRADO -> LO REGISTRO
              if(is_null($fb_user->email)) {
                $fb_user->email = $fb_user->name;
                }
           $clave_usuario = md5($fb_user->email . Config::ENCRYPT_STRING);
           RegistroUsuario($fb_user->first_name, $fb_user->last_name, $fb_user->email, $clave_usuario, $cliente, $fb_user->id, "facebook");
        } else { //SI EL USUARIO ESTA REGISTRADO -> LO LOGUEO
            LogInUsuario($user->email, $user->getClave());
       }
    } else { //TOKEN INVALIDO, DEVUELVO ERROR DE FACEBOOK
        print $json;
    }
}
//TWITTER
/**
 * Login o registro con twitter.
 * @param string $oauth_token
 * @param string $oauth_token_secret
 * @param string $oauth_verifier
 */
function loginConTwitter($oauth_token, $oauth_token_secret, $cliente = 53){
    
    require Reportv::CLASS_PATH . "tw/twitter.php";

    $db = new DB(Config::DB_HOST, Config::DB_USER, Config::DB_PASS);
    $db->selectDb(Config::DB_REPORTV_APP);
    $db->name = "webService_mobile";
    $db->ShowError(true);
    $dt_ws = new data_table();

    $db->run_queryi("SELECT * FROM tbl_cliente WHERE id_cliente = {$cliente};");
    $dt_ws = $db->get_data_table(0);
    if($dt_ws == null || $dt_ws->row_count() < 1  )
    {
        $twitter = new \TijsVerkoyen\Twitter\Twitter(Mobile::TW_CONSUMER_KEY, Mobile::TW_CONSUMER_SECRET);
    }
    else 
    {
        $cliente = new cliente($dt_ws->get_associative_row(0));
        $twitter = new \TijsVerkoyen\Twitter\Twitter($cliente->tw_consumer_key, $cliente->tw_consumer_secret);
    }
    
    $twitter->setOAuthToken($oauth_token);
    $twitter->setOAuthTokenSecret($oauth_token_secret);
    $tw_user = $twitter->accountVerifyCredentials();
  //  var_dump($tw_user);
        if (isset($tw_user["id"])){
        $user = getUserBySocialId($tw_user["id"],"twitter");
            if(!$user) {
                 $clave_usuario = md5($tw_user["screen_name"] . Config::ENCRYPT_STRING);
                RegistroUsuario($tw_user["name"], $tw_user["name"], $tw_user["screen_name"], $clave_usuario, $cliente->id_cliente, $tw_user["id"], "twitter");
                //print "El usuario no existe Chiche, hay que registrarlo.";
            } else {
                LogInUsuario($user->usuario, $user->getClave());
           }
        } else {
            print json_encode($tw_user);
            //print error::token_invalido();
        }
}
/**
 * Obtiene un usuario ya registrado usando como parametro de busqueda el id de facebook o id de twitter
 * @param int $id
 * @param string $red
 * @return boolean|\usuario
 */
function getUserBySocialId($id,$red){
    $db = new DB(Config::DB_HOST, Config::DB_USER, Config::DB_PASS);
    $db->selectDb(Config::DB_REPORTV_APP);
    $db->name = "webService_mobile";
    $db->ShowError(true);
    $dt_ws = new data_table();

    $db->run_queryi("CALL Sp_Get_Usuario_By_Social_Id({$id},'{$red}');");
    $dt_ws = $db->get_data_table(0);
    if($dt_ws == null || $dt_ws->row_count() < 1  )
    {
        return false;
    }
    else {
        $user = new usuario($dt_ws->get_associative_row(0));
        return $user;
    }
}
/**
 * Valida si el data table tiene datos, sino retorna un json informando el error
 * @param data_table $dt_ws
 * @return boolean
 */
function tiene_datos($dt_ws){
    if($dt_ws == null || $dt_ws->row_count() < 1  )
        {
            print error::sin_datos();
            return false;
        }
       return true;
}
/**
 * Obtiene el usuario correspondiente al token, retorna un data_table.
 * @param type $db Objeto DB.
 * Retorna Objeto usuario si tiene datos, sino retorna false
 */
function getUserByToken($db,$token){
    $db->selectDb(Config::DB_REPORTV_APP);
    $db->name = "webService_mobile";
    $db->ShowError(true);
    $dt_ws = new data_table();

    $db->run_queryi("CALL Sp_Get_Usuario_By_Token('{$token}')");
    $dt_ws = $db->get_data_table(0);
    if($dt_ws == null || $dt_ws->row_count() < 1  )
    {       
        print error::token_invalido();
        return false;
    }
    else {
        $user = new usuario($dt_ws->get_associative_row(0));
        return $user;
    }
}

/**
 * @param type $idPrograma
 * @param type $idAlineacion
 * @return type
 */
function ProgramacionPrograma($idPrograma,$idAlineacion) 
{
    $db = new DB(Config::DB_HOST, Config::DB_USER, Config::DB_PASS);
    $db->selectDb(Config::DB_SCHEMA);
    $db->name = "webService_mobile";
    $db->ShowError(true);
    $dt_ws = new data_table();
    $db->run_queryi("call sp_mobile_programacion_programa({$idPrograma},{$idAlineacion});");
    $dt_ws = $db->get_data_table(0);
    if(!tiene_datos($dt_ws)){ return; }
    $resultado="";
        for($i=0; $i<$dt_ws->row_count(); $i++)
        {
            $row = $dt_ws->get_associative_row($i);
            $pautado = new pautado($row);
            $pautado->senial = new senial(array("idSenial" => $row['idSenial']
                                                  ,"nombre" => $row['senialAlternativa']
                                                  ,"logo" => $row['logo']
                                                  ,"canal" => $row['canal']
                                                  ,"abreviatura" => $row['abreviatura']));
            $resultado .= $pautado->getJsonObj() . ",";
        }
  /* QUITO LA ULTIMA COMA */
  $resultado = substr($resultado, 0, strlen($resultado) - 1) ;
  print '{"error":"no","resultado":['.$resultado.']}';
}
/**
 * @param type $idPrograma
 * @param type $idAlineacion
 * @return type
 */
function alineacionXOrigen($idCliente,$latitud,$longitud) 
{
    /**
     * CONSULTO EN GOOGLE MAPS POR GEOLOCALIZACION
     */
    $url = 'http://maps.google.com/maps/api/geocode/json?sensor=false&language=es&latlng=' . $latitud . ',' . $longitud;
    $ch = curl_init(); 
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($ch,CURLOPT_HEADER, false);
    $output=curl_exec($ch);
    $data = json_decode($output, true);
    $zoneInfo = array('country' => null,'state' => null,'city' => null,'locality' => null);
    if(count($data['results'])>0){
        $components = $data['results'][0]['address_components'];
            foreach ($components as $c) {
                if ($c['types'][0] == 'country') {
                    $zoneInfo['country'] = $c['long_name'];
                }
                if ($c['types'][0] == 'administrative_area_level_1') {
                    $zoneInfo['state'] = $c['long_name'];
                }
                if ($c['types'][0] == 'administrative_area_level_2' || $c['types'][0] == 'neighborhood') {
                    $zoneInfo['city'] = $c['long_name'];
                }
            }
        }
        curl_close($ch);
        
    $db = new DB(Config::DB_HOST, Config::DB_USER, Config::DB_PASS);
    $db->selectDb(Config::DB_SCHEMA);
    $db->name = "webService_mobile";
    $db->ShowError(true);
    $dt_ws = new data_table();
    $pais = utf8_decode($zoneInfo['country']);
    $provincia = utf8_decode($zoneInfo['state']);
    $ciudad = utf8_decode($zoneInfo['city']);
   // print "call sp_mobile_alineacion_x_origen({$idCliente},'$pais','$ciudad','$provincia');";
    $db->run_queryi("call sp_mobile_alineacion_x_origen({$idCliente},'$pais','$ciudad','$provincia');");

    $dt_ws = $db->get_data_table(0);
    if(!tiene_datos($dt_ws)){ return; }
    $resultado="";
        for($i=0; $i<$dt_ws->row_count(); $i++)
        {
            $row = $dt_ws->get_associative_row($i);
            $alineacion = new alineacion($row);
            $alineacion->pais = new pais($row);
            $alineacion->provincia = new provincia($row);
            $alineacion->ciudad = new ciudad($row);
            $resultado .= $alineacion->getJsonObj() . ",";

        }
        
  /* QUITO LA ULTIMA COMA */
  $resultado = substr($resultado, 0, strlen($resultado) - 1) ;
  print '{"error":"no","resultado":['.$resultado.']}';
}

/**
 * @param int $idCliente
 * @return json
 */
function alineacionXCliente($idCliente) 
{
    $db = new DB(Config::DB_HOST, Config::DB_USER, Config::DB_PASS);
    $db->selectDb(Config::DB_SCHEMA);
    $db->name = "webService_mobile";
    $db->ShowError(true);
    $dt_ws = new data_table();
    $db->run_queryi("call sp_mobile_alineacion_x_origen({$idCliente},'','','');");
    $dt_ws = $db->get_data_table(0);
    if(!tiene_datos($dt_ws)){ return; }
    $resultado="";
    $paises = array();
    $pais = new pais(array("id" => 0, "nombre" => "nuevo","isoCountryCode" => ""));
    $provincia = new provincia(array("id_provincia" => 0, "provincia" => "nuevo"));
    $ciudad = new ciudad(array("id_ciudad" => 0, "ciudad" => "nuevo"));
    $i=0;
    //print_r($dt_ws); exit;
        while($i < $dt_ws->row_count())
        {
            $row = $dt_ws->get_associative_row($i);
           
            if($pais->id != $row['id']){
                $pais = new pais($row);
                $paises[] = $pais;
                }
            if($provincia->id != $row['id_provincia']){
                $provincia = new provincia($row);
                $pais->provincia[$provincia->nombre] = $provincia;
            }
            
            if($ciudad->id != $row['id_ciudad']){
                $ciudad = new ciudad($row);
                $pais->provincia[$provincia->nombre]->ciudad[$ciudad->nombre] = $ciudad;
            }
                $alineacion = new alineacion($row);
                $pais->provincia[$provincia->nombre]->ciudad[$ciudad->nombre]->alineacion[] = $alineacion;

            $i++;
        }
        foreach ($paises as $pais){
            $resultado .= $pais->getJsonObj() . ",";
        }
  /* QUITO LA ULTIMA COMA */
  $resultado = substr($resultado, 0, strlen($resultado) - 1) ;
  print '{"error":"no","resultado":['.$resultado.']}';
}

/**
 * @param type $idPrograma
 * @param type $idAlineacion
 * @return type
 */
//error_reporting(0);
function grillaXAlineacion($idAlineacion,$hora,$idCategoria,$cantProg=2)
{
    $db = new DB(Config::DB_HOST, Config::DB_USER, Config::DB_PASS);
    $db->selectDb(Config::DB_SCHEMA);
    $db->name = "webService_mobile";
    $db->ShowError(true);
    $dt_ws = new data_table();
    $db->run_queryi("call sp_mobile_grilla_x_alineacion({$idAlineacion},'{$hora}',{$idCategoria});");
   // print "call sp_mobile_grilla_x_alineacion({$idAlineacion},'{$hora}',{$idCategoria});";
    $dt_ws = $db->get_data_table(0);
    if(!tiene_datos($dt_ws)){ return; }
    $resultado="";
    $seniales = array();
    $senial = new senial(array("idSenial" => 0,"nombre" => "nueva" ,"logo" => "logo", "canal" => 'canal', "abreviatura" => 'abreviatura'));
    $i = 0;    
    // $cantProg = 3;
    while($i < $dt_ws->row_count())
        {
            $row = $dt_ws->get_associative_row($i);
            if($senial->id != $row['idSenial']){
                $senial = new senial($row);

                for($a = 0; $a<$cantProg; $a++){
                    //FALTA VALIDAR QUE LA SEÑAL TRAIGA POR LO MENOS 2 EVENTOS
                     $pautado = new pautado($dt_ws->get_associative_row($i+$a));
                     $pautado->senial = new senial($dt_ws->get_associative_row($i+$a));
                     //if(!is_null($pautado->idPautado))
                         $senial->pautados[] = $pautado; 
                    }
                $seniales[] = $senial;
             }
            $i++;
        }
        foreach ($seniales as $senial){
            $resultado .= $senial->getJsonObj() . ",";
        }
  /* QUITO LA ULTIMA COMA */
  $resultado = substr($resultado, 0, strlen($resultado) - 1) ;
  print '{"error":"no","resultado":['.$resultado.']}';
}

function grillaXAlineacionORIGINAL($idAlineacion,$hora,$idCategoria)
{
    $db = new DB(Config::DB_HOST, Config::DB_USER, Config::DB_PASS);
    $db->selectDb(Config::DB_SCHEMA);
    $db->name = "webService_mobile";
    $db->ShowError(true);
    $dt_ws = new data_table();
    $db->run_queryi("call sp_mobile_grilla_x_alineacion({$idAlineacion},'{$hora}',{$idCategoria});");
    
    $dt_ws = $db->get_data_table(0);
    if(!tiene_datos($dt_ws)){ return; }
    $resultado="";
    $seniales = array();
    $senial = new senial(array("idSenial" => 0,"nombre" => "nueva" ,"logo" => "logo", "canal" => 'canal', "abreviatura" => 'abreviatura'));
    $i = 0;    
    while($i < $dt_ws->row_count())
        {
            $row = $dt_ws->get_associative_row($i);
            if($senial->id != $row['idSenial']){
                $senial = new senial($row);
                
                //for($a = 0; $a<2; $a++){
                    //FALTA VALIDAR QUE LA SEÑAL TRAIGA POR LO MENOS 2 EVENTOS
                     $pautado = new pautado($dt_ws->get_associative_row($i));
                     $pautado->senial = new senial($dt_ws->get_associative_row($i));
                     $senial->pautados[] = $pautado;
                     if($i+1< $dt_ws->row_count()){
                        $row = $dt_ws->get_associative_row($i+1);
                        if($senial->id == $row['idSenial']){
                            $pautado = new pautado($dt_ws->get_associative_row($i+1));
                            $pautado->senial = new senial($dt_ws->get_associative_row($i+1));
                            $senial->pautados[] = $pautado;
                        } else {
                            $pautado = new pautado($dt_ws->get_associative_row($i));
                            $pautado->titulo = "A confirmar";
                            
                            $pautado->fechaInicio = $pautado->fechaFin;
                            
                            $fechaFin = date_create($pautado->fechaInicio);
                            date_add($fechaFin, date_interval_create_from_date_string($pautado->duracion .' minutes'));
                            $pautado->fechaFin = date_format($fechaFin,"Y-m-d H:i:s");
                            
                            $pautado->programa->tituloOriginal = "A confirmar";
                            $pautado->programa->tituloPrograma = "A confirmar";
                            $pautado->capitulo = null;

                            $pautado->senial = new senial($dt_ws->get_associative_row($i));
                            $senial->pautados[] = $pautado;
                        }
                     }
                //}
                $seniales[] = $senial;
               }
            $i++;
        }
        foreach ($seniales as $senial){
            $resultado .= $senial->getJsonObj() . ",";
        }
  /* QUITO LA ULTIMA COMA */
  $resultado = substr($resultado, 0, strlen($resultado) - 1) ;
  print '{"error":"no","resultado":['.$resultado.']}';
}
/***********************************************************
* Envio de email
************************************************************/
function mailBienvenida($para, $nombre){
	include(Reportv::CLASS_PATH . "/phpMailer_v2.3/class.phpmailer.php");
            $mail = new PHPMailer();
            $mail->IsSMTP();
            $mail->CharSet  = 'utf-8';
            $mail->Host     = "mail.reportv.com.ar";
            $mail->From     = "reportvmobile@reportv.com.ar";
            $mail->FromName = "Reportv Mobile";
            $mail->Subject  = "Bienvenido";
            $mail->AddAddress($para, $nombre);
            $mail->IsHTML(true);
            $mail->Body = file_get_contents("./email/email.html");
            $mail->Send();
}