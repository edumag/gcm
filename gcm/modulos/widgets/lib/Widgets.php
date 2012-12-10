<?php

/**
 * @file      Widgets.php
 * @brief     Módulo para insertar widgets
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 *   Created  23/11/09
 *  Revision  SVN $Id: Widgets.php 469 2011-02-04 07:56:07Z eduardo $
 * Copyright  Copyright (c) 2009, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

/**
 * @class Widgets
 * @brief Insertar widgets
 * @version 0.1
 */

class Widgets extends Modulos {

   /** Directorio donde buscar conntenido */

   private $dir_widgets = 'DATOS/modulos/widgets/';       

   function __construct() {

      parent::__construct();

      }

   function presenta_widget($e,$widget=NULL) {

      global $gcm;

      $gcm->registra(__FILE__,__LINE__,'Presentar widget '.$widget);

      $nomArchivo = $this->dir_widgets.$widget.'.php';

      if ( file_exists($nomArchivo) ) {
         $salida = file_get_contents($nomArchivo);
         return $salida;
         }

      $gcm->registra(__FILE__,__LINE__,'No se encontro widget ['.$widget.']','ERROR');

      return FALSE;

      }

   function widget_con_panel($e,$args=NULL) {

      $args = recoger_parametros($args);

      $widget = $args['widget'];

      $contenido = $this->presenta_widget($e,$widget);

      if ( $contenido ) {

         ?>
         <div class="panel">
            <span class="tituloPanel">
               <a href="javascript:visualizar('caja_<?=$widget;?>');">
                  <?=literal($widget)?>
               </a>
            </span>
            <div id="caja_<?=$widget;?>" class="subpanel_visible" style="text-align: left;">
               <?php echo $contenido; ?>
            </div>
         </div>
         <?php

         }

      }
   }

?>
