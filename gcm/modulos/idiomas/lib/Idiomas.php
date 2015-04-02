<?php

/**
 * @file      Idiomas.php
 * @brief     Módulo para la manipulación de literales
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 * Created    13/11/09
 * Revision   SVN $Id: Idiomas.php 645 2012-08-21 16:52:00Z eduardo $
 * Copyright  Copyright (c) 2009, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

require_once(GCM_DIR.'lib/int/idiomas/lib/IdiomasCore.php');

/** Modulo Idiomas
 * para la administración de los idiomas
 *
 * Se presentan los idiomas disponobles, se puede cambiar la imagen correspondiente
 * a cada idioma y eliminar un idioma, en tal caso se debe presentar las carpetas
 * que continen el contenido perteneciente a ese idioma y confirmar el borrado del mismo.
 *
 * Se crea un select con los idiomas disponibles y se da la posibilidad de cambiar
 *
 * Se presenta formulario para la entrada de un nuevo idioma, en tal caso:
 *
 * - se creara una nueva carpeta en el directorio Files con el codigo del idioma y en copiasSeguridad 
 * - se añade el literal del nuevo idioma en DATOS/idiomas/idiomas.php
 * - se crear archivo de literales LG_<idioma>.php
 * - se añade imagen al directorio.
 *
 * @author Eduardo Magrané
 *
 */

class Idiomas extends Modulos {

  public $idiomaxdefecto;
  public $idiomas_activados;

  private $iIdiomas;      ///< Isntancia de IdiomasCore

   function __construct() {

      parent::__construct();

      global $gcm;

      $proyecto = $gcm->config('admin','Proyecto');

      $dir_idiomas = $gcm->config('idiomas','Directorio idiomas');

      $this->idiomaxdefecto = $gcm->config('idiomas','Idioma por defecto');

      $this->idiomas_activados = $gcm->config('idiomas','Idiomas activados');

      $configuracion = array( 'dir_idiomas' => $dir_idiomas
                            , 'idiomaxdefecto' => $this->idiomaxdefecto
                            , 'idiomas_activados' => $this->idiomas_activados
                            , 'proyecto' => $proyecto
                            );

      $this->iIdiomas = new IdiomasCore($configuracion);

      }

   /**
    * Instalación
    *
    * Creamos los directorios necesarios para el funcionamiento del módulo
    */

   function instalacion() { return $this->iIdiomas->instalacion(); }

   /**
    * Acción que se realiza con el evento precarga, para comprobar la 
    * existencia del contenido en el idioma especificado.
    *
    */

   function definir_idioma(){

      global $gcm;

      if ( ! is_file(Router::$f) ) {

         /// Buscamos en idioma por defecto
         Router::$f=Router::$dd.Router::$s.Router::$c;

         if ( ! is_file(Router::$f) ) {

            // Si no existe archivo original puede ser que sea que se ha cambiado el
            // idioma predeterminado y no tenemos contenido por defecto
            // Se debería presentar el primero que se encuentre buscando entre los directorios
            // de los idiomas que tenemos y dar la opción de cambiar por otro idioma.

            $gcm->registra(__FILE__, __LINE__, 'Buscamos en otros idioamas');

            foreach($this->iIdiomas->getIdiomasActivados() as $key => $idioma) {
               $fichero_posible='File/'.$key.Router::$s.Router::$c;
               if ( is_file($fichero_posible) ) {
                  $idiomas_posibles[$idioma]=$fichero_posible;
                  }
               }

            // Si no tenemos ningun fichero posible presentamos contenido dinamicamente
            if ( ! isset($idiomas_posibles) ) {
               Router::$sin_contenido=TRUE;

            // Si solo hay una posibilidad la presentamos y avisamos que solo se encontro en ese idioma
            } elseif ( count($idiomas_posibles) == 1 ) {

               list($idioma, Router::$f) = each($idiomas_posibles);
               $gcm->registra(__FILE__,__LINE__,"No se encontro contenido con el idioma predeterminado, se presenta en ".$idioma,'AVISO');

            // Si hay más de una posibilidad presentamos la primera que encontramos y colocamos
            // un select con los posibles idiomas y una explicación
            } else {
               list($idioma, Router::$f) = each($idiomas_posibles);
               }

         } else {

            /* Conutamos sin_traduccion a TRUE */

            Router::$sin_traduccion = TRUE;

            }
         }
      }

