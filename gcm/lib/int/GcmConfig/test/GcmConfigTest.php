<?php

/**
 * @file Test phpUnit para GcmConfig
 */

DEFINE(ARCHIVO,'/tmp/TGC.php');
DEFINE(ARCHIVO_DESC,'/tmp/TGC_es.php');

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

file_put_contents(ARCHIVO_DESC, $contenido);

require_once 'PHPUnit/Framework.php';

require(dirname(__FILE__).'/../lib/GcmConfig.php');

class GcmConfigTest extends PHPUnit_Framework_TestCase{

   public $config;
   public $archivo;
   public $archivo_descripcion;

   function __construct() {

      $this->archivo = ARCHIVO;
      $this->archivo_descripcion  = ARCHIVO_DESC; 

      $this->config = new GcmConfig($this->archivo);

      }

   /** Comprobando valor de la primera variable */

   function testVerValores() {

      $this->assertEquals($this->config->get('v1'), 'variable1');
      $this->assertEquals($this->config->getDescripcion('v6'), 'descripcion_variable6');

      $nuevo_valor = 'Nuevo valor para v1';
      $this->config->set('v1',$nuevo_valor);
      $this->assertEquals($this->config->get('v1'), $nuevo_valor);

      $nuevo_valor = 'Nuevo valor para v1';
      $this->config->setDescripcion('v1',$nuevo_valor);
      $this->assertEquals($this->config->getDescripcion('v1'), $nuevo_valor);

      $this->assertTrue($this->config->del('v4'));

      $this->assertTrue($this->config->delDescripcion('v4'));

      $this->config->setDescripcion('v1','MODIFICADA');

      }


   }
?>
