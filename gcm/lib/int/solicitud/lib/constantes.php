<?php

/**
 * @file      constantes.php
 * @brief     Constantes predefinidas para el módulo Solicitud
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 *   Created  04/02/11
 *  Revision  SVN $Id: $
 * Copyright  Copyright (c) 2011, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

define('ENTRADAS_COOKIE',1);
define('ENTRADAS_GET',2);
define('ENTRADAS_POST',4);

/** Restricciones */

define('RT_LONG_MIN',1);
define('RT_LONG_MAX',2);
define('RT_CARACTERES_PERMITIDOS',3);
define('RT_CARACTERES_NO_PERMITIDOS',4);
define('RT_MENOR_QUE',5);
define('RT_MAYOR_QUE',6);
define('RT_IGUAL_QUE',7);
define('RT_NO_IGUAL',8);
define('RT_PASA_EXPRESION_REGULAR',9);
define('RT_NO_PASA_EXPRESION_REGULAR',10);
define('RT_NO_ES_NUMERO',11);
define('RT_REQUERIDO',12);
define('RT_MAIL',13);
?>
