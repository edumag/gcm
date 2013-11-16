<?php

/**
 * @file cache_http/eventos_usuario.php
 * @brief Eventos para cache_http
 * @defgroup eventos_cache_http Eventos de cache_http
 * @ingroup modulo_cache_http
 * @ingroup eventos
 * @{
 */

/** Antes de cargar la pagina comprobamos si tenemos cache */

$eventos['precarga']['alInicio'][1] = '';

/** Después de cargar la pagina guardamos en cache */

$eventos['postcarga']['alFinal'][1] = '';

/** Borramos la cache */

$eventos['borrar_cache']['borrar'][1] = 'todo';

/** @} */
?>
