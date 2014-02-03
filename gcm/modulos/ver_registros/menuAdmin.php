<?php

/**
 * @file ver_registros/menuAdmin.php
 * @brief Entradas para el menú administrativo
 * @defgroup menu_admin_ver_registros Menu admin para Ver_registros
 * @ingroup menu_admin
 * @ingroup modulo_ver_registros
 * @{
 */

if ( permiso('visualizar','ver_registros') ) {

   /** Visualizar registros del proyecto */
   $menuAdmin[literal('Seguimiento',3)]['boton'][literal('Registros',3)]['activado']=1;
   $menuAdmin[literal('Seguimiento',3)]['boton'][literal('Registros',3)]['title']=literal("Visualización de los registros de la aplicación",3);
   $menuAdmin[literal('Seguimiento',3)]['boton'][literal('Registros',3)]['link']=Router::$base.'ver_registros/visualizar/'.Router::$url;

   }

/** @} */
?>

