<?php

/**
 * @file contenidos/menuAdmin.php
 * @brief Entradas para el menú administrativo
 * @defgroup menu_admin_contenidos Menu admin para Contenidos
 * @ingroup menu_admin
 * @ingroup modulo_contenidos
 * @{
 */


/** Configuración para el menú administrativo */

if ( permiso('editar','contenidos') ) {

   /* sección */

   $menuAdmin[literal('Contenidos',3)]['boton'][literal('Nuevo',3)]['activado']= 1;
   $menuAdmin[literal('Contenidos',3)]['boton'][literal('Nuevo',3)]['title']=literal("Crear nuevo contenido",3);
   $menuAdmin[literal('Contenidos',3)]['boton'][literal('Nuevo',3)]['link']="?e=nuevo";

   $menuAdmin[literal('Contenidos',3)]['boton'][literal('Editar',3)]['activado']= (Router::$sin_traduccion) ? 0 : 1;
   $menuAdmin[literal('Contenidos',3)]['boton'][literal('Editar',3)]['title']=literal("Editar documento actual",3);
   $menuAdmin[literal('Contenidos',3)]['boton'][literal('Editar',3)]['link']=Router::$dir.Router::$url."?e=editar_contenido";

   $menuAdmin[literal('Contenidos',3)]['boton'][literal('Traducir',3)]['activado']= (Router::$sin_traduccion) ? 1 : 0;
   $menuAdmin[literal('Contenidos',3)]['boton'][literal('Traducir',3)]['title']=literal("Traducir documento actual",3);
   $menuAdmin[literal('Contenidos',3)]['boton'][literal('Traducir',3)]['link']=Router::$dir.Router::$url."?e=traducir";

   $menuAdmin[literal('Contenidos',3)]['boton'][literal('Borrar',3)]['activado'] = ( is_file( Router::$f ) ) ? 1 : 0;
   $menuAdmin[literal('Contenidos',3)]['boton'][literal('Borrar',3)]['title']=literal("Borrar documento actual",3);
   $menuAdmin[literal('Contenidos',3)]['boton'][literal('Borrar',3)]['link']=Router::$dir.Router::$url."?e=borrar";

   $menuAdmin[literal('Contenidos',3)]['boton'][literal('Renombrar / Mover',3)]['activado']= ( is_file(Router::$f) ) ? 1 : 0;
   $menuAdmin[literal('Contenidos',3)]['boton'][literal('Renombrar / Mover',3)]['title']=literal("Mover o cambiar nombre de contenido",3);
   $menuAdmin[literal('Contenidos',3)]['boton'][literal('Renombrar / Mover',3)]['link']=Router::$dir.Router::$url."?e=mover";

   $menuAdmin[literal('Contenidos',3)]['boton'][literal('Guardar como',3)]['activado']= ( is_file( Router::$f ) ) ? 1 : 0;
   $menuAdmin[literal('Contenidos',3)]['boton'][literal('Guardar como',3)]['title']=literal("Guardar contenido con otro nombre",3);
   $menuAdmin[literal('Contenidos',3)]['boton'][literal('Guardar como',3)]['link']=Router::$dir.Router::$url."?e=guardar_como";

   $menuAdmin[literal('Contenidos',3)]['boton'][literal('Nueva sección',3)]['activado']=1;
   $menuAdmin[literal('Contenidos',3)]['boton'][literal('Nueva sección',3)]['title']=literal("Crear una nueva sección",3);
   $menuAdmin[literal('Contenidos',3)]['boton'][literal('Nueva sección',3)]['link']=Router::$dir.Router::$url."?e=nueva_seccion";

   $menuAdmin[literal('Contenidos',3)]['boton'][literal('Renombrar sección',3)]['activado']= ( Router::$s ) ? 1 : 0;
   $menuAdmin[literal('Contenidos',3)]['boton'][literal('Renombrar sección',3)]['title']=literal("Renombrar o mover sección actual",3);
   $menuAdmin[literal('Contenidos',3)]['boton'][literal('Renombrar sección',3)]['link']=Router::$dir.Router::$url."?e=mover_seccion";

   $menuAdmin[literal('Contenidos',3)]['boton'][literal('Borrar sección',3)]['activado']= ( Router::$s ) ? 1 : 0;
   $menuAdmin[literal('Contenidos',3)]['boton'][literal('Borrar sección',3)]['title']=literal("Borrar sección",3);
   $menuAdmin[literal('Contenidos',3)]['boton'][literal('Borrar sección',3)]['link']=Router::$dir.Router::$s."?e=borrar";

   }

$menuAdmin[literal('Contenidos',3)]['boton']['Ver todo']['activado']= 1; 
$menuAdmin[literal('Contenidos',3)]['boton']['Ver todo']['title']=literal("Listar contenido",3);
$menuAdmin[literal('Contenidos',3)]['boton']['Ver todo']['link']=Router::$dir.Router::$url."?e=vertodo";

/** @} */

?>

