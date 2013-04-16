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
 * - Permite css personalizado o sin css.
 * 
 * @todo Tipos de campos a tratar que falta implementar; fecha, seleccion_multiple 
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

   /** Aplicar css's propios */

   public $css = FALSE;

   /** Array con los campos del formulario */

   protected $campos; 

   /** Array con los tipos de cada campo */

   protected $displayHash;

   /** Array con restricciones que seran aplicadas con validate.js @see Solicitud, Crud */

   protected $restricciones;

   /** Array con mensajes para las restricciones @see Solicitud, Crud */

   protected $mensajes;

   /**
    * Constructor
    *
    * @param $campos        Array con los campos y sus especificaciones @see $campos
    * @param $displayHash   @see $displayHash
    * @param $restricciones @see $restricciones
    * @param $mensajes      @see $mensajes
    */

   function __construct($campos, $displayHash=NULL, $restricciones=FALSE, $mensajes=FALSE) {

      $this->restricciones = $restricciones;
      $this->mensajes = $mensajes;
      $this->campos       = $campos;
      $this->displayHash  = $displayHash;

      $this->plantilla = dirname(__FILE__).'/../html/form_registro.html';
      $this->plantilla_visualizar = dirname(__FILE__).'/../html/registro.html';

      $this->css       = dirname(__FILE__).'/../css/formulario.css';

      // Ordenamos campos por su peso
      uasort($this->campos, 'ordenar_por_peso');

      }

   /**
    * Recoger los valores de los campos
    *
    * @param $campo Nombre del campo
    */

   function valores($campo) {

      return ( isset($this->campos[$campo]['valor']) ) ? $this->campos[$campo]['valor'] : NULL ;

      }

   /**
    * Generar formulario para registro
    *
    * @param $ver    Visualizar o editar, por defecto es editar, si es TRUE utilizamos plantilla de visualizar
    * @param $accion Acción que se esta realizando, por defecto 'insertando'
    */

   function genera_formulario($ver = FALSE, $accion = 'insertando') {

      if ( $ver ) {
         $plantilla = $this->plantilla_visualizar;
      } else {
         $plantilla = $this->plantilla;
         }

      if (! file_exists($plantilla) ) {
         throw new Exception('No se encontro plantilla: '.$plantilla);
         }

      require($plantilla);

      if ( $this->css && ! empty($this->css) ) {

         if ( ! file_exists($this->css) ) {
            registrar(__FILE__,__LINE__,'Archivo con css no encontrado ['.$this->css.']','ERROR');
         } else {
            ?>
            <style>
            <?php require($this->css); ?>
            </style>
            <?php
            }
         }


      ?>
      <script>
      <?php require(dirname(__FILE__).'/../js/validate.js'); ?>
      var validator = new FormValidator('crud', [<?php echo $this->crear_restricciones();?>], function(errors, events) {
          if (errors.length > 0) {
              // Show the errors
             //var caja_errores = document.getElementById('messageBoardDIV');
             //caja_errores.innerHTML = errors.join('<br />');
             //caja_errores.style.display = '';
             salida = errors.join("\n");
             alert(salida);
          }
      });
      <?=$this->crear_mensajes();?>
      </script>
      <?php


      }

   /**
    * Construir listado de mensajes paravalidador en javascript
    *
    * @todo mensajes por definir
    */

   function crear_mensajes() {

      $men= "";

      if ( isset($this->mensajes) ) {
         foreach ( $this->mensajes as $campo => $mensajes ) {

            foreach ( $mensajes as $restriccion => $mensaje ) {

               switch ($restriccion) {

               case RT_MAIL:
                  $men.= "\nvalidator.setMessage('valid_email', '%s $mensaje');";
                  break;

               case RT_LONG_MIN:
                  $men.= "\nvalidator.setMessage('min_length', '%s $mensaje');";
                  break;

               case RT_LONG_MAX:
                  $men.= "\nvalidator.setMessage('max_length', '%s $mensaje');";
                  break;

               // case RT_CARACTERES_PERMITIDOS:
               //    $men.= "\nvalidator.setMessage('min_length', '%s $mensaje');";
               //    break;

               // case RT_CARACTERES_NO_PERMITIDOS:
               //    $men.= "\nvalidator.setMessage('min_length', '%s $mensaje');";
               //    break;

               case RT_MENOR_QUE:
                  $men.= "\nvalidator.setMessage('greater_than', '%s $mensaje');";
                  break;

               case RT_MAYOR_QUE:
                  $men.= "\nvalidator.setMessage('less_than', '%s $mensaje');";
                  break;

               case RT_IGUAL_QUE:
                  $men.= "\nvalidator.setMessage('matches', '%s $mensaje');";
                  break;

               // case RT_NO_IGUAL:
               //    $men.= "\nvalidator.setMessage('min_length', '%s $mensaje');";
               //    break;

               // case RT_PASA_EXPRESION_REGULAR:
               //    $men.= "\nvalidator.setMessage('min_length', '%s $mensaje');";
               //    break;

               // case RT_NO_PASA_EXPRESION_REGULAR:
               //    $men.= "\nvalidator.setMessage('min_length', '%s $mensaje');";
               //    break;

               case RT_NO_ES_NUMERO:
                  $men.= "\nvalidator.setMessage('numeric', '%s $mensaje');";
                  break;

               case RT_REQUERIDO:
                  $men.= "\nvalidator.setMessage('required', '%s $mensaje');";
                  break;

                  }
               }

            }
         }

      if ( empty($men) ) return FALSE;

      return $men;

      }

   /**
    * Construimos las restricciones de javascript segun contenido de restricciones y mensajes
    *
    * Este metodo se basa en validate.js que se puede encontrar en:
    * http://rickharrison.github.com/validate.js/
    *
    * restricciones:
    *
    * campo[tipo restriccion][valor]
    *
    * @todo Los casos que estan comentados hay que buscar la manera de implementarlos
    * @todo Aplicar pass_md5
    */

   function crear_restricciones() {

      $salida = "";

      require_once(dirname(__FILE__)."/../../solicitud/lib/constantes.php");

      if ( isset($this->restricciones) ) {

         foreach ( $this->restricciones as $campo => $restricciones ) {

            foreach ($restricciones as $restriccion => $valor) {

               switch ($restriccion) {

               case RT_MAIL:
                  $salida .= "{
                     name: '$campo',
                     rules: 'valid_email'
                     },";
                  break;

               case RT_LONG_MIN:
                  $salida .= "{
                     name: '$campo',
                     rules: 'min_length[$valor]'
                     },";
                  break;

               case RT_LONG_MAX:
                  $salida .= "{
                     name: '$campo',
                     rules: 'max_length[$valor]'
                     },";
                  break;

               // case RT_CARACTERES_PERMITIDOS:
               //    $salida .= "{
               //       name: '$campo',
               //       rules: 'min_length[$valor]'
               //       },";
               //    break;

               // case RT_CARACTERES_NO_PERMITIDOS:
               //    $salida .= "{
               //       name: '$campo',
               //       rules: 'min_length[$valor]'
               //       },";
               //    break;

               case RT_MENOR_QUE:
                  $salida .= "{
                     name: '$campo',
                     rules: 'greater_than[$valor]'
                     },";
                  break;

               case RT_MAYOR_QUE:
                  $salida .= "{
                     name: '$campo',
                     rules: 'less_than[$valor]'
                     },";
                  break;

               case RT_IGUAL_QUE:
                  $salida .= "{
                     name: '$campo',
                     rules: 'matches[$valor]'
                     },";
                  break;

               // case RT_NO_IGUAL:
               //    $salida .= "{
               //       name: '$campo',
               //       rules: 'min_length[$valor]'
               //       },";
               //    break;

               // case RT_PASA_EXPRESION_REGULAR:
               //    $salida .= "{
               //       name: '$campo',
               //       rules: 'min_length[$valor]'
               //       },";
               //    break;

               // case RT_NO_PASA_EXPRESION_REGULAR:
               //    $salida .= "{
               //       name: '$campo',
               //       rules: 'min_length[$valor]'
               //       },";
               //    break;

               case RT_NO_ES_NUMERO:
                  $salida .= "{
                     name: '$campo',
                     rules: 'numeric[$valor]'
                     },";
                  break;

               case RT_REQUERIDO:
                  $salida .= "{
                     name: '$campo',
                     rules: 'required'
                     },";
                  break;

                  }

               }
            }
         }

      if ( empty($salida) ) return FALSE;

      // Quitamos última coma
      $salida = trim($salida, ',');
      return $salida;

      }


   }


?>
