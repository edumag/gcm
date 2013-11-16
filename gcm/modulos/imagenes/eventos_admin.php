<?php

/**
 * @file imagenes/eventos_admin.php
 * @brief Eventos administrativos para imagenes
 * @defgroup eventos_admin_imagenes Eventos administrativos de Imagenes
 * @ingroup modulo_imagenes
 * @ingroup eventos
 * @{
 */


/**
 * Mostramos miniadministrador de imágenes
 */

$eventos['columna']['formulario'][3] = '';

/** @} */

/**
 * @defgroup permisos_imagenes Permisos desde el módulo Imagenes
 * @ingroup modulo_imagenes
 * @ingroup permisos_usuarios
 * @{
 */

/** Configuración para los permisos de editor */

$acciones['imagenes']['formulario'][] = 'editor';

/** @} */
?>
