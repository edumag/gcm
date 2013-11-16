<?php

/**
 * @file roles/menuAdmin.php
 * @brief Entradas para el menú administrativo
 * @defgroup menu_admin_roles Menu admin para Roles
 * @ingroup menu_admin
 * @ingroup modulo_roles
 * @{
 */


if ( permiso('admin_roles','roles') ) {

   /** Administrar roles de usuario */
   $menuAdmin['Administración']['boton']['Roles']['activado'] = 1;
   $menuAdmin['Administración']['boton']['Roles']['title']    = literal('Roles');
   $menuAdmin['Administración']['boton']['Roles']['link']     = Router::$base.'roles/admin_roles';

   }
   

/** @} */
?>
