<?php

/**
 * @file      Contenidos.php
 * @brief     Gestión del contenido 
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 *   Created  15/04/10
 *  Revision  SVN $Id: Contenidos.php 373 2010-10-08 14:41:09Z eduardo $
 * Copyright  Copyright (c) 2010, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

if ( defined('GCM_DIR') === FALSE ) define('GCM_DIR',dirname(__FILE__).'/../../../');

/* Clase padre */

require_once(dirname(__FILE__).'/ContenidosAbstract.php');

/** Contenidos
 *
 * Clase para manejar el contenido en archivos
 *
 * @todo Separar la clase en funciones esenciales y las que recogen los eventos
 *
 * @package Contenidos Gestión de contenido
 * @author Eduardo Magrané
 * @version 0.1
 * 
 */

class Contenidos extends ContenidosAbstract {

   function __construct() { 

      parent::__construct(); 

      }

   /**
    * Renombrar contenido
    *
    * @param $origen documento original
    * @param $destino ruta final
    */

   function mover_contenido($origen, $destino) {
      global $gcm;
      registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$origen.','.$destino.')');
      $gcm->event->lanzar_accion_modulo('cache_http','borrar','mover_contenido',$origen);
      return rename($origen, $destino);
      }

   /**
    * Control sobre que no debe ser mostrado a no ser que se tenga permisos
    *
    * Como por ejemplo los borradores.
    */

   function comprobar_permisos($e, $args) {

      /* Si se pide un borrador mirar que tenga permisos */

      if ( strpos(Router::$c,'.btml') ) permiso(5);

      }

   /** 
    * Crear nueva sección
    *
    * @param $ruta_seccion Ruta de la nueva sección
    */

   function crear_nueva_seccion($ruta) {

      registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$ruta.')');

      return mkdir_recursivo($ruta);

      }

   /**
    * Borrar contenido
    *
    * @param $ruta Ruta de contenido
    */

   function borrar_contenido($ruta) {
      global $gcm;
      $gcm->event->lanzar_accion_modulo('cache_http','borrar','borrar_contenido',$ruta);
      return unlink($ruta);
      }

   /**
    * Verificar contenido
    *
    * @param $ruta Ruta de contenido
    */

   function verificar_contenido($ruta) {
      if ( file_exists($ruta) ) { return TRUE; }
      if ( file_exists(Router::$d.$ruta) ) { return TRUE; }
      return FALSE;
      }

   /**
    * Verificar sección
    *
    * @param $ruta Ruta de sección
    */

   function verificar_seccion($ruta) {
      if ( file_exists($ruta) ) { return TRUE; }
      if ( file_exists(Router::$d.$ruta) ) { return TRUE; }
      return FALSE;
      }

   /**
    * Devolver el contenido del documento
    *
    * @param $ruta Ruta del contenido
    */

   function getContenido($ruta) {
      return file_get_contents($ruta);
      }

   /**
    * Crear un array con el contenido de una sección
    *
    * Esta función esta pensada para tener de una tacada todos los archivos 
    * afectados por el borrado de una sección, paginas de contenido en otros 
    * idiomas, carpeta de imagenes, etc..
    *
    * La matriz devolvera los archivos afectados como items y los 
    * subdirectorios como submatrices con más items
    *
    * Si tenemos seleccionado algun contenido del idioma por defecto, tenemos 
    * que seleccionar sus correspondientes en las traducciones
    *
    * @author Eduardo Magrané
    * @version 1.0
    *
    * @param contenido Directorio de la seccion
    * @return Matriz con los ficheros y directorios afectados
    *
    */

   function seccion_matriz($contenido) {

      global $gcm;

      $contenidos = array();

      $idiomaXdefecto = FALSE;

      if ( $contenido == "" )  {
         registrar(__FILE__,__LINE__,"Necesito contenido especifico para poder trabajar",'ERROR');
         return ;
         }

      /* si contenido es una matriz */

      if ( !is_array($contenido) ) {
         $contenido = array($contenido);
      }

      foreach ( $contenido as $dir ) {

         $dir = stripslashes($dir);
         $desglose = Router::desglosarUrl($dir);
         $idioma = $desglose['i'];
         if ( $idioma == Router::$ii ) $idiomaXdefecto = TRUE;

          if (file_exists($dir)) {

             if ( is_dir($dir) ) {
                $contenidos[$idioma][$dir]=dir_array($dir);
             } else {
                $contenidos[$idioma][$dir]=$dir;
                }
          } else {
             registrar(__FILE__,__LINE__,"No existe contenido [ ".$dir." ]",'ADMIN');
             }

         if ( $idiomaXdefecto ) {                                    // Tenemos contenido con idioma x defecto
            
            // Buscamos información sobre los idiomas disponibles
            include("DATOS/idiomas/idiomas.php");
            include("DATOS/idiomas/desactivados.php");
            if ( !empty($desactivados) ) {
               $todosLosIdiomas = array_merge($i_activados, $desactivados);
            } else {
               $todosLosIdiomas = $i_activados;
            }

            // Recorremos los idiomas
            foreach ($todosLosIdiomas as $otro_idioma => $literal) {

               // Remplazar idioma predeterminado por el actual
               $dir_otros_idiomas = str_replace("File/".Router::$ii, "File/".$otro_idioma, $dir);

               if (file_exists($dir_otros_idiomas)) {

                  if ( is_dir($dir_otros_idiomas) ) {
                     $contenidos[$otro_idioma][$dir_otros_idiomas]=dir_array($dir_otros_idiomas);
                  } else {
                     $contenidos[$otro_idioma][$dir_otros_idiomas]=$dir_otros_idiomas;
                     }
                  }

               }
            }

         }
      return $contenidos;
      }
      
   /** Título para el contenido general
    *
    * El evento titulo vendra lanzado por la plantilla
    *
    * @param $e     Evento que recibimos de Eventos
    * @param $args  Argumentos posibles
    */

   function devolver_titulo($url=NULL) {
   
      global $gcm;

      $url = ( $url ) ? $url : Router::$f;

      if ( is_file($url)  ) {
         $urls = explode('/',$url);
         $titulo = end($urls);
         $titulo = str_replace('.html','',$titulo);
         return $titulo;
      } else {
         return FALSE;
         }  

      }

   /**
    * Mostrar las secciones para seleccionar dentro de un formulario
    *
    * Esta función crea una lista con los directorios y subdirectorios que se encuentran
    * a partir del directorio que se especifica para seleccionar uno de ellos, se puede
    * pasar un segundo directorio que puede ser un subdirectorio para que quede seleccionado
    * por defecto.
    *
    * Los subdirectorios quedan ocultos hasta que se clican en ellos a no ser que sea el que
    * se especifico por defecto.
    *
    * La función se debe llamar desde un formulario y la variable $_POST['directorio'] llevara
    * directorio seleccionado.
    *
    * Si se quiere presentar tambien los documentos para poder ser seleccionados se presentaran
    * y el nombre del campo sera $_POST['documento']
    *
    * @author Eduardo Magrané
    * @version 1.1
    * @param path Directorio donde comienzan las secciones
    * @param path_visible Directorio que queremos este visible
    * @param verDocumentos Ver los documentos o solo las secciones
    * @param seleccionable Incluir botones de formlario para seleccionar
    * @param filtro Array que contiene las extensiones de ficheros a filtrar
    * @param recursivo Bajamos a los subdirectorios también
    * @param checkqued Ruta de directorio que debe quedar seleccionada, Tiene que 
    *        coincidir exactamente con la ruta de las secciones, ejemplo: File/es/seccion/subseccion
    * @param $multiple Se permite seleccionar másde uno o no.
    *
    */

   function mostrarSecciones($path, $path_visible=FALSE, $verDocumentos=FALSE, $seleccionable=TRUE, 
                             $filtro=FALSE, $recursivo=TRUE, $checkqued=NULL, $multiple=TRUE) {

      global $gcm;

      // Comprobar descartados

      if ( ($this->descartar) ) {

         $descartar = FALSE;
         foreach ( $this->descartar as $descartado ) {
            if ( strpos($path,$descartado) !== FALSE ) {
               registrar(__FILE__,__LINE__,'Descartado: '.$path. ' coincide con '.$descartado);
               $descartar = TRUE;
               }
            }

         if ( $descartar ) {
            return;
            }
         }

      $path = comprobar_barra($path,'eliminar');
      $nombre_campo = 'seleccionado[]';

      if ( $multiple ) {
         $tipo_campo   = 'checkbox';
      } else {
         $tipo_campo   = 'radio';
         }

      // Si $checqued viene con barra se la quitamos
      if ( $checkqued ) {
         $checkqued = comprobar_barra($checkqued,'eliminar');
      }

      $ver='NO';

      $dir_por_defecto='File/'.Router::$ii;
      $d = dir($path);
      $HAY="NO";                                          //< Para saber si hay subdirectorios
      $subsecciones = array();
      $documentos = array();
      while($entry=$d->read()) {
         // descartamos directorios ocultos de linux
         if (is_dir($path."/".$entry) && $entry{0} != "." ) {
            $HAY="SI";
            $subsecciones[]=$path."/".$entry;
         } elseif ( $verDocumentos && $entry{0} != "." ) { // contenido html
            $documentos[$path][]=$entry;
            }

         }
      $d->close();

      // Si tenemos un path para seleccionar por defecto
      if ( $path_visible ) {
         $path1 = explode('/',$d->path);
         $path2 = explode('/',$path_visible);
       
         // Determinar si se oculta sección o se mantiene abierta por estar en el
         // camino del path seleccionado.
         if ( in_array($path1[count($path1)-1],$path2) )  {
            $ver='SI';
         }
         }

      echo "\n<ul>";

      // Boton de radio
      if (  ( $ver == 'SI' ) || $d->path == $dir_por_defecto) {

         if ( $d->path == $checkqued ) { // Si es el que queremos tener seleccionado
            echo " <input checked type='".$tipo_campo."' name='".$nombre_campo."' value='", htmlentities($d->path), "' />";
         } else {
            echo " <input type='".$tipo_campo."' name='".$nombre_campo."' value='", htmlentities($d->path), "' />";
            }

      } else {
         echo " <input type='".$tipo_campo."' name='".$nombre_campo."' value='", htmlentities($d->path), "' />";
         }

      // Contenido
      if ( $HAY == "SI" || $verDocumentos) {
         echo "<a href='#' class='interrogante' onclick=\"visualizar('",htmlentities($d->path),"'); return false;\" >";
         echo basename($d->path);
         echo "</a>";
      } else {
         echo basename($d->path);
         }
      if ( ( $ver == 'SI' )  || $d->path == $dir_por_defecto ) {
         echo "<div id='",$d->path,"' class='toggle'>";
      } else {
         echo "<div id='",$d->path,"' class='toggle' style='display: none' >";
         }

      // Si queremos ver documentos los presentamos
      if ( $verDocumentos ) {
         if ( ! empty($documentos[$d->path]) && count($documentos[$d->path]) > 0 ) {
            foreach( $documentos[$d->path] as $doc ) {
               echo '<li>';
               if ( GUtil::tipo_de_archivo($d->path.'/'.$doc) == 'text/html' ) {
                  echo '<span class="datos_fichero_html">';
               } else {
                  echo '<span class="datos_fichero">';
               }
               echo "<input type='".$tipo_campo."' name='".$nombre_campo."' ";
               echo 'value="'.htmlentities($d->path."/".$doc).'" />';
               // Segun tipo documento mostramos
               if ( esImagen($d->path.'/'.$doc) )  {
                  echo "<img align='center' width='50px' src='".htmlentities($d->path."/".$doc)."' />";
               }
               echo '<a title="'.literal('Visualizar').'" href="?edit=no&url='.htmlentities($d->path.'/'.$doc).'">';
               echo $doc;
               echo "</a>";
               // Solo ponemos link para editar si son paginas html
               if ( GUtil::tipo_de_archivo($d->path.'/'.$doc) == 'text/html' ) {
                  echo '<a title="'.literal('Editar').'" href="?e=editar_contenido&url='.htmlentities($d->path.'/'.$doc).'"> [#]</a>';
               }
               echo '<span class="detalles_fichero">';
               echo ' ['.presentarBytes(filesize($d->path.'/'.$doc)).',  '.presentarFecha(filemtime($d->path.'/'.$doc),2).']';
               echo '</span>';
               echo '</span>';
               echo '</li>';
               }
            }
         }

      foreach($subsecciones as $x) {
         $this->mostrarSecciones($x, $path_visible, $verDocumentos, $seleccionable, $filtro, $recursivo, $checkqued, $multiple);
         }

      echo "</div>";
      echo "\n</ul>";
      }

   /**
    * Guardar el contenido a un archivo
    *
    * @author Eduardo Magrané
    * @version 1.0
    * @param $fichero Nombre del fichero a crear
    * @param $contenido Contenido
    *
    */

   function guardar_contenido($fichero, $contenido){

      global $gcm;

      registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$fichero.','.$contenido.')');

      $fich = $fichero;

      $contenido = limpiarContenido($contenido);
      if ( ! $nuevoDoc = @fopen($fich, 'w') ) {
         registrar(__FILE__, __LINE__, "No se puede abrir el archivo [".$fich."] para escribir",'ERROR');
         return FALSE;
         }

      if ( fwrite($nuevoDoc, $contenido) === FALSE ) {
         registrar(__FILE__,__LINE__,literal("No se puede escribir en el archivo")." [".$fich."]",'ERROR');
         return FALSE;
         }

      fclose($nuevoDoc);

      registrar(__FILE__,__LINE__,literal("Fichero escrito").' ['.$fich.']');

      return TRUE;

      }

   /** 
    * Listar contenido de una sección
    *
    * Para cuando se entra en una sección, listamos el contenido que hay en ella.
    *
    * @todo Pasar metodo a Abstract y dejar solo las funciones imprescindibles
    *
    * @param $e     Evento que recibimos de Eventos
    * @param $seccion Directorio a mostra
    *
    */

   function listar_contenido_seccion($e, $seccion=NULL) {

      global $gcm;

      /* Comprobamos que no este anulado el evento contenido */

      if ( $gcm->event->anulado('contenido') ) {
         return;
         }

      if ( ! $seccion  ) $seccion = Router::$dd.Router::$s;

      $seccion = comprobar_barra($seccion);
      $d = dir($seccion);
      $subsecciones = array();
      $documentos = array();

      /* Si estamos en una sección añadimos ../ a las subsecciones */

      if ( Router::$s != '' ) {
         $subsecciones[] = '..';
      }

      while($entry=$d->read()) {
         // descartamos directorios ocultos de linux
         if (is_dir($seccion.$entry) && $entry{0} != "." ) {
            $subsecciones[]=$entry;
         } elseif ( $entry{0} != "." && GUtil::tipo_de_archivo($entry) == 'text/html' ) { // contenido html
            $documentos[]=$entry;
            }
         }
      $d->close();

      include ($gcm->event->instancias['temas']->ruta('contenidos','html','listar_contenido_seccion.html'));

      }

   /**
    * Renombrar sección
    *
    * Mover de ubicación o cambio de nombre de sección
    *
    * @param $ruta_origen Url origen
    * @param $ruta_destino Url Destino
    */

   function renombrar_seccion($ruta_origen, $ruta_destino) {

      global $gcm;
      $gcm->event->lanzar_accion_modulo('cache_http','borrar','renombrar_seccion',$ruta_origen);

      if ( rename($ruta_origen,$seccion_nueva) ) {
         return TRUE;
      } else {
         return FALSE;
         }
      }  

   /**
    * Borrar directorio
    *
    * Eliminamos un directorio recursivamente
    *
    * @author Eduardo Magrané
    * @version 1.0
    *
    * @param directorio Dirección del directorio a borrar
    *
    * @return TRUE/FALSE
    *
    */

   function eliminarDirectorio($directorio) {

      global $gcm;

      $gcm->event->lanzar_accion_modulo('cache_http','borrar','eliminarDirectorio',$directorio);

      return rmdir_recursivo($directorio);

      }
     
   /**
    * Obtener fecha de modificación de contenido
    *
    * @param $ruta Ruta de contenido
    */

   function getFechaActualizacion($ruta) {
      return filemtime($ruta);
      }


   }

?>
