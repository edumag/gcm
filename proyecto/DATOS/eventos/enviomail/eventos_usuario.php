<?php

/**
 * @file enviomail/eventos_usuario.php
 * @brief Listdo de eventos que no requieren permisos en Enviomail
 * @defgroup lista_blanca_enviomail Eventos de enviomail
 * @ingroup lista_blanca
 * @{
 */


/** Permitimos envio de email a usuarios */
$this->set_lista_blanca('enviomail','enviar_email');

/** @} */
