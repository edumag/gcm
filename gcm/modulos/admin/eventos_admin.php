<?php

/**
 * @file eventos_admin.php
 * @brief Definimos eventos administrativos y acciones permitidas para usuario registrado
 * @defgroup eventos_admin Eventos de Admin
 * @ingroup modulo_admin
 * @ingroup eventos
 * @{
 */

$eventos['menuadmin']['presentar_menu_administrativo'][1]='';
$eventos['infoserver']['infoserver'][1]='';
$eventos['test']['ejecutar_tests_modulos'][1]='';

/** Si no se ha confirmado el cambio del administrador por defecto */

if ( ! $gcm->config('admin','configuracion_confirmada') ) {

   $eventos['precontenido']['confirmar_configuracion'][1]='';

}

/// Caja con la información de los módulos para el desarrollo
$eventos['columna']['caja_info_dev'][3]='';

/// Contenido para la caja info dev
$eventos['contenido_caja_info_dev']['contenido_caja_info_dev'][1]='';

/// Tras ejecutar cron mostramos registros y salimos 
$eventos['cron']['cerrar_cron'][2000]='';

/// Salir de sesión 
$eventos['salir']['cerrar_sesion'][2000]='';

/** @} */

// Permitimos a cualquier usuario registrado ver la caja_dev
$this->set_lista_blanca('admin','cerrar_sesion');
$this->set_lista_blanca('admin','caja_info_dev');
$this->set_lista_blanca('admin','contenido_caja_info_dev');

?>
