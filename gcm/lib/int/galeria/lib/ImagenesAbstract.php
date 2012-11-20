<?php

/**
 * @file      Imagenes.php
 * @brief     Tratamiento de imágenes
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 *   Created  16/02/11
 *  Revision  SVN $Id: $
 * Copyright  Copyright (c) 2011, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

/**
 * Tratamiento de imágenes
 */

abstract class ImagenesAbstract {

   private $tipomime;    ///< Formato de imagen
   private $altura;      ///< Altura de la imagen
   private $anchura;     ///< Anchura de la imagen

   protected $nombre_imagen;  ///< Nombre de la imagen

   abstract function borrar($nombre_imagen);
   abstract function guardar($nombre_imagen);
   abstract function getUrl();
   abstract function getAltura();
   abstract function getAnchura();
   abstract function getTipomime();

   function __construct() {

      }

   protected static function listado($conf_imatges, $id_tabla) {

      echo __CLASS__.'::'.__METHOD__."\n".'Debe implementarse en clase hija';

      }

   /**
    * Devolver nombre de imagen
    */

   function getNombre() {

      return $this->nombre_imagen;

      }

   }
?>
