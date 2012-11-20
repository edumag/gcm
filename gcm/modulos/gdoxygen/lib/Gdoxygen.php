<?php

/**
 * @file Gdoxygen.php
 *
 * @category   Gcm
 * @package    Modulos
 * @subpackage Gdoxygen
 * @author     Eduardo Magrané eduardo@mamedu.com
 *
 * @internal
 *   Created  23/11/09
 *  Revision  SVN $Id: modulo.php 464 2011-02-02 16:12:12Z eduardo $
 * Copyright  Copyright (c) 2009, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

/**
 * @class Gdoxygen
 * @brief Adaptar documentación de doxygen a gcm
 *
 * El título del proyecto se coge de carpeta padre de gdoxygen
 *
 * Para que el módulo funcione, en la configuración de doxygen debe estar:
 *
 * - CREATE_SUBDIRS         = NO
 * - HTML_OUTPUT            = gdoxygen
 * - HTML_FILE_EXTENSION    = .htm
 * - HTML_HEADER            = /home/eduardo/.magtrabajos/proyectos/magtrabajos/doxygen_head.html 
 * - HTML_FOOTER            = /home/eduardo/.magtrabajos/proyectos/magtrabajos/doxygen_feed.html
 *
 * @version 0.1
 */

class Gdoxygen extends Modulos {

   /**
    * Nombre de las carpetas que contienen documentación
    * generada por doxygen
    */

   private $nombre_carpetas_doxygen = 'gdoxygen';

   /** Constructor */

   function __construct() {

      parent::__construct();

      }

   /**
    * Si estamos en una carpeta con documentación de doxygen
    *
    * Si estamos indexando contenido y es perteneciente a gdoxygen
    * generamos nuestra propia indexación, antes que lo intente el 
    * modulo indexador
    */

   function detectar_gdoxygen($e, $args) {

      global $gcm;

      $inicio = 'index.htm';

      if ( Router::$estamos == $this->nombre_carpetas_doxygen ) {

         if ( Router::$e == 'indexar'  ) {
            $this->anularEvento('indexar');
            $this->indexado($_GET['url']);
            if ( Router::$formato == 'ajax'  ) return;
            }

         $this->anularEvento('titulo');
         $gcm->event->unico('contenido',$this->nombre_carpetas_doxygen);
         $this->anularEvento('columna');

         if ( file_exists(Router::$d.Router::$s.'index.htm') === FALSE ) {

            $inicio = 'main.htm';

            }

         if ( Router::$formato != 'ajax'  ) {
            echo '<style>#contenedor { width: 100%;}</style>';
            }
         echo '<br /><iframe scrolling="auto" frameborder="0" width="99%" height="1000" src="http://'.$_SERVER['HTTP_HOST'].'/'.dirname($_SERVER['PHP_SELF']).'/'.Router::$dd.Router::$s.'">';
         }

      }

   /**
    * Reindexado completo
    *
    * Reindexamos todos los proyectos gdoxygen
    */

   function reindexado_completo($e, $args=NULL) {

      $this->listar_gdoxygen();

      // global $gcm;

      // $carpetas_doxygen = rglob('File/*/'.$this->nombre_carpetas_doxygen);

      // if ( ! $carpetas_doxygen ) { return; }

      // foreach ( $carpetas_doxygen as $carpeta ) {

      //    $archivo  = $carpeta.'/'.'main.htm';

      //    $this->indexado($archivo);

      //    }

      }

   /**
    * Reinexamos proyecto con doxygen
    *
    * - Buscar carpetas que contengan documentación de doxygen
    * - Recoger todos los archivos acabados en .htm e indexarlos
    */

   function reindexar($e, $args) {

      $this->listar_gdoxygen();

      }

   /**
    * Indexar gdoxygen
    *
    * Si creamos la documentación con el arbol lateral descripción
    * la cojemos del archivo main.htm sino del mismo index.htm
    *
    * $param $archivo Url de gdoxygen/index.htm
    */

