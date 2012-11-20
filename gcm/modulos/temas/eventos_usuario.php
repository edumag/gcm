<?php

/**
 * @file      eventos_usuario.php
 * @brief     Eventos para Temas
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 *   Created  25/11/09
 *  Revision  SVN $Id: eventos_usuario.php 545 2011-12-15 18:04:48Z eduardo $
 * Copyright  Copyright (c) 2009, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

/**
 * Recoger datos de Router para saber si se esta pidiendo
 * algun fichero de temas, proyecto.css o proyecto.js
 *
 */

$eventos['precarga']['inspeccionar_router'][3] = '';

/** Llenar el pie de página */
$eventos['pie']['pie'][1] = '';

/** Llenar el título de página */
$eventos['cabeceraIzquierda']['presentar_titulo'][1]='';

/** Añadir css a los heads */
$eventos['heads']['incluir_css_head'][5] = '';

/** Añadir javascript basico a los heads */
$eventos['heads']['incluir_javascript_head'][5] = '';

/** Añadir javascripts */
$eventos['scripts']['incluir_javascript'][1] = '';

/** Ocultar contenido desde título */
$eventos['precarga']['ocultar_contenido_desde_titulo'][10] = '';
?>
