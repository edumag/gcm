<?php

/**
 * @file      Plantilla.php
 * @brief     Sistema de plantilla
 * @ingroup gcm_lib
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 * Copyright  Copyright (c) 2010, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

/** 
 * Sistema de plantilla
 *
 * Añadir contenido sobre una plantilla html
 *
 * ejemplo de uso::
 *
 *     $plantilla = new Plantilla(GCM_DIR.'modulos/gcm/temas/html/principal.html', $this);
 *     echo $plantilla->procesaPlantilla();
 *
 * En la clase llamadora tendra que haber un metodo llamado procesaContenido($elemto)
 * que retorne el contenido correspondiente.
 *
 * EL archivo de plantilla html debe tener entre corchetes el nombre del elemento, {elemento}
 * IMPORTANTE: Solo debe tener caracteres alfanumericos.
 *
 * En caso de tener un elemento llamado contenido, se procesara el último, esto nos permite
 * que cuando procesemos contenido haya pasado ya por todo lo demas y si hay algún mensaje
 * que contenido deba de presentar lo tendra en cuenta.
 *
 * @ingroup gcm_lib
 */

class Plantilla {

   private $cPlantilla;                  ///< Contenido de la plantilla
   private $elementos;                   ///< Elementos a insertar en la plantilla
   private $objeto;                      ///< Referencia al objeto que genera el contenido
   private $plantilla;                   ///< fichero de plantilla
   private $elementos_vitales_primeros;  ///< lista de elementos imprescindibles primeros con el orden adecuado.

   /**
    *  lista de elementos imprescindibles últimos con el orden adecuado, scripts el último para que no se
    *  deje ningún archivo javascript
    */

   private $elementos_vitales_ultimos;
   private $contenido_elementos;         ///< Contenido de los elementos despues de procesarlos

   /**
    * Constructor
    *
    * Cargamos contenido de plantilla y lista de elementos
    *
    * @param $plantilla Fichero de plantilla
    * @param $objeto Objeto que procesara el contenido
    *
    */

   function __construct($plantilla, $objeto) {

      $this->objeto = $objeto;
      $this->plantilla = $plantilla; 

      $this->elementos_vitales_primeros = array('contenido','titulo');
      $this->elementos_vitales_ultimos  = array('avisos','debug','scripts','menuadmin');

      /* Los elementos los buscamos en plantilla */

      //$this->cPlantilla = @file_get_contents($this->plantilla);
      $this->cPlantilla = get_include_contents($this->plantilla);

      preg_match_all('/\{[A-Za-z]+\}/', $this->cPlantilla, $el, PREG_PATTERN_ORDER);

      $this->elementos = $el[0];

   }

   /**
    * Procesamos la plantilla.
    *
    * Recogemos el contenido de cada elemento para después remplazar los
    * elementos de la plantilla con el contenido.
    *
    * Primero elementos vitales primeros después elementos vitales 
    * últimos y por último los elementos que encontramos.
    *
    * Hacemos que contenido se reemplace el último para no dar 
    * problemas al editar el archivo de plantilla con los elementos
    * dentro de ella.
    */

   function procesaPlantilla() {

      $salida = $this->cPlantilla;

      foreach ( $this->elementos_vitales_primeros as $el) {
         registrar(__FILE__,__LINE__,"Procesamos elemento [$el] de plantilla");
         $this->contenido_elementos[$el] = $this->objeto->procesaContenido($el);
         }

      foreach ( $this->elementos as $el ) {

         $el = substr($el, 1, strlen($el)-2);

         if ( !in_array($el,$this->elementos_vitales_ultimos) && !in_array($el,$this->elementos_vitales_primeros) ) {
           registrar(__FILE__,__LINE__,"Procesamos elemento [$el] de plantilla");
            $this->contenido_elementos[$el] = $this->objeto->procesaContenido($el);
            }

         }
            
      foreach ( $this->elementos_vitales_ultimos as $el) {
         registrar(__FILE__,__LINE__,"Procesamos elemento [$el] de plantilla");
         $this->contenido_elementos[$el] = $this->objeto->procesaContenido($el);
         }

      foreach ( $this->contenido_elementos as $key => $contenidos ) {

         /* Dejamos contenido para el final */

         if ( $key == 'contenido' ) continue ;

         if ( !$contenidos ) {

            if ( ( in_array($key,$this->elementos_vitales_primeros) || in_array($key,$this->elementos_vitales_ultimos ) && $key !== 'scripts' ) ) {
               registrar(__FILE__,__LINE__,
                  'No tenemos contenido vital para ['.$key.'] de plantilla ['.$this->plantilla.'] y es necesario para el correcto funcionamiento');
            } else {
               registrar(__FILE__,__LINE__,'Elemento ['.$key.'] de plantilla ['.$this->plantilla.'] vacio');
               }

            }

         $resultado = "\n<!-- Elemento:".$key." -->\n".$contenidos."\n<!-- Acaba:".$key." -->\n";
         $salida = str_replace('{'.$key.'}', $resultado, $salida);

         registrar(__FILE__,__LINE__,'Plantilla->procesaPlantilla()  Finaliza proceso en plantilla para elemento ['.$el.']');


         }

      $key = 'contenido';
      $contenidos = $this->contenido_elementos['contenido'];

      if ( !$contenidos ) {
         registrar(__FILE__,__LINE__,'No tenemos contenido para contenido ['.$contenidos.'] de plantilla ['.$this->plantilla.']');
      } else {
         $resultado = '<div id="'.$key.'">'.$contenidos.'</div>';
         $salida = str_replace('{'.$key.'}', $resultado, $salida);
         }

      return $salida;

      }

   }

?>
