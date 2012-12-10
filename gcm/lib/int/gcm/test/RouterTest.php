<?php

/**
 * @file   Test phpUnit para Router.php
 *
 */

echo "No funciona, crea directorio de log ara registros";
exit

define("GCM_DIR",dirname(__FILE__).'/../../../../../gcm/');

require_once 'PHPUnit/Framework.php';

//require(dirname(__FILE__).'/../lib/Router.php');
require(dirname(__FILE__).'/../lib/Gcm.php');

$gcm = new Gcm();

/**
 * @test Comprobando Router
 */

class RouterTest extends PHPUnit_Framework_TestCase{

   protected function setUp() {
      global $gcm;
      }

   /**
    * @test Comprobando valor de la primera variable
    */

   function testdesglosarUrl() {

      global $gcm;

      $url = '/index.html?e=buscar&args=palabra_a_buscar';

      Router::desglosarUrl($url);

      $this->assertEquals(Router::$url,'index.html');

      }


   }

?>

