<?php

/**
 * @file admin/eventos_usuario.php
 * @brief Eventos para admin
 * @defgroup eventos_admin Eventos de admin
 * @ingroup modulo_admin
 * @ingroup eventos
 * @{
 */

/** 
 * Este evento solo es para evitar que de error al intentar presentr el menú 
 * administrativo sin estar logeados 
 */

$eventos['menuadmin']['menuadmin_sin_login'][1]='';

/** Panel para logearse */

$eventos['columna']['panel_login'][10]='';

/** Formulario de registro al entrar en registro/ */

$eventos['registro']['formulario_registro'][10]='';

/** Llamar al módulo correspondiente al encontrar un shorcode en contenido */

$eventos['postcontenido']['shortcode'][100]='';

/** En caso de error enviamos cabeceras 404 */

$eventos['error']['cabecera_error'][1] = '';

/** @} */
?>
