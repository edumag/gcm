<?php

/**
 * @file indexador/menuAdmin.php
 * @brief Entradas para el menú administrativo
 * @defgroup menu_admin_indexador Menu admin para Indexador
 * @ingroup menu_admin
 * @ingroup modulo_indexador
 * @{
 */

if ( permiso() ) {

   $menuAdmin['Seguimiento']['boton']['indexador']['activado']=1;
   $menuAdmin['Seguimiento']['boton']['indexador']['nombre']="Reindexador";
   $menuAdmin['Seguimiento']['boton']['indexador']['title']="Reindexar el contenido";
   $menuAdmin['Seguimiento']['boton']['indexador']['link']=Router::$base."?e=reindexar";

   }

/** @} */

?>
