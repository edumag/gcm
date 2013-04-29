<?php

/**
 * @file eventos_admin.php
 * @brief Eventos para editar
 */

$eventos['editar_contenido']['editor_web'][10]='';
$eventos['traducir']['editor_web'][10]='';
$eventos['nuevo']['editor_web'][10]='';

/**
 * Configuración para los permisos de usuario
 */

$acciones['editar']['editar_contenido'][] = 'editor';
$acciones['editar']['nuevo'][]            = 'editor';
$acciones['editar']['traducir'][]         = 'editor';
$acciones['editar']['traducir'][]         = 'traductor';

