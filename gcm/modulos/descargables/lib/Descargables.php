<?php

/**
 * @file      Descargables.php
 * @brief     Módulos para tratamiento de archivos descargables por el usuario.
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 *   Created  25/01/10
 *  Revision  SVN $Id: Descargables.php 501 2011-04-26 14:03:17Z eduardo $
 * Copyright  Copyright (c) 2010, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

/**
 * @class Descargables
 * @brief     Presentar contenido de archivos con opción de descargar
 *
 * @todo Falta panel para insertar codigo de archivo al editar contenido
 *
 * @version 0.1
 */

class Descargables extends Modulos {

   private $etiqueta_inicio;           ///< Formato de etiqueta (Inicio)
   private $etiqueta_final;            ///< Formato de etiqueta (Final)

   function __construct() {

      parent::__construct();

      $this->etiqueta_inicio = '{desc{';
      $this->etiqueta_final  = '}}';

      }

   /**
    * Verificar si un archivo es descargable o no según configuración
    *
    * @param $archivo Archivo a verificar
    */

   function es_descargable($archivo=FALSE) {

      include ( dirname(__FILE__).'/../config.php');

      if ( ! $archivo || empty($archivo) ) return FALSE;

      if ( esImagen($archivo) ) return FALSE;

      // Buscar la extensión del archivo
      $fileInfo = pathinfo($archivo);
      $ext = ( isset($fileInfo['extension']) ) ? $fileInfo['extension'] : NULL ;
      if ( isset($ext) ) $ext = trim(strtolower($ext));

      if ( in_array($ext,$extensiones_permitidas)  ) return TRUE;

      $tipo = trim(GUtil::tipo_de_archivo($archivo));

      if ( $tipo != '' && in_array($tipo,$tipos_mime_permitidos)  ) return TRUE;

      return FALSE;

      }

   /**
    * presentarArchivosDescargables
    *
    * Si encontramos ficheros descargables los presentamos
    *
    * @author Eduardo Magrané
    * @version 1.0
    *
    * @param dir Directorio a buscar
    * @param sufijo Buscamos solo archivos que contengan el sufijo en el nombre
    *
    * @return TRUE/FALSE
    *
    */

   function presentarArchivosDescargables($dir, $sufijo) {

      global $gcm;

      registrar(__FILE__,__LINE__,'dir: ['.$dir.'] sufijo a buscar: ['.$sufijo.']');

      if ( empty($dir) || empty($sufijo) ) { return FALSE; }

      if ( ! file_exists($dir)  ) {
         registrar(__FILE__,__LINE__,'dir: ['.$dir.'] No existe el directorio');
         return FALSE;
         }

      try {

         $dd = @dir($dir);

         while ( $archivos = $dd->read() ) {

            $aa = $dir.'/'.$archivos ;
            if ( $archivos{0} != "." && substr_count($archivos,'.html')==0 && substr_count($archivos,$sufijo) > 0 && ! is_dir($aa) ) {

               if ( $this->es_descargable($dir.'/'.$archivos) ) {
                  $descargables[]="$archivos";
                  }

               }
            }

         // Si tenemos algún archivo descargable
         if ( isset($descargables) && count($descargables) > 0 ) {

            $this->javascripts('descargables.js');

            $archivos_presentar_codigo = array();

            $salida = array();
            foreach ( $descargables as $arch ) {

               $na = $dir.'/'.$arch ;
               $tipo = GUtil::tipo_de_archivo($na);

               $salida[$arch]['nombre'] = $arch;
               $salida[$arch]['url']=$dir.'/'.$arch;
               $salida[$arch]['tamanyo']=presentarBytes(filesize($na));
               $salida[$arch]['fecha']=presentarFecha(fileatime($na));
               $salida[$arch]['tipo']=$tipo;

               // Si es un archivo zip lo descomprimimos
               // No funciona en servidor presentamos código solo de los no comprimidos

               switch($tipo) {

                  case 'application/zip':

                     $salida[$arch]['codigo'] = 0;
                     // ERROR EN SERVIDOR, No hacemos nada.

                     //// listar archivos incluidos para procesarlos despues
                     //$zip = zip_open($na);
                     //if ($zip) {
                     //   while ($zip_entry = zip_read($zip)) {
                     //     $listaZip[]=zip_entry_name($zip_entry);
                     //   }
                     //   zip_close($zip);
                     //} else {
                     //   $GCM_ERROR[]='gcm_descargables::No se pudo abrir el archivo zip: '.$na;
                     //   return null ;
                     //}

                     //if ( count($listaZip) < 1 ) {
                     //   $GCM_ERROR[]='gcm_descargables::'.$na.' vacio';
                     //   return null ;
                     //}

                     break;


                  default: 
                     $salida[$arch]['codigo'] = 1;
                     break;

                  }

               }

            // Si tenemos archivos
            if ( count($salida) > 0 ) {
               include(dirname(__FILE__).'/../html/descargables.html');

               // Si el formato es ajax se debe inicializar boton para ver contenido archivos

               if ( Router::$formato == 'ajax' ) {
                  echo '<script>init_descargables();</script>';
                  }

               }

            }

      } catch (Exception $ex) {
         registrar($ex->getFile(),$ex->getLine(),'Error al presentar archivo: '.$ex->getMessage(),'ERROR');
         }

      }

