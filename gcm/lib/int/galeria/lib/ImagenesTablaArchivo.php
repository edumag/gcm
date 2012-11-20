<?php

/**
 * @file      ImagenesPdo.php
 * @brief     Tratamiento de imágenes sobre Pdo
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

require_once(dirname(__FILE__).'/ImagenesAbstract.php');

/**
 * Tratamiento de imágenes
 *
 * - La imagen es guardada en una carpeta que debe ser definida.
 * - A la vez se guarda el nombre de la misma a la base de datos por 
 *   defecto en la tabla 'galerias' con los campos siguientes; 
 *    + id            Identificador
 *    + nombre        Nombre de la imagen coincide con el fichero
 *    + descripcion   Descripción (Opcional)
 *    + tabla         Tabla a la que pertenece, ejemplo productos
 *    + id_tabla      Identificador del elemento en la tabla en la pertenece
 *
 * Con este sistema tenemos los beneficios de no guardar las imágenes dentro 
 * de la base de datos pero si podemos saber en una coonsulta sql cual de los
 * elementos tienen imágenes, por ejemplo.
 *
 */

class ImagenesTablaArchivo extends ImagenesAbstract {

   public $tabla = 'galeria';                    ///< Tabla donde se guarda registro de galería
   public $campo_id = 'id';                      ///< Identificador dentro de galeria

   private $errores = array();                   ///< Lista de errores encontrados
   private $pdo;                                 ///< Instancia de PDO

   private $tabla_relacionada = '';              ///< Nombre de la tabla que contiene el registro a relacionar con la imagen
   private $id_tabla;                            ///< Identificador de registro dentro de su tabla
   private $dir_tmp;                             ///< Directorio temporal para las imágenes
   private $dir_imagenes;                        ///< Directorio definitivo para las imágenes


   /**
    * Constructor
    *
    * @param $configuracio Datos de configurción
    * @param $id_tabla     Identificador de registro en su tabla
    * @param $imagen       Nombre de imagen si la tenenmos
    */

   function __construct($configuracio, $id_tabla, $nombre_imagen=FALSE) {

      $this->id_tabla = $id_tabla;

      $this->pdo               = $configuracio['pdo'];
      $this->tabla_relacionada = $configuracio['tabla'];
      $this->dir_imagenes      = $configuracio['dir_imagenes'];
      $this->dir_tmp           = $configuracio['dir_tmp'];

      $this->nombre_imagen = $nombre_imagen;

      }

   /**
    * Borramos imagen de la tabla galeria
    * Borramos fichero imagen
    */

   function borrar($nombre_imagen) {

      $nombre_imagen = basename($nombre_imagen);

      $sql = "DELETE FROM ".$this->tabla." 
         WHERE tabla='".$this->tabla_relacionada."' 
         AND id_tabla=".$this->id_tabla." 
         AND imagen='".$nombre_imagen."'";

      $objStatement = $this->pdo->prepare($sql);

      if ( ! $objStatement->execute() ) {
         $error = $objStatement->errorInfo();
         trigger_error($error,E_USER_ERROR);
         return FALSE;
         }
      
      if ( file_exists($this->dir_imagenes.$nombre_imagen) ) unlink($this->dir_imagenes.$nombre_imagen);

      }

   /**
    * Guardar imagen
    *
    * Añadimos registro en galeria
    * copiamos archivo de directorio tempoal a directorio definitivo
    */

   function guardar($url_imagen) {

      $nombre_imagen = basename($url_imagen);

      $sql = "INSERT INTO ".$this->tabla." (tabla, id_tabla, imagen) VALUES (:tabla, :id_tabla, :imagen)";
      $objStatement = $this->pdo->prepare($sql);

      $objStatement->bindValue(':tabla', $this->tabla_relacionada, PDO::PARAM_STR);
      $objStatement->bindValue(':id_tabla', $this->id_tabla, PDO::PARAM_INT);
      $objStatement->bindValue(':imagen', $nombre_imagen, PDO::PARAM_STR);

      if ( ! $objStatement->execute() ) {
         $error = $objStatement->errorInfo();
         trigger_error($error,E_USER_ERROR);
         return FALSE;
         }

      rename($this->dir_tmp.$nombre_imagen, $this->dir_imagenes.$nombre_imagen);


      }

   /**
    * Devolver url de imagen
    */

   function getUrl() {

      return $this->dir_imagenes.$this->nombre_imagen;

      }

   /**
    * Devolver altura
    */

   function getAltura() {

      echo __METHOD__.' pendiente';

      }

   /**
    * Devolver Anchra
    */

   function getAnchura() {

      echo __METHOD__.' pendiente';

      }

   /**
    * Devolver tipomime
    */

   function getTipomime() {

      echo __METHOD__.' pendiente';

      }

   /**
    * Devolver listado con los identificadores de imágenes
    * de la galeria actual.
    */

   static function listado($conf_imatges, $id_tabla) {

      $imatges = array();

      $sql = "SELECT imagen FROM galeria
         WHERE tabla='".$conf_imatges['tabla']."' AND id_tabla=".$id_tabla;

      try {

         $resultado=$conf_imatges['pdo']->prepare($sql);
         $resultado->execute();

         } catch (Exception $ex) {
            trigger_error("Error generan llista d'imtages\n\nSQL: ".$sql."\n".$ex->getMessage(), E_USER_ERROR);
            return FALSE;
            }

      $arAll = $resultado->fetchAll(PDO::FETCH_ASSOC);

      foreach ( $arAll as $arRow ) {
         $imatges[] = $arRow['imagen'];
         }

      return $imatges;

      }

   }

?>
