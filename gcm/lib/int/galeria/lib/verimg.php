<?php

/**
 * @file
 * @brief Presentar imagen de la galería de una carpeta.
 *
 * @todo Permitir definir tamaño de salida.
 */

session_start();

define('GCM_DIR','../../../../');

require_once dirname(__FILE__)."/../../gcm/lib/helpers.php";
require_once dirname(__FILE__)."/../../gcm/lib/GUtil.php";

$dir_img = ( isset($_GET['dir_img']) && ! empty($_GET['dir_img']) ) ? $_GET['dir_img'] : FALSE ;

$imagen = dirname(__FILE__).'/../img/noimage.png';
$imgs_dir = glob($dir_img.'/*');
foreach ( $imgs_dir as $img ) {
  if ( esImagen($img) ) {
    $imagen = $img;
    break ;
  }
}

ob_start();
$archivo_contenido = file_get_contents($imagen);
$mime_type = GUtil::tipo_de_archivo($imagen);
header('Content-type: '. $mime_type);
header ("Expires: ".gmdate("D, d M Y H:i:s",time()+86400).' GMT'); // 86400 segundos, 24 horas 24x60x60
ob_clean();
echo $archivo_contenido;
exit();
