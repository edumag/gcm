<?php

/**
 * @file      index.php
 * @brief     Archivo de inicio para la aplicación
 *
 * Desde aquí definimos definimos el directorio de gcm respecto el del proyecto
 * y incluimos fichero de inicio.
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 *   Created  30/09/10
 *  Revision  SVN $Id: $
 * Copyright  Copyright (c) 2010, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

/**
 * @def GCM_DIR
 *
 * Ubicación de la carpeta de gcm
 *
 */

DEFINE('GCM_DIR','../gcm/');

require(GCM_DIR.'inicio.php');
?>
