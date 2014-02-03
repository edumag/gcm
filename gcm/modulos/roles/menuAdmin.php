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
   $menuAdmin[literal('Administración',3)]['boton'][literal('Roles',3)]['activado'] = 1;
   $menuAdmin[literal('Administración',3)]['boton'][literal('Roles',3)]['title']    = literal('Roles',3);
   $menuAdmin[literal('Administración',3)]['boton'][literal('Roles',3)]['link']     = Router::$base.'roles/admin_roles';

   }
   

/** @} */
?>
