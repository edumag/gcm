<?php

/**
 * @file Constantes.php
 * @brief Módulo para los constantes
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 *   Created  23/11/09
 *  Revision  SVN $Id: Constantes.php 638 2012-08-01 16:39:14Z eduardo $
 * Copyright  Copyright (c) 2009, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

/* GcmConfig */

require_once(GCM_DIR."lib/int/GcmConfig/lib/GcmConfigFactory.php");

/**
 * @class Constantes
 * @brief Manejo de constantes
 */

class Constantes extends Modulos {

   protected $etiqueta_inicio;           ///< Formato de etiqueta (Inicio)
   protected $etiqueta_final;            ///< Formato de etiqueta (Final)
   protected $fichero_constantes;        ///< Ruta del fichero donde guardamos las constantes
   protected $constantes;                ///< Array con las constates definidas

   function __construct() {

      parent::__construct();

      $this->etiqueta_inicio = '{C{';
      $this->etiqueta_final  = '}}';
      $this->fichero_constantes  = 'DATOS/constantes/GCM_CONS.php';

      }

   /**
    * Procesar texto para identificar etiqueta {Lit{archivo}} y presentar
    * contenido del archivo
    */

   function procesar_texto($e, $args=FALSE) {

      global $gcm;

      $buffer = $gcm->contenido;

      while ( strpos($buffer, $this->etiqueta_inicio) !== false ) {

         $pos1 = NULL;
         $pos2 = NULL;
         $archivo  = NULL;
         $remplazar = NULL;
         $archivo = NULL;

         $pos1 = strpos($buffer, $this->etiqueta_inicio);
         $pos2 = strpos($buffer, $this->etiqueta_final, $pos1);
         $remplazar = substr($buffer, $pos1, $pos2 - $pos1 + 2);
         $etiqueta = str_replace($this->etiqueta_inicio,'',$remplazar);
         $etiqueta = str_replace($this->etiqueta_final,'',$etiqueta);
         $etiqueta = html_entity_decode($etiqueta,ENT_NOQUOTES,'UTF-8');
         $etiqueta = trim($etiqueta);

         if ( $pos1 && $pos2 && $etiqueta && $remplazar ) {

            $etiqueta = $this->procesa_etiqueta($etiqueta);

            $buffer = str_replace($remplazar,$etiqueta,$buffer);

            }

         }

      $gcm->contenido=$buffer;

      }

   /**
    * Procesar la etiqueta
    *
    * Devuelve el valor a modificar desde procesar_texto()
    *
    * @see procesar_texto
    *
    * @param $etiqueta Etiqueta a modificar.
    */

   function procesa_etiqueta($etiqueta) {

      $this->recuperar_constantes();

      $constantes = $this->constantes;
      if ( isset($constantes[$etiqueta]) ) {
        return $constantes[$etiqueta];
      } else {
        registrar(__FILE__,__LINE__,"Constante sin definir [".$etiqueta."]",'ADMIN');
        }

      

      }

   /**
    * Recoger las constantes definidas
    */

   function recuperar_constantes() {

      if ( $this->constantes ) return ;

      $file = $this->fichero_constantes;

      $arr = GcmConfigFactory::GetGcmConfig($file);

      if ( $arr->variables() ) $this->constantes = $arr->variables();

      }


   }

?>
