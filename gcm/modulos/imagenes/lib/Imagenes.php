<?php

/**
 * @file Imagenes.php
 * @brief Módulo para la gestión de imágenes
 *
 * @package Modulos
 */

/** 
 * @class Imagenes
 * @brief Tratamiento de imagenes
 *
 * Con este módulo estandarizamos la forma de tratar las imágenes,
 * a la hora de subirla al servidor y adaptarlas al tamaño especificado y
 * a la hora de presentarlas con la generación dethumbnail, que agilice 
 * la presentación de las mismas.
 *
 */

class Imagenes extends Modulos {

   public $altoMaxImg;         ///< Alto máximo para las imágenes
   public $anchoMaxImg;        ///< Ancho máximo para las imágenes   
   public $altoMaxMiniatura;   ///< Alto máximo para las miniaturas
   public $anchoMaxMiniatura;  ///< Ancho máximo para las miniaturas

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
    * test
    */

   function test() {

      // Comprobar existencia de archivos necesarios
      $this->ejecuta_test('Comprobar configuración: alto máximo de imagenes',$this->altoMaxImg);
      $this->ejecuta_test('Comprobar configuración: ancho máximo de imagenes',$this->anchoMaxImg);
      $this->ejecuta_test('Comprobar configuración: alto máximo de miniaturas',$this->altoMaxMiniatura);
      $this->ejecuta_test('Comprobar configuración: alto máximo de miniaturas',$this->anchoMaxMiniatura);
      $this->ejecuta_test('Comprobar configuración: dominio de proyecto',$this->proyecto);
      $this->ejecuta_test('Comprobar configuración: Idioma por defecto',$this->idioma);

      }


   /** 
    * Presentar galeria
    *
    * Presentamos la galería de fotos
    *
    * @param $e Evento
    * @param $args Argumentos
    */

   function galeria($e, $args = FALSE) {

      global $gcm;

      $seccion = ( isset($_REQUEST['seccion']) ) ? $_REQUEST['seccion'] : FALSE;
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
    *
    * @param $e Evento
    * @param $args Argumentos
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
