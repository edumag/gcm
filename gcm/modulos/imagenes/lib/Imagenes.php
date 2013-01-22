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
 * a la hora de presentarlas con la generación de miniaturas, que agilice 
 * la presentación de las mismas.
 *
 * @todo Configuración en el módulo no en admin
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
   * Administración de las imágenes
   */

   function administrar() {

      global $gcm;

      $gcm->titulo = literal('Galería de imágenes',3);
      $gcm->event->anular('contenido','imagenes');
      $gcm->event->anular('titulo','imagenes');

      permiso(8);

      $this->javascripts('imagenes.js');
      $this->javascripts('administrar_imagenes.js'); // Lanzar javascript

      $s = Router::$s;

      // Formulario para la subida de una imagen con ajax

      ob_start();
      $this->contenido_ventana_subir_imagen();
      ?>
      <a class="boton" title="<?php echo literal('Subir imagen a ',3).' '.$s ?>" onclick="javascript:ventana('Subir Imagen',conFormUpload, 'subeImagen');" >Subir Imagen</a>
      <br />
      <div id='thumbnails' >
      <div id='cajaImg'>
      </div> <!-- Acaba cajaImg -->
      </div> <!-- Acaba thumbnails -->
      <?php
      $contenido = ob_get_contents();
      ob_end_clean(); 

      echo $contenido;

      }

   /**
    * Contenido para subir imagen con ventana flotante 
    */

   function contenido_ventana_subir_imagen() {

      ?>
      <script type="text/javascript">

         /// Contenido de la ventana para subir una imagen
         var conFormUpload = '<div id="formUpload" >';
         conFormUpload +=  '<form method="post" enctype="multipart/form-data" ';
         conFormUpload +=  'action="?a=subirImagen&m=imagenes&pro=<?=$this->proyecto?>" ';
         conFormUpload +=  'target="iframeUpload">';
         conFormUpload +=  '<input name="fimagen" type="file" onchange="javascript: submit();" />';
         conFormUpload +=  '<input name="directorio" type="hidden" value="File/<?=$this->idioma.'/'.$s ?>" />';
         //conFormUpload +=  '<input type="hidden" name="m" value="admin/subirimagen" />';
         conFormUpload +=  '<input type="hidden" name="metodo" value="ajax" />';
         conFormUpload +=  '<input type="hidden" name="subirImagenes" value="ajax" />';
         conFormUpload +=  '<iframe name="iframeUpload" style="display:none"></iframe>';
         conFormUpload +=  '</form>';
         conFormUpload +=  '</div>';

         /**
         * Recogemos la respuesta del modulo subirimagenes
         * si es afirmativa actualizamos caja de imágenes (cajaImg)
         */
         
         function resultadoUpload(estado, txt) {

            if (estado == 0) {
               // Actualizar caja de imágenes 
               if ( document.getElementById('imgEdit') ) {
                  pedirDatos('?m=imagenes&a=ajaxImg&s=<?='File/'.$this->idioma.'/'.$s ?>','editarImagenes');
               }

               if ( document.getElementById('thumbnails') ) {
                  pedirDatos('?m=imagenes&a=ajaxImg&s=<?='File/'.$this->idioma.'/'.$s ?>','editarImagenesAdmin');
               }
               return;
            }
            if (estado == 1) var mensaje = 'Falta directorio ' + txt;
            if (estado == 2) var mensaje = 'Falta archivo ' + txt ;
            if (estado == 3) var mensaje = 'No se pudo subir Archivo ' + txt ;
            if (estado == 4) var mensaje = txt;

            alert(mensaje);
         }
      </script>
      <?php

      }

   /**
    * Subir al imagenes al servidor
    */

   function subirImagen() {

      global $gcm;
 
      $this->javascripts('imagenes.js');

      if ($_POST['subirImagenes']) {

         registrar(__FILE__,__LINE__,"Subiendo Imagen");

         // Selecionar carpeta donde guardar la imagen

         if (empty($_POST['directorio']) || $_FILES['fimagen']['name'] == "" ) {

            if ( empty($_POST['directorio']) )  {
               // Si el metodo es ajax enviamos mensaje a la ventana padre
               if ( $_POST['metodo'] == 'ajax' ) {
                  echo '<script>parent.resultadoUpload (\'1\', \''.$_FILES['fimagen']['name'].'\');</script>';
               } else {
                  registrar(__FILE__,__LINE__,literal('Falta directorio destino',3),'ERROR');
               }
            }

            if (  $_FILES['fimagen']['name'] == "" )  {
               // Si el metodo es ajax enviamos mensaje a la ventana padre
               if ( $_POST['metodo'] == 'ajax' ) {
                  echo '<script>parent.resultadoUpload (\'2\', \''.$_FILES['fimagen']['name'].'\');</script>';
               } else {
                  registrar(__FILE__,__LINE__,literal('Falta archivo'),'ERROR');
               }
            }


         } else {

            // *** Tratamiento de la imágen

            $fimagen 	= $_FILES['fimagen'];
            include_once(GCM_DIR.'lib/int/gcm_imagen.php');
            registrar(__FILE__,__LINE__,"Imagenen [".$fimagen."] Directorio destino: ".$_POST['directorio']);
            if ( ! gcm_imagen_copiar($fimagen, $_POST['directorio'], $this->altoMaxImg, $this->anchoMaxImg, '.miniaturas', $this->altoMaxMiniatura, $this->anchoMaxMiniatura) ) {
               // Si el metodo es ajax enviamos mensaje a la ventana padre
               if ( $_POST['metodo'] == 'ajax' ) {
                  echo '<script>parent.resultadoUpload (\'3\', \''.$_FILES['fimagen']['name'].'\');</script>';
               } else {
                  trigger_error(literal('Error al subir imagen',3), E_USER_ERROR);
               }

            } else {
               // Actualizamos lista de imagenes para tiny
               include_once(GCM_DIR."lib/int/gcm_imagen.php");
               // Creamos lista de imagenes pata tiny al momento
               if ( ! gcm_listaImagenes($_POST['directorio']) ) {
                  registrar(__FILE__,__LINE__,"No se pudo crear lista de imagenes",'ERROR');
               }
               // Si el metodo es ajax enviamos mensaje a la ventana padre
               if ( $_POST['metodo'] == 'ajax' ) {
                  echo '<script>parent.resultadoUpload (\'0\', \''.$_FILES['fimagen']['name'].'\');</script>';
               } else {
                  registrar(__FILE__,__LINE__,literal('Imagen subida',3),'AVISO');
                  ?>
                  <script language='javascript'>
                     window.close();
                  </script>
                  <?php
               }


            }

         }

      } else {
         
         echo "<h2>Modulo para subida de imágenes</h2";

      }

   }

   /**
   * Administrador de imagenes
   *
   * Que acompaña al formulario de tiny
   * solo lo presentamos si estamos editando
   *
   */

   function formulario() {

      permiso(8);

      $this->javascripts('imagenes.js');

      $s = Router::$s;

      // Formulario para la subida de una imagen con ajax

      ob_start();
      $this->contenido_ventana_subir_imagen();
      ?>
      <a class="boton" title="<?php echo literal('Subir imagen a ',3).' '.$s ?>" onclick="javascript:ventana('Subir Imagen',conFormUpload, 'subeImagen');" >Subir Imagen</a>
      <br />
      <div id='imgEdit' >
      <div id='cajaImg'>
      </div> <!-- Acaba cajaImg -->
      </div> <!-- Acaba thumbnails -->
      <?php
      $contenido = ob_get_contents();
      ob_end_clean(); 

      $panel = array();
      $panel['titulo'] = literal('Imágenes',3);
      $panel['oculto'] = TRUE;
      $panel['ajax'] = 'pedirDatos(\''.Router::$dir.'?m=imagenes&a=ajaxImg&s=File/'.Router::$ii.'/'.$s.'\',\'editarImagenes\');';
      $panel['contenido'] =$contenido;

      Temas::panel($panel);


   }

   /**
    * Listado de imágenes con ajax
    */

   function ajaxImg() {

      ob_end_clean();
      include_once(GCM_DIR.'lib/int/gcm_imagen.php');
      gcm_listaImagenes(Router::$s, 3);
      exit();

   }

   /**
    * Borrado de imagenes con ajax
    *
    * @todo Crear función que nos devuelva los errores habidos
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

   /** Presentar galeria
    *
    * Presentamos la galería de fotos con apoyo del módulo gcmImg
    */

   function presentar_galeria() {

      global $gcm;

      $this->javascripts('imagenes.js');

      //ob_end_clean();
      $img = $_GET['img'];
      // presentamos cabeceras
      $gcm->event->anular('contenido','imagenes');
      $gcm->titulo = literal('Galería de imágenes',3);
      $gcm->event->anular('titulo','imagenes');

      $gcm->event->anular('columna','imagenes');

      // Añadimos lista javascript con las imagenes
      //echo '<script src="'.$dd.$s.'.listaImg.js'.'" type="text/javascript" language="JavaScript"></script>';
      include_once(GCM_DIR.'lib/int/gcm_imagen.php');
      echo "\n<script language='javascript'>";
      gcm_listaImagenes(Router::$dd.Router::$s, 4);
      echo "\n</script>";

      // Añadimos gcmImgs.js para la presentación de imagenes
      echo "\n",'<script src="'.Router::$dir.GCM_DIR.'modulos/imagenes/lib/gcmImgs.js'.'" type="text/javascript" language="JavaScript"></script>';

      // Sistema de listado de imagenes manual
      //echo "\n<script language='javascript'>";
      //echo "\nimagen1= new Array(3);";
      //echo "\nimagen1[0]='".$img."';";
      //echo "\n</script>";

      echo '<div id="contenido" >';

      //echo '<img src=\'',$img,'\' />';
      echo "\n<script language='javascript'>";
      echo "\npresentar_contenido();";
      echo "\n</script>";
      echo '</div>';
      //echo '</body></html>';
      //exit();

   }
}

?>
