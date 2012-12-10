<?php

/**
 * @file      inicio.php
 * @brief     Archivo principal para los proyectos
 *
 * Desde aqui se va construllendo todo el sistema.
 *                                                                                                 
 * @author    Eduardo Magrané 
 *
 * @internal
 *   Created  07/10/10
 *  Revision  SVN $Id: inicio.php 468 2011-02-04 07:37:39Z eduardo $
 * Copyright  Copyright (c) 2010, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

/* Clase Gcm */

require(GCM_DIR.'lib/int/gcm/lib/Gcm.php');

$GCM_LG = array();      ///< Literales de gcm
$LG = array();          ///< Literales de proyecto

$gcm = new Gcm();       ///< Instancia de Gcm

/* Iniciamos aplicación */

$gcm->inicia();

?>

