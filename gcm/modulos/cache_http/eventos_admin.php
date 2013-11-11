<?php

/**
 * @file      eventos_admin.php
 * @brief     Eventos de usuario para Cache_http
 *
 * formato:
 * @code
 * $eventos['<evento>']['<acción>'][<prioridad>] = "<argumentos>";
 * @endcode
 *
 * ejemplo:
 * @code
 * $eventos['columna']['ultimas_entradas'][2] = "num=7&seccion=".Router::get_s()."&formato=1";
 * @endcode
 *
 * @ingroup Cache_http
 * @author    Eduardo Magrané eduardo@mamedu.com
 *
 * @internal
 *   Created  25/11/09
 *  Revision  SVN $Id: eventos_admin.php 182 2010-02-26 15:11:50Z eduardo $
 * Copyright  Copyright (c) 2009, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

/** Vaciar cache */
$eventos['cron']['cron'][2]='';


?>
