<?php

/**
 * @file Imagenes.php
 * @brief Módulo para la gestión de imágenes
 *
 * @package Modulos
 */

/** 
 * @class Imagenes
 * @brief Tratamiento de imagenes
 *
 * Con este módulo estandarizamos la forma de tratar las imágenes,
 * a la hora de subirla al servidor y adaptarlas al tamaño especificado y
 * a la hora de presentarlas con la generación dethumbnail, que agilice 
 * la presentación de las mismas.
 *
 */

class Imagenes extends Modulos {

   public $altoMaxImg;         ///< Alto máximo para las imágenes
   public $anchoMaxImg;        ///< Ancho máximo para las imágenes   
   public $altoMaxMiniatura;   ///< Alto máximo para las miniaturas
   public $anchoMaxMiniatura;  ///< Ancho máximo para las miniaturas

   /** Nombre del proyecto */

   private $proyecto;

   /** Idioma por defecto */

   private $idioma;

   /** Constructor */

   function __construct() {

      global $gcm;

      parent::__construct();

      $this->altoMaxImg = $this->config('altoMaxImg');
      $this->anchoMaxImg = $this->config('anchoMaxImg');
      $this->altoMaxMiniatura = $this->config('altoMaxMiniatura');
      $this->anchoMaxMiniatura = $this->config('anchoMaxMiniatura');

      $this->proyecto = $gcm->config('admin','Proyecto');
      $this->idioma   = $gcm->config('idiomas','Idioma por defecto');

      }

   /**
    * test
    */

   function test() {

      // Comprobar existencia de archivos necesarios
      $this->ejecuta_test('Comprobar configuración: alto máximo de imagenes',$this->altoMaxImg);
      $this->ejecuta_test('Comprobar configuración: ancho máximo de imagenes',$this->anchoMaxImg);
      $this->ejecuta_test('Comprobar configuración: alto máximo de miniaturas',$this->altoMaxMiniatura);
      $this->ejecuta_test('Comprobar configuración: alto máximo de miniaturas',$this->anchoMaxMiniatura);
      $this->ejecuta_test('Comprobar configuración: dominio de proyecto',$this->proyecto);
      $this->ejecuta_test('Comprobar configuración: Idioma por defecto',$this->idioma);

      }

      
   /**
    * Enlazamos las imágenes dentro de contenido hacia thickbox
    *
    * @param $e Evento
    * @param $args Argumentos
    */

   function imagenes2thickbox($e, $args=FALSE) {

      global $gcm;

      $this->librerias_js('jquery.colorbox.js');
      $this->javascripts('imagenes.js');
      ?>
      <script>
      addLoadEvent(function(){
         img2thickbox();
      });
      </script>
      <?php
      }
   }

?>
