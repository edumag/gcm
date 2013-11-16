<?php

/**
 * @file descargables/eventos_usuario.php
 * @brief Eventos para descargables
 * @defgroup eventos_descargables Eventos de descargables
 * @ingroup modulo_descargables
 * @ingroup eventos
 * @{
 */

if ( !isset($_SESSION['edit']) || $_SESSION['edit'] != 'si' ) {

   /** Si estamos editando procesamos textp */
   $eventos['postcontenido']['procesar_texto'][1] = '';

   }

/** Si se pide un archivo se presenta */
$eventos['precarga']['presenta_contenido'][1] = '';

/** Presentar lista de archivos descargables */
$eventos['postcontenido']['lista_descargables'][1] = '';

/** @} */
?>
