<?php

/**
 * @file      TemaGcm.php
 * @brief     Manipulación de tema de proyecto
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 *   Created  25/10/10
 *  Revision  SVN $Id: $
 * Copyright  Copyright (c) 2010, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

/**
 * Tratar con temas de proyectos 
 */

class TemaGcm {

   /** Listado de ficheros del tema [tipo][modulo/tipo/nombre] = url de archivo */

    public $ficheros;

    /** Lista de colores del tema formato: [capa][valor] = '#ffffff' */

    public $colores;

    /** Listado de colores demandados que no estan implementados */

    protected $colores_faltantes = NULL ;

    /**
     * Constructor
     * 
     * @param  array $ficheros Lista de ficheros del tema
     * @param  array $colores Lista de colores del tema
     * @access public
     */

    public function __construct($ficheros, $colores) {

       $this->ficheros = $ficheros;
       $this->colores  = $colores;

       }

    /**
     * Devolvemos string para enlazar el javascript con html
     *
     * Para evitar la carga innecesaria solo cargamos los archivos que
     * se especifica en la lista recibida como parametro, en caso de 
     * tenerla si que presentamos todos los archivos javascript del tema.
     *
     * @param $ficheros  Ficheros javascript para construir el del proyecto
     * @param $librerias Librerias javascript a incluir
     * @param $url_base  Url base para añadir a los enlaces
     */

    public function incluir_javascript($ficheros=NULL,$librerias=NULL,$url_base) {

      $ficheros  = ( $ficheros ) ? $ficheros : $this->ficheros[js];

      $lista     = ( !empty($ficheros) ) ? implode(',',$ficheros) : NULL ;

      if ( $librerias && !empty($librerias)  ) {
         foreach ( $librerias as $items ) {
            list($modulo,$nombre) = explode(':',$items);
            $f = $this->ruta(strtolower($modulo),'libjs',$nombre);
            echo "\n".'<script src="'.$url_base.$f.'" type="text/javascript"></script>';
            }
         }


      if ( $lista ) echo "\n".'<script src="proyecto.js?arch='.$lista.'" type="text/javascript"></script>';

      if ( ! $lista ) echo "\n<!-- sin javascript para añadir -->";

      }

   /**
    * Devolver color para archivos css, en caso de no existir devolvemos
    * 'red' y añadimos color a $this->colores_faltantes
    *
    * @param $color Identificador de color
    */
    
   function color($color) {

      if ( isset($this->colores[$color])  ) {
         return $this->colores[$color];
      } else {
         $this->colores_faltantes[$color] = NULL;
         //registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$color.') Color no definido');
         trigger_error('Color ['.$ruta.'] no definido',E_USER_ERROR);
         return 'red';
         }

      }

   /** Construcción del archivo css para proyectos
    *
    * Cojemos todos los ficheros css del tema y generamos el archivo css del proyecto
    *
    * Si se detecta que se esta pasando mediante proyectos.css?css=contenido se genera el css solo
    * con el contenido. Eto va muy bien para que tiny recoga los css y los vea como los css 
    * principales presentando la misma imagen que cuando estamos viendo.
    *
    * Si encontramos un archivo llamado variables.php dentro de la carpeta del tema lo incluimos
    * antes de los css para que puedan utilizar las variables generadas. Esto lo utilizamos por ejemplo 
    * para cambiar la imagen de fondo según la sección en la que estamos.
    *
    * @return string Con el código css
    */

   function construir_css() {

      global $gcm;

      echo "/* Archivo generado por ".__CLASS__." */\n\n";

      // Incluimos archivo de variables

      $fich_variables = dirname($this->ficheros['css']['temas/css/body.css']).'/../../../variables.php';

      if ( file_exists( $fich_variables ) ) include ($fich_variables);


      // Si se nos pide un css concreto añadimos solo el body.css + el css que se pide

      if ( isset($_GET['css']) && !empty ($_GET['css']) ) {

         $css = $_GET['css'];

         echo "\n/* body */\n";
         include($this->ficheros['css']['temas/css/body.css']);
         echo "\n/* css especifico: " . $css . " */\n\n";
         $incluir = $this->ficheros['css'][$css];
         if ( $incluir ) {
            include($incluir);
         } else {
            // registrar(__FILE__,__LINE__,'Archivo css no encontrado: ['.$_GET['css'].']','ERROR');
            trigger_error('Archivo css no encontrado: ['.$_GET['css'].']',E_USER_ERROR);
            }
         echo "\n";

      } else {
         
         echo "\n/* fichero:".$this->ficheros['css']['temas/css/body.css'].": */\n";
         include($this->ficheros['css']['temas/css/body.css']);
         echo "\n/* acaba:".$this->ficheros['css']['temas/css/body.css'].": */\n";

         foreach ( $this->ficheros['css'] as $llave => $fichero) {

            if ( $llave != 'temas/css/body.css' ) {
               echo "\n/* fichero:".$llave.": */\n";
               include($fichero);
               echo "\n/* acaba:".$llave.": */\n";
               }

            }

         }

      $SALIDA = ob_get_contents();
      ob_end_clean();

      if ( GCM_DEBUG ) {
         return $SALIDA;
      } else {
         return minimizar_css($SALIDA);
         }

      }

