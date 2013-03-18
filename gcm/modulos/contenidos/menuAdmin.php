<?php

/** Configuración para el menú administrativo */

if ( permiso('editar_contenido') ) {

   /* sección */

   $menuAdmin['Contenidos']['boton']['Nuevo']['activado']= 1;
   $menuAdmin['Contenidos']['boton']['Nuevo']['title']="Crear nuevo contenido";
   $menuAdmin['Contenidos']['boton']['Nuevo']['link']="?e=nuevo";

   $menuAdmin['Contenidos']['boton']['Editar']['activado']= 1;
   $menuAdmin['Contenidos']['boton']['Editar']['title']="Editar documento actual";
   $menuAdmin['Contenidos']['boton']['Editar']['link']=Router::$dir.Router::$url."?e=editar_contenido";

   $menuAdmin['Contenidos']['boton']['Traducir']['activado']= (Router::$sin_traduccion) ? 1 : 0;
   $menuAdmin['Contenidos']['boton']['Traducir']['title']=literal("Traducir documento actual",3);
   $menuAdmin['Contenidos']['boton']['Traducir']['link']=Router::$dir.Router::$url."?e=traducir";

   $menuAdmin['Contenidos']['boton']['Borrar']['activado'] = ( is_file( Router::$f ) ) ? 1 : 0;
   $menuAdmin['Contenidos']['boton']['Borrar']['title']="Borrar documento actual";
   $menuAdmin['Contenidos']['boton']['Borrar']['link']=Router::$dir.Router::$url."?e=borrar";

   $menuAdmin['Contenidos']['boton']['Renombrar / Mover']['activado']= ( is_file(Router::$f) ) ? 1 : 0;
   $menuAdmin['Contenidos']['boton']['Renombrar / Mover']['title']="Mover o cambiar nombre de contenido";
   $menuAdmin['Contenidos']['boton']['Renombrar / Mover']['link']=Router::$dir.Router::$url."?e=mover";

   $menuAdmin['Contenidos']['boton']['Guardar como']['activado']= ( is_file( Router::$f ) ) ? 1 : 0;
   $menuAdmin['Contenidos']['boton']['Guardar como']['title']="Guardar contenido con otro nombre";
   $menuAdmin['Contenidos']['boton']['Guardar como']['link']=Router::$dir.Router::$url."?e=guardar_como";

   $menuAdmin['Contenidos']['boton']['Nueva sección']['activado']=1;
   $menuAdmin['Contenidos']['boton']['Nueva sección']['title']="Crear una nueva sección";
   $menuAdmin['Contenidos']['boton']['Nueva sección']['link']=Router::$dir.Router::$url."?e=nueva_seccion";

   $menuAdmin['Contenidos']['boton']['Renombrar sección']['activado']= ( Router::$s ) ? 1 : 0;
   $menuAdmin['Contenidos']['boton']['Renombrar sección']['title']="Renombrar o mover sección actual";
   $menuAdmin['Contenidos']['boton']['Renombrar sección']['link']=Router::$dir.Router::$url."?e=mover_seccion";

   $menuAdmin['Contenidos']['boton']['Borrar sección']['activado']= ( Router::$s ) ? 1 : 0;
   $menuAdmin['Contenidos']['boton']['Borrar sección']['title']="Borrar sección";
   $menuAdmin['Contenidos']['boton']['Borrar sección']['link']=Router::$dir.Router::$url."?e=borrar_seccion";

   }

$menuAdmin['Contenidos']['boton']['Ver todo']['activado']= 1; 
$menuAdmin['Contenidos']['boton']['Ver todo']['title']="Listar contenido";
$menuAdmin['Contenidos']['boton']['Ver todo']['link']=Router::$dir.Router::$url."?e=vertodo";

/** Secciones del menú predeterminadas */
$menuAdmin['Administración']['title']='Administrar proyecto';
$menuAdmin['Configuración']['title']='Configurar proyecto';
$menuAdmin['Seguimiento']['title']='Seguimiento del proyecto';
$menuAdmin['Salir']['title']="Cerrar sessión";
$menuAdmin['Salir']['link']='./?salir=1';

?>

