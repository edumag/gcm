<?php

# $menuAdmin['Administración']['title']='Administrar proyecto';

$menuAdmin['Administración']['boton']['Editar perfil']['activado']= 1;
$menuAdmin['Administración']['boton']['Editar perfil']['title']="Editar información de usuario";
$menuAdmin['Administración']['boton']['Editar perfil']['link']=dirname($_SERVER['PHP_SELF'])."/admin/perfil_usuario";

$menuAdmin['Administración']['boton']['Tests']['activado']= 1;
$menuAdmin['Administración']['boton']['Tests']['title']="Ejecutar tests";
$menuAdmin['Administración']['boton']['Tests']['link']=dirname($_SERVER['PHP_SELF'])."/test";

if ( permiso() ) {

   $menuAdmin['Administración']['boton']['Usuarios']['activado']= 1;
   $menuAdmin['Administración']['boton']['Usuarios']['title']="Administración de usuarios";
   $menuAdmin['Administración']['boton']['Usuarios']['link']=dirname($_SERVER['PHP_SELF'])."/admin/usuarios/";

   $menuAdmin['Administración']['boton']['Info server']['activado']= 1;
   $menuAdmin['Administración']['boton']['Info server']['title']="Información de servidor";
   $menuAdmin['Administración']['boton']['Info server']['link']=dirname($_SERVER['PHP_SELF'])."/admin/infoserver";

   $menuAdmin['Administración']['boton']['Visualizar conexiones']['activado']= 1;
   $menuAdmin['Administración']['boton']['Visualizar conexiones']['title']="Visualizar conexiones entre Eventos y módulos";
   $menuAdmin['Administración']['boton']['Visualizar conexiones']['link']=dirname($_SERVER['PHP_SELF'])."/admin/configurar_conexiones";

   }

?>