   /** 
    * Construcción del fichero javascript del proyecto con los
    * ficheros javascript recibidos
    *
    * Los archivos que debe contener vienen definidos por $_GET[arch] y separados por coma.
    *
    * @see incluir_javascript()
    *
    * @return String con el código de javavscript
    */

   function construir_js() {

      global $gcm;

      // Incluir librerías

      $ficheros  = $gcm->lista_js();
      $url_base  = Router::$dir;

      $ficheros  = ( $ficheros ) ? $ficheros : $this->ficheros[js];

      $lista = ( !empty($ficheros) ) ? implode(',',$ficheros) : NULL ;

      if ( !isset($_GET['arch'])  ) {
         echo "\n /**********************************************/";
         echo "\n /* ERROR: No se especifico archivos a incluir */";
         echo "\n /**********************************************/";
         exit();
         }

      if ( GCM_DEBUG ) echo "\n/* Ficheros javascript a incluir\n".depurar($_GET['arch'])."\n*/\n";

      // if ( $librerias && !empty($librerias)  ) {
      //    foreach ( $librerias as $items ) {
      //       list($modulo,$nombre) = explode(':',$items);
      //       $f = $this->ruta(strtolower($modulo),'libjs',$nombre);
      //       //echo "\n".'<script src="'.$url_base.$f.'" type="text/javascript"></script>';
      //       echo "\n/* Librería: $f */\n\n";
      //       include($f);
      //       echo "\n";
      //       }
      //    }

      $SALIDA='';

      // Generar array con los archvos js de los modulos.
      $ficheros_js = explode(',',$_GET['arch']);

      echo "\n\n/* Archivo javascript de proyecto */\n\n";

      // Incluir archivos de módulos

      echo "/* Ficheros: ".depurar($ficheros_js)."*/";

      if ( $ficheros_js ) {

         foreach ( $ficheros_js as $items ) {

            if ( empty($items)  ) break;
            list($modulo,$nombre) = explode(':',$items);
            echo "\n/* $modulo: $nombre */\n\n";
            $f = $this->ruta(strtolower($modulo),'js',$nombre);
            include ($f);
            echo "\n";
            }

         }

      $SALIDA = ob_get_contents();
      ob_end_clean();

      return $SALIDA;

      }

   /**
    * devolver ruta de archivo de tema
    *
    * @param $modulo Módulo al que corresponde
    * @param $tipo   Tipo de archivo: html, css, js, icono, img
    * @param $fichero Nombre del fichero, ejemplo: caja.css
    */

    function ruta($modulo, $tipo, $fichero) {

       $ruta = $modulo.'/'.$tipo.'/'.$fichero;
       $fichero_tema = ( isset($this->ficheros[$tipo][$ruta]) ) ? $this->ficheros[$tipo][$ruta] : NULL;

       if ( $fichero_tema ) {
          return $fichero_tema;
       } else {
          trigger_error('Fichero del modulo: ['.$ruta.'] no definido',E_USER_ERROR);
          //registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$modulo.','.$tipo.','.$fichero.') Fichero no encontrado','ADMIN');
          }
      }

    /**
     * Devolver lista de los colores que se han demandado y no estan 
     * implementados
     * 
     * @return array
     * @access public
     */

    public function devolver_colores_faltantes() {
       return ( $this->colores_faltantes ) ? $this->colores_faltantes : FALSE ;
      }

   /**
    * Devolver rura de icono del tema actual o por defecto de gcm
    *
    * @param nombre Nombre del icono
    *
    * @return ruta del icono
    *
    */

   function icono($nombre) {

      if ( empty($nombre)  ) return FALSE;

      global $gcm;

      $tipo_iconos = array('.gif','.png');

      foreach ( $tipo_iconos as $tipo_icono ) {

         $icono = $nombre.$tipo_icono;

         if ( isset($this->ficheros['iconos']['temas/iconos/'.$icono])  ) {

            $ruta = $this->ficheros['iconos']['temas/iconos/'.$icono];

            if ( is_file($ruta) ) {
               return Router::$dir.$ruta;
            } else {
               return FALSE;
               }
            }

         }

      return FALSE;

      }

   }

?>
