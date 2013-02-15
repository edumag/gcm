<?php

/**
 * @file menuAdmin.php
 * @brief Fichero plantilla para generar contenido en el menú administrativo
 *
 * Formato:
 * @code
 * $menuAdmin['<Sección del menú>']['<tipo>']['<literal>']['activado']= <prioridad>;
 * $menuAdmin['<Sección del menú>']['<tipo>']['<literal>']['title']="<Descripción>";
 * $menuAdmin['<Sección del menú>']['<tipo>']['<literal>']['link']="<Enlace>";
 * @endcode
 *
 * Ejemplo:
 * @code
 * $menuAdmin['Archivo']['boton']['Borrar documento']['activado'] = ( $gcm->presentar_contenido && is_file( Router::get_f() ) ) ? 1 : 0;
 * $menuAdmin['Archivo']['boton']['Borrar documento']['title']="Borrar documento actual";
 * $menuAdmin['Archivo']['boton']['Borrar documento']['link']=Router::get_dir().Router::get_url()."?e=peticion_borrado";
 * @endcode
 *
 * @see menuAdmin
 * @ingroup Cache_http
 * @author    Eduardo Magrané eduardo@mamedu.com
 * @internal
 *  license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU/GPL
 *  version   SVN $Id: menuAdmin.php 182 2010-02-26 15:11:50Z eduardo $ 
 */

/** Contenido para menú de administración de Cache_http */

$menuAdmin['Administración']['boton']['Borrar cache']['activado']= 1;
$menuAdmin['Administración']['boton']['Borrar cache']['title']="Borrar cache";
$menuAdmin['Administración']['boton']['Borrar cache']['link'] = 
   "javascript:pedirDatos(\"".Router::$dir."ajax/borrar_cache\",\"respuesta_borrar_cache\");";

