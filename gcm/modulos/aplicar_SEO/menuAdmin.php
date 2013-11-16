<?php

/**
 * @file aplicar_SEO/menuAdmin.php
 * @brief Entradas para el menú administrativo
 * @defgroup menu_admin_aplicar_SEO Menu admin para Aplicar_SEO
 * @ingroup menu_admin
 * @ingroup modulo_aplicar_SEO
 * @{
 */

/** Información sobre SEO del proyecto */

$menuAdmin['Administración']['boton']['SEO']['activado']= 1;
$menuAdmin['Administración']['boton']['SEO']['title']="Gestionar SEO";
$menuAdmin['Administración']['boton']['SEO']['link']=dirname($_SERVER['PHP_SELF'])."/seo";
/** @} */
?>
