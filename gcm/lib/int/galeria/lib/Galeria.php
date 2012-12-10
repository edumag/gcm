<?php

/**
 * @file      Galeria.php
 * @brief     Galería de imagenes
 *
 * Detailed description starts here.
 *
 * @author    Eduardo Magrané
 *
 * @internal
 *   Created  16/02/11
 *  Revision  SVN $Id: $
 * Copyright  Copyright (c) 2011, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */


/**
 * Galería de imágenes
 *
 * Añadimos galeria desde un formulario de registro, se presentaraun input para subir
 * imágenes que seran subidas inmediatamente a una carpeta temporal del proyecto, que
 * debe ser definida.
 *
 * El formulario ocultara campos input text con los nombres de las imágenes al hacer
 * submit sobre el formulario, se recibira el nombre de las imágenes y se podran incluir
 * con el método especificado:
 *
 * - tabla:         La imagen se añade en el mismo registro teniendo que especificar el
 *                  campo blob que debe contener la imagen y el campo miemetipe para
 *                  saber el tipo de imagen.
 *
 * - archivo:       La imagen se añade a una carpeta destino.
 *
 * - tabla-archivo: La imagen se guarda en carpeta pero a la vez generamos una referencia
 *                  en la base de datos, tabla galerias que apunta al registro y su tabla,
 *                  con este sistema conseguimos tener la ventaja de poder saber desde una
 *                  sentencia sql que registros tienen imágenes y cuantas, pero sin
 *                  guardarlas en la base de datos,
 *
 * @todo Falta implementar tipo: archivo y tabla
 * @todo Flata presentación de imágenes en sus distintas formas; imagen() formato original
 *       como galeria() con miniaturas de las imágenes con scripts javascript para visuali-
 *       zarlas de forma amena, y miniatura() para presentar su miniatura solamente.
 * @todo Metodo cron que borre las imágenes que se vayan quedando en la carpeta temporal.
 */

class Galeria {

   /* Configurable */

   public $limit_imatges       = 1;        ///< Nombre maxim de imatges de galeria
   public $grandaria_max       = '520000'; ///< Grandaria maxima permesa per imatge en Bytes
   public $amplaria_max        = 640;      ///< Amplària maxima
   public $altura_max          = NULL;     ///< Altura max
   public $amplada_presentacio = 140;      ///< Amplada de presentació
   public $altura_presentacio  = NULL;     ///< Altura de presentació
   public $js_post_pujada      = NULL;     ///< Funció javascript que rep el numero de miniatura que es va pujar
   public $js_post_esborra     = NULL;     ///< Funció javascript que rep avis de imatge esborrada
   public $imatge_espera       = '../imatges/uploading.gif'; ///< Imatge de 'imatge pujan'
   /** Contingut del enllaç per a esborrar */
   public $contingut_enllac_borrar = '<img src="../grafics/goma.gif" width="18" height="16" border="0">';
   public $color_bord          = '#000';   ///< Color bord
   public $dir_tmp             = 'tmp/';   ///< Directorio temporal para las imágenes
   public $url_raiz            = '../';    ///< Camino para llegar a la raiz del proyecto
   public $amplada_boto_file   = 30;       ///< Amplària en caràcters per al camp de pujar fitxer
   public $sufijo              = '01_';    ///< Nos permite trabajar con más de una a la vez y que no choquen.

   /* Internes */

   private $num_miniatures = 0;            ///< Nombre de miniatures
   private $imagenes       = NULL;         ///< Colecció d'imatges
   private $errores        = array();      ///< Llistat d'errors

   /**
    * Estado de la galería, nos permite saber en que estado se encuentra:
    *
    * - insertando:    Para cuando se esta añadiendo un nuevo registro y la imagen de añade
    *                  sin tener por el momento un registro a la que ser vinculada.
    * - modifcando:    Tenemos registro y se puede borrar o añadir imagenes vinculadas a él.
    * - visualizando:  Se muestran imagenes sin permitir edición
    */

   private $estado = NULL;

