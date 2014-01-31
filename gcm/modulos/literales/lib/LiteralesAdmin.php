<?php

/**
 * @file LiteralesAdmin.php
 * @brief Administración del módulo literales
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

require_once(dirname(__FILE__).'/Literales.php');

/**
 * @class LiteralesAdmin
 * @brief Administración de literales
 */

class LiteralesAdmin extends Literales {

   function __construct() {

      parent::__construct();

      }

   /** 
    * Presentar panel de literales 
    *
    * @param $e Evento
    * @param $args Array de argumentos
    *
    */

   function panel_literales($e,$args='') {

      $this->javascripts('literales.js');

      ob_start(); 
      echo '<div id="panelLiterales">';
      // $this->devolverLiterales(); 
      echo '</div>';
      $salida = ob_get_contents() ; ob_end_clean();

      $panel = array();
      $panel['titulo']     = literal('Literales',3).'['.Router::$i.']';
      $panel['oculto']     = TRUE;
      $panel['subpanel']   ='panelLiterales';
      $panel['jajax']      = "?formato=ajax&m=literales&a=devolverLiterales"; 
      $panel['contenido']  = $salida; 
         
      Temas::panel($panel);

      }

   /**
    * Eliminar literal
    *
    * Eliminamos literal especifico
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
   * Creamos panel con lista de literales para editar.
   *
   * Recogemos primero los literales por defecto para poder modificarlos y 
   * añadimos los del idioma actual.
   *
   * @param $file Archivo que contiene los literales.
   *
   * @return HTML con panel para gestionar literales
   *
   */

