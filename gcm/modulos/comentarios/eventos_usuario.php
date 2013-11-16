<?php

/**
 * @file comentarios/eventos_usuario.php
 * @brief Eventos para comentarios
 * @defgroup eventos_comentarios Eventos de comentarios
 * @ingroup modulo_comentarios
 * @ingroup eventos
 * @{
 */

/** Presentamos últimos comentarios */
$eventos['columna']['ultimos'][6]='';

/** Presentamos comentarios de una entrada */
$eventos['postcontenido']['presentar_comentarios'][3]='';

/** Formulario para añadir comentario a una entrada */
$eventos['postcontenido']['formulario'][4]='';

/** Modificación de comentario */
$eventos['modificar_comentario']['modificar'][1] = ( isset($_REQUEST['id']) ) ? $_REQUEST['id'] : NULL ;

//$eventos['precontenido']['verificar_entrada'][1]='';

/** @} */
?>
