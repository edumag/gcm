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
 */

class ConstantesAdmin extends Constantes {

   function __construct() {

      parent::__construct();

      }

   /**
    * Devolvemos contenido del panel de constantes
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

      }

   /** 
    * Presentar panel de constantes 
    *
    * @param $e Evento
    * @param $args Array de argumentos
    *
    */

   function panel_constantes($e,$args='') {

      $panel = array();
      $panel['titulo'] = literal('Añadir constantes',3);
      $panel['oculto'] = TRUE;
      $panel['jajax']   = "?formato=ajax&m=constantes&a=devolverConstantes";
      $panel['contenido'] = '<div id="panelConstantes"></div>';
      $panel['subpanel']  ='panelConstantes';
      Temas::panel($panel);


      }

   /**
    * Administración de constantes
    *
    * @param  $e Evento
    * @param  $args Argumentos
    * @return TRUE/FALSE
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
