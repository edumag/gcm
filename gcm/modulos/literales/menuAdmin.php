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
   $menuAdmin['Administración']['boton']['Literales']['activado']=1;
   $menuAdmin['Administración']['boton']['Literales']['title']="Editar literales";
   $menuAdmin['Administración']['boton']['Literales']['link']="?m=literales&a=administrar";

   }

/** @} */
