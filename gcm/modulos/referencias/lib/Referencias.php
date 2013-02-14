<?php

/**
 * @file Módulo de referencias
 *
 * Este móduo nos permite tener un mecanismo para insertar referencias en el contenido.
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 *   Created  23/11/09
 *  Revision  SVN $Id: Referencias.php 510 2011-05-10 14:11:37Z eduardo $
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

class Referencias extends Modulos {

   private $etiqueta_inicio;           ///< Formato de etiqueta (Inicio)
   private $etiqueta_final;            ///< Formato de etiqueta (Final)

   function __construct() {

      parent::__construct();

      $this->etiqueta_inicio = '{Ref{';
      $this->etiqueta_final  = '}}';

      }

   /**
    * Contenido de cabecera de las referencias
    */

   function preprocesa_texto() {

      $salida = '<div class="panel">';
      $salida .= '<span class="tituloPanel">';
      $salida .= '<a href="javascript:visualizar(\'caja_enlaces\')" >'.literal('Enlaces relacionados').'</a>';
      $salida .= '</span>';
      $salida .= '<div id="caja_enlaces" class="subpanel_visible">';
      $salida .= '<ul>';
      return $salida;

      }

   /**
    * postprocesa_texto
    *
    * Pie de las referencias
    */

   function postprocesa_texto() {

      $salida = '</ul>';
      $salida .= '</div>';
      $salida .= '</div>';

      return $salida;

      }

   /**
    * Procesar texto donde se encuentran las etiquetas
    */

   function procesar_texto() {

      global $gcm;

      $buffer = ob_get_contents().$gcm->contenido ;
      ob_clean();

      $num_etiqueta = 0;

      while ( strpos($buffer, $this->etiqueta_inicio) !== false ) {

         $num_etiqueta++;
         $pos1 = NULL;
         $pos2 = NULL;
         $archivo  = NULL;
         $remplazar = NULL;
         $archivo = NULL;

         $pos1 = strpos($buffer, $this->etiqueta_inicio);
         $pos2 = strpos($buffer, $this->etiqueta_final, $pos1);
         $remplazar = substr($buffer, $pos1, $pos2 - $pos1 + 2);
         $etiqueta = str_replace($this->etiqueta_inicio,'',$remplazar);
         $etiqueta = str_replace($this->etiqueta_final,'',$etiqueta);

         if ( $pos1 && $pos2 && $etiqueta && $remplazar ) {

            $etiqueta = $this->procesa_etiqueta($etiqueta,$num_etiqueta);
            $buffer = str_replace($remplazar,$etiqueta,$buffer);

            }

         }

      /* Tratamos la última etiqueta para poner el pie de las referencias */

      if ( !empty($etiqueta)  ) {

         $buffer = str_replace($etiqueta,$etiqueta.$this->postprocesa_texto(),$buffer);

         }

      $gcm->contenido=$buffer;

      }

   /**
    * Procesar la etiqueta
    *
    * Devuelve el valor a modificar desde procesar_texto()
    *
    * @see procesar_texto
    *
    * @param $etiqueta Etiqueta a modificar.
    * @param $num_etiqueta Numero de etiqueta.
    */

   function procesa_etiqueta($ref,$num_etiqueta) {

      $salida = '';

      if ( $num_etiqueta == 1  ) $salida = $this->preprocesa_texto();

      if ( strpos($ref,'href') ) {                  ///< Si ya se trato con rst2html lo dejamos igual
         $salida .= '<li>'.$ref.'</li>';
      } elseif ( strpos($ref,'::') ) {
         list($ref,$nombre) = explode('::',$ref);
         $salida .= '<li><a href="'.$ref.'">'.$nombre.'</a></li>';
      } else {
         $nombre = $ref;
         $salida .= '<li><a href="'.$ref.'">'.$nombre.'</a></li>';
         }

      return $salida;

      }

   /** Panel de referencias
    *
    * Añadir referencias del contenido que estamos editando
    */

   function panel() {

      // Presentamos panel de referencias si estamos editando

      if ( Router::$e !== 'editar_contenido' && Router::$e !== 'nuevo'  ) return;

      $panel = array();
      $panel['titulo'] = literal('Insertar').' '.literal('referencia');
      $panel['oculto'] = TRUE;
      $panel['href'] = 'javascript:visualizar(\'referencias_form\');';
      $panel['subpanel'] ='referencias_form';
      $panel['contenido'] = 
         '<form class="formAdmin" onSubmit="javascript:insertaReferencia(this); return false; ">
         Nombre: <input type="text" name="nombre" />
         <br />
         Enlace: <input type="text" name="enlace" />
         <br />
         <input type="submit" value="'.literal('Insertar').'" />
         </form>';

      Temas::panel($panel);

      }


   }

?>
