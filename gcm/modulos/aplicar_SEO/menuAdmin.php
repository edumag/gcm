<?php

/**
 * @file aplicar_SEO/menuAdmin.php
 * @brief Entradas para el menú administrativo
 * @defgroup menu_admin_aplicar_SEO Menu admin para Aplicar_SEO
 * @ingroup menu_admin
 * @ingroup modulo_aplicar_SEO
 * @{
 */

if ( permiso('seo','aplicar_SEO') ) {

   /** Información sobre SEO del proyecto */
   $menuAdmin[literal('Administración',3)]['boton']['SEO']['activado']= 1;
   $menuAdmin[literal('Administración',3)]['boton']['SEO']['title']=literal("Gestionar SEO",3);
   $menuAdmin[literal('Administración',3)]['boton']['SEO']['link']=dirname($_SERVER['PHP_SELF'])."/seo";

   }

/** @} */
?>
