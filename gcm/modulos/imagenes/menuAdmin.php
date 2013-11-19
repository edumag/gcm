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

   $menuAdmin['Administración']['boton']['Imagenes']['activado']= 1;
   $menuAdmin['Administración']['boton']['Imagenes']['title']="Administrar imágenes";
   $menuAdmin['Administración']['boton']['Imagenes']['link']=Router::$dir.Router::$url."?m=imagenes&a=galeria";
   }

/** @} */
?>
