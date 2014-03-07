<?php

/**
 * @file ImatgeAbstract.php
 *
 * @brief Clase abstracta para imágenes
 **/

/**
 * Imágenes 
 */

class  Imatge {

   public    $id;               ///< Identificador de imatge
   protected $nom;              ///< Nom de l'imatge
   protected $mime;             ///< Tipo mime
   protected $altura;           ///< Altura
   protected $amplada;          ///< Amplada
   protected $src;              ///< Url de l'imatge

   protected $atributs = array(); ///< atributs de imatges

   protected $config;           ///< Configuración de Imágenes, (Viene de Galeria)

   protected $galeria_id;       ///< Identificador de galeria

   protected $loaded;           ///< Tenim la informació de la imatge? TRUE/FALSE 

   /**
    * constructor
    *
    * @param $id Identifiador de imatge, en cas de ser temporal el nom de la imatge
    * @param $galeria Instancia de Galeria
    * @param $atributs Atributs de imatge, de moment sense us.
    */

   function __construct($id, $config=FALSE, $galeria_id=FALSE, $atributs=FALSE) {

      $this->id = $id;
      $this->loaded = FALSE;

      if ( $config ) $this->config = $config;
      if ( $galeria_id ) $this->galeria_id = $galeria_id;

      }

   /**
    * Recogir atributs d'imatge
    */

   public function __get($atribut) { 

      if(!array_key_exists($atribut,$this->atributs)) {
         throw new Exception("Atribut de imatge inexistent [$atribut] en ".__CLASS__);
      } else {
         return $this->atributs[$atribut];
         }
      }

   /**
    * Crear o modificar atribut d'imatge
    */

   public function __set($atribut, $valor) {

      if(!array_key_exists($atribut,$this->atributs)) {
         throw new Exception("Aquet atribut no existeix [$atribut]");
      } else {
         $this->atributs[$atribut] = $valor;
         }
      }

   /** Tornar el src de la imatge */

   public function getSrc() {
      $this->load();
      return $this->src; 
      }

   public function getId() { return $this->id; }

   /**
    * Retorna el cos de la imatge i el tipo mime amb un array per poder preesentarla
    */

   function getImatge() {

      $this->load();

      return array($this->cos, $this->mime);

      }

   function getAltura() {
      $this->load();
      return $this->altura;
      }

   function getAmplada() {
      $this->load();
      return $this->amplada;
      }

   function getMime() {
      $this->load();
      return $this->mime;
      }

   function getCos() {
      $this->load();
      return $this->cos;
      }

   function getNom() {
      $this->load();
      return $this->nom;
      }

   public function __toString() {

      $loaded = ( $this->loaded ) ? 'TRUE' : 'FALSE' ;
      $sortida  = "<p>Imatge: ".$this->id." (loaded: ".$loaded.")</p>";

      $sortida .= "<br />nom: ".$this->nom;
      $sortida .= "<br />mime: ".$this->mime;
      $sortida .= "<br />altura: ".$this->altura;
      $sortida .= "<br />amplada: ".$this->amplada;
      $sortida .= "<br />src: ".$this->src;
      $sortida .= "<br />getMiniaturaSrc: ".$this->getMiniaturaSrc();

      if ( $this->atributs ) {
         foreach ( $this->atributs as $atribut => $valor ) {
            $sortida .= "<br />".$atribut.": "."<b>".$valor."</b>";
            }
         }
      return $sortida;

      }

  /**
   * Generar imagen
   *
   * Generamos una imagen a partir de una origen con las dimensiones y ubicación pasados
   * como argumento.
   *
   * @param $url_imagen  imagen origen
   * @param $url_destino destino Carpeta de destino
   * @param $altura      Alto maximo de la imagen
   * @param $amplaria    Ancho maximo de la imagen
   *
   * @return TRUE/NULL
   */

