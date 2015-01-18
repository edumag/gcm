<?php

/**
 * @file Responsiveslide.php
 * @brief
 *
 * @ingroup modulo_responsiveslide
 * @author    Eduardo Magrané 
 *
 * @internal
 * Copyright  Copyright (c) 2011, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

/**
 * @class responsiveslides
 * @brief Slideshow de imágenes
 *
 * Recogemos lista de imágenes de la configuración del módulo.
 * para invocar el slide $gcm->events->responsiveslides->slide();
 *
 * @code
 * $args = array('parametros'=>FALSE);
 * $gcm->event->lanzar_accion_modulo('responsiveslides','slide','evento_slide_cabecera',$args);
 * @endcode
 */

class Responsiveslides extends Modulos {

   public $imagenes;

   /** Constructor */

   function __construct() {

      parent::__construct();

      $this->imagenes = $this->config('Imágenes');

      }

   function slide() {

      global $gcm;

      $imagenes_sin_comprobar = glob(Router::$dd.$this->imagenes.'/*');
      $imagenes = array();
      foreach ( $imagenes_sin_comprobar as $img ) {
         if ( esImagen($img) && strpos(basename($img),'-poster') === FALSE ) $imagenes[] = $img;
         }
      $this->javascripts('responsiveslides-modificado.js');
      include($gcm->event->instancias['temas']->ruta('responsiveslides','html','slide.phtml'));

      }

   }

?>