   /**
    * Seleccionamos idioma con el que se presenta la página
    *
    * Este metodo detecta idioma actual y genera el array de los 
    * literales
    *
    * El idioma puede venir por GET o POST ['idioma'] o puede estar
    * en la variable de sessión '[proyecto]-idioma'
    */

   public function seleccion_idioma() { 

     $this->iIdiomas->seleccion_idioma(); 

     // Añadimos literales de idioma actual a sesión para poder
     // trabajar con la lista.

     if ( permiso('administrar','literales') 
       && isset($_SESSION['literales_faltantes']) 
       && !empty($_SESSION['literales_faltantes']) ) {

       if ( $this->iIdiomas->literales_faltantes ) {
         $_SESSION['literales_faltantes'] = $this->iIdiomas->literales_faltantes;
       }
     }
   } 

   /**
   * Formulario de entrada
   */

   function printForm() {

      global $_POST, $gcm ;

      echo 'Módulo sin acabar';

      $gcm->event->anular('contenido','idiomas');

      // Buscar los idiomas activados y presentarlos
      // cogemos los literales del archivo de idiomas

      include($this->dir_idiomas."idiomas.php");

      // Buscar los idiomas desactivados y presentarlos

      include($this->dir_idiomas."desactivados.php");

      include ($gcm->event->instancias['temas']->ruta('idiomas','html','form_idiomas.html'));

      }

   /**
    * Administración de los idiomas disponibles en el proyecto
    */

