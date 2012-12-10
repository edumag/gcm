<?php

/**
 * Fichero plantilla para generar eventos de usuario
 *
 * formato:
 * $eventos['<evento>']['<acción>']="<prioridad>|<argumentos>";
 *
 * ejemplo:
 * $eventos['columna']['ultimas_entradas']="2|num=7&seccion=".Router::get_s()."&formato=1";
 *
 * @category Gcm
 * @package Modulos
 * @subpackage <nombre de módulo>
 * @author    <autor> <email>
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU/GPL
 * @version   SVN $Id: eventos_usuario.php 227 2010-04-22 08:56:35Z eduardo $ 
 */

if ( !isset($_SESSION['edit']) || $_SESSION['edit'] != 'si' ) {
   $eventos['postcontenido']['procesar_texto'][1] = '';
   }
?>
