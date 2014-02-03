<?php

/**
 * @file idiomas/menuAdmin.php
 * @brief Entradas para el menú administrativo
 * @defgroup menu_admin_idiomas Menu admin para Idiomas
 * @ingroup menu_admin
 * @ingroup modulo_idiomas
 * @{
 */

if ( permiso() ) {

   /** Administrar idiomas */
   $menuAdmin[literal('Administración',3)]['boton'][literal('idiomas',3)]['activado']=1;
   $menuAdmin[literal('Administración',3)]['boton'][literal('idiomas',3)]['nombre']=literal("Idiomas",3);
   $menuAdmin[literal('Administración',3)]['boton'][literal('idiomas',3)]['title']=literal("Administracion de los diferentes idiomas",3);
   $menuAdmin[literal('Administración',3)]['boton'][literal('idiomas',3)]['link']= Router::$base."idiomas/administrar";

   }

/** @} */
?>

