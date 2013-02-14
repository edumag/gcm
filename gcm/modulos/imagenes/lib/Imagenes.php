<?php

/**
 * Gcm - Gestor de contenido mamedu
 *
 * @category Gcm
 * @package Modulos
 * @subpackage Imagenes
 * @author    Eduardo Magrané <eduardo mamedu com>
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU/GPL
 * @version   SVN $Id: Imagenes.php 554 2012-01-17 17:12:56Z eduardo $ 
 */

/** 
 * Tratamiento de imagenes
 *
 * Con este módulo estandarizamos la forma de tratar las imágenes,
 * a la hora de subirla al servidor y adaptarlas al tamaño especificado y
 * a la hora de presentarlas con la generación dethumbnail, que agilice 
 * la presentación de las mismas.
 *
 * @category Gcm
 * @package   Imagenes
 * @author Eduardo Magrané
 * @version 0.1
 */

class Imagenes extends Modulos {

   public $altoMaxImg;
   public $anchoMaxImg;
   public $altoMaxMiniatura;
   public $anchoMaxMiniatura;

   /** Nombre del proyecto */

   private $proyecto;

   /** Idioma por defecto */

   private $idioma;

   /** Constructor */

   function __construct() {

      global $gcm;

      parent::__construct();

      $this->altoMaxImg = $this->config('altoMaxImg');
      $this->anchoMaxImg = $this->config('anchoMaxImg');
      $this->altoMaxMiniatura = $this->config('altoMaxMiniatura');
      $this->anchoMaxMiniatura = $this->config('anchoMaxMiniatura');

      $this->proyecto = $gcm->config('admin','Proyecto');
      $this->idioma   = $gcm->config('idiomas','Idioma por defecto');

      }

   /** 
    * Presentar galeria
    *
    * Presentamos la galería de fotos
    */

   function galeria($e, $args = FALSE) {

      global $gcm;

      $seccion = ( isset($_REQUEST['s']) ) ? $_REQUEST['s'] : FALSE;
      if ( ! $seccion ) $seccion = Router::$dd.Router::$s ;
      $seccion = comprobar_barra($seccion);
      if ( strpos($seccion,Router::$dd) === FALSE ) {
         $seccion = Router::$dd.$seccion;
         }

      $imagen_actual = ( isset($_GET['img']) ) ? $_GET['img'] : FALSE ;

      if ( Router::$formato != 'ajax' ) {

         $gcm->titulo = literal('Galería de imágenes',3);
         $gcm->event->anular('contenido','imagenes');
         $gcm->event->anular('titulo','imagenes');

         }

      $this->librerias_js('jquery.ui.widget.js');
      $this->librerias_js('jquery.iframe-transport.js');
      $this->librerias_js('jquery.fileupload.js');
      // $this->librerias_js('jquery.fileupload-ui.js');
      $this->librerias_js('jquery.colorbox.js');
      $this->javascripts('imagenes.js');
      $this->javascripts('aplicar_colorbox.js');
      //$this->javascripts('administrar_imagenes.js'); // Lanzar javascript

      ?>
      <div id='thumbnails' >

      <?php
      // Formulario para la subida de una imagen con ajax

      if ( permiso('administrar_imagenes') ) {

         ?>

         <form>
         <input style="display: none;" onclick="subida_imagenes_jquery('fileupload','progeso','galeria','<?php echo $seccion ?>');" id="fileupload" type="file" name="files[]" placeholder="Subir imágenes" data-url="?formato=ajax&m=imagenes&a=upload&seccion=<?php echo $seccion;?>" multiple>
         <input onclick="$('#fileupload').click();return false;" type="submit" value="<?php echo literal('Subir imagen');?>">
         </form>
         <div id="progreso">
             <div class="bar" style="width: 0%;"></div>
         </div>
         <div id="mensajes"></div>

         <?php } ?>

      <div id='cajaImg'>

      <?php
      include_once(GCM_DIR.'lib/int/gcm_imagen.php');
      $datos = gcm_listaImagenes($seccion, 5);
      include($gcm->event->instancias['temas']->ruta('imagenes','html','contenido.phtml'));
      ?>
      </div> <!-- Acaba cajaImg -->
      </div> <!-- Acaba thumbnails -->
      <?php

      }
      
   /**
    * Enlazamos las imágenes dentro de contenido hacia thickbox
    */

   function imagenes2thickbox($e, $args=FALSE) {

      global $gcm;

      $this->librerias_js('jquery.colorbox.js');
      $this->javascripts('imagenes.js');
      ?>
      <script>
      addLoadEvent(function(){
         img2thickbox();
      });
      </script>
      <?php
      }
   }

?>
