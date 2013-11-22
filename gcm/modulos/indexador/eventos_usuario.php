<?php

/**
 * @file indexador/eventos_usuario.php
 * @brief Eventos para indexador
 * @defgroup eventos_indexador Eventos de indexador
 * @ingroup modulo_indexador
 * @ingroup eventos
 * @{
 */

/** Formulario de busqueda */
$eventos['cabeceraDerecha']['formulario_busqueda'][1]='';

/** Panel con últimas entradas */
$eventos['columna']['ultimas_entradas'][5]="num=4&seccion=".Router::$s."&formato=1";

/** Si estamos en una sección presentamos ultimas entradas de la misma */
$eventos['contenido_dinamico']['contenido_dinamico'][1]='';

/** Presentamos resultado de una busqueda */
$eventos['buscar']['presentar_busquedas'][1]='';

/** Indexar archivos remotamente */
$eventos['indexar']['indexado'][3] = '';

/** 
 * En caso de error de pagina no encontrada mostramos sugerencias según 
 * la pagina que buscó 
 */

$eventos['error']['error'][1]='';

/** @} */
?>
