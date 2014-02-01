<?php

/**
 * @file Literales.php
 * @brief Módulo para los literales
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 *   Created  23/11/09
 *  Revision  SVN $Id: Literales.php 638 2012-08-01 16:39:14Z eduardo $
 * Copyright  Copyright (c) 2009, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

/* GcmConfig */

require_once(GCM_DIR."lib/int/GcmConfig/lib/GcmConfigFactory.php");

/**
 * @class Literales
 * @brief Manejo de literales
 */

class Literales extends Modulos {

   private $etiqueta_inicio;           ///< Formato de etiqueta (Inicio)
   private $etiqueta_final;            ///< Formato de etiqueta (Final)

   function __construct() {

      parent::__construct();

      $this->etiqueta_inicio = '{L{';
      $this->etiqueta_final  = '}}';

      }

   /**
    * Procesar texto para identificar etiqueta {Lit{archivo}} y presentar
    * contenido del archivo
    */

   function procesar_texto() {

      global $gcm; $LG ;

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

      return literal($etiqueta,1) ;

      }

   /**
    * Añadir elemento nuevo a array
    *
    * Se espera recibir desde $_GET
    *   - elemento: clave del array a modificar
    *   - valor:    Valor a añadir
    *   - file:     Archivo con array, de formato especifico
    *               En caso de no haberlo cogemos el del idioma actual
    */

   function anyadirLiteral() {
      
      global $gcm;

      $file = $gcm->config('idiomas','Directorio idiomas')."LG_".Router::$ii.".php";

      $arr = GcmConfigFactory::GetGcmConfig($file);
      $arr->set($_GET['elemento'],$_GET['valor']);
      $arr->guardar_variables();

      echo "[ ".$_GET['elemento']." ] = [ ".$_GET['valor']." ] en [ ".$file." ]";

      }

   }

?>
