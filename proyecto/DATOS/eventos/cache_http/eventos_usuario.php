<?php

/**
 * @file  eventos_usuario.php
 * @brief Eventos de usuario para módulo Cache_http
 *
 * formato:
 * @code
 * $eventos[evento][acción][prioridad] = "argumentos";
 * @endcode
 *
 * ejemplo:
 * @code
 * $eventos['columna']['ultimas_entradas'][2] = "num=7&seccion=".Router::get_s()."&formato=1";
 * @endcode
 *
 * @ingroup Cache_http
 *
 * @author   Eduardo Magrané edu@lesolivex.com
 * @internal
 *   license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU/GPL
 *   version   SVN $Id: eventos_usuario.php 182 2010-02-26 15:11:50Z eduardo $ 
 */

/**
 * Eventos de usuario
 */

$eventos['precarga']['alInicio'][1] = '';
$eventos['postcarga']['alFinal'][1] = '';
$eventos['borrar_cache']['borrar'][1] = 'todo';
?>
