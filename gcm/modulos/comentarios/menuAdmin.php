<?php

/**
 * @file comentarios/menuAdmin.php
 * @brief Entradas para el menú administrativo
 * @defgroup menu_admin_comentarios Menu admin para Comentarios
 * @ingroup menu_admin
 * @ingroup modulo_comentarios
 * @{
 */

/** Administrar comentarios */
$menuAdmin[literal('Administración',3)]['boton'][literal('Comentarios',3)]['activado']= 1;
$menuAdmin[literal('Administración',3)]['boton'][literal('Comentarios',3)]['title']=literal("Listado de comentarios",3);
$menuAdmin[literal('Administración',3)]['boton'][literal('Comentarios',3)]['link'] = Router::$base.'comentarios/listar';

/** @} */
?>
