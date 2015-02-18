<?php

/**
 * @file      Metatags.php
 * @brief     Metatags html
 *
 * Añadir heads a la página web
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

/** 
 * Metatags html
 *
 * Los metatags generales se crean a partir del archivo de configuración del módulo.
 *
 * Podemos ampliar y mejorar los metatags desde dublincore
 *
 * En este enlace encontraremos un formulario para añadirlos http://webposible.com/utilidades/dublincore-metadata-gen/
 *
 * Tambien podemos añadir geotags, http://www.addressfix.com/
 *
 * @category Gcm
 * @package   Metatags
 * @author Eduardo Magrané
 * @version 0.1
 *
 */

class Metatags extends Modulos {

   private $Proyecto;
   private $Titulo;
   private $Descripcion;
   private $keywords;


   /** Constructor */

   function __construct() {

      parent::__construct();

      $this->Titulo      = $this->config('name');

      }

   /**
    * Presentar los heads dinámicos
    */

   function presentar_heads_dinamicos() {

      global $gcm;

      if ( $gcm->titulo ) {
         $titulo = trim($gcm->titulo);
      } elseif ( Router::$c == 'index.html' || ! Router::$c ) {
         $titulo=trim(Router::$estamos);
         $titulo = literal(trim($titulo),1);
      } else {
         $titulo=str_replace('.html','',Router::$c);
         $titulo = literal(trim($titulo),1);
         }

      $titulo_pagina = ( $gcm->titulo ) ? trim($gcm->titulo) : trim($titulo);
      $titulo_pagina = strip_tags($titulo_pagina);
      $titulo_pagina = eregi_replace("[\n|\r|\n\r]",'',$titulo_pagina);
      $titulo_pagina = trim($titulo_pagina);
      // Quitamos más de un espacio en blanco.
      while ( strpos($titulo_pagina,'  ') !== FALSE ) {
        $titulo_pagina = str_replace('  ',' ',$titulo_pagina);
      }

      $titulo = eregi_replace("[\n|\r|\n\r]",'',strip_tags(trim($this->Titulo).' :: '.trim($titulo_pagina)));

      // Recogemos archivo de configuración
      include(dirname(__FILE__).'/../config/config.php');
      $metatags = $config;

      if ( is_file('DATOS/configuracion/metatags/config.php') ) {
        include('DATOS/configuracion/metatags/config.php');
        $metatags = array_merge($metatags, $config);
      }

      include ($gcm->event->instancias['temas']->ruta('metatags','html','heads.phtml'));

      }
   }

?>
