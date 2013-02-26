<?php

/**
 * @file comentarios/menuAdmin.php
 * @brief Entradas para el menú administrativo
 *
 * @ingroup modulo_comentarios
 */

/** Administrar comentarios */
$menuAdmin['Administración']['boton']['Comentarios']['activado']= 1;
$menuAdmin['Administración']['boton']['Comentarios']['title']="Listado de comentarios";
$menuAdmin['Administración']['boton']['Comentarios']['link'] = Router::$base.'comentarios/listar';

/** @} */
?>