   /**
    * Tipo de galeria
    *
    * - tabla-archivo: Tenemos una tabla galeria donde se guarda la referencia a la imagen en archivo
    * - archivo:       Se guarda la imagen en carpeta sin relacionarse
    * - tabla:         Un registro tiene los campos imagen y tipomime.
    */

   private $tipoGaleria;

   private $identificador;   ///< Identificador de galería

   private $clase_imagen;    ///< Nombre de la clase a utilizar para Imagen, según tipoGaleria

   private $conf_imatges;    ///< Configuracio per Imatges

   private $fitxer_js;       ///< Fitxer javascript necesari per el funcionament de la galeria

   /**
    * Constructor
    *
    * Si recibimos un identificador el estado sera 'visualizando' por defecto, a no ser que
    * especifiquemos otra cosa.
    *
    * Si no tenemos identificadaor de galería el estado es 'insertando'
    *
    * @param $conf_imatges   Configuracio per les imatges
    * @param $identificadaor Identificador de la galería
    */

   public function __construct($conf_imatges, $identificador=NULL) {

      $this->conf_imatges = $conf_imatges;
      $this->imagenes = array();
      $this->tipoGaleria = $this->conf_imatges['tipo'];
      $this->identificador = $identificador;
      $this->fitxer_js = dirname(__FILE__).'/../js/galeria.js';

      $this->estado = ( $this->identificador ) ? 'visualizando' : 'insertando' ;

      switch($this->tipoGaleria) {

      case 'tabla-archivo':
         $this->clase_imagen = 'ImagenesTablaArchivo';
         break;
      case 'archivo':
         $this->clase_imagen = 'ImagenesArchivo';
         break;
      case 'tabla':
         $this->clase_imagen = 'ImagenesMysql';
         break;
      default:
         $this->clase_imagen = 'ImagenesTablaArchivo';
         break;
         }

      require(dirname(__FILE__).'/'.$this->clase_imagen.'.php');

      }

   /**
    * Subir imagen a directorio temporal
    */

