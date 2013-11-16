<?php

/**
 * @file mapas/eventos_usuario.php
 * @brief Eventos para mapas
 * @defgroup eventos_mapas Eventos de mapas
 * @ingroup modulo_mapas
 * @ingroup eventos
 * @{
 */


/** añadimos metodo mapas a la lista blanca, para que no se necesiten permisos */

$this->set_lista_blanca('mapas','mapa');


/** @} */
?>
