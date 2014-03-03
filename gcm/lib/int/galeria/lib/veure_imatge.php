<?php

/**
 * @file veure_imatge.php
 *
 * @brief Presentar imatge d'una galeria. 
 *
 * Parametres:
 *
 * -  $_GET['g']    Nom galeria 
 * -  $_GET['id']   Identificador d'imatge
 * -  $_GET['nci']  Nom camp Identificador, sino per defecte
 * -  $_GET['ncc']  Nom camp cos d'imatger, sino per defecte
 * -  $_GET['ncm']  Nom camp mimetipe, sino per defecte
 * -  $_GET['v']    Tipo de visualització 'normal' 'pop', per defecte 'normal'
 */

session_start();

/* Comprovar dades rebudes */

$nom_galeria   = ( $_GET['g']   ) ? $_GET['g']   : NULL;
$id            = ( $_GET['id']  ) ? $_GET['id']  : NULL;
$nom_camp_id   = ( $_GET['nci'] ) ? $_GET['nci'] : NULL;
$nom_camp_cos  = ( $_GET['ncc'] ) ? $_GET['ncc'] : NULL;
$nom_camp_mime = ( $_GET['ncm'] ) ? $_GET['ncm'] : NULL;

// Noms per defecta
$nom_camp_id   = ( $nom_camp_id   ) ? $nom_camp_id    : 'nIdImatge';
$nom_camp_cos  = ( $nom_camp_cos  ) ? $nom_camp_cos   : 'blImgFile';
$nom_camp_mime = ( $nom_camp_mime ) ? $nom_camp_mime  : 'vchImgMime';
$nom_camp_size = ( $nom_camp_size ) ? $nom_camp_size  : 'nImgSize';

$tipo_visualitzacio = ( $_GET['v'] ) ? $_GET['v'] : NULL;

if ( !$nom_galeria ) { echo 'Error: Sense galeria' ; exit(); }
if ( !$id ) { echo 'Error: Sense identificador' ; exit(); }

if ( $nom_camp_id )   { $bd_nom_camp_id = $nom_camp_id;     }
if ( $nom_camp_cos )  { $bd_nom_camp_cos = $nom_camp_cos;   }
if ( $nom_camp_mime ) { $bd_nom_camp_mime = $nom_camp_mime; }

/* En cas d'imatge temporal sera  la url  que apunta cap a ella */

if ( stripos($id,'http') !== FALSE ) {
   header('Location: '. $id);
}

$sql = 'SELECT '.$nom_camp_mime.', '.$nom_camp_cos.', '.$nom_camp_size.' FROM '.$nom_galeria. ' WHERE ' . $nom_camp_id. '='.$id;

$RSImatge = mysql_query($sql);

$imatge = mysql_fetch_array($RSImatge);

$cos  = $imatge[$nom_camp_cos];
$mime = $imatge[$nom_camp_mime];
$size =  $imatge[$nom_camp_size];

if ( !isset($_GET['width']) && ! isset($_GET['height']) ) {  // Presentem imatge tal com es

   header('Content-type: '. $mime);
   echo $cos;
   exit();
   }


Header( "Content-type: $mime");   

$src = imagecreatefromstring($cos);
$width = imagesx($src);
$height = imagesy($src);
$aspect_ratio = $height/$width;

if ( isset($_GET['width']) ) {                 // Generar miniatura per amplada
   
   $amplada_nova = $_GET["width"];
    
    if ($width <= $amplada_nova) {
      $new_w = $width;
      $new_h = $height;
    } else {
      $new_w = $amplada_nova;
      $new_h = abs($new_w * $aspect_ratio);
    }

} else {                                    // Genera miniatura per altura  

   $altura_nova = $_GET["height"];
    
    if ($height <= $altura_nova) {
      $new_w = $width;
      $new_h = $height;
    } else {
      $new_h = $altura_nova;
      $new_w = $width * $altura_nova / $height;
      }

   }

$img = imagecreatetruecolor($new_w,$new_h);
imagecopyresized($img,$src,0,0,0,0,$new_w,$new_h,$width,$height);

// determine image type and send it to the client   
if ($mime == "image/pjpeg" || $mime == "image/jpeg") {   
   imagejpeg($img);
} else if ($mime == "image/x-png" || $mime == "image/png") {
   imagepng($img);
} else if ($mime == "image/gif") {
   imagegif($img);
}
imagedestroy($img);

exit();

?>
