<?php

/** Eventod de módulo menu
 *
 * @package   menu
 * @category  Modulos
 * @author    Eduardo Magrané <eduardo mamedu com>
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU/GPL
 * @version   SVN $Id: eventos_usuario.php 126 2009-11-24 22:20:10Z eduardo $ 
 *
 */

$eventos['cabecera']['menu_principal'][0] = '';
$eventos['columna']['barra_navegacion'][1] = '';

// añadimos metodo menu_ajax a la lista blanca, para que no se necesiten 
// permisos para lanzarlo.

$this->set_lista_blanca('menu','menu_ajax');
$this->set_lista_blanca('menu','menu_ajax_off');

?>
