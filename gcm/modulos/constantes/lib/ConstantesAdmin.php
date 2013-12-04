<?php

/**
 * @file ConstantesAdmin.php
 * @brief Administración del módulo constantes
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 *   Created  23/11/09
 *  Revision  SVN $Id: Constantes.php 638 2012-08-01 16:39:14Z eduardo $
 * Copyright  Copyright (c) 2009, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

require_once(dirname(__FILE__).'/Constantes.php');

/**
 * @class ConstantesAdmin
 * @brief Administración de constantes
 * @version 0.3
 *
 * @todo Crear mecanismo para eliminar constante de todos los idiomas no solo del que estamos.
 */

class ConstantesAdmin extends Constantes {

   function __construct() {

      parent::__construct();

      }

   /**
    * Devolvemos lista con formato json para javascript con el contenido del array 
    * de las constantes
    *
    * @return array en formato json
    *
    */

   function devolverConstantes() {

      global $gcm;

      $file = $this->fichero_constantes;

      $arr = GcmConfigFactory::GetGcmConfig($file);

      $salida = '<div id="panelConstantes">';
      $salida .= '<br />';


      if ( $arr->variables() ) {

         foreach ( $arr->variables() as $key => $valor ) {

            $clase = ( empty($valor) ) ? 'subpanelNegativo' : "subpanel" ;
               
            $salida .= '<p class="'.$clase.'" >';
            $salida .= '<a href="javascript:;" onmousedown="tinyMCE.execCommand(\'mceInsertContent\',false,\'{C{' . $key . '}}\');" ';
            $salida .= '" >';
            $salida .= '<span class="desc_cons">'.$arr->getDescripcion($key).'</span><span class="val_cons"> '.$valor . '</span></a>';
            $salida .= '</p>';
            }
         }

      $salida .= '</div>';

      echo $salida;

      return;

      $arr = GcmConfigFactory::GetGcmConfig($file);

      print json_encode($arr->variables());

      }

   /** 
    * Presentar panel de constantes 
    *
    * @param $e Evento
    * @param $args Array de argumentos
    *
    */

   function panel_constantes($e,$args='') {

      $this->javascripts('constantes.js');

      $panel = array();
      $panel['titulo'] = literal('Añadir constantes',3);
      $panel['oculto'] = TRUE;
      $panel['jajax']   = "?formato=ajax&m=constantes&a=devolverConstantes";
      $panel['contenido'] = '<div id="panelConstantes"></div>';
      $panel['subpanel']  ='panelConstantes';
      Temas::panel($panel);


      }

   /**
    * Eliminar constante
    *
    * Eliminamos constante especifico
    *
    * @todo Hacer los mismo en todos los idiomas.
    *
    */

   function eliminarConstante() {

      global $gcm;

      $idioma = Router::$i;
      $file   = $this->fichero_constantes;

      $arr = GcmConfigFactory::GetGcmConfig($file);
      $arr->del($_GET['elemento']);
      $arr->guardar_variables();

      echo "Elemento [ ".$_GET['elemento']." ] eliminado";

      }

   /**
    * Modificar array
    *
    * $_GET Parametros recogidos mediante GET
    *   - elemento: clave del array a modificar
    *   - valor:    Valor a añadir
    *   - file:     Archivo con array, de formato especifico
    *               En caso de no haberlo cogemos el del idioma actual
    *
    * @see GcmConfig
    */

   function modificarConstante() {

      global $gcm;

      $file=$gcm->config('idiomas','Directorio idiomas')."LG_".Router::$ii.".php";

      $arr = GcmConfigFactory::GetGcmConfig($file);

      $arr->set($_GET['elemento'],$_GET['valor']);

      $arr->guardar_variables();

      echo "[ ".$_GET['elemento']." ] = [ ".$_GET['valor']." ] en [ ".$file." ]";

      }

   /** Añadir constante para el contenido nuevo
    *
    * En caso de que ya exista constante no lo modificamos.
    *
    * @param $e    Evento
    * @param $args Array de argumentos
    *
    * @see GcmConfig
    */

   function contenido_nuevo($e, $args='') {

      $extension = ( $e == "postGuardar" ) ? '.html' : '.btml' ;

      $nombre_fichero  = str_replace($extension,'',Router::$c);
      $constante_fichero = str_replace($extension,'',$_POST['documento']);

      /* Eliminar secciones de documento para el constante */

      $conts = explode('/',$constante_fichero);
      $constante_fichero = $conts[count($conts)-1];

      $file   = $this->fichero_constantes;

      $arr = GcmConfigFactory::GetGcmConfig($file);

      /* si hay constante no hacemos nada */

      $litold = $arr->get($nombre_fichero);

      if ( empty($litold)  ) {

         $arr->set($nombre_fichero,$constante_fichero);

         $arr->guardar_variables();

         /* Incluimos elemento en Array global para que no sea añadido con varlor nulo en la recarga de página */

         global $LG;
         $LG[$nombre_fichero]=stripslashes($constante_fichero);

         }

      }

   /**
    * Administración de constantes
    *
    * @param  $e Evento
    * @param  $args Argumentos
    * @return TRUE/FALSE
    * @bug Al presentar de nuevo el formulario despues de una actualización no muestra los cambios
    *
    */
   
   function administrar($e,$args=NULL) {

      global $gcm;

      // Añadimos contenido a título
      $gcm->titulo = 'Administración de constantes';
      // Anulamos eventos que son llamados para generar el título
      $gcm->event->anular('titulo','constantes');  
      $gcm->event->anular('contenido','constantes');  

      require_once(GCM_DIR.'lib/int/GcmConfig/lib/GcmConfigGui.php');

      $file   = $this->fichero_constantes;
      $idiomaxdefecto = $gcm->config('admin','Idioma por defecto');

      // Guardar la configuració rebuda

      if ( isset($_POST['accion']) && $_POST['accion'] == 'escribir_gcmconfig'  ) {

         /* Nos llega configuración modificada */

         try {

            require_once(GCM_DIR.'lib/int/GcmConfig/lib/GcmConfigGui.php');

            $configuracion = new GcmConfigGui($file);

            $configuracion->idiomaxdefecto = $idiomaxdefecto;
            $configuracion->idioma = Router::$i;
            $configuracion->ordenar = TRUE;

            $configuracion->escribir_desde_post();

            unset($configuracion);

         } catch (Exception $ex) {
            registrar($ex->getFile(),$ex->getLine(),$ex->getMessage(),'ERROR');
            return FALSE;
            }

         registrar(__FILE__,__LINE__,
            literal('Constantes guardadas'),'AVISO');

      } 

      // Presentar formulario

      $configuracion = new GcmConfigGui($file);

      $args['eliminar'] = TRUE; // Se permet elimiar variables
      $args['ampliar']  = TRUE; // Se permet ampliar variables
      $args['modificar_descripciones'] = TRUE;


      $configuracion->idiomaxdefecto = $idiomaxdefecto;
      $configuracion->idioma = Router::$i;

      $configuracion->formulario($args);

      }
   
   }

?>
