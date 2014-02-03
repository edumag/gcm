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
$menuAdmin2[$usuario]['boton'][literal('Editar perfil',3)]['activado']= 1;
$menuAdmin2[$usuario]['boton'][literal('Editar perfil',3)]['title']=literal("Editar información de usuario",3);
$menuAdmin2[$usuario]['boton'][literal('Editar perfil',3)]['link']=dirname($_SERVER['PHP_SELF'])."/admin/perfil_usuario";
// $menuAdmin2[$usuario]['class']='user';

$menuAdmin2[literal('Salir',3)]['title']=literal("Cerrar sesión",3);
$menuAdmin2[literal('Salir',3)]['link']=Router::$base.Router::$s.Router::$c.'?e=salir';

if ( permiso('test','admin') ) {

$menuAdmin[literal('Administración',3)]['boton']['Tests']['activado']= 1;
$menuAdmin[literal('Administración',3)]['boton']['Tests']['title']=literal("Ejecutar tests",3);
$menuAdmin[literal('Administración',3)]['boton']['Tests']['link']=dirname($_SERVER['PHP_SELF'])."/test";

   }

if ( permiso('usuarios','admin') ) {

   $menuAdmin[literal('Administración',3)]['boton'][literal('Usuarios',3)]['activado']= 1;
   $menuAdmin[literal('Administración',3)]['boton'][literal('Usuarios',3)]['title']=literal("Administración de usuarios",3);
   $menuAdmin[literal('Administración',3)]['boton'][literal('Usuarios',3)]['link']=dirname($_SERVER['PHP_SELF'])."/admin/usuarios/";
   }

if ( permiso('infoserver','admin') ) {

   $menuAdmin[literal('Administración',3)]['boton']['Info server']['activado']= 1;
   $menuAdmin[literal('Administración',3)]['boton']['Info server']['title']=literal("Información de servidor",3);
   $menuAdmin[literal('Administración',3)]['boton']['Info server']['link']=dirname($_SERVER['PHP_SELF'])."/admin/infoserver";
   }

if ( permiso('configurar_conexiones','admin') ) {


   $menuAdmin[literal('Administración',3)]['boton']['Visualizar conexiones']['activado']= 1;
   $menuAdmin[literal('Administración',3)]['boton']['Visualizar conexiones']['title']=literal("Visualizar conexiones entre Eventos y módulos",3);
   $menuAdmin[literal('Administración',3)]['boton']['Visualizar conexiones']['link']=dirname($_SERVER['PHP_SELF'])."/admin/configurar_conexiones";

   }

/** Secciones del menú predeterminadas */
$menuAdmin[literal('Administración',3)]['title']=literal('Administrar proyecto',3);
$menuAdmin[literal('Configuración',3)]['title']=literal('Configurar proyecto',3);
$menuAdmin[literal('Seguimiento',3)]['title']=literal('Seguimiento del proyecto',3);

/** Sección proyecto */
$proyecto = $gcm->config('admin','Proyecto');
$menuAdmin[$proyecto]['boton'][literal('Modo view',3)]['activado']= 1;
$menuAdmin[$proyecto]['boton'][literal('Modo view',3)]['title']="Modo usuario";
$menuAdmin[$proyecto]['boton'][literal('Modo view',3)]['link']=dirname($_SERVER['PHP_SELF'])."?tema=";
$menuAdmin[$proyecto]['boton'][literal('Modo admin',3)]['activado']= 1;
$menuAdmin[$proyecto]['boton'][literal('Modo admin',3)]['title']="Modo administración";
$menuAdmin[$proyecto]['boton'][literal('Modo admin',3)]['link']=dirname($_SERVER['PHP_SELF'])."?tema=admin";


/** Especificamos peso para ordenar menú */


$menuAdmin[$proyecto]        ['peso'] = -20;
$menuAdmin[literal('Contenidos',3)]     ['peso'] = -10;
$menuAdmin[literal('Administración',3)] ['peso'] = -8;
$menuAdmin[literal('Configuración',3)]  ['peso'] = -6;
$menuAdmin[literal('Seguimiento',3)]    ['peso'] = -4;


/** @} */
?>
