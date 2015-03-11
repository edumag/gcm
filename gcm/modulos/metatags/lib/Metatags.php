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

   private $Titulo;

   // Permitimos cambios en los metatags al vuelo
   public $metatags;

   /** Constructor */

   function __construct() {

     global $gcm;

      parent::__construct();

      $this->Titulo      = $this->config('name');
      $this->metatags    = $this->config();

      $this->metatags['titulo'] = 
        ( isset($this->metatags['url:'.Router::$s.Router::$c][0]['titulo']) )
        ? $this->metatags['url:'.Router::$s.Router::$c][0]['titulo']
        : $this->metatags['titulo'];

      $this->metatags['description'] = 
        ( isset($this->metatags['url:'.Router::$s.Router::$c][0]['description']) )
        ? $this->metatags['url:'.Router::$s.Router::$c][0]['description']
        : $this->metatags['description'];

      $this->metatags['keyworks'] = 
        ( isset($this->metatags['url:'.Router::$s.Router::$c][0]['keyworks']) )
        ? $this->metatags['url:'.Router::$s.Router::$c][0]['keyworks']
        : $this->metatags['keyworks'];

      if ( ! $this->metatags['titulo'] ) {
        if ( $gcm->titulo ) {
          $this->metatags['titulo'] = $this->limpiar_cadena($gcm->titulo);
        } else {
          $this->metatags['titulo'] = ( Router::$c != 'index.html' ) ? str_replace('.html','',Router::$c) : Router::$s;
        }
      }
    }

   /**
    * Limpiar cadena.
    */

   function limpiar_cadena($cadena) {
     $cadena = trim($cadena);
     $cadena = strip_tags($cadena);
     $cadena = eregi_replace("[\n|\r|\n\r]",'',$cadena);
     while ( strpos($cadena,'  ') !== FALSE ) {
       $cadena = str_replace('  ',' ',$cadena);
     }
     return $cadena;
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
      $titulo_pagina = $this->limpiar_cadena($titulo_pagina);

      $titulo = eregi_replace("[\n|\r|\n\r]",'',strip_tags(trim($this->Titulo).' :: '.trim($titulo_pagina)));

      // Recogemos archivo de configuración
      include(dirname(__FILE__).'/../config/config.php');
      $metatags = $config;

      if ( is_file('DATOS/configuracion/metatags/config.php') ) {
        include('DATOS/configuracion/metatags/config.php');
        $metatags = array_merge($metatags, $config);
      }

      // Recogemos cambios que nos hayan enviado desde otros módulos
      if ( $this->metatags ) {
        foreach ( $this->metatags as $key => $val ) {
          $metatags[$key] = $val;
        }
      }

      // Literalizamos los metatags que sea necesario.
      $metatags['subject'] = literal($metatags['subject'],1,NULL,FALSE);
      $metatags['description'] = literal($metatags['description'],1,NULL,FALSE);
      $keys_array = explode(',',$metatags['keywords']);
      $keywords = array();
      foreach ($keys_array as $m) {
        $keywords[] = literal($m,1);
      }
      $metatags['keywords'] = implode(',',$keywords);
      include ($gcm->event->instancias['temas']->ruta('metatags','html','heads.phtml'));

      }
   }

?>
