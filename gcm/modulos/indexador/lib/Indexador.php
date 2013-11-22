<?php

/**
 * @file      Indexador.php
 * @brief     Indexar contenido
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 *   Created  04/02/11
 *  Revision  SVN $Id: Indexador.php 651 2012-10-17 09:19:07Z eduardo $
 * Copyright  Copyright (c) 2011, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

/** Módulo Indexador
 *
 * Para tener una base de datos que nos permita:
 *
 * - hacer busquedas.
 * - Presentar ultimos añadidos o modificados
 * - entre otras cosas.
 *
 * @todo Crear módulo para etiquetas
 *
 * @version 0.1
 * 
 */

class Indexador extends Modulos {

   public $descartar;                          ///< Archivos o directorios a descartar
   public $maxLong = 200;                      ///< Tamaño en caracteres de la descripción de los archivos a indexar

   protected $pdo = NULL;                        ///< Instancia a la base de datos
   protected $BD  = 'DATOS/proyecto.db';         ///< Archivo sqlite
   protected $prefijo = '';                      ///< Prefijo para base de datos

   /**
    * Saber si se presento 'últimas entradas' en contenido, en tal caso no se debe
    * presentar en columna
    */

   protected $ultimos_en_contenido = FALSE;

   /** Constructor */

   function __construct() {

      global $gcm;

      parent::__construct();

      $this->pdo = $gcm->pdo_conexion($this->BD);

      $this->prefijo = $gcm->sufijo;

      $this->descartar = $gcm->config('contenidos','descartar');
      $this->maxLong = $this->config('maxLong');

      }

   /** formulario_busqueda
    *
    * Presentamos formulario de busqueda
    */

   function formulario_busqueda() {

      global $gcm;

      $this->javascripts('indexador.js');

      include($gcm->event->instancias['temas']->ruta('indexador','html','caja_buscar.html'));

      }

   /** sql_quote
    *
    * para evitar sql injection y escapar contenido que podría dar problemas al insertar en la 
    * base de datos.
    *
    * @param string Cadena a validar, para evitar injecciones de sql
    *
    * @return Cadena escapada
    *
    * @author Eduardo Magrané
    * @version 1.0
    * @fuente http://www.forosdelweb.com/f18/funcion-anti-sql-injection-587921/
    *
    */

   function sql_quote($valor) {

      $valor = sqlite_escape_string($valor);
      return $valor;

      }

   /**
    * ultimas_entradas
    *
    * Presentamos ultimas entradas 
    *
    * @author Eduardo Magrané
    * @version 1.0
    *
    * @param num Numero de entradas que queremos que se presenten
    * @param seccion filtramos por sección
    * @param formato Formato de salida, por defecto 0:
    *        0: Salida para contenido
    *        1: columna
    *
    * @return TRUE/FALSE
    *
    */

