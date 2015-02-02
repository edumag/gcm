<?php

/**
 * @file  GUtil.php
 * @brief Utilidades para GCM
 * @ingroup gcm_lib
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 * Copyright  Copyright (c) 2010, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

/**
 * @brief     Clase de metodos utiles generales
 *
 * Los metodos de esta clase son todos estáticos, y se llaman directamente.
 *
 * ejemplo:
 *
 * <pre>
 * Gutil::archibo_manipulable('/temas/ejemplo.css');
 * </pre>
 * @ingroup gcm_lib
 */

class GUtil {

   /** Comprobar que se puede leer en el archivo
    *
    * @param $archivo Ruta de archivo
    */

   static function archivo_leible($archivo) {

      if ( !file_exists($archivo) ) {
         trigger_error('Archivo no encontrado ['.$archvio.']', E_USER_ERROR);
         return FALSE;
         }

      if ( !is_file($archivo) ) {
         trigger_error('Archivo no regular ['.$archvio.']', E_USER_ERROR);
         return FALSE;
         }

      if ( !is_readable($archivo) ) {
         trigger_error('Archivo no tiene permisos de lectura ['.$archvio.']', E_USER_ERROR);
         return FALSE;
         }

      return TRUE;

      }

   /** Comprobar que se puede escribir en el archivo
    *
    * @param $archivo Ruta de archivo
    */

   static function archivo_modificable($archivo) {

      if ( ! self::archivo_leible($archivo) ) {
         trigger_error('Archivo no tiene permisos de lectura ['.$archivo.']', E_USER_NOTICE);
         return FALSE;
         }

      if ( ! is_writable($archivo) ) {
         trigger_error('Archivo no tiene permisos de escritura ['.$archivo.']', E_USER_WARNING);
         return FALSE;
         }

      return TRUE;

      }

   /**
    *
    * Para evitar guardar los nombres de los ficheros con acentos y otras cosas que pueda
    * producir que no funcione bien el sistema de ficheros
    *
    * @author Eduardo Magrané
    * @version 1.0
    * @param $text Texto a modificar
    *
    * @return Texto modificado
    *
    */

   static function textoplano($text) {

      $text = stripslashes($text);
      $text = trim($text);
      $text = preg_replace("/á|à|â|À|Á|Â/", "a", $text);
      $text = preg_replace("/é|è|ê|È|É|Ê/", "e", $text);
      $text = preg_replace("/í|ì|î|Ì|Í|Î/", "i", $text);
      $text = preg_replace("/ó|ò|ô|Ò|Ó|Ô/", "o", $text);
      $text = preg_replace("/ú|ù|û|ü|Ù|Ú|Û|Ü/", "u", $text);
      $text = preg_replace("/ñ/", "ny", $text);
      $text = preg_replace("/Ñ/", "NY", $text);
      $text = preg_replace("/Ç/", "C", $text);
      $text = preg_replace("/ç/", "c", $text);
      $text = str_replace(" ", "_", $text);                  // Espacios en blanco entre palabras
      $text = str_replace("'", "", $text);
         
      return($text);	

      }

   /**
    * Escapar las comillas simples
    *
    * @param $texto
    *
    * @return Texto escapado
    */

   static function escomsimple($texto) {

      return str_replace("'","\'",stripslashes($texto));
   
      }

   /**
    * Escapar las comillas dobles
    *
    * @param $texto
    *
    * @return Texto escapado
    */

   static function escomdobles($texto) {

      return str_replace('"','\"',stripslashes($texto));
   
      }

   /**
    * Sacado de los ejemplos del manual de php para gestionar los errores
    *
    * ejemplo:
    * trigger_error("Par&aacute;metros incorrectos, se esperan matrices", E_USER_ERROR);
    *
    * @param num_err numero de error
    * @param mens_err mensage del error
    * @param nombre_archivo Nombre del archivo afectado
    * @param num_linea Numero de linia
    * @param vars Variables
    *
    * @return nada
    *
    */

