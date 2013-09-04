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
 *  Revision  SVN $Id: eventos_admin.php 422 2010-12-02 15:41:29Z eduardo $
 * Copyright  Copyright (c) 2009, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

$menuAdmin['Administración']['boton']['SEO']['activado']= 1;
$menuAdmin['Administración']['boton']['SEO']['title']="Gestionar SEO";
$menuAdmin['Administración']['boton']['SEO']['link']=dirname($_SERVER['PHP_SELF'])."/seo";

?>
