<?php

/**
 * @file ImagenesAdmin.php
 * @brief Métodos administrativos para el módulo imagenes
 */

require_once ( 'Imagenes.php' );

/** 
 * Administración para el módulo imagenes
 */

class ImagenesAdmin extends Imagenes {

   /** Constructor */

   function __construct() {

      global $gcm;

      parent::__construct();

      }


   /**
   * Administrador de imagenes
   *
   * Que acompaña al formulario de tiny
   * solo lo presentamos si estamos editando
   *
   */

   function formulario($e, $args = FALSE) {

      if ( ! permiso('administrar_imagenes') ) return ;


      $this->librerias_js('jquery.ui.widget.js');
      $this->librerias_js('jquery.iframe-transport.js');
      $this->librerias_js('jquery.fileupload.js');
      $this->librerias_js('jquery.colorbox.js');
      $this->javascripts('imagenes.js');
      $this->javascripts('aplicar_colorbox.js');
      $contenido = '<div id="thumbnails_columna"></div>';

      $panel = array();
      $panel['titulo']    = literal('Imágenes',3);
      $panel['href']      = '/imagenes/galeria';
      $panel['oculto']    = TRUE;
      $panel['jajax']     = '?formato=ajax&m=imagenes&a=galeria_columna&s='.Router::$dd.Router::$s;
      $panel['contenido'] = $contenido;

      Temas::panel($panel);


   }

   /**
    * Borrado de imagenes con ajax
    */

   function borrarImg() {

      global $gcm;

      ob_end_clean();
      include_once(GCM_DIR.'lib/int/gcm_imagen.php');
      if ( ! gcm_borrarImagen($_GET['img']) ) {
         print json_encode(array('Error al borrar imagen'));
      } else {
         $res[] = 0;
         $res[] = dirname($_GET['img']) ;
         print json_encode($res);
      }
      exit();
   }

   /** 
    * Presentar galeria en columna 
    */

   function galeria_columna($e, $args = FALSE) {

      global $gcm;

      $seccion = ( isset($_REQUEST['s']) ) ? $_REQUEST['s'] : FALSE;
      if ( ! $seccion ) $seccion = Router::$dd.Router::$s ;
      $seccion = comprobar_barra($seccion);
      if ( strpos($seccion,Router::$dd) === FALSE ) {
         $seccion = Router::$dd.$seccion;
         }

      ?>
      <div id='thumbnails_columna' >

      <?php
      // Formulario para la subida de una imagen con ajax

      if ( permiso('administrar_imagenes') ) {

         ?>

         <form>
         <input style="display: none;" onclick="subida_imagenes_jquery('fileupload_columna','progreso_columna','galeria_columna','<?php echo $seccion ?>');" id="fileupload_columna" type="file" name="files[]" placeholder="Subir imágenes" data-url="?formato=ajax&m=imagenes&a=upload&seccion=<?php echo $seccion;?>" multiple>
         <input onclick="$('#fileupload_columna').click();return false;" type="submit" value="<?php echo literal('Subir imagen');?>">
         </form>
         <div id="progreso_columna">
             <div class="bar" style="width: 0%;"></div>
         </div>
         <div id="mensajes"></div>

         <?php } ?>

      <div id='cajaImg'>

      <?php
      include_once(GCM_DIR.'lib/int/gcm_imagen.php');
      $datos = gcm_listaImagenes($seccion, 5);
      include($gcm->event->instancias['temas']->ruta('imagenes','html','columna.phtml'));
      ?>
      </div> <!-- Acaba cajaImg -->
      </div> <!-- Acaba thumbnails -->
      <?php

      }

   /**
    * Recoger la subida de imagenes
    */

   function upload($e, $args = FALSE) {

      global $gcm;

      require('UploadHandler.php');
      $opciones = array(
         'image_versions' => array(
            '' => array(
                'max_width' => $this->anchoMaxImg,
                'max_height' => $this->altoMaxImg,
                'jpeg_quality' => 100
               ),
            'thumbnail' => array(
                'max_width' => $this->anchoMaxMiniatura,
                'max_height' => $this->altoMaxMiniatura
               )
            )
         );

      $upload_handler = new UploadHandler($opciones);
      return;

      }
      

   }

?>
