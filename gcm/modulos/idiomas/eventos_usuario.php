<?php

/**
 * @file idiomas/eventos_usuario.php
 * @brief Eventos para idiomas
 * @defgroup eventos_idiomas Eventos de idiomas
 * @ingroup modulo_idiomas
 * @ingroup eventos
 * @{
 */

$eventos['precarga']['seleccion_idioma'][1] = '';
$eventos['precarga']['definir_idioma'][2] = '';
$eventos['cabeceraDerecha']['selector_idiomas'][1] = '';
$eventos['cabeceraIzquierda']['lista_idiomas'][0] = '';
$eventos['heads']['metatags'][1] = '';

$this->set_lista_blanca('idiomas','metatags');

/** @} */

?>
