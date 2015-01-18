<?php

/**
 * @file imagenes/menuAdmin.php
 * @brief Entradas para el menú administrativo
 * @defgroup menu_admin_imagenes Menu admin para Imagenes
 * @ingroup menu_admin
 * @ingroup modulo_imagenes
 * @{
 */

if ( permiso('galeria','imagenes') ) {

   $menuAdmin[literal('Administración',3)]['boton'][literal('Imagenes',3)]['activado']= 1;
   $menuAdmin[literal('Administración',3)]['boton'][literal('Imagenes',3)]['title']=literal("Administrar imágenes",3);
   $menuAdmin[literal('Administración',3)]['boton'][literal('Imagenes',3)]['link']=Router::$dir.'imagenes/galeria/'.Router::$s;
   }

/** @} */
?>
