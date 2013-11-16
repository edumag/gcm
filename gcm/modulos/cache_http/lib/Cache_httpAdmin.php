<?php

/**
 * @file Cache_httpAdmin.php
 * @brief Administración de módulo para generar cache de páginas
 * @ingroup modulo_cache_http
 */

require_once dirname(__FILE__).'/Cache_http.php';

/**
 * @class Cache_httpAdmin
 * @brief Admin de Cache http
 */

class Cache_httpAdmin extends Cache_http {

   /**
    * Borramos cache al pasar el cron
    */

   function cron($e, $args=FALSE) {

      registrar(__FILE__,__LINE__,"Borramos cache desde cron",'ADMIN');
      
      $this->borrar('cron','todo');

      global $gcm;


      }

   }

?>