   static function gestion_errores($num_err, $mens_err, $nombre_archivo,
                                     $num_linea, $vars)
      {

      global $gcm;

       // marca de fecha/hora para el registro de error
       $dt = date("Y-m-d H:i:s (T)");
       $fichero_registro = Router::$dir."../log/errores.log";

       // definir una matriz asociativa de cadenas de error
       // en realidad las unicas entradas que deberiamos
       // considerar son E_WARNING, E_NOTICE, E_USER_ERROR,
       // E_USER_WARNING y E_USER_NOTICE

       $tipo_error = array (
                   E_ERROR           => "Error",
                   E_WARNING         => "Advertencia",
                   E_PARSE           => "Error de Intérprete",
                   E_NOTICE          => "Anotación",
                   E_CORE_ERROR      => "Error de Núcleo",
                   E_CORE_WARNING    => "Advertencia de Núcleo",
                   E_COMPILE_ERROR   => "Error de Compilación",
                   E_COMPILE_WARNING => "Advertencia de Compilación",
                   E_USER_ERROR      => "Error de Usuario",
                   E_USER_WARNING    => "Advertencia de Usuario",
                   E_USER_NOTICE     => "Anotación de Usuario",
                   E_STRICT          => "Anotación de tiempo de ejecución"
                   );
       // conjunto de errores de los cuales se almacenara un rastreo
       $errores_a_registrar = array(E_WARNING, E_USER_WARNING, E_USER_ERROR, E_ERROR, E_CORE_ERROR );
       $errores_a_mostrar = array(E_WARNING, E_USER_WARNING, E_USER_ERROR, E_ERROR, E_CORE_ERROR);

       if ( isset($tipo_error[$num_err])  ) {
          $tipo = $tipo_error[$num_err];
       } else {
          $tipo = $num_err;
         }

       // if ( GCM_DEBUG  ) {
       //    echo "<div class='error'>";
       //    echo "<p><b>$mens_err</b></p>";
       //    echo '<a href="vim://'.$nombre_archivo.'@'.$num_linea.'">';
       //    echo "<p>$nombre_archivo : $num_linea $tipo</p>";
       //    echo "</a>";
       //    echo "</div>";
       //    }

       if (in_array($num_err, $errores_a_mostrar)) {

          // Errores que mostramos

          registrar($nombre_archivo,$num_linea, $mens_err,'ADMIN');

       } elseif ( in_array($num_err, $errores_a_registrar) ) {

          // Errores que registramos

          registrar($nombre_archivo,$num_linea, $tipo.' :: '.$mens_err,'ADMIN');

       } else {

          // Sin determinar

          registrar($nombre_archivo,$num_linea, $tipo.' :: '.$mens_err,'DEBUG');

         }

      }

   /**
    * Descubrir el tipo mime del archivo
    *
    * Mientras no funcione la clase  finfo en el servidor, en caso de ser un directorio devolvemos $mime_dir
    *
    * @author Eduardo Magrané
    * @version 1.0
    *
    * @param archivo nombre del archivo
    *
    * @return mime_type
    *
    */

   static function tipo_de_archivo($archivo) {

      global $gcm;

      // mimetype para directorio
      $mime_dir='text/directory';

      // Si es un directorio devolvemos $mime_dir
      if ( @is_dir($archivo) ) {
         registrar(__FILE__,__LINE__,"Gutil::tipo_de_archivo()::tipo_de_archivo::mimetype=".$mime_dir);
         return $mime_dir;
      }

      // Buscar la extensión del archivo
       $fileInfo = pathinfo($archivo);
       $ext = ( isset($fileInfo['extension']) ) ? $fileInfo['extension'] : NULL ;
       if ( isset($ext) ) $ext = strtolower($ext);

       /* Si es un borrador devolvemos text/html */

       if ( $ext == "btml"  ) return 'text/html';

       // incluinos array de tipos mime
       require(GCM_DIR.'lib/int/mimetypes.php');

       if ( ! empty($mimetypes[trim($ext)]) ) {

          registrar(__FILE__,__LINE__,"Gutil::tipo_de_archivo(".$archivo.")::tipo_de_archivo::mimetype=".$mimetypes[$ext]);
          return $mimetypes[$ext];

       } else {
          
         // Si no se reconoce por extensión buscamos en la primera linea de archivo

         if ( file_exists($archivo) ) { 
            $ar=fopen($archivo,"r");
            $linea=fgets($ar);
            $pal = explode('/',$linea);
            fclose($ar);
            $tipo = trim($pal[count($pal)-1]);

            switch ($tipo) {

               case 'bash':
                  registrar(__FILE__,__LINE__,"Gutil::tipo_de_archivo()::tipo_de_archivo::mimetype=".$mimetypes['sh']);
                  return $mimetypes['sh'];
                  break;
               case 'perl':
                  registrar(__FILE__,__LINE__,"Gutil::tipo_de_archivo()::tipo_de_archivo::mimetype=".$mimetypes['perl']);
                  return $mimetypes['perl'];
                  break;
               case 'python':
                  registrar(__FILE__,__LINE__,"Gutil::tipo_de_archivo()::tipo_de_archivo::mimetype=".$mimetypes['python']);
                  return $mimetypes['py'];
                  break;
               case 'php':
                  registrar(__FILE__,__LINE__,"Gutil::tipo_de_archivo()::tipo_de_archivo::mimetype=".$mimetypes['php']);
                  return $mimetypes['php'];
                  break;
               default:
                  break;
               }

            }
      
         }

      registrar(__FILE__,__LINE__,"Gutil::tipo_de_archivo()::tipo_de_archivo::No se pudo detectar tipo de archivo ni mirando dentro, $archivo");
      return FALSE;

      }

