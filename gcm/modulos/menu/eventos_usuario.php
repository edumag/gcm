<?php

/**
 * @file menu/eventos_usuario.php
 * @brief Eventos para menu
 * @defgroup eventos_menu Eventos de menu
 * @ingroup modulo_menu
 * @ingroup eventos
 * @{
 */

/** Menú principal */
$eventos['cabecera']['menu_principal'][0] = '';

/** Menú para la barra de navegación */
$eventos['columna']['barra_navegacion'][1] = '';

// añadimos metodo menu_ajax a la lista blanca, para que no se necesiten 
// permisos para lanzarlo.

$this->set_lista_blanca('menu','menu_ajax');
$this->set_lista_blanca('menu','menu_ajax_off');

/** @} */

?>
