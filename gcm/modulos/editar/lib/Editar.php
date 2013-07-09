<?php

/**
 * @file      Editar.php
 * @brief     Módulo para la edición de contenido
 *
 * Módulo con las herramientas nacesarias para la edición de contenido
 *
 * @category  Modulos
 * @package   Editar
 * @author    Eduardo Magrané 
 * @version   0.2
 *
 * @internal
 * Created    13/11/09
 * Revision   SVN $Id: Editar.php 554 2012-01-17 17:12:56Z eduardo $
 * Copyright  Copyright (c) 2009, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

require_once(GCM_DIR."lib/int/GcmConfig/lib/GcmConfigFactory.php");

/** 
 * Editar
 *
 * Metodos que acompañan a la acción de editar contenido, como ejemplo
 * añadir tinyHTML al textarea del contenido.
 *
 * @category  Modulos
 * @package   Editar
 * @author    Eduardo Magrané
 *
 */

class Editar extends Modulos {

   function __construct() { parent::__construct() ; }

   /** editorweb
    *
    * Presentamos editor web
    *
    * @param $contingut_inicial Contenido del textarea
    * @param $nom_textarea Nombre del campo textarea al que se tiene que aplicar el editor web
    * @param $css Archivo css para el textarea
    *
    * @return TRUE/FALSE
    *
    * @author Eduardo Magrané
    * @version 1.0
    *
    */

   function editor_web($e, $args=NULL) {

      global $gcm;

      $idiomaxdefecto = $gcm->config('idiomas','Idioma por defecto');

      /* Añadimos accesorios a columna */

      $gcm->event->accion2evento('columna','editar','panel_insertarEmail',6);
      $gcm->event->accion2evento('columna','editar','panel_constantes',6);

      ?>
      <script language="javascript" type="text/javascript" src="<?php echo GCM_DIR_P ?>lib/ext/tiny_mce/tiny_mce.js"></script>

      <script language="javascript" type="text/javascript">
      tinyMCE.init({
         mode : "textareas",
         editor_selector : "editor_completo",
         theme : "advanced",
         languages : 'es',
         apply_source_formatting : true,
         content_css : "proyectos.css?css=contenidos/css/contenido.css",
         plugins : "table,advhr,advimage,advlink,insertdatetime,preview,searchreplace,print,contextmenu,fullscreen,spellchecker",
         theme_advanced_buttons1_add : "fontselect,fontsizeselect",
         theme_advanced_buttons2_add : "separator,insertdate,inserttime,preview,separator,forecolor,backcolor",
         theme_advanced_buttons2_add_before: "cut,copy,paste,code,separator,search,replace,separator",
         theme_advanced_buttons3_add_before : "tablecontrols,separator",
         theme_advanced_buttons3_add : "emotions,iespell,advhr,separator,print,fullscreen,spellchecker",
         theme_advanced_toolbar_location : "top",
         theme_advanced_toolbar_align : "left",
         theme_advanced_path_location : "bottom",
         plugin_insertdate_dateFormat : "%Y-%m-%d",
         plugin_insertdate_timeFormat : "%H:%M:%S",
         extended_valid_elements : "a[name|href|target|title|onclick],img[class|src|border=0|alt|title|hspace|vspace|width|height|align|onmouseover|onmouseout|name],hr[class|width|size|noshade],font[face|size|color|style],span[class|align|style]",
         file_browser_callback : "fileBrowserCallBack",
         external_link_list_url : "example_data/example_link_list.js",
         external_image_list_url : "<?='File/'.$idiomaxdefecto.'/'.Router::$s ?>/.listaImg.js",
         spellchecker_languages : "+spanish=es,Catala=ca,English=en,Swedish=sv"
         });

      function fileBrowserCallBack(field_name, url, type, win) {
         // This is where you insert your custom filebrowser logic
         alert("Example of filebrowser callback: field_name: " + field_name + ", url: " + url + ", type: " + type);

         // Insert new URL, this would normaly be done in a popup
         win.document.forms[0].elements[field_name].value = "someurl.htm";
         }

      </script>
      <?php

   }

   /** Insertar email en el editor web
    *
    * De esta forma insertamos email desglosados para protegerlos
    * del spam
    */

   function panel_insertarEmail() {

      global $gcm;

      $panel = array();
      $panel['titulo'] = literal('Insertar Email');
      $panel['oculto'] = TRUE;
      $panel['contenido'] = get_include_contents($gcm->event->instancias['temas']->ruta('editar','html','form_insertar_email.html',$gcm));

      Temas::panel($panel);

      }

   /**
   * Devolvemos lista con formato json para javascript con el contenido del array especificado
   *
   * La lista se compondra de los valores.
   *
   * @return array en formato json
   *
   */

