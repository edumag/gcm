<?php
if ( !isset($_SESSION['edit']) || $_SESSION['edit'] != 'si' ) {
   $eventos['postcontenido']['procesar_texto'][1] = '';
   }
$eventos['precarga']['presenta_contenido'][1] = '';
$eventos['postcontenido']['lista_descargables'][1] = '';
?>
