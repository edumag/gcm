<?php

/**
 * @file      GcmConfigFactory.php
 * @brief     Patron Factory para la clase GcmConfig
 * @ingroup   GcmConfig
 * @author    Eduardo Magrané 
 *
 * @internal
 * Copyright  Copyright (c) 2010, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

/** Añadimos GcmConfig */

require_once('GcmConfig.php');

/**
 * @class GcmConfigFactory
 * @brief Patron Factory para la clase GcmConfig
 * @ingroup GcmConfig
 *
 * Para asegurarnos que recogemos la misma instancia para cada fichero de 
 * configuración
 *
 * Uso:
 * @code
 * $conf = GcmConfigFactory::GetGcmConfig('archivo');
 * @endcode
 *
 * @version 0.1
 */

class GcmConfigFactory {

   public static function GetGcmConfig($archivo) {

      $key = md5(serialize($archivo));

      if ( ! isset($GLOBALS['GcmConfig'][$key]) || ! ($GLOBALS['GcmConfig'][$key] instanceof GcmConfig) ) {

         $GLOBALS['GcmConfig'][$key] = new GcmConfig($archivo);

         }

      return $GLOBALS['GcmConfig'][$key];

      }

   }

?>