   function subir_imagen() {

      if ( ! file_exists($this->dir_tmp)  ) {
         $this->errores[] = "Sin directorio temporal para imágenes [".$this->dir_tmp."]";
         return FALSE;
         }

      /* Paràmetres enviats des del formulari */

      $nIdThumb	= $_POST["nummin"];     ///< Nombre de miniatura
      $altura     = $this->altura_max;                  ///< Forçar imatge a l'altura establerta
      $amplaria   = $this->amplaria_max;                ///< Forçar imatge a l'amplària establerta

      /** tenim una imatge per pujar */

      $fFileTmpName 	= $_FILES['fimatge']['tmp_name'];
      $fFileName		= $_FILES['fimatge']['name'];
      $fFileSize		= $_FILES['fimatge']['size'];
      $fFileType		= $_FILES['fimatge']['type'];
      $fFileError		= $_FILES['fimatge']['error'];

      if ($fFileError==4){	//No s'ha pujat cap fitxer

         $fFileTmpName	= null;
         $fFileName		= null;
         $fFileSize		= null;
         $fFileType		= null;

         $this->errores[]	= "No ha seleccionat cap fitxer";
         return FALSE;

      } else {

         // *** Control de errores
         if ($fFileError>0){

             switch ($fFileError){

               case 1:  // ** Mida més gran que limit al php.ini (upload_max_filesize)
                  $this->errores[]	= "El fitxer no pot superar les ".$_POST['MAX_FILE_SIZE']." bytes";
               break;

               case 2:  // ** Mida més gran que MAX_FILE_SIZE
                  if ( $_POST['MAX_FILE_SIZE'] ) {
                     $this->errores[]	= "El fitxer no pot superar les ".$_POST['MAX_FILE_SIZE']." bytes";
                  } else {
                     $this->errores[] = 'El fitxer a superat el pes permès pel servidor';
                  }
               break;

               case 3:  // ** Upload interrumput (parcial)
                  $this->errores[]	= "S'ha produit un error en el procés d'upload";
               break;

             }

         } else {

            // *** Control de seguridad
            if (!is_uploaded_file($fFileTmpName)){

               $this->errores[]	= "S'ha produit un error en el procés d'upload del fitxer. Possible intent d'accès no autoritzat al servidor";

            } else {

               $vDatosImg = @getimagesize($_FILES['fimatge']['tmp_name']);


               if (!$vDatosImg) {

                  $this->errores[] = 'Error llegint informació d\'imatge';

               } else {

                  if (isset($vDatosImg['mime'])) {
                     $sTipo = $vDatosImg['mime'];
                  } else if(isset($vDatosImg[2])) {
                      $sTipo = image_type_to_mime_type($vDatosImg[2]);
                  } else if (isset($iTipo)) {
                      $sTipo = image_type_to_mime_type($iTipo);
                  }

                  if ( !$sTipo ) {

                     $this->errores[] = 'Error llegint informació d\'imatge: mime';

                  } else {

                     switch($sTipo){
                        case "image/gif":
                           $fuente = imagecreatefromgif($fFileTmpName) or
                              $this->errores[] = 'Error treballant amb format d\'imatge gif, provi amb altre format';
                           break;
                        case "image/jpeg":
                           $fuente = imagecreatefromjpeg($fFileTmpName) or
                              $this->errores[] = 'Error treballant amb format d\'imatge jpeg, provi amb altre format';
                           break;
                        case "image/png":
                           $fuente = imagecreatefrompng($fFileTmpName) or
                              $this->errores[] = 'Error treballant amb format d\'imatge png, provi amb altre format';
                           break;
                        default:
                           $this->errores[] = 'Error treballant amb format d\'imatge '.$sTipo.', provi amb altre format';
                           break;
                        }


                     if ( empty($this->errores) ) {

                        $amplaria_imatge	= $vDatosImg[0];
                        $altura_imatge	= $vDatosImg[1];

                        /* Deduïm si hem de transformar depenent dels paràmetres rebuts */

                        $transformacio = FALSE;

                        if ( !empty($amplaria) && empty($altura) ) {

                           // Volem imatge amb altura fixada
                           $altura	= $altura_imatge * $amplaria / $amplaria_imatge ;
                           $transformacio = TRUE;

                        } elseif ( !empty($altura) && empty($amplaria) ) {

                           // Volem imatge amb amplària fixada
                           $amplaria = $amplaria_imatge * $altura / $altura_imatge ;
                           $transformacio = TRUE;

                        } elseif ( !empty($altura) && !empty($amplaria) ) {

                           // Volem imatge amb amplària i altura fixada
                           $transformacio = TRUE;

                        }

                        /* Transformen nomes si es mes gran */

                        if ( $transformacio && ( $amplaria_imatge > $amplaria || $altura_imatge > $altura ) ) {

                           $nova_imatge= ImageCreateTrueColor($amplaria, $altura)
                             or $nova_imatge=ImageCreate($amplaria, $altura);

                           ImageCopyResized($nova_imatge,$fuente,0,0,0,0,$amplaria,$altura,$amplaria_imatge,$altura_imatge);

                           /*
                            * Un tercer parametro podemos definir la calidad
                            * que es de 0 a 100 siendo 100 la max calidad.
                            * Por defecto es 75.
                            * imagejpeg($nova_imatge,$fFileTmpName);
                            */

                           /* Se pinta la imagen según el tipo */

                            switch($sTipo){
                                case "image/gif":
                                    imagegif($nova_imatge,$fFileTmpName);
                                    break;
                                case "image/jpeg":
                                    imagejpeg($nova_imatge,$fFileTmpName, 100);
                                    break;
                                case "image/png":
                                    imagepng($nova_imatge,$fFileTmpName, 9);
                                    break;
                                default:
                                    $this->errores[] = 'Error treballant amb format d\'imatge '.$sTipo.', provi amb altre format';
                                    break;
                            }

                           $fLogoSize	= filesize($fFileTmpName);

                        }

                       //  la imatge es guarda en directori temporal
                       $nom_imatge = $fFileName ;
                       $src_img = $this->dir_tmp.$nom_imatge;

                       if ( ! rename($fFileTmpName,$src_img) ) {
                          $this->errores[] = 'No se pudo guardar imagen en directorio temporal';
                           }
                        }
                     }
                  }
               }

            }

         }

      $src_img = ( isset($src_img) ) ? $src_img : '';

      ?>
      <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
      <html xmlns="http://www.w3.org/1999/xhtml">
      <head>
      <title></title>
      <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
      </head>

      <script language="JavaScript">

         if (document.images) {
            thumbIO = new Image();
            thumbIO.src='<?php echo $this->url_raiz.$src_img;?>';
         }

         function showResult(){

           <?php if (empty($this->errores)) { ?>

               /* Canviar el src del darrer thumbnail	*/
               oThumb		=	parent.document.getElementById('thumbIMG<?php echo $nIdThumb;?>');

               oThumb.src	= 	thumbIO.src;

               // Afegim nom d'imatge a formulari

               var input_imatge = document.createElement('input');
               input_imatge.name = 'imatges_formulari[]';
               input_imatge.value = thumbIO.src;
               input_imatge.type = 'text';
               input_imatge.style.display = 'none';
               oThumb.appendChild(input_imatge);


               oThumbButton			= parent.document.getElementById('buttonDIV<?php echo $nIdThumb;?>');
               oThumbButton.innerHTML	= '<a href=\"javascript:esborrarImatge(\'<?=$this->sufijo?>\',<?php echo $nIdThumb;?>);\"><?=$this->contingut_enllac_borrar?></a>';

           <?php } else { ?>

              //** Mostrar missatge d'error
              <?php foreach ( $this->errores as $error ) { ?>
              parent.showMessageBoard('<?php echo addslashes($error);?>');
              <?php } ?>

              //** Esborrar darrer thumbnail
              oThumb		= parent.document.getElementById('thumbDIV<?php echo $nIdThumb;?>');
              oThumb.style.display	= 'none';

              parent.document.getElementById('<?=$this->sufijo?>fimatge').style.display = 'block';

           <?php } ?>

         }

         window.onload	= 	showResult;

      </script>

      <body>
      <?php
      if ( empty($this->errores) ) {
        echo '<img src="'.$this->url_raiz.$src_img.'"/>';
      } else {
         foreach ( $this->errores as $error ) echo '<br />- '.$error;
        }
      ?>

      </body>
      </html>
      <?php
      exit();

      }

