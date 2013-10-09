<?php

/**
 * @file  eventos_usuario.php
 * @brief Eventos de usuario para módulo responsiveslides
 *
 * formato:
 *
 * @code
 * $eventos['<evento>']['<acción>'][<prioridad>] = "<argumentos>";
 * @endcode
 *
 * ejemplo:
 *
 * @code
 * $eventos['columna']['ultimas_entradas'][2] = "num=7&seccion=".Router::get_s()."&formato=1";
 * @endcode
 *
 * @author    Eduardo Magrané eduardo@mamedu.com
 *
 * @internal
 *   Created  25/11/09
 *  Revision  SVN $Id: eventos_usuario.php 663 2012-11-08 08:54:42Z eduardo $
 * Copyright  Copyright (c) 2009, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

$eventos['cabeceraIzquierda']['slide'][1]='';
?>
