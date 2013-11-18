<?php

/**
 * @file      Config.php
 * @brief     Mecanismo independiente de confiuración
 * @ingroup   GcmConfig
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 * Created    13/11/09
 * Copyright  Copyright (c) 2009, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

require_once(GCM_DIR.'lib/int/GcmConfig/lib/GcmConfigFactory.php');

/**
 * Configuración general
 * @ingroup GcmConfig
 */

class Config {

   private $archivo_configuracion; ///< Archivo de configuración
   private $configuracion;         ///< Array con las configuraciones

   /** Constructor */

   function __construct($archivo_configuracion) {

      $this->archivo_configuracion = $archivo_configuracion;

      }

   /**
    * Configuración
    *
    * Si se nos da un valor para una variable, siempre la colocamos en el 
    * archivo de configuración del proyecto.
    *
    * El archivo llevara la variable $config[<variable>][<valor>];
    *
    * Para acelerar el proceso guardamos en cache 
    *
    * @param $nombre_variable Nombre de la variable
    * @param $valor Valor para la variable
    *
    * @return En caso de pedir valor de variable: False en caso de no encontrar o su valor
    *         En caso de darnos un valor TRUE o FALSE dependiendo del exito de la operación
    */

   function config($nombre_variable, $valor=FALSE) {

      // Si se pide un valor y ya lo tenemos recogido lo devolvemos
      if ( !$valor && isset($this->configuracion[$nombre_variable]) ) return $this->configuracion[$nombre_variable];

      if ( $valor ) {                  // Guardar valor de variable

         $arr = GcmConfigFactory::GetGcmConfig($$this->archivo_configuracion);

         $arr->set($nombre_variable,$valor);
         $arr->guardar_variables();
         $this->configuracion[$nombre_variable] = $valor;

      } else {                         // Devoler valor de variable

         if ( !file_exists($this->archivo_configuracion) ) {
            die ('Archivo de configuración no existe: ['.$this->archivo_configuracion.']');
            return FALSE;
            }

         $arr = GcmConfigFactory::GetGcmConfig($this->archivo_configuracion);
         $valor = $arr->get($nombre_variable);
         $this->configuracion[$nombre_variable] = $valor;
         return $valor;

         }

      }

   }
?>
