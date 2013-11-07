<?php

/**
 * @file eventos_usuarios.php
 * @brief Eventos de usuario para ver_registros
 */

/** Añadimos formulario de debug */
$eventos['debug']['debug'][15] = '';

/** Avisos para usuario */
$eventos['avisos']['presentar_caja_de_avisos'][1]='';

/** Enviar email con los registros generados */
$eventos['cron']['envio_registros_mail'][100]='';

/** Borrar registros viejos */
$eventos['cron']['borrar_registros_antiguos'][10]='';

?>
