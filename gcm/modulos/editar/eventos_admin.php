<?php

/**
 * @file editar/eventos_admin.php
 * @brief Eventos administrativos para editar
 * @defgroup eventos_admin_editar Eventos administrativos de Editar
 * @ingroup modulo_editar
 * @ingroup eventos
 * @{
 */

/** Añadir editor web al editar contenido */
$eventos['editar_contenido']['editor_web'][10]='';

/** Añadir editor web al traducir contenido */
$eventos['traducir']['editor_web'][10]='';

/** Añadir editor web al añadir nuevo contenido */
$eventos['nuevo']['editor_web'][10]='';

/** @} */

/**
 * @defgroup permisos_editar Permisos desde el módulo Editar
 * @ingroup modulo_editar
 * @ingroup permisos_usuarios
 * @{
 */

/** Permitimos editar cualquier contenido a editor */
$acciones['editar']['editar_contenido'][] = 'editor';

/** rol editor puede crear contenido nuevo */
$acciones['editar']['nuevo'][]            = 'editor';

/** rol editor puede traducir */
$acciones['editar']['traducir'][]         = 'editor';

/** rol traductor puede traducir  */
$acciones['editar']['traducir'][]         = 'traductor';

/** @} */

?>