   function devolverArray($file=NULL) {

      $file = ( isset($file) ) ? $file : $_GET['file'];

      $arr = GcmConfigFactory::GetGcmConfig($file);

      print json_encode($arr->variables());

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

   function anyadirArray() {

      $file=$_GET['file'];
      $arr = GcmConfigFactory::GetGcmConfig($file);
      $arr->set($_GET['elemento'],$_GET['valor']);
      $arr->guardar_variables();

      echo "Elemento [ ".$_GET['elemento']." ] añadido o modificado por [ ".$_GET['valor']." ]";

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

   function modificarArray() {

      global $gcm;

      $file=$_GET['file'];

      $arr = GcmConfigFactory::GetGcmConfig($file);

      $arr->set($_GET['elemento'],$_GET['valor']);

      $arr->guardar_variables();

      echo "Elemento [ ".$_GET['elemento']." ] añadido o modificado por [ ".$_GET['valor']." ]";

      }

   /** Cambio de nombre de contenido
    *
    * El nombre del fichero a cambiar el nombre viene en POST['original'] 
    * y el destino en POST['destino'].
    *
    * @param $evento Evento
    * @param $args Array de argumentos
    *
    * @todo ¿Borrar el literal del nombre anterior?
    *
    */

   function cambio_nombre_contenido($e,$args='') {

      global $gcm, $LG;

      $original = $_POST['original'];
      $destino  = $_POST['destino'];

      $original_r = $gcm->router->desglosarUrl($original);
      $destino_r  = $gcm->router->desglosarUrl($destino);

      $original = str_replace('\.html','',$original_r['c']);
      $destino  = str_replace('\.html','',$destino_r['c']);

      $file=$gcm->config('idiomas','Directorio idiomas')."LG_".Router::$ii.".php";

      $arr = GcmConfigFactory::GetGcmConfig($file);

      $arr->del($original);
      $arr->set(GUtil::textoplano($destino),$destino);

      $arr->guardar_variables();

      global $LG;
      $LG[GUtil::textoplano($destino)]=$destino;

      }

   /** 
   * Creamos un panel con las constantes para poder ser insertados en la pagina editada.
   *
   * @todo Constantes debe tener su propio módulo
   */

   function panel_constantes() {

      global $gcm;

      $this->javascripts('generico.js');

      if ( $gcm->au->logeado() ) { // solo lo presentamos si estamos editando

         $panel = array();
         $panel['titulo'] = literal('Constantes',3);
         $panel['oculto'] = TRUE;
         $panel['ajax']   = "pedirDatos('?formato=ajax&m=editar&a=devolverArray&file=DATOS/constantes/GCM_CONS.php','presentaConstantes');";
         $panel['contenido'] = '<div id="panelConstantes"></div>';
         Temas::panel($panel);

         }
      }

   /**
   * Formulario de entrada
   */

   function formAdminConstantes() {

      global $gcm;

      $this->javascripts('generico.js');

      ?>
      <br />
      <form method='post' >
      <fieldset>
      <legend><?php literal('Administración de constantes',3);?></legend>
      <input type='hidden' name='m' value='editar' />
      <input type='hidden' name='a' value='administrar_constantes' />
      <input type='submit' name='conf' value='Configurar' />
      <input type='submit' name='copia' value='Recuperar copia de seguridad' />
      </fieldset>
      </form>
      <br />
      <?php
      $gcm->event->anular('contenido','editar');

      }

   /**
    * Administración de las constantes del proyecto
    *
    * @param $e Evento
    * @param $args Argumentos en formato array
    */

   function administrar_constantes() {

      global $gcm;

      if ( $gcm->au->logeado() ) {

         $ARCHIVO="DATOS/constantes/GCM_CONS.php";

         include_once($ARCHIVO);

         $gcm->event->anular('contenido','editar');

         if ( isset($_POST['conf'])) {

            include_once(GCM_DIR."funciones/gcm_arrayFile.php");
            gcm_leerArray($ARCHIVO, 'es', 'si', 'si');

         } elseif ( $_POST['accion'] == 'escribir') {

            include_once(GCM_DIR."funciones/gcm_arrayFile.php");

            if (gcm_escribirArray($ARCHIVO) === FALSE){
               $gcm->registra(__FILE__,__LINE__,"No se pudo guardar los cambios",'ERROR');
               return false;
            } else {
               $gcm->registra(__FILE__,__LINE__,"Archivo modificado",'AVISO');
               }
            $this->formAdminConstantes();
            return true;

         } else {

            $this->formAdminConstantes();

            }

      } else {
         $gcm->registra(__FILE__,__LINE__,"Las constantes solo pueden ser administradas por los administradores",'AVISO');
         }

      }

   }
?>
