<?php

/**
 * @file   Test phpUnit para RegistroFactory
 */

require_once 'PHPUnit/Framework.php';

require(dirname(__FILE__).'/RegistroFactory.php');

class RegistroFactoryTest extends PHPUnit_Framework_TestCase{

   public $registroFactory;
   public $archivo;

   function __construct() {

      $this->archivo = '/tmp/TestRegistroFactory.db';
      $this->registroFactory = RegistroFactory::getRegistro($this->archivo);

      }

   /** Comprobando valor de la primera variable */

   function testAnyadirRegistros() {

      $this->registroFactory->registra(__FILE__,__LINE__,'Test sobre Registro');
      $this->assertTrue(file_exists($this->archivo), 'Archivo de registro creado');

      /** Cambiamos base de datos */

      $this->archivo = '/tmp/TestRegistroFactory2.db';
      $this->registroFactory = RegistroFactory::getRegistro($this->archivo);

      $this->registroFactory->registra(__FILE__,__LINE__,'Test sobre Registro2');
      $this->assertTrue(file_exists($this->archivo), 'Archivo de registro creado');

      }


   }
?>
