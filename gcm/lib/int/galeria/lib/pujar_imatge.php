<?php

/**
 * @file pujar_imatge.php
 * @brief Pujada d'imatges de Galeria 
 */

// $nom_camp_imatge_formulari = ( isset($nom_camp_imatge_formulari) ) ? $nom_camp_imatge_formulari : 'fimatge';

registrar(__FILE__,__LINE__,"Galeria desde pujar_imatge: ".depurar($this));


/* Comprobar directorio temporal */

if ( $this->count() >= $this->limit_imatges ) {
   $strMessage = 'Error limit d\'imatges superat';

} elseif ( !file_exists($this->dir_tmp) ) {
   $strMessage = "Directorio temporal [".$this->dir_tmp."] no existe, atual: [".getcwd()."]";

} elseif ( !is_writable($this->dir_tmp) ) {
   $strMessage = "Directorio temporal [".$this->dir_tmp."] sin permisos de escritura";
   }
/* Paràmetres enviats des del formulari */

$miniatura_id	= ( isset($_POST["pIdThumb"]) ) ? $_POST["pIdThumb"] : FALSE; ///< Nombre de miniatura
$altura_galeria     = $this->altura_max;             ///< Forçar imatge a l'altura establerta 
$amplaria_galeria   = $this->amplaria_max;           ///< Forçar imatge a l'amplària establerta 

registrar(__FILE__,__LINE__,"Altura max de galeria: $altura_galeria");
registrar(__FILE__,__LINE__,"Anchura max de galeria: $amplaria_galeria");

/** tenim una imatge per pujar */

if ( isset($_FILES) ) {

   foreach ( $_FILES as $file ) {

      $fFileTmpName 	= $file['tmp_name'];
      $fFileName		= $file['name'];
      $fFileSize		= $file['size'];
      $fFileType		= $file['type'];
      $fFileError		= $file['error'];

      break;
      }

if ($fFileError==4){	//No s'ha pujat cap arxiu

   $fFileTmpName	= null;
   $fFileName		= null;
   $fFileSize		= null;
   $fFileType		= null;
   
   $strMessage	= "No ha seleccionat cap arxiu";

} else {

   // *** Control de errores
   if ($fFileError>0){
       
       switch ($fFileError){
         
         case 1:  // ** Mida més gran que limit al php.ini (upload_max_filesize)
            $strMessage	= "L'arxiu no pot superar les ".$_POST['MAX_FILE_SIZE']." bytes";
         break;
         
         case 2:  // ** Mida més gran que MAX_FILE_SIZE
            if ( $_POST['MAX_FILE_SIZE'] ) {
               $strMessage	= "L'arxiu no pot superar les ".$_POST['MAX_FILE_SIZE']." bytes";
            } else {
               $strMessage = 'L\'arxiu a superat el pes permès pel servidor';
            }
         break;
         
         case 3:  // ** Upload interrumput (parcial)
            $strMessage	= "S'ha produit un error en el procés d'upload";
         break;
         
       }
       
   } else {
    
      // *** Control de seguridad
      if (!is_uploaded_file($fFileTmpName)){

         $strMessage	= "S'ha produit un error en el procés d'upload de l'arxiu. Possible intent d'accès no autoritzat al servidor";

      } else {	
   
         $vDatosImg = @getimagesize($fFileTmpName);


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
                     $fuente = imagecreatefromgif($fFileTmpName) or 
                        $strMessage = 'Error treballant amb format d\'imatge gif, provi amb altre format';
                     break;
                  case "image/jpeg":
                     $fuente = imagecreatefromjpeg($fFileTmpName) or
                        $strMessage = 'Error treballant amb format d\'imatge jpeg, provi amb altre format';
                     break;
                  case "image/png":
                     $fuente = imagecreatefrompng($fFileTmpName) or
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

                  registrar(__FILE__,__LINE__,"Imagen vertical ".( $imatge_vertical ) ? 'SI' : 'NO' );
                  
                  /* Deduïm si hem de transformar depenent dels paràmetres rebuts */

                  $transformacio = FALSE;

                  if ( $imatge_vertical ) {

                     // Volem imatge amb amplària fixada
                     $amplaria = $amplaria_imatge * $altura_galeria / $altura_imatge ; 
                     $altura = $altura_galeria;
                     registrar(__FILE__,__LINE__,"amplaria: $amplaria_imatge X $altura_galeria / $altura_imatge = $amplaria");
                     $transformacio = TRUE;

                  } else {

                     // Volem imatge amb altura fixada
                     $altura	= $altura_imatge * $amplaria_galeria / $amplaria_imatge ; 
                     $amplaria = $amplaria_galeria;
                     registrar(__FILE__,__LINE__,"altura: $altura_imatge X $amplaria_imatge / $amplaria_imatge = $altura");
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
                              $strMessage = 'Error treballant amb format d\'imatge '.$sTipo.', provi amb altre format';
                              break;
                      } 

                     $fLogoSize	= filesize($fFileTmpName);
                          
                  }

                  $nom_imatge = $fFileName ;
                  //  la imatge es guarda en directori temporal 
                  $src_img = $this->galeria_url.$nom_imatge;

                  if ( ! file_exists($this->galeria_url) ) { 
                     if ( ! mkdir($this->galeria_url) ) {
                        $strMessage = literal("No se pudo crear directorio destino",3). "[".$this->galeria_url."]";
                        echo stripslashes($strMessage);
                        exit();
                        } 
                     }
                  registrar(__FILE__,__LINE__,"src_img: $src_img");
                  
                  rename($fFileTmpName,$src_img);
                  // @todo Hacer configurable los permisos a modificar
                  chmod($src_img,0777);

                  if ( $this->temporal ) {

                     $imatge = new Imatge($nom_imatge);
                     $this->addImatge($imatge);
                     $imatge_id = $nom_imatge;

                  } else {

                     $imatge = new Imatge($nom_imatge, $this);
                     $imatge_id = $imatge->guardar($this);
                     $this->addImatge($imatge);
                     $src_img = $this->dir_base.$imatge->getMiniaturaSrc();

                     }
                  }
               }
            }
         }
      }		
   }

} else { // Acaba si hay imagen
   $strMessage	= "No ha seleccionat cap arxiu";
   } // Acaba no hay imagen

// <degug>
if ( empty($strMessage) ) {
   echo $src_img;
   registrar(__FILE__,__LINE__,"Imagen guardada en $src_img");
   
} else {
   echo stripslashes($strMessage);
   registrar(__FILE__,__LINE__,$strMessage);
   }
// </debug>
?>
