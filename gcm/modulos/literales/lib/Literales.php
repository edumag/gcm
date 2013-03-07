<?php

/**
 * @file Literales
 * Módulo para los literales
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 *   Created  23/11/09
 *  Revision  SVN $Id: Literales.php 638 2012-08-01 16:39:14Z eduardo $
 * Copyright  Copyright (c) 2009, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

/* GcmConfig */

require_once(GCM_DIR."lib/int/GcmConfig/lib/GcmConfigFactory.php");

/**
 * @class Literales
 * @brief Manejo de literales
 * @version 0.3
 *
 * @todo Crear administrador de literales.
 * @todo Crear mecanismo para eliminar literal de todos los idiomas no solo del que estamos.
 */

class Literales extends Modulos {

   private $etiqueta_inicio;           ///< Formato de etiqueta (Inicio)
   private $etiqueta_final;            ///< Formato de etiqueta (Final)

   function __construct() {

      parent::__construct();

      $this->etiqueta_inicio = '{L{';
      $this->etiqueta_final  = '}}';

      }

   /**
    * Procesar texto para identificar etiqueta {Lit{<archivo>}} y presentar
    * contenido del archivo
    */

   function procesar_texto() {

      global $gcm; $LG ;

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
         $etiqueta = str_replace($this->etiqueta_inicio,'',$remplazar);
         $etiqueta = str_replace($this->etiqueta_final,'',$etiqueta);

         if ( $pos1 && $pos2 && $etiqueta && $remplazar ) {

            $etiqueta = $this->procesa_etiqueta($etiqueta);

            $buffer = str_replace($remplazar,$etiqueta,$buffer);

            }

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
    */

   function procesa_etiqueta($etiqueta) {

      return literal($etiqueta,1) ;

      }

   /** 
    * Presentar panel de literales 
    *
    * @todo Difrenciar entre literales sin contenido y literales sin traducción
    *
    * @param $evento Evento
    * @param $args Array de argumentos
    *
    */

   function panel_literales($e,$args='') {

      $this->javascripts('literales.js');

      ob_start(); 
      echo '<div id="panelLiterales">';
      $this->devolverLiterales(); 
      echo '</div>';
      $salida = ob_get_contents() ; ob_end_clean();

      $panel = array();
      $panel['titulo'] = literal('Literales',3).'['.Router::$i.']';
      $panel['oculto'] = TRUE;
      $panel['href'] = 'javascript:visualizar(\'panelLiterales\');';
      $panel['subpanel'] ='panelLiterales';
      $panel['contenido'] = $salida; 
         
      Temas::panel($panel);

      }

   /**
    * Eliminar literal
    *
    * Eliminamos literal especifico
    *
    * @todo Hacer los mismo en todos los idiomas.
    *
    */

   function eliminarLiteral() {

      global $gcm;

      $idioma = Router::$i;
      $file=$gcm->config('idiomas','Directorio idiomas')."LG_".$idioma.".php";

      $arr = GcmConfigFactory::GetGcmConfig($file);
      $arr->del($_GET['elemento']);
      $arr->guardar_variables();

      echo "Elemento [ ".$_GET['elemento']." ] eliminado";

      }

   /**
   * Devolvemos lista con formato json para javascript con el contenido del array especificado
   *
   * La lista se compondra de los valores.
   *
   * @param $file Archivo que contiene los literales.
   *
   * @return array en formato json
   *
   */

   function devolverLiterales($file=NULL) {

      global $gcm;

      $file = $gcm->config('idiomas','Directorio idiomas')."LG_".Router::$i.".php";

      if ( !file_exists($file) ) {
         trigger_error('Archivo de idiomas ['.$file.'] no existe', E_USER_ERROR);
         return FALSE;
         }

      $arr = GcmConfigFactory::GetGcmConfig($file);

      $salida = '<a class="boton" style="cursor: pointer;" onclick="javascript:insertarLiteral()" >'
         .literal('Añadir',3)
         .'</a>';
      $salida .= '<a class="boton" style="cursor: pointer;" onclick="javascript:filtra()" >'
         .literal('Filtrar',3)
         .'</a>';
      $salida .= '<br /><br />';

      if ( $arr->variables() ) {

         foreach ( $arr->variables() as $key => $valor ) {

            $clase = ( empty($valor) ) ? 'subpanelNegativo' : "subpanel" ;
               
            $salida .= '
               <p class="'.$clase.'">
               <a href="javascript:;" 
                  onclick="tinyMCE.execCommand(\'mceInsertContent\',false,\'{L{'.$key.'}}\'); return false"
                  title="'.$valor.'" >
                  '.$key.'
               </a>
               <a style="font-size: smaller;" title="Eliminar" 
                  href="javascript:;" onclick="eliminarLiteral(\''.str_replace("'","\'",$key).'\')" >
                  [X]
               </a>
               <a style="font-size: smaller;" title="Modificar" 
                  href="javascript:;" onclick="modificarLiteral(\''.$key.'\',\''.$valor.'\')" >
                  [M]
               </a>
               </p>';
            }
         }

      echo $salida;

      }

   /**
    * Añadir elemento nuevo a array
    *
    * @param $_GET Parametros recogidos mediante GET
    *
    *        - elemento: clave del array a modificar
    *        - valor:    Valor a añadir
    *        - file:     Archivo con array, de formato especifico
    *                    En caso de no haberlo cogemos el del idioma actual
    */

   function anyadirLiteral() {
      
      global $gcm;

      $file = $gcm->config('idiomas','Directorio idiomas')."LG_".Router::$i.".php";

      $arr = GcmConfigFactory::GetGcmConfig($file);
      $arr->set($_GET['elemento'],$_GET['valor']);
      $arr->guardar_variables();

      echo "[ ".$_GET['elemento']." ] = [ ".$_GET['valor']." ] en [ ".$file." ]";

      }

   /**
    * Modificar array
    *
    * @param $_GET Parametros recogidos mediante GET
    *
    *        - elemento: clave del array a modificar
    *        - valor:    Valor a añadir
    *        - file:     Archivo con array, de formato especifico
    *                    En caso de no haberlo cogemos el del idioma actual
    *
    * @see GcmConfig
    */

   function modificarLiteral() {

      global $gcm;

      $file = $gcm->config('idiomas','Directorio idiomas')."LG_".Router::$i.".php";

      $arr = GcmConfigFactory::GetGcmConfig($file);

      $arr->set($_GET['elemento'],$_GET['valor']);

      $arr->guardar_variables();

      echo "[ ".$_GET['elemento']." ] = [ ".$_GET['valor']." ] en [ ".$file." ]";

      }

   /** Añadir literal para el contenido nuevo
    *
    * En caso de que ya exista literal no lo modificamos.
    *
    * @param $evento Evento
    * @param $args Array de argumentos
    *
    * @see GcmConfig
    */

   function contenido_nuevo($e, $args='') {

      $extension = ( $e == "postGuardar" ) ? '.html' : '.btml' ;

      $nombre_fichero  = str_replace($extension,'',Router::$c);
      $literal_fichero = str_replace($extension,'',$_POST['documento']);

      /* Eliminar secciones de documento para el literal */

      $conts = explode('/',$literal_fichero);
      $literal_fichero = $conts[count($conts)-1];

      $file=$gcm->config('idiomas','Directorio idiomas')."LG_".Router::$ii.".php";

      $arr = GcmConfigFactory::GetGcmConfig($file);

      /* si hay literal no hacemos nada */

      $litold = $arr->get($nombre_fichero);

      if ( empty($litold)  ) {

         $arr->set($nombre_fichero,$literal_fichero);

         $arr->guardar_variables();

         /* Incluimos elemento en Array global para que no sea añadido con varlor nulo en la recarga de página */

         global $LG;
         $LG[$nombre_fichero]=stripslashes($literal_fichero);

         }

      }

   }

?>
