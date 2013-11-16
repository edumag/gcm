<?php

/**
 * @file Cache_http.php
 * @brief Módulo para generar cache de páginas
 *
 * @ingroup modulo_cache_http
 * @author    Eduardo Magrané 
 *
 * @internal
 * Copyright  Copyright (c) 2011, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

require_once GCM_DIR.'lib/int/GcmCache.php';

/**
 * @class Cache_http
 * @brief Cache para paginas y variables
 */

class Cache_http extends Modulos {

   /** Instancia de GcmCache */

   protected $instancia_cache; 

   /** Constructor */

   function __construct() {

      parent::__construct();

      $dir_cache = 'cache/';
      $duracion  = 60*60*2;   // 60 segundos * 60 minutos * 2 horas = cada 2 horas

      $this->instancia_cache = new GcmCache($dir_cache, $duracion);

      }

   function test() {

      $this->ejecuta_test('Directorio para cache: '.$this->instancia_cache->dir_cache, file_exists($this->instancia_cache->dir_cache), TRUE);
      $this->ejecuta_test('Permisos de directorio: '.$this->instancia_cache->dir_cache,is_readable($this->instancia_cache->dir_cache), TRUE);

      }

   /**
    * Comprobar cache
    *
    * Antes de cargar la pagina comprobamos si tenemos en cache
    * si es así, la presentamos y salimos
    *
    * En caso de que estemos en modo debug o haya un error o aviso no hacemos cache
    *
    * Tenemos en cuenta el navegador, la versión y plataforma ya que puede haber 
    * modificaciones en el código según estos criterios.
    *
    * @return TRUE/FALSE
    */

   function alInicio() {

      global $gcm;

      /* Añadimos javascript por si se apreta el boton de borrar cache en menu administrativo */
      if ( $gcm->au->logeado() ) $this->javascripts('cache_http.js');

      if ( $gcm->au->logeado() ) {
         registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'() En modo debug no cacheamos');
         return;
         }

      if ( GCM_DEBUG ) {
         registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'() En modo debug no cacheamos');
         return;
         }

      if ( isset($_SESSION[$gcm->sufijo.'registros']) ) {
         registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'() Con errores o avisos no cacheamos');
         return;
         }

      require_once(GCM_DIR.'lib/ext/detectar_navegador/browser_class_inc.php');

      $b = new browser();
      $bb = $b->whatBrowser(); 

      $navegador  = $bb['browsertype'];
      $version    = $bb['version'];
      $plataforma = $bb['platform'];

      $url = $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];

      $solicitud = array ('url' => $url, $_POST, $_GET);

      $aBasename = explode('?',$url);
      $aDirname  = explode('?',$url);
      $basename = basename($aBasename[0]);
      $dirname = $this->instancia_cache->dir_cache.dirname($aDirname[0]);
      mkdir_recursivo($dirname);

      $this->instancia_cache->archivo_cache = $dirname.'/'.$basename.'_'.
         $plataforma.'_'.$navegador.'_'.$version.'_'.
         md5(serialize($solicitud)).$this->instancia_cache->extension;

      if (@file_exists($this->instancia_cache->archivo_cache)) {
          $fecha_cache = @filemtime($this->instancia_cache->archivo_cache);
      } else {
          $fecha_cache = 0;
         }

      /* Borramos cache de php al mirar información de archivo */
      @clearstatcache();

      // Mostramos el archivo si aun no vence
      if (time() - $this->instancia_cache->duracion < $fecha_cache) {
         registrar(__FILE__,__LINE__,'Archivo en cache: '.$this->instancia_cache->archivo_cache,'ADMIN');
         /* Cabeceras de archivo */
         $aExt = explode('.',Router::$url);
         $ext = end($aExt);
         switch($ext) {
         case 'css':
            header('Content-Type: text/css');
            $salida = "\n".'/* Archivo en cache '.$this->instancia_cache->archivo_cache.' */'."\n";
            break;
         case 'js':
            header('Content-Type: text/javascript');
            $salida = "\n".'/* Archivo en cache '.$this->instancia_cache->archivo_cache.' */'."\n";
            break;
         default:
            $salida = "\n".'<!-- Archivo en cache '.$this->instancia_cache->archivo_cache.' -->'."\n";
            break;
            }
          readfile($this->instancia_cache->archivo_cache);
          echo $salida;
          exit();
         }

      // ob_start();

      }

   /**
    * Al final creamos archivo en cache con la salida de la página
    *
    * En caso de que estemos en modo debug o haya errores o estemos administrando 
    * o tengamos mensajes para el usuario no hacemos nada
    *
    * @return TRUE/FALSE
    */

   function alFinal() {

      global $gcm;

      if ( isset($_SESSION[$gcm->sufijo.'registros']) || $gcm->reg->errores() || $gcm->au->logeado() || GCM_DEBUG ) return;

      $salida = $gcm->salida;

      // Si no tenemos contenido nos volvemos
      if ( empty($gcm->salida) ) return;

      if ( empty($this->instancia_cache->archivo_cache)  ) return;

      /* Generamos el nuevo archivo cache */
      $fp = fopen($this->instancia_cache->archivo_cache, "w");

      if ( $fp  ) {

         /* guardamos el contenido */
         @fwrite($fp, $salida);
         @fclose($fp);

         }
      
      registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'() Archivo guardado en cache: '.$this->instancia_cache->archivo_cache);
      }

   /**
    * Borrar cache sobre el contenido especificado
    *
    * Borramos todos los archivos que contengan la url en el nombre sino pasamos
    * url borramos todos.
    *
    * @param $e Evento
    * @param $args Argumentos
    */

   function borrar($e, $args) {

      global $gcm;

      $this->instancia_cache->borrar($args);

      /* Si el evento es borrar_cache, presentamos información para que salga en ventana emergente */

      if ( $e == 'borrar_cache' ) {

         return;
         exit();
         $gcm->salir();

         }
      }

   /**
    * Recuperar variable en cache
    *
    * @param $nombre_variable Nombre de variable, Asegurarse de que no se 
    *                         diferencien los nombres entre diferentes 
    *                         variables
    * @param $tiempo_expiracion Tiempo en que expira la variable, en caso de 
    *                           no pasar valor, sera el estipulado por 
    *                           $this->instancia_cache->duracion_variables
    * @return Contenido de variable o FALSE en caso de no existir o exceder tiempo
    */

   function recuperar_variable($nombre_variable, $tiempo_expiracion=NULL) {

      return $this->instancia_cache->recuperar_variable($nombre_variable, $tiempo_expiracion);

      }

   /**
    * Guardar variable en cache
    */

   function guardar_variable($nombre_variable,$valor) {

      return $this->instancia_cache->guardar_variable($nombre_variable, $valor);

      }

   }

?>
