<?php

/**
 * @file Sitemap.php
 * @brief Creación de archivo sitemap y rss
 *
 * @ingroup modulo_sitemap
 * @author    Eduardo Magrané 
 *
 * @internal
 * Copyright  Copyright (c) 2011, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

/**
 * @class Sitemap
 * @brief
 */

class Sitemap extends Modulos {

   /** Constructor */

   function __construct() {

      parent::__construct();

      }

   /**
    * Generar sitemap
    */

   function generar($e, $args=FALSE) {

     global $gcm;

     // Recogemos listado de contenido
     $listado = $gcm->event->instancias['contenidos']->listado();
     // contruimos array para la plantilla
     // cargamos plantilla
     header('Content-Type: application/xml; charset=utf-8');
     include($gcm->event->instancias['temas']->ruta('sitemap','html','sitemap.phtml'));
     exit();


   }

}

?>
