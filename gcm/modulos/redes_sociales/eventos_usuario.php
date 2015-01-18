<?php

/**
 * @file redes_sociales/eventos_usuario.php
 * @brief Eventos para redes_sociales
 * @defgroup eventos_redes_sociales Eventos de redes_sociales
 * @ingroup modulo_redes_sociales
 * @ingroup eventos
 * @{
 */

/** Evento de ejemplo */
// $eventos['evento']['metodo'][6]='';

$eventos['cabeceraDerecha']['botones'][2]='';
$eventos['heads']['presentar_heads_dinamicos'][1] = '';

$this->set_lista_blanca('redes_sociales','insert_button');
$this->set_lista_blanca('redes_sociales','insert_metatags');

/** @} */
?>
