<?php

/**
 * Gcm - Gestor de contenido mamedu
 *
 * @category Gcm
 * @package Modulos
 * @subpackage Modulo
 * @author    Eduardo Magrané
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU/GPL
 * @version   SVN $Id: index-modulos.php 96 2009-10-23 07:16:57Z eduardo $ 
 */

require_once(dirname(__FILE__).'/lib/Modulo.php');
if ( ! $modulo ) $modulo = new Modulo();
$modulo->$a($args);

?>
