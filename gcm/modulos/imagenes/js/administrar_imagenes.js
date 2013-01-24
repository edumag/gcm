/**
 * Administración de imágenes
 */

addLoadEvent(function(){
   pedirDatos('?m=imagenes&a=ajaxImg&s=<?php echo Router::$d.Router::$s ?>','galeria')
   });
