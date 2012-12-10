<?php

/** Presentar galeria
 *
 * Presentamos la galería de fotos con apoyo del módulo gcmImg
 */

if ( $a == 'verImg' ) { ///< presentar imagenes
   // Presentar imagen
   ob_end_clean();
   $img = $_GET['img'];
   // presentamos cabeceras
   include(GCM_DIR."includes/cabecera.php");
   presentarTitulo();

   // Añadimos lista javascript con las imagenes
   //echo '<script src="'.$dd.$s.'.listaImg.js'.'" type="text/javascript" language="JavaScript">';
   //echo "\n</script>";
   include_once(GCM_DIR.'lib/int/gcm_imagen.php');
   echo "\n<script language='javascript'>";
   gcm_listaImagenes($dd.$s, 4);
   echo "\n</script>";

   // Añadimos gcmImgs.js para la presentación de imagenes
   echo '<script src="'.GCM_DIR_P.'modulos/gcmImgs/gcmImgs.js'.'" type="text/javascript" language="JavaScript">';
   echo "\n</script>";

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
   echo '</body></html>';
   exit();

}

?>
