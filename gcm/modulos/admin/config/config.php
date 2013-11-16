<?php

/**
 * @file admin/config/config.php
 * @brief Configuración Admin
 * @defgroup configuracion_admin Configuración para Admin
 * @ingroup modulo_admin
 * @{
 */

$config['Proyecto']='gcm';
$config['bd_conexion']='sqlite:DATOS/proyecto.db';
$config['bd_usuario'] = '';
$config['bd_pass']='';
$config['Sufijo para base de datos']='';
$config['Idioma por defecto']='es';
$config['Palabra secreta para depurar']='debug';
$config['Módulos activados'][] = 'cache_http';
$config['Módulos activados'][] = 'comentarios';
$config['Módulos activados'][] = 'descargables';
$config['Módulos activados'][] = 'indexador';
$config['Módulos activados'][] = 'referencias';
$config['Módulos activados'][] = 'widgets';

$config['bd_conexion']='sqlite:DATOS/proyecto.db';
$config['bd_usuario'] = '';
$config['bd_pass']='';
$config['Sufijo para base de datos']='';

/** @} */

?>
