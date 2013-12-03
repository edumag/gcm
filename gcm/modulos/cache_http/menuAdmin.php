<?php

/**
 * @file cache_http/menuAdmin.php
 * @brief Entradas para el menú administrativo
 * @defgroup menu_admin_cache_http Menu admin para Cache_http
 * @ingroup menu_admin
 * @ingroup modulo_cache_http
 * @{
 */

if ( permiso('borrar_cache','cache_http') ) {

   /** Contenido para menú de administración de Cache_http */
   $menuAdmin['Administración']['boton']['Borrar cache']['activado']= 1;
   $menuAdmin['Administración']['boton']['Borrar cache']['title']="Borrar cache";
   $menuAdmin['Administración']['boton']['Borrar cache']['link'] = 
      "javascript:pedirDatos(\"".Router::$dir."ajax/borrar_cache\",\"respuesta_borrar_cache\");";

   }

/** @} */
?>
