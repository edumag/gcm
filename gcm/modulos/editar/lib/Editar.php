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

   /**
    * Ruta hacia el código javascript del editor desde html
    */

   private $editor = "lib/ext/tiny_mce/tiny_mce.js";
   private $ruta_editor_html;

   function __construct() { 

      parent::__construct() ; 

      $this->ruta_editor_html = GCM_DIR_P.$this->editor;

      }

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

      ?>
      <script language="javascript" type="text/javascript" src="<?php echo $this->ruta_editor_html?>"></script>

      <script language="javascript" type="text/javascript">
      tinyMCE.init({
         mode : "textareas",
         editor_selector : "editor_completo",
         theme : "advanced",
         languages : 'es',
         apply_source_formatting : true,
         content_css : "proyectos.css?css=contenidos/css/contenido.css",
         plugins : "table,advhr,advimage,advlink,preview,searchreplace,contextmenu,fullscreen,spellchecker",
         theme_advanced_buttons1_add : "fontselect,fontsizeselect",
         theme_advanced_buttons2_add : "separator,preview,separator,forecolor,backcolor",
         theme_advanced_buttons2_add_before: "code,separator,search,replace,separator",
         theme_advanced_buttons3_add_before : "tablecontrols,separator",
         theme_advanced_buttons3_add : "emotions,iespell,advhr,separator,fullscreen,spellchecker",
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

   /**
    * test
    */

   function test() {

      // Comprobar existencia de archivos necesarios
      $this->ejecuta_test('Comprobar editor',is_file(GCM_DIR.$this->editor));

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

   }
?>
