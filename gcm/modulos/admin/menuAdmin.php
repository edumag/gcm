<?php

/**
 * @file admin/menuAdmin.php
 * @brief Entradas para el menú administrativo
 * @defgroup menu_admin_admin Menu admin para Admin
 * @ingroup menu_admin
 * @ingroup modulo_admin
 * @{
 */


# $menuAdmin['Administración']['title']='Administrar proyecto';

$usuario = $_SESSION[$gcm->sufijo.'usuario'];

/** Editar perfil usuario */
$menuAdmin2[$usuario]['boton']['Editar perfil']['activado']= 1;
$menuAdmin2[$usuario]['boton']['Editar perfil']['title']="Editar información de usuario";
$menuAdmin2[$usuario]['boton']['Editar perfil']['link']=dirname($_SERVER['PHP_SELF'])."/admin/perfil_usuario";
// $menuAdmin2[$usuario]['class']='user';

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

/** Secciones del menú predeterminadas */
$menuAdmin['Administración']['title']='Administrar proyecto';
$menuAdmin['Configuración']['title']='Configurar proyecto';
$menuAdmin['Seguimiento']['title']='Seguimiento del proyecto';

/** Sección proyecto */
$proyecto = $gcm->config('admin','Proyecto');
$menuAdmin[$proyecto]['boton']['Modo view']['activado']= 1;
$menuAdmin[$proyecto]['boton']['Modo view']['title']="Modo usuario";
$menuAdmin[$proyecto]['boton']['Modo view']['link']=dirname($_SERVER['PHP_SELF'])."?tema=";
$menuAdmin[$proyecto]['boton']['Modo admin']['activado']= 1;
$menuAdmin[$proyecto]['boton']['Modo admin']['title']="Modo administración";
$menuAdmin[$proyecto]['boton']['Modo admin']['link']=dirname($_SERVER['PHP_SELF'])."?tema=admin";


/** Especificamos peso para ordenar menú */


$menuAdmin[$proyecto]        ['peso'] = -20;
$menuAdmin['Contenidos']     ['peso'] = -10;
$menuAdmin['Administración'] ['peso'] = -8;
$menuAdmin['Configuración']  ['peso'] = -6;
$menuAdmin['Seguimiento']    ['peso'] = -4;


/** @} */
?>
