<?php

/**
 * @file literales/menuAdmin.php
 * @brief Entradas para el menú administrativo
 * @defgroup menu_admin_literales Menu admin para Literales
 * @ingroup menu_admin
 * @ingroup modulo_literales
 * @{
 */

if ( permiso('administrar','literales') ) {

   /** Administrar literales */
   $menuAdmin[literal('Administración',3)]['boton'][literal('Literales',3)]['activado']=1;
   $menuAdmin[literal('Administración',3)]['boton'][literal('Literales',3)]['title']=literal("Editar literales",3);
   $menuAdmin[literal('Administración',3)]['boton'][literal('Literales',3)]['link']=Router::$base."literales/administrar";

   }

/** @} */
