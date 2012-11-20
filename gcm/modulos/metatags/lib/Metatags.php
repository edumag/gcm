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

      $this->Titulo      = $this->config('title');

      }

   /**
    * Presentar los heads dinámicos
    */

   function presentar_heads_dinamicos() {

      global $gcm;

      if ( $gcm->titulo ) {
         $titulo = $gcm->titulo;
      } elseif ( Router::$c == 'index.html' || ! Router::$c ) {
         $titulo=Router::$estamos;
         $titulo = literal($titulo);
      } else {
         $titulo=str_replace('.html','',Router::$c);
         $titulo = literal($titulo);
         }

      $titulo_pagina = ( $gcm->titulo ) ? $gcm->titulo : $titulo;

      $titulo         = strip_tags($this->Titulo.' :: '.$titulo_pagina);

      // Recogemos archivo de configuración
      include(dirname(__FILE__).'/../config/config.php');

      // Recorremos variables configurables
      foreach ( $config as $name => $valor ) {

         echo "\n".'<meta name="'.$name.'" content="'.$valor.'" />';

         }

      echo "\n\n";

      include (dirname(__FILE__).'/../html/heads.html');

      }
   }

?>
