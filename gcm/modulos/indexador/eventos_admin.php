<?php

/**
 * @file indexador/eventos_admin.php
 * @brief Eventos administrativos para indexador
 * @defgroup eventos_admin_indexador Eventos administrativos de Indexador
 * @ingroup modulo_indexador
 * @ingroup eventos
 * @{
 */

/** @todo Documentar eventos de indexador */
$eventos['postejecutar_borrar']['borrar_archivo_pdo'][1] = '';

$eventos['postejecutar_nuevo']['indexar_archivo_pdo'][1] = '' ;
$eventos['postactualizar_contenido']['indexar_archivo_pdo'][1] = '';
$eventos['postejecutar_mover']['borrar_archivo_pdo'][1] = '';
$eventos['postejecutar_mover']['indexar_archivo_pdo'][2] = '';
$eventos['postpublicar_borrador']['indexar_archivo_pdo'][2] = '';
$eventos['postejecutar_mover_seccion']['cambio_ruta_seccion'][2] = '';
$eventos['reindexar']['reindexar'][1] = '';
$eventos['reindexado_completo']['reindexado_completo'][1] = '';
$eventos['indexar']['indexado'][1] = '';

/** @} */
?>
