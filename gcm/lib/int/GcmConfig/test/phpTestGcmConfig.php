<?php

/**
 * @file   Test en php para GcmConfig
 */

DEFINE(ARCHIVO,'/tmp/TGC.php');
DEFINE(ARCHIVO_DESC,'/tmp/TGC_es.php');
DEFINE(ARCHIVO_DESC2,'/tmp/TGC_ca.php');

/* construimos archivo para pruebas */

$contenido = "\n".'<?php ';
$contenido .= "\n".'$TGC[v1]="variable1";';
$contenido .= "\n".'$TGC[v2]="variable2";';
$contenido .= "\n".'$TGC[v3]="variable3";';
$contenido .= "\n".'$TGC[v4]="variable4";';
$contenido .= "\n".'$TGC[v5]="variable5";';
$contenido .= "\n".'$TGC[v6]="variable6";';
$contenido .= "\n".'?>';

file_put_contents(ARCHIVO, $contenido);

$contenido = "\n".'<?php ';
$contenido .= "\n".'$TGC_DESC[v1]="descripcion_variable1";';
$contenido .= "\n".'$TGC_DESC[v2]="descripcion_variable2";';
$contenido .= "\n".'$TGC_DESC[v3]="descripcion_variable3";';
$contenido .= "\n".'$TGC_DESC[v4]="descripcion_variable4";';
$contenido .= "\n".'$TGC_DESC[v5]="descripcion_variable5";';
$contenido .= "\n".'$TGC_DESC[v6]="descripcion_variable6";';
$contenido .= "\n".'?>';

file_put_contents(ARCHIVO_DESC2, $contenido);

$contenido = "\n".'<?php ';
$contenido .= "\n".'$TGC_DESC[v1]="descripcion_variable1";';
$contenido .= "\n".'$TGC_DESC[v2]="descripcion_variable2";';
$contenido .= "\n".'$TGC_DESC[v3]="descripcion_variable3";';
$contenido .= "\n".'$TGC_DESC[v4]="descripcion_variable4";';
$contenido .= "\n".'$TGC_DESC[v5]="descripcion_variable5";';
$contenido .= "\n".'$TGC_DESC[v6]="descripcion_variable6";';
$contenido .= "\n".'?>';

file_put_contents(ARCHIVO_DESC, $contenido);

require('GcmConfig.php');

$config = new GcmConfig(ARCHIVO);

$config->set('v1','NUEVO VALOR PARA v1');
$config->setDescripcion('v1','NUEVO VALOR PARA DESCRIPCION v1');
$config->del('v4');

echo 'Valor de v1: '.$config->get('v1');

/* Añadimos un array */

$v2 = array( 'valor1', 'valor2');
$config->set('v2', $v2);

/* Añadimos un nuevo valor */

$config->set('v7', 'Nueva variable');


echo $config->get('v2');

echo "\nVariables:" ; print_r($config->variables()) ; echo ""; // DEV  
echo "\nDescripciones es:" ; print_r($config->descripciones()) ; echo ""; // DEV  
echo "\nDescripciones ca:" ; print_r($config->descripciones('ca')) ; echo ""; // DEV  


// echo $config->debug();

?>
