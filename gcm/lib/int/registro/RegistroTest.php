<?php

require_once 'PHPUnit/Framework.php';

require_once('Registro.php');

class RegistroTest extends PHPUnit_Framework_TestCase{

   public $registro = '';

   function __construct() {

      $this->registro = new Registro('/tmp/test.bd');

      }

    function testCreandoNuevoRegistro() {

       $this->registro->registra(__FILE__,__LINE__,'Test sobre Registro');

       $this->assertTrue(file_exists('/tmp/test.bd'), 'Archivo de registro creado');

       }

   /**
    * Comprobando la entrada de un array
    */

   function testInsertandoArray() {

      $array = array('Test de entrada de array');
      $array[1]['Listado subdirectorio'] = glob("../*");
      $array[2]['Listado directorio temporal'] = glob("/tmp/*");
      $this->assertNotNull($this->registro->registra(__FILE__,__LINE__,$array), 'Ingresar un array');

       }

   /** Comprobar registro con tipo erroneo */

   public function testException() {

      try {
         @$this->registro->registra(__FILE__,__LINE__,'Test sobre Registro','SIN_TIPO');

      } catch (Exception $expected) {

         return;
         }

      $this->fail('Se esperaba un error que no se produjo');
      }

   /** Registro nulo */

   function testRegistroNulo() {

      try {
         @$this->registro->registra();

      } catch (Exception $expected) {

         return;
         }

      $this->fail('Registro nulo sin error');

      }

   /** Registro grande */

   function testRegistroGrande() {

       $this->assertNotNull($this->registro->registra(__FILE__,__LINE__,str_repeat("Test de Registro grande ",80)), 'Ingresar un registro grande');

      }

   /** Comprobar retorno de registros de sesión */

   public function testSessionActual() {

      $this->assertNotNull($this->registro->ver_registros(), 'Registros de la sesión actual');
      $this->assertNotNull($this->registro->ver_registros(array('DEBUG','ADMIN')), 'Registros de la sesión actual');
      $this->assertFalse($this->registro->ver_registros(array('AVISO')), 'No debe heber registros de AVISO');

      }

   /** Validar tipos de registro */

   function testValidar_tipo() {

      $this->assertTrue($this->registro->validar_tipo('DEBUG'), 'Validar tipo registros');

      try {
         $this->registro->validar_tipo('TIPO_NO_VALIDO');
      } catch (Exception $e) {
         return;
         }

      $this->fail('Validador de tipo de registro dio uno malo por bueno');

      }

   /** Cambiamos nivel de registro */

   function testNivel() {

      $this->assertTrue($this->registro->nivel('ADMIN'), 'Cambiamos nivel');

      }

   }
?>
