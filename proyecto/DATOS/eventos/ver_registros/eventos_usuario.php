<?php

/**
 * @file eventos_usuarios.php
 * @brief Eventos de usuario para ver_registros
 */

/** Añadimos formulario de debug */
$eventos['debug']['debug'][15] = '';

/** Para iniciar paneles en javascript */
$eventos['postcontenido_ajax']['postcontenido_ajax'][1]='';

/** Enviar email con los registros generados */
$eventos['cron']['envio_registros_mail'][100]='';

/** Borrar registros viejos */
$eventos['cron']['borrar_registros_antiguos'][10]='';

?>
