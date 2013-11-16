<?php

/**
 * @file temas/eventos_admin.php
 * @brief Eventos administrativos para temas
 * @defgroup eventos_admin_temas Eventos administrativos de Temas
 * @ingroup modulo_temas
 * @ingroup eventos
 * @{
 */


/** ¿ Estamos editando temas ? */

if ( Router::$a == 'administrar' && Router::$m == 'temas' ) {
   /** Presentamos colores del tema en columna */
   $eventos['columna']['panel_colores'][1] = '';
   }

