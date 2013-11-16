<?php

/**
 * @file admin/config/config_es.php
 * @brief Descripciones para la configuración de Admin
 * @defgroup descripciones_configuracion_admin Descripciones para la configuración para Admin 
 * @ingroup modulo_admin
 * @ingroup configuracion_admin
 * @{
 */

$config_DESC['Proyecto']='Dominio del proyecto';
$config_DESC['Módulos activados']='Desactivación de módulos';
$config_DESC['Palabra secreta para depurar']='Añadiendo la palabra seleccionada a la url (?<palabra_secreta>=1), se inicia sesión de depuración, con un cero se termina';

$config_DESC['bd_conexion'] = 'Producción: Driver a usar para conexión con base de datos (PDO). Ejemplos sqlite:/DATOS/proyecto.db o mysql:dbname=gcm';
$config_DESC['bd_usuario']='Producción: Usuario para el servidor de base de datos, en caso de utilizar sqlite no es necesario';
$config_DESC['bd_pass']='Producción: Contraseña para servidor de base de datos, en caso de sqlite no es necesario';
$config_DESC['Sufijo para base de datos']='Producción: Nos permite diferenciar las tablas de los diferentes proyectos en una misma base de datos';

/** @} */
?>
