<?php

/**
 * @file responsiveslides.php
 * @brief Slideshow de imágenes
 *
 * @package Modulos
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

      $imagenes_sin_comprobar = glob(Router::$d.$this->imagenes.'/*');
      $imagenes = array();
      foreach ( $imagenes_sin_comprobar as $img ) {
         if ( esImagen($img) ) $imagenes[] = $img;
         }
      $this->javascripts('responsiveslides.min.js');
      include($gcm->event->instancias['temas']->ruta('responsiveslides','html','slide.phtml'));

      }

   }

?>
