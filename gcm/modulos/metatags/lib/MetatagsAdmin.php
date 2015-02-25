<?php

/**
 * @file      MetatagsAdmin.php
 * @brief     Administración de metatags.
 *
 * Añadir metatags a la página web
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 *   Created  26/02/10
 *  Revision  SVN $Id: Metatags.php 651 2012-10-17 09:19:07Z eduardo $
 * Copyright  Copyright (c) 2010, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

include "Metatags.php";

/** 
 * Administrar Metatags.
 *
 * @category Gcm
 * @package   Metatags
 * @author Eduardo Magrané
 * @version 0.1
 *
 */

class MetatagsAdmin extends Metatags {

   /** Constructor */

   function __construct() {

      parent::__construct();

      $this->Titulo      = $this->config('name');

      }

   function panel($e, $args=FALSE) {
     
     global $gcm;



   }

   }

?>
