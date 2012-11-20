<?php

/**
 * Fichero plantilla para generar contenido en el menú administrativo
 *
 * Formato:
 *
 * $menuAdmin['<Sección del menú>']['<tipo>']['<literal>']['activado']= <prioridad>;
 * $menuAdmin['<Sección del menú>']['<tipo>']['<literal>']['title']="<Descripción>";
 * $menuAdmin['<Sección del menú>']['<tipo>']['<literal>']['link']="<Enlace>";
 *
 * Ejemplo:
 *
 * $menuAdmin['Archivo']['boton']['Borrar documento']['activado'] = ( $gcm->presentar_contenido && is_file( Router::get_f() ) ) ? 1 : 0;
 * $menuAdmin['Archivo']['boton']['Borrar documento']['title']="Borrar documento actual";
 * $menuAdmin['Archivo']['boton']['Borrar documento']['link']=Router::get_dir().Router::get_url()."?e=peticion_borrado";

 * @category Gcm
 * @package MenuAdministratico
 * @subpackage <nombre de módulo>
 * @author    <autor> <email>
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU/GPL
 * @version   SVN $Id: menuAdmin.php 182 2010-02-26 15:11:50Z eduardo $ 
 */

$menuAdmin['Administración']['boton']['Comentarios']['activado']= 1;
$menuAdmin['Administración']['boton']['Comentarios']['title']="Listado de comentarios";
$menuAdmin['Administración']['boton']['Comentarios']['link'] = Router::$base.'comentarios/listar';
