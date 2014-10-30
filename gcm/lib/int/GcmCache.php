<?php

/**
 * @file GcmCache.php
 * @brief Cache
 *
 * @ingroup librerias
 * @author    Eduardo Magrané 
 *
 * @internal
 * Copyright  Copyright (c) 2009, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

/**
 * @defgroup GcmCache GcmCache
 * @ingroup librerias
 * 
 * Cache para páginas y variables
 *
 * @{
 */

/**
 * @class Cache
 * @brief Cache
 *
 * @version 0.2
 */

class GcmCache {

   /**
    * Directorio donde se guarda la cache
    */

   public $dir_cache;

   /**
    * Tiempo de expiración de la cache de archivos html (en segundos)
    *
    * 60x60x2 = 2 hora
    */

   public $duracion; 

   /**
    * Nombre del archivo de cache
    */

   public $archivo_cache;

   /**
    * Extensión para archivos de cache
    */

   public $extension = '.cache';

   /** Constructor */

   function __construct($dir_cache=FALSE, $duracion=FALSE) {

      $this->dir_cache = ( $dir_cache ) ? $dir_cache : 'cache/' ;
      $this->duracion  = ( $duracion  ) ? $duracion  : 60*60*2  ;   // 60 segundos * 60 minutos * 2 horas = cada 2 horas

      if ( ! file_exists($this->dir_cache) ) {
         if ( ! mkdir($this->dir_cache) ) {
            registrar(__FILE__,__LINE__,'No se pudo crear directorio para cache','ERROR');
            }
         }

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
      $dirname = $this->dir_cache.dirname($aDirname[0]);
      mkdir_recursivo($dirname);

      $this->archivo_cache = $dirname.'/'.$basename.'_'.
         $plataforma.'_'.$navegador.'_'.$version.'_'.
         md5(serialize($solicitud)).$this->extension;

      if (@file_exists($this->archivo_cache)) {
          $fecha_cache = @filemtime($this->archivo_cache);
      } else {
          $fecha_cache = 0;
         }

      /* Borramos cache de php al mirar información de archivo */
      @clearstatcache();

      // Mostramos el archivo si aun no vence
      if (time() - $this->duracion < $fecha_cache) {
         registrar(__FILE__,__LINE__,'Archivo en cache: '.$this->archivo_cache,'ADMIN');
         /* Cabeceras de archivo */
         $aExt = explode('.',Router::$url);
         $ext = end($aExt);
         switch($ext) {
         case 'css':
            header('Content-Type: text/css');
            $salida = "\n".'/* Archivo en cache '.$this->archivo_cache.' */'."\n";
            break;
         case 'js':
            header('Content-Type: text/javascript');
            $salida = "\n".'/* Archivo en cache '.$this->archivo_cache.' */'."\n";
            break;
         default:
            $salida = "\n".'<!-- Archivo en cache '.$this->archivo_cache.' -->'."\n";
            break;
            }
          readfile($this->archivo_cache);
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

      if ( empty($this->archivo_cache)  ) return;

      /* Generamos el nuevo archivo cache */
      $fp = fopen($this->archivo_cache, "w");

      if ( $fp  ) {

         /* guardamos el contenido */
         @fwrite($fp, $salida);
         @fclose($fp);

         }
      
      registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'() Archivo guardado en cache: '.$this->archivo_cache);
      }

   /**
    * Borrar cache sobre el contenido especificado
    *
    * Borramos todos los archivos que contengan la url en el nombre sino pasamos
    * url borramos todos.
    *
    * @param $url url a borrar se utiliza comodin para borrar todo lo que contenga la url
    */

