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
      $panel['jajax']      = "?formato=ajax&m=literales&a=columna"; 
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
   * @return HTML con panel para gestionar literales
   *
   */

   function devolverLiterales() {

      global $gcm;

      $literales = $this->recoger_literales();

      $salida = '<div id="panelLiterales">';
      $salida .= '<br />';
      $salida .= '<a class="boton" style="cursor: pointer;" onclick="javascript:insertar_literal_columna()" >'
         .literal('Añadir',3)
         .'</a>';
      $salida .= '<a class="boton" title="'.htmlentities(literal('Mostrar únicamente literales vacíos',3),ENT_QUOTES, "UTF-8").'" style="cursor: pointer;" onclick="javascript:filtra(this,\'panelLiterales\')" >'
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
                  href="javascript:;" onclick="eliminar_literal_columna(\''.str_replace("'","\'",$key).'\')" >
                  [X]
               </a>
               <a style="font-size: smaller;" title="Modificar" 
                  href="javascript:;" onclick="modificar_literal_columna(\''.$key.'\',\''.$valor.'\')" >
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

      if ( isset($_GET['admin']) && $_GET['admin'] == 1 ) {
         $file = GCM_DIR."DATOS/idiomas/GCM_LG_".Router::$i.".php";
      } else {
         $file = $gcm->config('idiomas','Directorio idiomas')."LG_".Router::$i.".php";
         }

      $arr = GcmConfigFactory::GetGcmConfig($file);

      $arr->set($_GET['elemento'],trim($_GET['valor']));

      $arr->guardar_variables();

      echo "[ ".$_GET['elemento']." ] = [ ".$_GET['valor']." ] en [ ".$file." ]";

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
   
   function administrar($e,$args=NULL) {

      global $gcm;

      $this->javascripts('literales.js');

      // Añadimos contenido a título
      $gcm->titulo = 'Literales de '.literal(Router::$i);
      // Anulamos eventos que son llamados para generar el título
      $gcm->event->anular('titulo','literales');  
      $gcm->event->anular('contenido','literales');  

      $this->lista('panel_admin');
      echo '<h3>'.literal('Literales de aplicación').'</h3>';
      $this->lista('panel_admin_gcm',TRUE);

      return; // DEV
   
      }

   function columna($e, $args=FALSE) {

      global $gcm;

      $this->javascripts('literales.js');
      $this->devolverLiterales();

      }

   /**
    * Recoger literales 
    *
    * @param $admin Espeficificar si queremos los de administración (aplicación) T/F
    *
    * @return Array con los literales
    */

   function recoger_literales($admin=FALSE) {

      global $gcm;

      $literales_default = FALSE;

      if ( $admin ) {
         $file = GCM_DIR."DATOS/idiomas/GCM_LG_".Router::$i.".php";
      } else {
         $file = $gcm->config('idiomas','Directorio idiomas')."LG_".Router::$i.".php";
         }

      $arr = GcmConfigFactory::GetGcmConfig($file);

      $literales = $arr->variables();

      if ( Router::$i != Router::$ii ) {

         if ( $admin ) {
            $file_default = GCM_DIR."DATOS/idiomas/GCM_LG_".Router::$ii.".php";
         } else {
            $file_default = $gcm->config('idiomas','Directorio idiomas')."LG_".Router::$ii.".php";
            }

         if ( !file_exists($file_default) ) {
            trigger_error('Archivo de idiomas ['.$file_default.'] no existe', E_USER_ERROR);
            return FALSE;
            }
         $arr_default = GcmConfigFactory::GetGcmConfig($file_default);
         $literales_default = $arr_default->variables();
         
         }

      if ( $literales_default ) {
         foreach ( $literales_default as $key => $lit ) {
            if ( ! isset($literales[$key]) ) $literales[$key] = '';
            }
         }

      return $literales;

      }

   /**
    * Listado de literales para modificar
    *
    * @param $admin Literales de admin o no TRUE/FALSE
    */

   function lista($panel, $admin = FALSE) {

      global $gcm;

      $admin_js = ( $admin ) ? '1' : '0' ;

      $literales = $this->recoger_literales($admin);

      // Acciones

      ?>

      <div id="<?php echo $panel?>">
         <br />
         <a class="boton" style="cursor: pointer;" onclick="javascript:insertar_literal(<?php echo $admin_js ?>)" >
            <?php echo literal('Añadir',3);?>
         </a>
         <a class="boton" title="<?php echo htmlentities(literal('Mostrar únicamente literales vacíos',3),ENT_QUOTES, "UTF-8")?>" style="cursor: pointer;" onclick="javascript:filtra(this,'<?php echo $panel?>');" >
            <?php echo literal('Ocultar literales con contenido',3) ?>
         </a>

         <ul id="litadmin">

            <?php
            if ( $literales ) {

               foreach ( $literales as $key => $valor ) {

                  $clase = ( empty($valor) ) ? 'subpanelNegativo' : "subpanel" ;
                     
                  $valor = ($valor) ? $valor : $key ;

                     ?>
                     <li id="lit_<?php echo $key ?>" class="<?php echo $clase?>">
                        <a title="Modificar <?php echo htmlentities($key) ?>" 
                           href="javascript:;" onclick="modificar_literal('lit_<?php echo $key?>','<?php echo str_replace("'","\'",$valor)?>',<?php echo $admin_js ?>)" >
                           <?php echo $key ?>
                        </a>
                        <div style="visibility: hidden;">
                           <a title="Eliminar" 
                              href="javascript:;" onclick="eliminar_elemento('lit_<?php echo $key ?>',<?php echo $admin_js ?>)" >
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

      if ( isset($_GET['admin']) && $_GET['admin'] == 1 ) {
         $dir   = GCM_DIR."DATOS/idiomas/";
         $array = "GCM_LG_";
      } else {
         $dir   = $gcm->config('idiomas','Directorio idiomas');
         $array = "LG_";
         }

      foreach ( Router::$idiomas as $idioma ) {
         $file=$dir.$array.$idioma.".php";
         $arr = GcmConfigFactory::GetGcmConfig($file);
         $arr->del($_GET['elemento']);
         $arr->guardar_variables();
         }

      echo "Elemento [ ".$_GET['elemento']." ] eliminado";

      }

   /**
    * Test para literales.
    * @ingroup testgcm
    */
   function test() {

     global $gcm;

     $gcm->event->anular('titulo','admin');
     $gcm->event->anular('contenido','admin');

     $gcm->titulo = '<h1>Testeando proyecto</h1>';

     // Crear un literal de prueba.
     $litg = literal('Probando_literal',3,'Probando literal gcm');
     $litp = literal('Probando_literal',1,'Probando literal proyecto');
     // Comprobar que devuelve bien el resultado.
     $this->ejecuta_test('Verficar literal de GCM: '.$litg,$litg, 'Probando literal gcm');
     $this->ejecuta_test('Verficar literal de proyecto: '.$litp,$litp, 'Probando literal proyecto');

     // Modificarlo.
     $litg = literal('Probando_literal',3,'Probando literal gcm modificado');
     $litp = literal('Probando_literal',1,'Probando literal proyecto modificado');
     $this->ejecuta_test('Verficar literal de GCM: '.$litg,$litg, 'Probando literal gcm modificado');
     $this->ejecuta_test('Verficar literal de proyecto: '.$litp,$litp, 'Probando literal proyecto modificado');

     // Borrarlo.
     // Hacer lo mismo en otro idioma.

   }

   }

?>