   public function administrar() {

      global $gcm;

      // No presentamos contenido
      $presentar_contenido = FALSE;

      if ( isset($_POST['conf'])) {

         include(GCM_DIR."funciones/gcm_arrayFile.php");
         gcm_leerArray($ARCHIVO, $_SESSION[$gcm->config('admin','Proyecto').'-idioma'], 'si', 'si');

      } elseif ( isset($_POST['accion']) && $_POST['accion'] == 'escribir') {

         include(GCM_DIR."funciones/gcm_arrayFile.php");
         // Generamos copia de seguridad
         if ( ! copiaSeguridad($ARCHIVO) ) {
            $gcm->registra(__FILE__,__LINE__,'No se pudo hacer copia de seguridad','ADMIN');
         }


         if (gcm_escribirArray($ARCHIVO) === FALSE){
            $gcm->registra(__FILE__,__LINE__,'No se pudo guardar los cambios','ERROR');
            return false;
         } else {
            $gcm->registra(__FILE__,__LINE__,'Archivo modificado','AVISO');
         }
         $this->printForm();
         return true;

      } elseif ( isset($_POST['idioma']) ) {

         $gcm->registra(__FILE__,__LINE__,'Se cambia idioma predeterminado a '.$_POST['idioma'],'AVISO');

         $gcm->config('idiomas','Idioma por defecto',$_POST['idioma']);

         $this->printForm();

      } elseif ( isset($_POST['nuevoIdioma']) ) {
         $nombreIdioma=$_POST['idiomaNombre'];
         $codIdioma=substr($_POST['idiomaNombre'],0,3);

         // crear fichero con los literales del nuevo idioma
         $archivoIdioma=$this->dir_idiomas."LG_".$codIdioma.".php";
         if (!$file = fopen($archivoIdioma, "w")) {
            $gcm->registra(__FILE__,__LINE__,"No se puede crear archivo idioma ".$archivoIdioma,'ERROR');
            return FALSE;
            }
         fputs($file, "<?php\n");
         fputs($file, "?>");
         fclose($file);

         // copiar imagen si la hay
         if ( isset($_FILES['imagenIdioma'])) {
            include(GCM_DIR."lib/int/gcm_imagen.php");
            echo "Copiando imagen a DATOS/idiomas/".$codIdioma.".gif";
            if ( ! gcm_imagen_copiar($_FILES["imagenIdioma"],$this->dir_idiomas."".$codIdioma.".gif") ) {
               $gcm->registra(__FILE__,__LINE__,'No se pudo subir imágen','AVISO');
               }
            }
         // añadir nuevo idioma en el fichero de idiomas.php
         include_once(GCM_DIR."funciones/gcm_arrayFile.php");
         if ( gcm_anyadirElemento($this->dir_idiomas."idiomas.php", $codIdioma, $nombreIdioma) === FALSE ) {
            $gcm->registra(__FILE__,__LINE__,'No se pudo añadir idioma nuevo','ERROR');
            }

      } elseif ( isset($_POST['modificarIdioma']) ) {
         $nombreIdioma=$_POST['idiomaNombre'];
         $codIdioma=$_POST['codIdioma'];

         // copiar imagen si la hay
         if ( isset($_FILES['imagenIdioma'])) {
            include(GCM_DIR."lib/int/gcm_imagen.php");
            echo "Copiando imagen a DATOS/idiomas/".$codIdioma.".gif";
            if ( ! gcm_imagen_copiar($_FILES["imagenIdioma"],$this->dir_idiomas."".$codIdioma.".gif") ) {
               $gcm->registra(__FILE__,__LINE__,'No se pudo subir imágen','AVISO');
               }
            }
         // añadir nuevo idioma en el fichero de idiomas.php
         include_once(GCM_DIR."funciones/gcm_arrayFile.php");
         if ( gcm_anyadirElemento($this->dir_idiomas."idiomas.php", $codIdioma, $nombreIdioma) === FALSE ) {
            $GCM_ERROR[]="Mod:idiomas::No se pudo añadir idioma nuevo a DATOS/idiomas/idiomas.php";
            }
         $gcm->registra(__FILE__,__LINE__,'Idioma modificado','AVISO');
         $this->printForm();

      } elseif ( isset($_POST['eliminarIdioma']) ) {

         echo "PENDIENTE DE PROGRAMAR: Eliminar ".$_POST['codIdioma'];
         // Borrar directorio del idioma
         // eliminar imagen del idioma
         // eliminar archivo del idioma con los literales
         // quitar idioma del archivo idiomas

      } elseif ( isset($_POST['desactivarIdioma']) ) {

         include(GCM_DIR."funciones/gcm_arrayFile.php");

         // Añadimos idioma a idioma.php
         $fileIdiomas=$this->dir_idiomas."idiomas.php";
         $fileDesactivados=$this->dir_idiomas."desactivados.php";
         $codIdioma=$_POST['codIdioma'];
         $nameIdioma=$_POST['nameIdioma'];

         if ( gcm_anyadirElemento($fileDesactivados, $codIdioma, $nameIdioma) === FALSE ) {
            $gcm->registra(__FILE__, __LINE__, "No se pudo añadir elemento en ".$fileIdiomas,'ERROR');
         } else {
            // sacamos idioma de desactivados.php
            gcm_eliminarElemento($fileIdiomas, $codIdioma);
            $gcm->registra(__FILE__, __LINE__, 'Idioma: '.$nameIdioma." desactivado",'AVISO');
            }
         $this->printForm();

      } elseif ( isset($_POST['activarIdioma']) ) {

         include(GCM_DIR."funciones/gcm_arrayFile.php");

         // Añadimos idioma a idioma.php
         $fileIdiomas=$this->dir_idiomas."idiomas.php";
         $fileDesactivados=$this->dir_idiomas."desactivados.php";
         $codIdioma=$_POST['codIdioma'];
         $nameIdioma=$_POST['nameIdioma'];

         if ( gcm_anyadirElemento($fileIdiomas, $codIdioma, $nameIdioma) === FALSE ) {
            $gcm->registra(__FILE__, __LINE__, "No se pudo añadir elemento en ".$fileIdiomas, 'ERROR');
         } else {
            // sacamos idioma de desactivados.php
            gcm_eliminarElemento($fileDesactivados, $codIdioma);
            $gcm->registra(__FILE__, __LINE__, 'Idioma: '.$nameIdioma." activado",'AVISO');
            }
         $this->printForm();

      } else {
         
         $this->printForm();
         
         }

   }

   /** 
    * Formulario para selección de idioma
    */

   function selector_idiomas() { $this->iIdiomas->selector_idiomas(); }

   /** 
    * banderas de idiomas
    */
      
   function banderas($e, $args=FALSE) { 
      
      global $gcm;

      $this->iIdiomas->banderas($gcm->event->instancias['temas']->ruta('idiomas','html','banderas.html')); 
      }

   /** 
    * lista de idiomas
    */
      
   function lista_idiomas($e, $args=FALSE) { 
      
      global $gcm;

      $this->iIdiomas->lista_idiomas(); 
      }

   /**
    * Añadir metatags de idiomas.
    */

   function metatags($e, $args=FALSE) {

     global $gcm;

     $contenido = ( Router::$c == 'index.html' ) ? '' : Router::$c;

      echo '<!-- Idiomas -->'."\n";
     foreach ( $this->idiomas_activados as $i ) {
       echo '<link rel="alternate" hreflang="'.$i.'" href="'.$i.'/'.Router::$s.$contenido.'" />'."\n";
     }

   }

   }
?>
