<?php

/**
 * @file menuAdmin.php
 * @brief Fichero plantilla para generar contenido en el menú administrativo
 *
 * Formato:
 *
 * @code
 * $menuAdmin['<Sección del menú>']['<tipo>']['<literal>']['activado']= <prioridad>;
 * $menuAdmin['<Sección del menú>']['<tipo>']['<literal>']['title']="<Descripción>";
 * $menuAdmin['<Sección del menú>']['<tipo>']['<literal>']['link']="<Enlace>";
 * @endcode
 *
 * Ejemplo:
 *
 * @code
 * $menuAdmin['Archivo']['boton']['Borrar documento']['activado'] = ( $gcm->presentar_contenido && is_file( Router::get_f() ) ) ? 1 : 0;
 * $menuAdmin['Archivo']['boton']['Borrar documento']['title']="Borrar documento actual";
 * $menuAdmin['Archivo']['boton']['Borrar documento']['link']=Router::get_dir().Router::get_url()."?e=peticion_borrado";
 * @endcode
 *
 * @author    Eduardo Magrané eduardo@mamedu.com
 *
 * @internal
 *   Created  25/11/09
 *  Revision  SVN $Id: menuAdmin.php 663 2012-11-08 08:54:42Z eduardo $
 * Copyright  Copyright (c) 2009, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

if ( permiso('admin_roles','roles') ) {

   $menuAdmin['Administración']['boton']['Roles']['activado'] = 1;
   $menuAdmin['Administración']['boton']['Roles']['title']    = literal('Roles');
   $menuAdmin['Administración']['boton']['Roles']['link']     = Router::$base.'roles/admin_roles';

   }
   
?>
