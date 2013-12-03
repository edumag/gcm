<?php

/**
 * @file editar/menuAdmin.php
 * @brief Entradas para el menú administrativo
 * @defgroup menu_admin_editar Menu admin para Editar
 * @ingroup menu_admin
 * @ingroup modulo_editar
 * @{
 */

if ( permiso('administrar_constantes','editar') ) {

   //Constantes
   $menuAdmin['Administración']['boton']['Constantes']['activado']=1;
   $menuAdmin['Administración']['boton']['Constantes']['title']="Editar constantes";
   $menuAdmin['Administración']['boton']['Constantes']['link']="?m=editar&a=administrar_constantes";

   }

/** @} */
?>