   function devolverLiterales($file=NULL) {

      global $gcm;

      $file = $gcm->config('idiomas','Directorio idiomas')."LG_".Router::$i.".php";
      $arr = GcmConfigFactory::GetGcmConfig($file);

      if ( Router::$i != Router::$ii ) {

         $file_default = $gcm->config('idiomas','Directorio idiomas')."LG_".Router::$ii.".php";
         if ( !file_exists($file_default) ) {
            trigger_error('Archivo de idiomas ['.$file_default.'] no existe', E_USER_ERROR);
            return FALSE;
            }
         $arr_default = GcmConfigFactory::GetGcmConfig($file_default);
         $literales = array();
         
         foreach ( $arr_default->variables() as $key => $val ) { 
            $literales[$key] = FALSE;  
            }
         
         $literales = array_merge($literales,$arr->variables());

      } else {
         $literales = $arr->variables();
         }


      $salida = '<div id="panelLiterales">';
      $salida .= '<br />';
      $salida .= '<a class="boton" style="cursor: pointer;" onclick="javascript:insertarLiteral()" >'
         .literal('Añadir',3)
         .'</a>';
      $salida .= '<a class="boton" title="'.htmlentities(literal('Mostrar únicamente literales vacíos',3),ENT_QUOTES, "UTF-8").'" style="cursor: pointer;" onclick="javascript:filtra()" >'
         .literal('Filtrar',3)
         .'</a>';

      $salida .= '<br /><br />';

      if ( $literales ) {

         foreach ( $literales as $key => $valor ) {

            $clase = ( empty($valor) ) ? 'subpanelNegativo' : "subpanel" ;
               
            $salida .= '
               <p class="'.$clase.'">
               <a href="javascript:;" 
                  onclick="tinyMCE.execCommand(\'mceInsertContent\',false,\'{L{'.$key.'}}\'); return false"
                  title="'.htmlentities(literal('Añadir literal a contenido',3).' ('.$valor.')',ENT_QUOTES, "UTF-8").'" 
                  >
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

      $salida .= '</div>';

      echo $salida;

      }

   /**
    * Modificar literal
    *
    * $_GET Parametros recogidos mediante GET
    *   - elemento: clave del array a modificar
    *   - valor:    Valor a añadir
    *   - file:     Archivo con array, de formato especifico
    *               En caso de no haberlo cogemos el del idioma actual
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
    * @param $e    Evento
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

/************************************************************* NUEVO ****************************/


    /**
     * Administración de literales
     *
     * @param  $e Evento
     * @param  $args Argumentos
     * @return TRUE/FALSE
     *
     */
   
   function administrar($e,$args=NULL) {

      global $gcm;

      // Añadimos contenido a título
      $gcm->titulo = 'Literales de '.literal(Router::$i);
      // Anulamos eventos que son llamados para generar el título
      $gcm->event->anular('titulo','literales');  
      $gcm->event->anular('contenido','literales');  

      require_once(GCM_DIR.'lib/int/GcmConfig/lib/GcmConfigGui.php');

      $file = $gcm->config('idiomas','Directorio idiomas')."LG_".Router::$i.".php";
      $idiomaxdefecto = $gcm->config('admin','Idioma por defecto');

      // Guardar la configuració rebuda

      if ( isset($_POST['accion']) && $_POST['accion'] == 'escribir_gcmconfig'  ) {

         /* Nos llega configuración modificada */

         try {

            require_once(GCM_DIR.'lib/int/GcmConfig/lib/GcmConfigGui.php');

            $configuracion = new GcmConfigGui($_POST['archivo']);

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
            literal('Configuración guardada en '.$_POST['archivo'],3),'AVISO');

      } 

      // Presentar formulario

      if ( !file_exists($file) ) {
         trigger_error('Archivo de idiomas ['.$file.'] no existe', E_USER_ERROR);
         return FALSE;
         }

      $configuracion = new GcmConfigGui($file);

      // si no es el idioma por defecto añadimos fichero por defecto para tener los
      // literales por defecto.

      if ( Router::$i != Router::$ii ) {
         $file_default = $gcm->config('idiomas','Directorio idiomas')."LG_".Router::$ii.".php";
         $configuracion_default = new GcmConfigGui($file_default);
         
         foreach ( $configuracion_default->variables() as $key => $val ) { 
            $configuracion->set($key,$configuracion->get($key));  
            }

         }

      $args['eliminar'] = TRUE; // Se permet elimiar variables
      $args['ampliar']  = TRUE; // Se permet ampliar variables
      // $args['css']      = Router::$base.GCM_DIR.'lib/ext/pajinate/styles.css'; // Se permet ampliar variables
      $args['plantilla']= GCM_DIR.'lib/int/GcmConfig/html/formGcmConfigGuiPajinate.phtml'; // Se permet ampliar variables

      $this->javascripts('paginacion.js');

      $configuracion->idiomaxdefecto = $idiomaxdefecto;
      $configuracion->idioma = Router::$i;

      $configuracion->formulario($args);

      }
   
    /**
     * Administración de literales
     *
     * @param  $e Evento
     * @param  $args Argumentos
     * @return TRUE/FALSE
     *
     */
   
   function admin($e,$args=NULL) {

      global $gcm;

      // Añadimos contenido a título
      $gcm->titulo = 'Literales de '.literal(Router::$i);
      // Anulamos eventos que son llamados para generar el título
      $gcm->event->anular('titulo','literales');  
      $gcm->event->anular('contenido','literales');  

      $this->lista();
      return; // DEV
   
      }

   function lista($file=NULL) {

      global $gcm;

      $file = $gcm->config('idiomas','Directorio idiomas')."LG_".Router::$i.".php";
      $arr = GcmConfigFactory::GetGcmConfig($file);

      if ( Router::$i != Router::$ii ) {

         $file_default = $gcm->config('idiomas','Directorio idiomas')."LG_".Router::$ii.".php";
         if ( !file_exists($file_default) ) {
            trigger_error('Archivo de idiomas ['.$file_default.'] no existe', E_USER_ERROR);
            return FALSE;
            }
         $arr_default = GcmConfigFactory::GetGcmConfig($file_default);
         $literales = array();
         
         foreach ( $arr_default->variables() as $key => $val ) { 
            $literales[$key] = FALSE;  
            }
         
         $literales = array_merge($literales,$arr->variables());

      } else {
         $literales = $arr->variables();
         }

      // Acciones

      ?>

      <div id="panel_admin">
         <br />
         <a class="boton" style="cursor: pointer;" onclick="javascript:insertarLiteral()" >
            <?php echo literal('Añadir',3);?>
         </a>
         <a class="boton_activo" title="<?php echo htmlentities(literal('Mostrar únicamente literales vacíos',3),ENT_QUOTES, "UTF-8")?>" style="cursor: pointer;" onclick="javascript:filtra(this);" >
            <?php echo literal('Filtrar',3) ?>
         </a>

         <ul id="litadmin">

            <?php
            if ( $literales ) {

               foreach ( $literales as $key => $valor ) {

                  $clase = ( empty($valor) ) ? 'subpanelNegativo' : "subpanel" ;
                     
                  $valor = ($valor) ? $valor : $key ;

                     ?>
                     <li id="<?php echo $key ?>" class="<?php echo $clase?>">
                        <a title="Modificar" 
                           href="javascript:;" onclick="modificar_literal('<?php echo $key?>','<?php echo $valor?>')" >
                           <?php echo $valor ?>
                        </a>
                        <div style="visibility: hidden;">
                           <a title="Eliminar" 
                              href="javascript:;" onclick="eliminar_elemento('<?php echo $key ?>')" >
                              [X]
                           </a>
                        </div>
                     </li>
                     <?php
                  }
               }

            ?>
         </ul>
      </div>
      <?php

      }

   /**
    * Eliminar literal
    *
    * Eliminamos literal especifico
    */

   function eliminar_elemento() {

      global $gcm;

      foreach ( Router::$idiomas as $idioma ) {
         $file=$gcm->config('idiomas','Directorio idiomas')."LG_".$idioma.".php";
         $arr = GcmConfigFactory::GetGcmConfig($file);
         $arr->del($_GET['elemento']);
         $arr->guardar_variables();
         }

      echo "Elemento [ ".$_GET['elemento']." ] eliminado";

      }
   }

?>
