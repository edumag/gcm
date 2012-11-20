<?php

/**
 * @file  eventos_usuario.php
 * @brief Eventos de usuario para módulo Gdoxygen
 *
 * formato:
 * $eventos['<evento>']['<acción>'][<prioridad>] = "<argumentos>";
 *
 * ejemplo:
 * $eventos['columna']['ultimas_entradas'][2] = "num=7&seccion=".Router::get_s()."&formato=1";
 *
 * @category Gcm
 * @package Modulos
 * @subpackage Gdoxygen
 * @author    Eduardo Magrané eduardo@mamedu.com
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU/GPL
 * @version   SVN $Id: eventos_usuario.php 422 2010-12-02 15:41:29Z eduardo $ 
 */

/** Presentar resultados de doxygen */
$eventos['buscar']['presentar_busquedas'][5]='';
/** Detectar si estamos dentro de doxygen */
$eventos['precarga']['detectar_gdoxygen'][5] = '';

?>
