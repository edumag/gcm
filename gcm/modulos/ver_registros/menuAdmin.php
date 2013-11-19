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
   $menuAdmin['Seguimiento']['boton']['Registros']['activado']=1;
   $menuAdmin['Seguimiento']['boton']['Registros']['title']="Visualización de los registros de la aplicación";
   $menuAdmin['Seguimiento']['boton']['Registros']['link']=Router::$base.'ver_registros/visualizar/'.Router::$url;

   }

/** @} */
?>

