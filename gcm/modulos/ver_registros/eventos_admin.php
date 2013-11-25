<?php

/**
 * @file ver_registros/eventos_admin.php
 * @brief Eventos administrativos para ver_registros
 * @defgroup eventos_admin_ver_registros Eventos administrativos de Ver_registros
 * @ingroup modulo_ver_registros
 * @ingroup eventos
 * @{
 */


/** Enviar email con los registros generados */
$eventos['cron']['envio_registros_mail'][100]='';

/** Borrar registros viejos */
$eventos['cron']['borrar_registros_antiguos'][10]='';

/** Avisos para usuario */
$eventos['avisos']['presentar_caja_de_avisos'][1]='';


/** @} */

?>
