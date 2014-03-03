<?php

/**
 * @file
 * @brief Desde GaleriaFactory recogemos o creamos las galerías
 */

require('Galeria.php');

class GaleriaFactory {

   static function galeria($config=FALSE, $id=FALSE) {

      if ( isset($_SESSION['galeria']) ) {

         $config  = $_SESSION['galeria']['config'];

         $id = ( isset($_SESSION['id']) && ! empty($_SESSION['id']) ) ? $_SESSION['id'] : FALSE ;

         unset ( $_SESSION['galeria'] );

         $galeria = new Galeria($config, $id);
         $galeria->load();

         return $galeria;

         }


      $_SESSION['galeria']['config'] = $config;
      $_SESSION['galeria']['id']     = $id;

      return new Galeria($config, $id);

      }

}