   /**
    * Inicia
    *
    * - Comprobamos POST en busca del estado de la galeria
    * - segun estado actuamos
    */

   public function inicia() {

      if ( isset($_GET['accio_galeria']) && $_GET['accio_galeria'] == 'agafa_imatge' ) {

         $this->subir_imagen();

      } else {

         $this->presentaGaleria();

         }

      }

   /**
    * Guardar imagenes de galería
    *
    * Recogemos imagenes de formulario y las guardamos, la forma en que se guardan
    * depende de Imagenes.
    */

   function guardar($id_tabla) {

      if ( isset($_POST['imatges_formulari'])  ) {
         foreach ( $_POST['imatges_formulari'] as $nombre_imagen ) {
            $img = new $this->clase_imagen($this->conf_imatges,$id_tabla);
            $img->guardar($nombre_imagen);
            $this->addItem($img);
            }
         }

      if (isset($_POST['imatges_borrar']) ) {

         foreach ( $_POST['imatges_borrar'] as $nombre_imagen ) {
            $img = new $this->clase_imagen($this->conf_imatges,$id_tabla);
            $img->borrar($nombre_imagen);
            $this->removeItem($img);
            }

         }

      }

   /**
    * Presentar galería
    *
    * Recogemos imágenes de galería
    * presentamos imagenes
    * Presentamos input para subir imagen
    */

