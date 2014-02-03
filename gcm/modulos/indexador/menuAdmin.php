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

   $menuAdmin[literal('Seguimiento',3)]['boton'][literal('indexador',3)]['activado']=1;
   $menuAdmin[literal('Seguimiento',3)]['boton'][literal('indexador',3)]['nombre']=literal("Reindexador",3);
   $menuAdmin[literal('Seguimiento',3)]['boton'][literal('indexador',3)]['title']=literal("Reindexar el contenido",3);
   $menuAdmin[literal('Seguimiento',3)]['boton'][literal('indexador',3)]['link']=Router::$base."?e=reindexar";

   }

/** @} */

?>
