<?php

/**
 * @file Formato.php
 * @brief Generador de código segun formato html, csv, json, etc...
 * @ingroup formato
 */

/**
 * Clase Formato para mostrar contenido en diferentes formatos
 * @ingroup formato
 */

class Formato {

   static $formato;

   function __construct($formato='html') {
      self::$formato = $formato;
      }

   /**
    * Al recibir una llamada a un método que no existe, redirigimos la llamada
    * a las clases del formato seleccionado
    *
    * @param $nombre_metodo Nombre del método
    * @param $argumentos Argumentos
    */

   function __call($nombre_metodo, $argumentos) {

      return $formato::$nombre_metodo($argumentos);

      }

   }

?>
