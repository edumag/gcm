<?php

/**
 * @file constantes/menuAdmin.php
 * @brief Entradas para el menú administrativo
 * @defgroup menu_admin_constantes Menu admin para Constantes
 * @ingroup menu_admin
 * @ingroup modulo_constantes
 * @{
 */

if ( permiso('administrar','constantes') ) {

   /** Administrar constantes */
   $menuAdmin[literal('Administración',3)]['boton'][literal('constantes',3)]['activado']=1;
   $menuAdmin[literal('Administración',3)]['boton'][literal('constantes',3)]['title']=literal("Editar constantes",3);
   $menuAdmin[literal('Administración',3)]['boton'][literal('constantes',3)]['link']="?m=constantes&a=administrar";

   }

/** @} */
