/**
 * Administración de imágenes
 */

//window.onload(pedirDatos('?m=imagenes&a=ajaxImg&s=<?=$d ?>','editarImagenesAdmin'));
addLoadEvent(function(){
   pedirDatos('?m=imagenes&a=ajaxImg&s=<?=Router::$d ?>','editarImagenesAdmin')
   });
