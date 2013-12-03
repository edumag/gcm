<?php

/**
 * @file    RegistroFactory.php
 * @brief   Patron Factory para la clase Registro
 * @ingroup registro
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 *   Created  22/01/10
 *  Revision  SVN $Id: RegistroFactory.php 478 2011-02-28 08:31:31Z eduardo $
 * Copyright  Copyright (c) 2010, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */


/**
 * @class RegistroFactory
 * @brief Patron Factory para la clase Registro
 * @ingroup registro
 *
 * Para asegurarnos que recogemos la misma instancia para cada fichero de 
 * configuración
 *
 * @version 0.1
 */

require_once(dirname(__FILE__).'/Registro.php');

class RegistroFactory {

   public static function getRegistro($base_datos=NULL,$sufijo='') {

      $key = md5(serialize($base_datos));

      if ( ! isset($GLOBALS['Registro']) || ! ($GLOBALS['Registro'][$key] instanceof Registro) ) {

         $GLOBALS['Registro'][$key] = new Registro($base_datos,$sufijo);

         }

      return $GLOBALS['Registro'][$key];

      }

   }

?>
