<?php

/**
 * @file temas/menuAdmin.php
 * @brief Entradas para el menú administrativo
 * @defgroup menu_admin_temas Menu admin para Temas
 * @ingroup menu_admin
 * @ingroup modulo_temas
 * @{
 */

if ( permiso('administrar','temas') ) {

   /** Editar temas */
   $menuAdmin['Administración']['boton']['Editor de temas']['activado']=1;
   $menuAdmin['Administración']['boton']['Editor de temas']['nombre']="Editor";
   $menuAdmin['Administración']['boton']['Editor de temas']['title']="Editar ficheros del proyecto";
   $menuAdmin['Administración']['boton']['Editor de temas']['link']="?m=temas&a=administrar";

   }

/** @} */
?>

