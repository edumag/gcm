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
   $menuAdmin['Administración']['boton']['constantes']['activado']=1;
   $menuAdmin['Administración']['boton']['constantes']['title']="Editar constantes";
   $menuAdmin['Administración']['boton']['constantes']['link']="?m=constantes&a=administrar";

   }

/** @} */
