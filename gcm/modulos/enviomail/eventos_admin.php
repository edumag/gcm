<?php

/**
 * @file enviomail/eventos_admin.php
 * @brief Eventos administrativos para enviomail
 * @defgroup eventos_admin_enviomail Eventos administrativos de Enviomail
 * @ingroup modulo_enviomail
 * @ingroup eventos
 * @{
 */

/** Eventos para eviomail, para informe de errores */
$eventos['columna']['formulario']['10']='';

/** Error al enviar email */
$eventos['enviar_email_error']['enviar_email_error'][1]='';

/** @} */

/**
 * @defgroup permisos_enviomail Permisos desde el módulo Enviomail
 * @ingroup modulo_enviomail
 * @ingroup permisos_usuarios
 * @{
 */

/** Configuración para los permisos de usuario */
$acciones['enviomail']['enviar_email_error'][] = 'usuario';

/** @} */