   /** extraerArchivos
    *
    * Descomprimimos archivo en directorio temporal
    *
    * @param na Descripcion
    * @param listaZip Pendiente
    *
    * @return TRUE/FALSE
    *
    * @author Eduardo Magrané
    * @version 1.0
    *
    * @todo No funciona en servidor el directorio temporal, de momento hacemos que se 
    *       presente el código solo de los archivos no comprimidos
    *
    */

   function extraerArchivos($na, $listaZip) {

      global $gcm;

      require_once(GCM_DIR."funciones/zip.php");

      if ( !zip_extract_to($na,sys_get_temp_dir().'/', true) ) {
         registrar(__FILE__,__LINE__,'Archivo '.$na.' no se pudo descomprimir','ERROR');
         return NULL ;
      }

      $num = count($listaZip);

      if ( $num > 0 ) {
         for ($i=0 ; $i!=$num; $i++) {
            $archivo_codigo = $dirFinal.$listaZip[$i] ;
            $salida.=$archivo_codigo.'\n';

            // Generamos archivo_codigo
            echo $this->presentar_contenido_archivo($archivo_codigo);

         }
      }

      echo '<script>parent.resultadoUpload (\'0\', \''.$na.' '.$num.' imagenes\\n'.$salida.'\');</script>';

      return TRUE ;
      }

   /** mimetype2geshi 
    *
    * Transformar de mime-type a un formato valido para geshi
    *
    * @param mimetype Tipo mime
    *
    * @return tipo valido para geshi o FALSE si no se encuentra
    *
    * @author Eduardo Magrané
    * @version 1.0
    *
    */

   function mimetype2geshi($mimetype) {

      switch( $mimetype) {

         case 'application/x-httpd-php':
            return 'php';
            break;

         case 'text/x-python':
            return 'python';
            break;

         case 'text/x-perl':
            return 'perl';
            break;

         case 'text/x-sh':
            return 'bash';
            break;

         default:
            return FALSE;
            break;
         } 

      }

   /** 
    * Presentar una lista con los archivos que tienen el mismo nombre que el contenido
    */

   function lista_descargables($e, $args) {

      $this->presentarArchivosDescargables(Router::$dd.Router::$s,str_replace('.html','',Router::$c));

      }

   /** Presentar contenido de archivo para una llamada ajax */

   function presenta_contenido($e, $args) {   // Ajax

      // Cargamos librería javascript siempre por si se necesita en listado, cuando
      // se previsualiza un contenido

      $this->javascripts('descargables.js');

      if ( !empty($_GET['p']) ) {

         echo $this->presentar_contenido_archivo($_GET['p']);
         exit();

         }

      }

   /**
    * Procesar texto para identificar etiqueta {desc{<archivo>}} y presentar
    * contenido del archivo
    */

   function procesar_texto() {

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
         $archivo = str_replace($this->etiqueta_inicio,'',$remplazar);
         $archivo = str_replace($this->etiqueta_final,'',$archivo);

         $archivo = Router::$dd.Router::$s.$archivo;

         if ( $pos1 && $pos2 && $archivo && $remplazar ) {

            $contenido_archivo = $this->presentar_contenido_archivo($archivo);

            $buffer = str_replace($remplazar,$contenido_archivo,$buffer);

            }

         }

      $gcm->contenido=$buffer;

      }


   /** Presentar contenido de archivo
    *
    * Utilizamos Guesi para presentar el contenido del archivo indicado.
    *
    * @param $archivo url de archivo
    * @param $geshi si/no por defecto si
    *
    * @return TRUE/FALSE
    *
    * @author Eduardo Magrané
    * @version 1.0
    *
    */

   function presentar_contenido_archivo($archivo, $geshi='si') {

      global $gcm;

      if ( ! is_file($archivo) ) {
         registrar(__FILE__,__LINE__,"Archivo no encontrado".' ['.$archivo.']');
         return FALSE;
         }

      $nombre = basename($archivo);
      $mime = GUtil::tipo_de_archivo($archivo);
      $tipo = $this->mimetype2geshi($mime) ? $this->mimetype2geshi($mime) : 'txt';
      $gestor = fopen($archivo, "r");
      $contenido = stream_get_contents($gestor);
      fclose($gestor);

      if ( $geshi == 'si' ) {

         if ( empty($contenido) || empty($tipo)  ) { 

            registrar(__FILE__,__LINE__,"Falta contenido o tipo, contenido: [$contenido], tipo: [$tipo]",'ADMIN');

            return FALSE; 
         }

         require_once(GCM_DIR.'lib/ext/geshi/geshi.php');

         $migeshi= new GeSHi($contenido,$tipo);
         //$migeshi->set_overall_style('background: #000000; color: #ffffff;');
         $migeshi->enable_classes();
         //$migeshi->set_header_type(GESHI_HEADER_DIV);
         $migeshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
         $contenido = $migeshi->parse_code();

         ob_start();

         include(dirname(__FILE__)."/../html/codigo_decorado.html");

         $salida=ob_get_contents();


      } else {
         
         ob_start();

         include(dirname(__FILE__)."/../html/presentar_codigo.html");

         $salida=ob_get_contents();

         }

      ob_end_clean();

      return $salida ;

      }

   }

?>
