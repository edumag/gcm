<?php

/**
 * @file temas/eventos_usuario.php
 * @brief Eventos para temas
 * @defgroup eventos_temas Eventos de temas
 * @ingroup modulo_temas
 * @ingroup eventos
 * @{
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
$eventos['tituloProyecto']['presentar_titulo'][1]='';

/** Añadir css a los heads */
$eventos['heads']['incluir_css_head'][5] = '';

/** Añadir javascript basico a los heads */
$eventos['heads']['incluir_javascript_head'][5] = '';

/** Añadir javascripts */
$eventos['scripts']['incluir_javascript'][1] = '';

/** Ocultar contenido desde título */
$eventos['precarga']['ocultar_contenido_desde_titulo'][10] = '';

/** @} */
?>
