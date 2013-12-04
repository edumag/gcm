<?php

/**
 * @file constantes/eventos_usuario.php
 * @brief Eventos para constantes
 * @defgroup eventos_constantes Eventos de constantes
 * @ingroup modulo_constantes
 * @ingroup eventos
 * @{
 */


if ( !isset($_SESSION['edit']) || $_SESSION['edit'] != 'si' ) {
   /** Si no estamos editando procesamos texto */
   $eventos['postcontenido']['procesar_texto'][100] = '';
   }

/** @} */
?>
