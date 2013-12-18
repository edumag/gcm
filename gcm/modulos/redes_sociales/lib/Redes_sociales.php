<?php

/**
 * @file Redes_sociales.php
 * @brief
 *
 * @ingroup modulo_redes_sociales
 * @author    Eduardo Magrané 
 *
 * @internal
 * Copyright  Copyright (c) 2011, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

/**
 * @class Redes_sociales
 * @brief
 */

class Redes_sociales extends Modulos {

   /** Constructor */

   function __construct() {

      parent::__construct();

      }

   function botones($e, $args=FALSE) {
      
      global $gcm;

      include ($gcm->event->instancias['temas']->ruta('redes_sociales','html','redes_sociales.phtml'));
      }

   }

?>
