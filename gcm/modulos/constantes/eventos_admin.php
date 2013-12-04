<?php

/**
 * @file constantes/eventos_admin.php
 * @brief Eventos administrativos para constantes
 * @defgroup eventos_admin_constantes Eventos administrativos de Constantes
 * @ingroup modulo_constantes
 * @ingroup eventos
 * @{
 */

/** Panel para acceder de forma rapida a los constantes */
$eventos['columna']['panel_constantes'][5] = '';

/** Borrar constantes despues renombrar contenido */
//$eventos['postejecutar_mover']['borrar_constantes_seleccionado'][5] = '';

/** Borrar constantes despues renombrar una sección */
//$eventos['postejecutar_mover_seccion']['borrar_constante_seccion'][5] = '';


/**
 * @defgroup permisos_constantes Permisos desde el módulo Constantes
 * @ingroup modulo_constantes
 * @ingroup permisos_usuarios
 * @{
 */

/** Permitimos administrar constantes a editores */
$acciones['constantes']['panel_constantes'][] = 'editor';

/** Permitimos administrar constantes a traductores */
$acciones['constantes']['panel_constantes'][] = 'traductor';

/** @} */

?>
