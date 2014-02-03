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
   $menuAdmin[literal('Administración',3)]['boton'][literal('Editor de temas',3)]['activado']=1;
   $menuAdmin[literal('Administración',3)]['boton'][literal('Editor de temas',3)]['nombre']=literal("Editor",3);
   $menuAdmin[literal('Administración',3)]['boton'][literal('Editor de temas',3)]['title']=literal("Editar ficheros del proyecto",3);
   $menuAdmin[literal('Administración',3)]['boton'][literal('Editor de temas',3)]['link']="?m=temas&a=administrar";

   }

/** @} */
?>

