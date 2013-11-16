<?php

/**
 * @file contenidos/eventos_admin.php
 * @brief Eventos administrativos para contenidos
 * @defgroup eventos_admin_contenidos Eventos administrativos de Contenidos
 * @ingroup modulo_contenidos
 * @ingroup eventos
 * @{
 */

/** 
 * Guardamos contenido como borrador, solo sera visible para los 
 * administradores: Contenidos::guardar_como_borrador()
 */
$eventos['guardar_como_borrador']['guardar'][10]='';

/** Contenidos::enrutar() */
$eventos['postguardar_como_borrador']['enrutar'][100]='';

/**
 * Convertir borrador en contenido visible a usuario: 
 * Contenidos::publicar_borrador() 
 */
$eventos['publicar_borrador']['ejecutar_mover'][1]='';

/** Tras publicar borrador lo presentamos: Contenidos::enrutar()  */
$eventos['postpublicar_borrador']['enrutar'][100]='';

/** actualizar_contenido a Contenidos::guardar() */
$eventos['actualizar_contenido']['guardar'][1]='';

/** Contenidos::enrutar() */
$eventos['postactualizar_contenido']['enrutar'][100]='';

/** borrar a Contenidos::borrar() */
$eventos['borrar']['borrar'][1]='';

/** ejecutar_borrar a Contenidos::ejecutar_borrar() */
$eventos['ejecutar_borrar']['ejecutar_borrar'][1]='';

/** Contenidos::enrutar() */
$eventos['postejecutar_borrar']['enrutar'][100]='';

/** nueva_seccion a Contenidos::nueva_seccion() */
$eventos['nueva_seccion']['nueva_seccion'][1]='';

/** ejecutar_mover_seccion a Contenidos::ejecutar_nueva_seccion() */
$eventos['ejecutar_nueva_seccion']['ejecutar_nueva_seccion'][1]='';

/** Contenidos::enrutar() */
$eventos['postejecutar_nueva_seccion']['enrutar'][100]='';

/** 
 * Presentar formulario para guardar contenido:
 * Contenidos::guardar_como()
 */
$eventos['guardar_como']['guardar_como'][1]='';

/** 
 * vertodo a Contenidos::vertodo()
 * Presentar todo el contenido, para tener una vista rapida 
 */
$eventos['vertodo']['vertodo'][1]='';

/** Traducir a otro idioma traducir a Contenidos::editar() */
$eventos['traducir']['editar'][1]='';

/** Traducir a otro idioma traducir a Contenidos::guardar() */
$eventos['ejecutar_traducir']['guardar'][1]='';

/** Contenidos::enrutar() */
$eventos['postejecutar_traducir']['enrutar'][100]='';

/** Cambiar el titulo del contenido: Contenidos::mover() */
$eventos['mover']['mover'][1]='';

/** Contenido nuevo Contenidos::ejecutar_mover() */
$eventos['ejecutar_mover']['ejecutar_mover'][1]='';

/** Contenidos::enrutar() */
$eventos['postejecutar_mover']['enrutar'][100]='';

/** Cambiar el nombre de la sección: Contenidos::mover_seccion() */
$eventos['mover_seccion']['mover_seccion'][1]='';

/** Ejecutar Cambiar el nombre de la sección: Contenidos::ejecutar_mover_seccion() */
$eventos['ejecutar_mover_seccion']['ejecutar_mover_seccion'][1]='';

/** Contenidos::enrutar() */
$eventos['postejecutar_mover_seccion']['enrutar'][100]='';

/** Formulario para contenido nuevo: Contenidos::nueco() */
$eventos['nuevo']['nuevo'][1]='';

/** Guardar contenido nuevo: Contenidos::guardar() */
$eventos['ejecutar_nuevo']['guardar'][1]='';

/** Contenidos::enrutar() */
$eventos['postejecutar_nuevo']['enrutar'][100]='';

/** Editar contenido ya existente: Contenidos::editar() */
$eventos['editar_contenido']['editar'][1]='';

/** @} */

?>