   /**
    * Enlazar a la documentación de la API de Gcm
    *
    * @param $buscar Elemento a buscar
    */

   static function enlace_documentacion($buscar) {

      return "<a href='".Router::$base.GCM_DIR."../docs/doxygen/html/search.php?query=".$buscar."'>".$buscar."</a>";

      }

   /**
    * @brief generar url valida
    *
    * Generar la url que se entienda con htacces
    *
    * @author Eduardo Magrané
    * @version 1.0
    *
    * @param $url Url
    *
    * @return TRUE/FALSE
    *
    */

   static function gcm_generar_url($url=NULL) {

      if ( isset($url) ) {
         // Separamos sección de contenido
         $seccion = dirname ( $url );
         $contenido = basename ( $url );
         $u = Router::$dir.$seccion."/".$contenido;
         $u = str_replace(' ','%20',$u);
         return $u;
      }

      $b=dirname($_SERVER['PHP_SELF']);
      $u=$b.'/'.$sesion.'/'.$contenido;
      $u = str_replace('//','/',$u);
      $u = str_replace(' ','%20',$u);
      return $u;
      }

   /** desglosar_url()
    *
    * A partir de una url de un archivo, devolvemos la url desde la seccion sin él 'File/es'
    *
    * @param url url completa hacia el archivo
    *
    * @return TRUE/FALSE
    *
    * @author Eduardo Magrané
    * @version 1.0
    *
    */

   static function desglosar_url($url) {

      global $gcm;

      // Quitamos directorio por defecto si lo hay
      $url = str_replace('File/'.$gcm->config('idiomas','Idioma por defecto').'/','',$url);
      // Quitamos directorio con idioma selecionado si lo hay.
      $url = str_replace(Router::$d.'/','',$url);

      return $url;

      }

   /**
    * camino
    *
    * Presentamos la url recibida separada por secciones, con los 
    * literales y sus enlaces correspondientes
    *
    * @param $url url
    * @param $enlazar Enlazar las secciones o contenidos, por defecto FALSE
    * @param $contenido Mostrar contenido o solo secciones
    *
    * @return html del camino
    */

   static function camino($url,$enlazar = FALSE, $contenido = TRUE) {

      $aUrl = Router::desglosarUrl($url);
      $camino = explode('/',$aUrl['s']);
      $primero = TRUE;
      $salida = '';
      $enlace = '';

      foreach ($camino as $elemento) {

         if ( ! empty($elemento) ) {
            if ( !$primero ) $salida .= ' / ';
            if ( $enlazar ) {
               $enlace .= $elemento.'/';
               $salida .= '<a href="'.Router::$base.$enlace.'">'.literal($elemento).'</a>';
            } else {
               $salida .= literal($elemento);
               }
            $primero = FALSE;
            }

         }

      if ( $contenido ) {
         if ( isset($aUrl['c']) ) { 
            if ( !$primero ) $salida .= ' / ';
               if ( $enlazar ) {
                  $salida .= '<a href="'.Router::$base.$url.'">'.literal(str_replace('.html','',$aUrl['c'])).'</a>';
               } else {
                  $salida .= literal(str_replace('.html','',$aUrl['c']));
                  }
            }
         }
      return $salida;
      }

   }

   
?>