   function borrar($url='todo') {

      if ( $url != 'todo' && !empty($url) ) {
         $url = str_replace('/','_',$url);
         $archivos = glob($this->dir_cache.'*'.$url.'*');
      } else {
         $archivos = glob($this->dir_cache.'*');
         if ( function_exists('apc_clear_cache') ) apc_clear_cache();
         }

      registrar(__FILE__,__LINE__,
         __CLASS__.'->'.__FUNCTION__.'(url: '.depurar($url).')',FALSE,'Archivos a borrar: '.depurar($archivos));

      if ( !isset($archivos) || empty($archivos) ) return;

      foreach ( $archivos as $archivo ) {
         if ( is_dir($archivo) ) {
            rmdir_recursivo($archivo);
         } else {
            @unlink($archivo);
            }
         }

      registrar(__FILE__,__LINE__,'Archivos borrados en cache ['.count($archivos).']');

      }

   /**
    * Recuperar variable en cache
    *
    * @param $nombre_variable Nombre de variable, Asegurarse de que no se 
    *                         diferencien los nombres entre diferentes 
    *                         variables
    * @param $tiempo_expiracion Tiempo en que expira la variable, en caso de 
    *                           no pasar valor, sera el estipulado por 
    *                           $this->duracion
    * @return Contenido de variable o FALSE en caso de no existir o exceder tiempo
    */

   function recuperar_variable($nombre_variable, $tiempo_expiracion=NULL) {

      $tiempo_expiracion = ( $tiempo_expiracion ) ? $tiempo_expiracion : $this->duracion;

      if ( function_exists('apc_fetch') ) {
         $retorno = apc_fetch($nombre_variable);
         if ( $retorno ) return $retorno;
         }

      $archivo_variable = $this->dir_cache.$nombre_variable.$this->extension;

      if (@file_exists($archivo_variable)) {
          $fecha_cache = @filemtime($archivo_variable);
      } else {
          $fecha_cache = 0;
         }

      /* Borramos cache de php al mirar información de archivo */
      @clearstatcache();

      // devolvemos la variable si aun no vence
      if (time() - $tiempo_expiracion < $fecha_cache) {
         $contenido = file_get_contents($archivo_variable);
         $retorno = unserialize($contenido);
         // registrar(__FILE__,__LINE__,'Recuperamos variable en cache ['.$nombre_variable.'] '."\n".depurar($retorno),'DEBUG');
         return $retorno; 
         }

      return FALSE;

      }

   /**
    * Guardar variable en cache
    */

   function guardar_variable($nombre_variable,$valor) {

      if ( function_exists('apc_add') ) return apc_add($nombre_variable, $valor);

      $archivo_variable = $this->dir_cache.$nombre_variable.$this->extension;

      $fp = @fopen($archivo_variable, "w");
      @fwrite($fp, serialize($valor));
      @fclose($fp);
      
      registrar(__FILE__,__LINE__,'Guardamos variable en cache ['.$nombre_variable.'] '."\n".depurar($valor));
      }

   /**
    * Guardar contenido
    *
    * @param $contenido   Fichero a incluir. 
    * @param $variables Array con las variables a tener en cuenta para 
    *          diferenciar contenidos, ejemplo: localización.
    * @param $duracion Tiempo de expiración de la cache, por defecto $this->duracion.
    */

   function recuperar_contenido($contenido, $variables=FALSE, $duracion=FALSE) {

     $duracion = ( $duracion ) ? $duracion : $this->duracion;

     if ( $variables ) {
       $archivo_cache = $this->dir_cache.$contenido.implode('-',$variables).'.html';
     } else {
       $archivo_cache = $this->dir_cache.$contenido.'.html';
       }

     if (@file_exists($archivo_cache)) {
        $fecha_cache = @filemtime($archivo_cache);
     } else {
        $fecha_cache = 0;
       }

     // Mostramos el archivo si aun no vence
     if (time() - $duracion < $fecha_cache) {

       // Contenido en cache
       readfile($archivo_cache);

       return TRUE;

       }

     return FALSE;
     }

   function guardar_contenido($contenido, $salida, $variables = FALSE) {
   
     if ( $variables ) {
       $archivo_cache = $this->dir_cache.$contenido.implode('-',$variables).'.html';
     } else {
       $archivo_cache = $this->dir_cache.$contenido.'.html';
       }

      $fp = @fopen($archivo_cache, "w");
      @fwrite($fp, $salida);
      @fclose($fp);

     }

   }

/** @} */
?>