   function indexado($archivo) {

      global $gcm;

      $carpeta  = dirname($archivo);
      $aCamino  = explode('/',$carpeta);
      $proyecto = $aCamino[count($aCamino)-2];
      $archivo_contenido = $carpeta.'/main.htm';

      if ( file_exists($archivo_contenido) === FALSE ) {
         $archivo_contenido = $archivo;
         }

      if ( file_exists($archivo) === FALSE ) {
         $men = "No se encuentra ni $archivo";
         registrar(__FILE__,__LINE__,$men,'ERROR');
         return FALSE;
         }

         $parametros  = 'url='.$archivo;
         $parametros .= '&literal=Documentación '.$proyecto;
         $parametros .= '&sin_comprobar_extension=1';
         $parametros .= '&archivo_descripcion='.$archivo_contenido;
         $parametros .= '&inicio_descripcion="contents">';

      if ( $gcm->event->lanzar_accion_modulo('indexador','indexar_archivo_pdo','gdoxygen',$parametros) !== FALSE ) {
         $salida = "Contenido reindexado: Documentación ".$proyecto;
      } else {
         $salida  = '<p class="error">';
         $salida .= 'Error al indexar contenido';
         $salida .= '</p>';
         return FALSE;
         }

      if ( Router::$formato == 'ajax'  ) {
         echo $salida;
      } else {
         registrar(__FILE__,__LINE__,$salida,'AVISO');
         }

      }

   /**
    * Listar proyectos con gdoxygen para poder ser indexados
    * individualmente
    */

   function listar_gdoxygen() {

      global $gcm;

      $funcion_js_indexar = 'indexar';

      $carpetas_doxygen = rglob('File/*/'.$this->nombre_carpetas_doxygen);

      if ( ! $carpetas_doxygen ) { return; }

      echo '<h2>Listado de gdoxygen</h2>';

      foreach ( $carpetas_doxygen as $carpeta ) {

         $archivo  = $carpeta.'/'.'index.htm';
         $aCamino  = explode('/',$carpeta);
         $proyecto = $aCamino[count($aCamino)-2];

         $camino   = $archivo;
         $elemento = $archivo;

         include($gcm->event->instancias['temas']->ruta('indexador','html','elemento_lista_indexar.html'));

         }

      }

   /**
    * Presentar resultados de gdoxygen
    */

   function presentar_busquedas($e, $args) {

      if ( isset($_GET['buscar']) ) {

         $query = $_GET['buscar'];

         include('search_doxygen.php');

         $carpetas_doxygen = rglob('File/*/'.$this->nombre_carpetas_doxygen);

         if ( ! $carpetas_doxygen ) { return; }

         foreach ( $carpetas_doxygen as $carpeta ) {

            $proyectos = explode('/',$carpeta);
            $proyecto = $proyectos[3];
            echo '<h2>Resultados en el código de '.$proyecto.'</h2>';

            if (!($file=fopen($carpeta."/search/search.idx","rb"))) 
            {
               die("Error: Search index file could NOT be opened!");
            }
            $results = array();
            $requiredWords = array();
            $forbiddenWords = array();
            $foundWords = array();
            $word=strtok($query," ");
            while ($word) // for each word in the search query
            {
               if (($word{0}=='+')) { $word=substr($word,1); $requiredWords[]=$word; }
                  if (($word{0}=='-')) { $word=substr($word,1); $forbiddenWords[]=$word; }
                     if (!in_array($word,$foundWords))
                     {
                        $foundWords[]=$word;
                        search($file,strtolower($word),$results);
                     }
                     $word=strtok(" ");
            }
            $docs = array();
            combine_results($results,$docs);
            // filter out documents with forbidden word or that do not contain
            // required words
            $filteredDocs = filter_results($docs,$requiredWords,$forbiddenWords);
            // sort the results based on rank
            $sorted = array();
            sort_results($filteredDocs,$sorted);
            // report results to the user
            $prefijo_url = str_replace(Router::$dd,'',$carpeta);
            if ( !empty($sorted) ) {
               for ( $i=0; $i < sizeof($sorted); ++$i ) {
                  $sorted[$i]['url'] = $prefijo_url.'/'.$sorted[$i]['url'];
                  }
               }
            report_results($sorted);
            echo "</div>\n";
            end_page();
            fclose($file);
         }
      }
   }


   }

?>
