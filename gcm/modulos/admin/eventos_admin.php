<?php

$eventos['menuadmin']['presentar_menu_administrativo'][1]='';
$eventos['infoserver']['infoserver'][1]='';
$eventos['test']['ejecutar_tests_modulos'][1]='';
$eventos['precarga']['precarga'][1]='';

/* Si no se ha confirmado el cambio del administrador por defecto */

if ( ! $gcm->config('admin','configuracion_confirmada') ) {

   $eventos['precontenido']['confirmar_configuracion'][1]='';

}
?>
