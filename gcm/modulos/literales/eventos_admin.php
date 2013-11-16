<?php

/**
 * @file literales/eventos_admin.php
 * @brief Eventos administrativos para literales
 * @defgroup eventos_admin_literales Eventos administrativos de Literales
 * @ingroup modulo_literales
 * @ingroup eventos
 * @{
 */

/** Panel para acceder de forma rapida a los literales */
$eventos['columna']['panel_literales'][5] = '';

/** Borrar literales despues renombrar contenido */
//$eventos['postejecutar_mover']['borrar_literales_seleccionado'][5] = '';

/** Borrar literales despues renombrar una sección */
//$eventos['postejecutar_mover_seccion']['borrar_literal_seccion'][5] = '';


/**
 * @defgroup permisos_literales Permisos desde el módulo Literales
 * @ingroup modulo_literales
 * @ingroup permisos_usuarios
 * @{
 */

/** Permitimos administrar literales a editores */
$acciones['literales']['panel_literales'][] = 'editor';

/** Permitimos administrar literales a traductores */
$acciones['literales']['panel_literales'][] = 'traductor';

/** @} */

?>
