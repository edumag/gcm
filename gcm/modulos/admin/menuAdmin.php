<?php

# $menuAdmin['Administración']['title']='Administrar proyecto';

$usuario = $_SESSION[$gcm->sufijo.'usuario'];

$menuAdmin2[$usuario]['boton']['Editar perfil']['activado']= 1;
$menuAdmin2[$usuario]['boton']['Editar perfil']['title']="Editar información de usuario";
$menuAdmin2[$usuario]['boton']['Editar perfil']['link']=dirname($_SERVER['PHP_SELF'])."/admin/perfil_usuario";

$menuAdmin2['Salir']['title']="Cerrar sessión";
$menuAdmin2['Salir']['link']='./?salir=1';

if ( permiso('test','admin') ) {

$menuAdmin['Administración']['boton']['Tests']['activado']= 1;
$menuAdmin['Administración']['boton']['Tests']['title']="Ejecutar tests";
$menuAdmin['Administración']['boton']['Tests']['link']=dirname($_SERVER['PHP_SELF'])."/test";

   }

if ( permiso('usuarios','admin') ) {

   $menuAdmin['Administración']['boton']['Usuarios']['activado']= 1;
   $menuAdmin['Administración']['boton']['Usuarios']['title']="Administración de usuarios";
   $menuAdmin['Administración']['boton']['Usuarios']['link']=dirname($_SERVER['PHP_SELF'])."/admin/usuarios/";
   }

if ( permiso('infoserver','admin') ) {

   $menuAdmin['Administración']['boton']['Info server']['activado']= 1;
   $menuAdmin['Administración']['boton']['Info server']['title']="Información de servidor";
   $menuAdmin['Administración']['boton']['Info server']['link']=dirname($_SERVER['PHP_SELF'])."/admin/infoserver";
   }

if ( permiso('configurar_conexiones','admin') ) {


   $menuAdmin['Administración']['boton']['Visualizar conexiones']['activado']= 1;
   $menuAdmin['Administración']['boton']['Visualizar conexiones']['title']="Visualizar conexiones entre Eventos y módulos";
   $menuAdmin['Administración']['boton']['Visualizar conexiones']['link']=dirname($_SERVER['PHP_SELF'])."/admin/configurar_conexiones";

   }

?>
