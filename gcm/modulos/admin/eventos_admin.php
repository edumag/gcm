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

/** Si estamos configurando un módulo activamos tema admin */

$eventos['precarga']['activar_tema_admin'][1]='';

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

/** @} */
?>
