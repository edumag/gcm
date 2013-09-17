<?php

/**
 * @file      Formulario.php
 * @brief     Generardor de formularios
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 *   Created  09/03/11
 *  Revision  SVN $Id: $
 * Copyright  Copyright (c) 2011, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 *
 * @ingroup crud
 */

/**
 * Formulario.
 *
 * Clase que gestiona la forma en que se presentan los datos para ser
 * modificados, según los criterios que se le pasen.
 *
 * El peso especificado en un campo nos permite controlar el orden de presentación
 *
 * Debe:
 * 
 * - Recibir un array con los campos del formulario y sus especificaciones, ejemplo:
 *
 * <pre>
 * Array (
 *
 *    [nombre] => Array
 *        (
 *            [tipo] => text
 *            [maxlength] => 100
 *            [size] => 30
 *            [valor] => Probando nombre
 *            [peso] => -10
 *        )
 *
 *    [descripcion] => Array
 *        (
 *            [tipo] => textarea
 *            [cols] => 30
 *            [rows] => 3
 *            [valor] => Probando nombreProbando 
 *            [oculto_form] => 1                  // Queda oculto dentro del formulario
 *            [privado] => 1                      // No se muestra en la visualización del registro
 *        )
 *
 *    [categorias_id] => Array
 *        (
 *            [tipo] => relacion
 *            [maxlength] => 11
 *            [size] => 11
 *            [tabla] => categorias
 *            [valor] => 
 *            [opciones] => Array
 *                (
 *                    [0] => Array
 *                        (
 *                            [id] => 5
 *                            [nombre] => nombre
 *                        )
 *
 *                    [1] => Array
 *                        (
 *                            [id] => 4
 *                            [nombre] => Cuarta
 *                        )
 *
 *                )
 *
 *        )
 *
 *    [fecha_creacion] => Array
 *        (
 *            [tipo] => text
 *            [maxlength] => 20
 *            [size] => 20
 *            [valor] => 2011-03-10 10:21:27
 *            [oculto_form] => 1                  // Queda oculto dentro del formulario
 *        )
 *
 *    [stock] => Array
 *        (
 *            [tipo] => text
 *            [maxlength] => 11
 *            [size] => 11
 *            [valor] => 
 *        )
 *    [mail] => Array
 *        (
 *            [tipo] => mail
 *            [maxlength] => 100
 *            [size] => 30
 *            [valor] => 
 *        )
 * </pre>
 *
 * - Recibir array con el displayHash generao por Solicitud
 * 
 * - Permite plantilla personalizada.
 * 
 * @todo Tipos de campos a tratar que falta implementar; seleccion_multiple 
 *       (Para poder seleccionar a más de una opción)
 *
 * @todo La validación en javascript de mail no funciona
 *        
 * @ingroup crud
 */


class Formulario {

   /** Plantilla a utilizar, por defecto la de Formulario */

   public $plantilla;

   /** Plantilla a utilizar, por defecto para visualizar */

   public $plantilla_visualizar;

   /** Array con los campos del formulario */

   protected $campos; 

   /** Array con los tipos de cada campo */

   protected $displayHash;

   /**
    * Constructor
    *
    * @param $campos        Array con los campos y sus especificaciones @see $campos
    * @param $displayHash   @see $displayHash
    */

   function __construct($campos, $displayHash=NULL) {

      $this->campos        = $campos;
      $this->displayHash   = $displayHash;

      $this->plantilla = dirname(__FILE__).'/../html/form_registro.phtml';
      $this->plantilla_visualizar = dirname(__FILE__).'/../html/registro.phtml';

      // Ordenamos campos por su peso
      uasort($this->campos, 'ordenar_por_peso');

      }

   /**
    * Recoger los valores de los campos
    *
    * @param $campo Nombre del campo
    * @param $tabla Nombre de la tabla, solo necesario en caso de ser registros relacionados que vienen
    *        en un array y hay que buscarlo por su nombre.
    * @param $indice En caso de ser una tabla relacionada los valores están en un subarray
    *        será necesario paras el indice para recuperarlos.
    */

   function valores($campo,$tabla=FALSE,$indice=FALSE) {

      if ( $indice === FALSE ) {
         return ( isset($this->campos[$campo]['valor']) ) ? $this->campos[$campo]['valor'] : NULL ;
      } else {
         if ( isset($this->displayHash['VALORES'][$tabla.'_'.$campo][$indice]) ) {
            return $this->displayHash['VALORES'][$tabla.'_'.$campo][$indice];
         } else {
            return ( isset($this->campos[$campo]['valor']) ) ? $this->campos[$campo]['valor'] : NULL ;
            }
         }

      }

   /**
    * Generar formulario para registro
    *
    * @param $ver    Visualizar o editar, por defecto es editar, si es TRUE utilizamos plantilla de visualizar
    * @param $accion Acción que se esta realizando, por defecto 'insertando'
    * @param $objeto_padre Objeto encargado de la carga de los css, librerías javascript y código javascript,
    *         debe tener los siguientes atributos:
    *           - ficheros_css: Array con las urls de los archivos css a cargar
    *           - librerias_js: Array con las urls de las librerías javascript
    *           - codigo_js:    Cadena con el javascript que queremos que se ejecute al cargarse la pagina.
    * @param $nombre_tabla_relacionada Nombre de la tabla relacionada.
    * @param $contador Contador, nos servira para las tablas relaciones_varias diferenciar entre registros.
    */

   function genera_formulario($ver = FALSE, $accion = 'insertando', $objeto_padre = FALSE, $nombre_tabla_relacionada=FALSE, $contador=FALSE) {

      if ( $ver ) {
         $plantilla = $this->plantilla_visualizar;
      } else {
         $plantilla = $this->plantilla;
         }

      if (! file_exists($plantilla) ) {
         throw new Exception('No se encontro plantilla: '.$plantilla);
         }

      require($plantilla);

      }

   }


?>
