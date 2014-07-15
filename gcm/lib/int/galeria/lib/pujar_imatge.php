<?php

/**
 * @file pujar_imatge.php
 * @brief Pujada d'imatges de Galeria 
 */

// $nom_camp_imatge_formulari = ( isset($nom_camp_imatge_formulari) ) ? $nom_camp_imatge_formulari : 'fimatge';


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

                  /* Deduïm si hem de transformar depenent dels paràmetres rebuts */

                  $transformacio = FALSE;

                  if ( $imatge_vertical ) {

                     // Volem imatge amb amplària fixada
                     $amplaria = $amplaria_imatge * $altura_galeria / $altura_imatge ; 
                     $altura = $altura_galeria;
                     if ( GCM_DEBUG ) registrar(__FILE__,__LINE__,"amplaria: $amplaria_imatge X $altura_galeria / $altura_imatge = $amplaria");
                     $transformacio = TRUE;

                  } else {

                     // Volem imatge amb altura fixada
                     $altura	= $altura_imatge * $amplaria_galeria / $amplaria_imatge ; 
                     $amplaria = $amplaria_galeria;
                     if ( GCM_DEBUG ) registrar(__FILE__,__LINE__,"altura: $altura_imatge X $amplaria_imatge / $amplaria_imatge = $altura");
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
                  $dir_img = $this->dir_base_acciones.$this->galeria_url;
                  $src_img = $dir_img.$nom_imatge;

                  if ( ! file_exists($dir_img) ) { 
                     if ( ! mkdir_recursivo($dir_img) ) {
                        $strMessage = literal("No se pudo crear directorio destino",3). "[".$this->galeria_url."]";
                        echo stripslashes($strMessage);
                        exit();
                        } 
                     }
                  if ( GCM_DEBUG ) registrar(__FILE__,__LINE__,"src_img: $src_img");
                  
                  rename($fFileTmpName,$src_img);
                  chmod($src_img,$this->tipos_permisos);

                  $imatge = new Imatge($nom_imatge, $this->config, $this->id);
                  $this->addImatge($imatge);

                  if ( GCM_DEBUG ) registrar(__FILE__,__LINE__,"Galeria al guardar imagen: ".print_r($this,1));

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
   echo $this->dir_base.$this->galeria_url.$nom_imatge;
} else {
   echo stripslashes($strMessage);
   }
// </debug>
?>