   public function presentaGaleria($nom_imatge_temporal=NULL) {

      echo '<script language="JavaScript">';
      include_once($this->fitxer_js);
      echo '</script>';


      if ( $this->identificador ) {
         $this->recoger_imagenes_galeria();
         }

      ?>

		<div class="galeria_imatges" id="<?=$this->sufijo?>galeria">
      <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $this->grandaria_max;?>" />
      <input type="hidden" id="<?=$this->sufijo?>nummin" name="nummin" value="<?=$this->num_miniatures?>" />
      <input type="hidden" id="<?=$this->sufijo?>total_miniaturas" name="total_miniaturas" value="<?=$this->num_miniatures?>" />
      <input type="hidden" name="estado" value="<?=$this->estado?>" />
      <input type="hidden" name="identificador" value="<?=$this->identificador?>" />
      <input id="<?=$this->sufijo?>fimatge" name="fimatge"
      <?php if ( $nom_imatge_temporal ) {
         echo 'value="'.$imatge.'" type="text"';
      } else {
         echo ' type="file" ';
      } ?>
      <?php if ( $this->num_miniatures >= $this->limit_imatges  ) echo " style='display: none;'"; ?>
      size="<?php echo $this->amplada_boto_file; ?>"
      <?php if ( $this->js_post_pujada ) { ?>
      onchange="javascript: pujarImatge(this.form,<?=$this->limit_imatges?>,'<?=$this->js_post_pujada?>');" />
      <?php } else { ?>
      onchange="javascript: pujarImatge(this.form,<?=$this->limit_imatges?>);" />
      <?php } ?>
      <iframe name="frameImatge" width="100%" height="200px" style="display:none;"></iframe>
      </div>

      <?php

      $this->caixa_galeria();
      $this->caixa_missatges();

      /* css */

      echo '<style>';
      include(dirname(__FILE__).'/../css/galeria.css');
      echo '</style>';
      }

   /** Caixa amb missatges de error */

   function caixa_missatges() {

      ?>
      <div id="messageBoardDIV" style="display:none;">
         <div>&nbsp;</div>
         <div class="genButton"><a href="javascript:hideMessageBoard();">ok</a></div>
      </div>
      <?php

      }

   /**
    * Presentem caixa amb les imatges
    *
    * @param $imatge En cas de tindre una imatge temporal pasem el nom
    */

   function caixa_galeria($imatge=NULL) {

      $nIdImg = ( isset($_POST['nIdImg']) ) ? $_POST['nIdImg'] : '';

      echo '<div id="galleryContainerDIV">';

      if ( ! empty($this->imagenes) ) {

         $conta=0;
         foreach ( $this->imagenes as $img ) {
            $conta++;
            $this->presentaImatgeEditar($img,$conta);
            $this->num_miniatures++;
            }

         }
   ?>
      </div>
   <?php
   }

   /**
    * Recoger identificador de imágenes de galeria
    */

   private function recoger_imagenes_galeria() {

      if ( empty($this->imagenes) ) {
         $imagenes = call_user_func( array($this->clase_imagen, "listado"),$this->conf_imatges, $this->identificador );
         foreach ( $imagenes as $nombre_imagen) {
            $this->num_miniatures++;
            $this->imagenes[] = new $this->clase_imagen($this->conf_imatges, $this->identificador, $nombre_imagen);
            }
         }

      }

   /**
    * Editar Imatge
    *
    * @param $img Objecto Imagen
    * @param $num_miniatura Numero de miniatura
    *
    */

   function presentaImatgeEditar($img, $conta=1) {

      ?>
      <div id="thumbDIV<?php echo $conta; ?>" class="galleryThumbnail">
         <div>
            <img id="thumbIMG<?=$conta?>" 
               src="<?=$this->url_raiz.$img->getUrl();?>" 
               style="border: 1px solid <?php echo $this->color_bord;?>" 
               <?php if ( $this->amplada_presentacio ) echo " width='".$this->amplada_presentacio."'"; ?>
               <?php if ( $this->altura_presentacio ) echo " height='".$this->altura_presentacio."'"; ?>
               >
         </div>
         <div id="buttonDIV<?php echo $conta;?>">
            <a href="javascript:esborrarImatge('<?=$this->sufijo?>',<?=$conta?>);">
            <?=$this->contingut_enllac_borrar?>
            </a>
            <input type="hidden" name="imatgeGaleria[<?=$conta?>]" value="<?=$img->getNombre()?>" />
         </div>
      </div>
      <?php

      }

   /**
    * Añadir imagen
    */

   function addItem($img) {
   
      $this->imagenes[] = $img;
      $this->num_miniatures++;

      }

   /**
    * Eliminar imagen de la lista
    */

   function removeItem($img) {
   
      $this->num_miniatures--;

      }

   }
