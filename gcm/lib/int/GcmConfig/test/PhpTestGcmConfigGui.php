<?php

/**
 * @file Test phpUnit para GcmConfigGui
 */

require(dirname(__FILE__).'/../lib/GcmConfigGui.php');

if ( $_POST['accion'] == 'escribir_gcmconfig' ) {

   $post_config = new GcmConfigGui($_POST['archivo']);
   $post_config->idioma = $_POST['idioma'];

   if ( $post_config->escribir_desde_post() ) {
      echo '<h1>Archivo escrito</h1>';

      /* Destruimos objeto para que escriba en archivo */

      $post_config = NULL;

   } else {
      echo 'Error';
      }
   }

DEFINE(ARCHIVO,'/tmp/TGC.php');
DEFINE(ARCHIVO_DESC,'/tmp/TGC_es.php');
DEFINE(ARCHIVO_DESC_ca,'/tmp/TGC_ca.php');

if ( ! file_exists(ARCHIVO) ) {

   /* construimos archivo para pruebas */

   $contenido = "\n".'<?php ';
   $contenido .= "\n".'$TGC[v1]="variable1";';
   $contenido .= "\n".'$TGC[v2]="variable2";';
   $contenido .= "\n".'$TGC[v3]="variable3";';
   $contenido .= "\n".'$TGC[v4]="variable4";';
   $contenido .= "\n".'$TGC[v5][]="variable5a";';
   $contenido .= "\n".'$TGC[v5][]="variable5b";';
   $contenido .= "\n".'$TGC[v5][]="variable5c";';
   $contenido .= "\n".'$TGC[v6]="variable6";';
   $contenido .= "\n".'?>';

   file_put_contents(ARCHIVO, $contenido);

   }

if ( ! file_exists(ARCHIVO_DESC) ) {

   $contenido = "\n".'<?php ';
   $contenido .= "\n".'$TGC_DESC[v1]="descripcion_variable1";';
   $contenido .= "\n".'$TGC_DESC[v2]="descripcion_variable2";';
   $contenido .= "\n".'$TGC_DESC[v3]="descripcion_variable3";';
   $contenido .= "\n".'$TGC_DESC[v4]="descripcion_variable4";';
   $contenido .= "\n".'$TGC_DESC[v5]="descripcion_variable5";';
   $contenido .= "\n".'$TGC_DESC[v6]="descripcion_variable6";';
   $contenido .= "\n".'?>';

   file_put_contents(ARCHIVO_DESC, $contenido);

   }

if ( ! file_exists(ARCHIVO_DESC_ca) ) {

   $contenido = "\n".'<?php ';
   $contenido .= "\n".'$TGC_DESC[v1]="descripcion_variable1_ca";';
   $contenido .= "\n".'$TGC_DESC[v2]="descripcion_variable2_ca";';
   $contenido .= "\n".'$TGC_DESC[v3]="descripcion_variable3_ca";';
   $contenido .= "\n".'?>';

   file_put_contents(ARCHIVO_DESC_ca, $contenido);

   }

$config = new GcmConfigGui(ARCHIVO);
$config_ca = new GcmConfigGui(ARCHIVO);

echo '<h2>Presentamos variables</h2>';

echo "<pre>" ; print_r($config->variables()) ; echo "</pre>"; 

/** Comprobando valor de la primera variable */

echo '<h2>Formulario en idioma predeterminado Solo se puede modificar valores</h2>';
echo $config->formulario();

echo '<h2>Formulario en catalan ampliable</h2>';
$config_ca->idioma = 'ca';
$opciones = array('ampliar' => TRUE, 'eliminar' => TRUE, 'modificar_descripciones' => TRUE );
echo $config_ca->formulario($opciones);

echo '<h2>POST</h2>';
echo "<pre>" ; print_r($_POST) ; echo "</pre>"; // DEV  

?>