   function generarImatge($url_imagen, $url_destino, $altura, $amplaria) {

      $vDatosImg = @getimagesize($url_imagen);

      if (!$vDatosImg) {

         $strMessage = 'Error llegint informació d\'imatge';

      } else {

         if (isset($vDatosImg['mime'])) {
            $sTipo = $vDatosImg['mime'];
         } else if(isset($vDatosImg[2])) {
             $sTipo = image_type_to_mime_type($vDatosImg[2]);
         } else if (isset($iTipo)) {
             $sTipo = image_type_to_mime_type($iTipo);
            }

         if ( !$sTipo ) {

            $strMessage = 'Error llegint informació d\'imatge: mime';

         } else {

            switch($sTipo){
               case "image/gif":
                  $fuente = imagecreatefromgif($url_imagen) or 
                     $strMessage = 'Error treballant amb format d\'imatge gif, provi amb altre format';
                  break;
               case "image/jpeg":
                  $fuente = imagecreatefromjpeg($url_imagen) or
                     $strMessage = 'Error treballant amb format d\'imatge jpeg, provi amb altre format';
                  break;
               case "image/png":
                  $fuente = imagecreatefrompng($url_imagen) or
                     $strMessage = 'Error treballant amb format d\'imatge png, provi amb altre format';
                  break;
               default:
                  $strMessage = 'Error treballant amb format d\'imatge '.$sTipo.', provi amb altre format';
                  break;
               }
                

            if ( empty($strMessage) ) {

               $amplaria_imatge	= $vDatosImg[0];
               $altura_imatge	= $vDatosImg[1];

               $imatge_vertical = FALSE;
               if ( $altura_imatge > $amplaria_imatge ) $imatge_vertical = TRUE;

               /* Deduïm si hem de transformar depenent dels paràmetres rebuts */

               $transformacio = FALSE;

               if ( $imatge_vertical ) {

                  // Volem imatge amb altura fixada
                  $amplaria = $amplaria_imatge * $altura / $altura_imatge ; 
                  $transformacio = TRUE;

               } else {

                  // Volem imatge amb amplaria fixada
                  $altura	= $altura_imatge * $amplaria / $amplaria_imatge ; 
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
                   * imagejpeg($nova_imatge,$url_imagen);
                   */

                  /* Se pinta la imagen según el tipo */

                   switch($sTipo){
                       case "image/gif":
                           imagegif($nova_imatge,$url_destino);
                           break;
                       case "image/jpeg":
                           imagejpeg($nova_imatge,$url_destino, 100);
                           break;
                       case "image/png":
                           imagepng($nova_imatge,$url_destino, 9);
                           break;
                       default:
                           $strMessage = 'Error treballant amb format d\'imatge '.$sTipo.', provi amb altre format';
                           break;
                      } 

                  return TRUE;
                       
               } else {

                  if ( copy($url_imagen,$url_destino) ) {

                     return TRUE;

                  } else {

                     $strMessage = 'Error al copiar imatge';

                     }

                  }

               }
            }
         }

      trigger_error($strMessage, E_USER_ERROR);

      return FALSE ;

      } // Acaba generarMiniatura

   /**
    * Generar miniatura
    */

   function generarMiniatura() {

      $nom_imatge = basename($this->src);
      $dir_miniatura = dirname($this->src).'/miniatures/';
      $url_miniatura = $dir_miniatura.$nom_imatge;

      // Si no existe miniatura la creamos
      if ( !file_exists($url_miniatura) ) {

         // Si no existe directorio miniatures lo creamos
         if ( !file_exists($dir_miniatura) ) mkdir($dir_miniatura);

         $this->generarImatge($this->src, $url_miniatura
            , $this->config['altura_presentacio']
            , $this->config['amplada_presentacio']);

         }

      }

   /**
    * Generar miniaturas en subcarpeta .miniatures en caso de no haberla
    * Devolver la url de la miniatura.
    */

   function getMiniaturaSrc() {

      $this->load();
      $nom_imatge = basename($this->src);
      $dir_miniatura = dirname($this->src).'/miniatures/';
      $url_miniatura = $dir_miniatura.$nom_imatge;

      return $url_miniatura;

      }

   /**
    * Recoger imágenes
    */

   function load() {

      if ( $this->loaded ) return;

      // si estamos recogiendo imágenes de una galería existente
      if ( ! $this->src && $this->galeria_id ) {
         $this->src = $this->config['dir_gal'].$this->galeria_id.'/'.$this->id;

         }

      if ( file_exists($this->src) ) {

         $vDatosImg = @getimagesize($this->src);

         if (isset($vDatosImg['mime'])) {
            $this->mime = $vDatosImg['mime'];
         } else if(isset($vDatosImg[2])) {
             $this->mime = image_type_to_mime_type($vDatosImg[2]);
         } else if (isset($iTipo)) {
             $this->mime = image_type_to_mime_type($iTipo);
            }

         $this->nom     = $this->id;
         $this->amplada =  $vDatosImg[0];
         $this->altura  =  $vDatosImg[1];

         $this->generarMiniatura();

      } else {
         return FALSE;
         }

      $this->loaded = TRUE;

      }

   /**
    * Guardar imagen temporal a destino
    *
    * @param $config     Configuración de galería
    * @param $galeria_id Identificador de galería
    */

   function guardar($config, $galeria_id) {

      $this->config = $config;

      $dir_tmp     = $config['dir_tmp'].session_id().'/';
      $dir_destino = comprobar_barra($config['dir_gal'].$galeria_id);

      $img_tmp     = $dir_tmp.$this->id;
      $img_destino = $dir_destino.$this->id;

      // Si no existe la carpeta se crea
      if ( !file_exists($dir_destino) ) mkdir_recursivo($dir_destino);

      if ( ! rename($img_tmp,$img_destino) ) {
         registrar(__FILE__,__LINE__,"ERROR Guardando imagen: [".$img_tmp."] [".$img_destino."]","ERROR");
         }

      // permisos

      chmod($img_destino,$this->config['tipos_permisos']); 

      $this->src = $img_destino;

      $this->load();

      }

   /** Borrar imatge */

   function borrar() {

      $this->load();

      if (! @unlink($this->src) ) {

         echo '<pre>DEPURANDO: ' ; print($this) ; echo '</pre>'; // exit() ; // DEV  
         trigger_error("No s'ha pogut esborrar la imatge [".$this->src."]", E_USER_ERROR); // FALTA LITERAL
         return FALSE;

         }

      // Borramos miniatura

      if ( file_exists($this->getMiniaturaSrc()) ) {

         if (!unlink($this->getMiniaturaSrc()) ) {
            trigger_error(
               "No s'ha pogut esborrar la miniatura: ".$this->getMiniaturaSrc
               , E_USER_ERROR);
            }

         }
   
      return TRUE;

      }

   }

?>