   function ultimas_entradas($e, $args) {

      global $gcm;

      $this->javascripts('indexador.js');

      $sufijo = 'uec_'; // Sufijo para pajinador (ultimas entradas contenido)

      // Por defecto
      $num_items_df = 5;
      $formato_df = 0; // Listado

      $parametros = recoger_parametros($args);

      $num = ( isset($parametros['num']) ) ? $parametros['num'] : $num_items_df ;
      $seccion = ( isset($parametros['seccion']) ) ? $parametros['seccion'] : Router::$s ;
      $formato = ( isset($parametros['formato']) ) ? $parametros['formato'] : $formato_df ;

      if ( $formato == 0  ) $this->ultimos_en_contenido = TRUE;

      if ( $formato == 1  && $this->ultimos_en_contenido ) {
         registrar(__FILE__,__LINE__,
            __CLASS__.'->'.__FUNCTION__.'('.$e.','.depurar($args).') Ya tenemos ultimas entradas 
            en contenido, no las ponemos en columna');
         return;
         }

      if ( !empty($seccion) ) {
         $aSeccion_actual = explode('/',comprobar_barra($seccion));
         $seccion_actual = ( $aSeccion_actual[count($aSeccion_actual)-2] ) ? literal($aSeccion_actual[count($aSeccion_actual)-2],1) : NULL ;
         }

      $estamos = ( isset($seccion_actual) ) ? $seccion_actual : 'inicio' ;

      $condicion = ( $seccion ) ? "url like '".$seccion."%'" : NULL ;

      $sql = "SELECT nombre, url, descripcion, fecha_actualizacion_in, fecha_creacion_at FROM ".$this->prefijo."archivos ";
      if ( $condicion) $sql .= "WHERE $condicion";
      $sql .= " ORDER BY fecha_actualizacion_in desc";

      require_once(GCM_DIR.'lib/int/GcmPDO/lib/paginarPDO.php');

      if ( $formato == 0 ) { // Formato para contenido

         $gcm->event->anular('titulo','indexador');
         if ( $estamos == 'inicio' || empty($seccion) ) {
            $gcm->titulo=literal('Últimas entradas');
         } else {
            $gcm->titulo=literal('Últimas entradas en',3).' '.$seccion_actual;
            }

         $pd = new PaginarPDO($this->pdo, $sql, $sufijo);

         if ( $pd->validar() ) {

            $gcm->event->anular('contenido_dinamico','indexador');

            $pd->elementos_pagina=$num;
            $pd->plantilla_resultados=dirname(__FILE__).'/../html/listado_contenido.html';

            // $pd->botonera();
            // $pd->pagina();
            // $pd->botonera();
            $pd->generar_pagina('&formato=ajax&m=indexador&a=ultimas_entradas');

         }

      } else {

         /* Formato para columna */

         $sufijo = 'uecl_'; // Sufijo para pajinador (ultimas entradas columna)

         if ( $estamos == 'inicio' || empty($seccion) ) {
            $titulo=literal('Últimas entradas');
         } else {
            $titulo=literal('Últimas entradas en',3).' '.$seccion_actual;
            }

         $pd = new PaginarPDO($this->pdo, $sql, $sufijo);

         if ( $pd->validar() ) {

            $pd->plantilla_resultados=dirname(__FILE__).'/../html/listado_columna.html';
            $pd->elementos_pagina=$num;
            ob_start();
            $pd->pagina();
            $contenido = ob_get_contents();
            ob_end_clean(); 

            $panel = array();
            $panel['titulo'] = $titulo;
            $panel['oculto'] = TRUE;
            $panel['href'] = Router::$dir.'indexador/ultimas_entradas/'.Router::$s;
            $panel['subpanel'] ='list_ultimas_entradas';
            $panel['contenido'] =$contenido;

            Temas::panel($panel);

            }
         }

      }

   /** presentar_busquedas
    *
    * Presentamos los resultados.
    *
    * @see PaginarPDO.php
    *
    */

   function presentar_busquedas($e, $args=FALSE) {

      global $gcm;

      if ( $e == 'error' ) {  // Viene de evento error
         $palabras = array($args);
      } elseif ( isset($_REQUEST['buscar']) ) {
         $palabras = array($_REQUEST['buscar']); // explode(' ',$_REQUEST['buscar']);
      } else {
         $palabras = Router::$args;
         }

      $gcm->registra(__FILE__,__LINE__,"Buscando ".depurar($palabras));

      if ( !empty($palabras) ) {

         $CONDICION = "";
         $ORDER = " fecha_actualizacion_in desc ";
         $conta = 0;
         $fin = count($palabras);
         foreach ( $palabras as $pal ) {
            // Seguridad, evitar sqlinjection
            if ( get_magic_quotes_gpc() ) {
               $pal = stripslashes($pal);
               }
            $pal = addslashes($pal);
            $pal = str_replace('"','',$pal); // Eliminamos " para que no de error

            if ( $conta != 0 ) {
               $CONDICION .= ' OR ';
               }
            $CONDICION .= " ( nombre like '%".$pal."%' OR descripcion like '%".$pal."%' ) ";
            $conta++;
            }

         require_once(GCM_DIR.'lib/int/GcmPDO/lib/paginarPDO.php');

         /* Título de página */

         if ( $e != 'error' ) {
            $gcm->titulo = literal('Busqueda para').': <i>'.implode(' ',$palabras).'</i>';
            $gcm->event->anular('titulo','Indexador');
            $gcm->event->anular('contenido','Indexador');
            }

         $sql = "SELECT nombre, url, descripcion, fecha_actualizacion_in, fecha_creacion_at FROM ".$this->prefijo."archivos WHERE $CONDICION ORDER BY fecha_actualizacion_in desc ";

         $pd = new PaginarPDO($this->pdo, $sql);

         if ( $pd->validar() ) {

            if ( $pd->numero_registros() == 1 && $e != 'error' ) {

               $gcm->registra(__FILE__,__LINE__,literal('Solo hubo un resultado, se presenta'),'AVISO');

               while ( $fila = $pd->to_array() ) {
                  $url = GUtil::gcm_generar_url($fila[0]['url']);
                  header ("Location:".$url.'?mens='.$gcm->reg->sesion);
                  exit();
                  }

            } else {
               
               $this->javascripts('indexador.js');

               $gcm->event->anular('contenido_dinamico','Indexador');

               $pd->url_base = '?e=buscar&buscar='.htmlentities(implode('+',$palabras));
               $pd->elementos_pagina=8;
               $pd->plantilla_resultados=dirname(__FILE__).'/../html/listado_contenido.html';

               $pd->botonera();
               $pd->pagina();
               $pd->botonera();

               }

         } else {
            if ( $e != 'error' ) {
               $gcm->registra(__FILE__,__LINE__,literal('No hubo resultados dentro del contenido <b>'.implode(' ',$palabras)).'</b>','AVISO');
               }
            return FALSE;
            }
      } else {
         registrar(__FILE__,__LINE__,'Indexador->presentar_resultados() Sin contenido a buscar');
         return FALSE;
         }

      return TRUE;
      }

   /**
    * En caso de no tener página de sección presentamos
    * ultimos elementos
    */

   function contenido_dinamico() {

      global $gcm;

      /* Comprobamos que no este anulado el evento contenido */

      if ( $gcm->event->anulado('contenido') ) {
         return;
         }

      $this->javascripts('indexador.js');

      // Por defecto
      $num_items_df = 5;
      $secccion_df = Router::$s; // Sección actual
      $formato_df = 0; // Listado

      $parametros = recoger_parametros(func_get_args());

      $num = ( isset($parametros['num']) ) ? $parametros['num'] : $num_items_df ;
      $seccion = ( isset($parametros['seccion']) ) ? $parametros['seccion'] : $secccion_df ;
      $formato = ( isset($parametros['formato']) ) ? $parametros['formato'] : $formato_df ;

      $this->ultimas_entradas(NULL,'num='.$num.'&seccion='.$seccion.'&formato='.$formato);

      }

   /**
    * En caso de error probamos a buscar contenido utilizando el nombre de la 
    * pagina que buscó
    */

   function error($e, $args=FALSE) {

      global $gcm;

      $args = recoger_parametros($args);

      if ( isset($args['sin_contenido']) ) {

         if ( $this->presentar_busquedas('error',$args['sin_contenido']) ) {
            registrar(__FILE__,__LINE__,literal("Presentamos resultados que quiza estes buscando"),'AVISO');
            }

         }

      }
   }
?>
