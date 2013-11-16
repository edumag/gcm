<?php

/**
 * @file literales/eventos_usuario.php
 * @brief Eventos para literales
 * @defgroup eventos_literales Eventos de literales
 * @ingroup modulo_literales
 * @ingroup eventos
 * @{
 */


if ( !isset($_SESSION['edit']) || $_SESSION['edit'] != 'si' ) {
   /** Si no estamos editando procesamos texto */
   $eventos['postcontenido']['procesar_texto'][1] = '';
   }

/** @} */
?>
