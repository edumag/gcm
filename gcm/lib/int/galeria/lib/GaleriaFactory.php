<?php

/**
 * @file
 * @brief Desde GaleriaFactory recogemos o creamos las galerías
 */

if ( ! defined("GCM_DEBUG") ) define("GCM_DEBUG",FALSE);

if ( ! defined("GCM_DIR") ) {
   define('GCM_DIR','../../../../');
}

require('Galeria.php');

class GaleriaFactory {

   static function galeria($config=FALSE, $id=FALSE) {

      // Si tenemos identificador la galeria deja de ser temporal
      // Ya no necesita guardarse en sesión
      //  if ( $id ) {

      //     if ( isset($_SESSION['galeria']) ) unset($_SESSION['galeria']);
      //     $galeria = new Galeria($config, $id);
      //     return $galeria;
      //     
      //     };

      if ( isset($_SESSION['galeria']) ) {

         $config  = $_SESSION['galeria']['config'];

         $id = ( isset($_SESSION['galeria']['id']) && ! empty($_SESSION['galeria']['id']) ) ? $_SESSION['galeria']['id'] : FALSE ;

         self::clean();

         $galeria = new Galeria($config, $id);
         $galeria->load();

         return $galeria;

         }

      $galeria = new Galeria($config, $id);

      $_SESSION['galeria']['config'] = $config;
      $_SESSION['galeria']['id']     = $id;

      return $galeria;

      }

   static function inicia($config=FALSE, $id=FALSE) {
      self::clean();
      return self::galeria($config, $id);
      }

   static function clean() {
      unset ( $_SESSION['galeria'] );
      }
}
