<?php

/**
 * @file registros.php
 * @brief Modelo para registros
 */

require_once(GCM_DIR.'lib/int/databoundobject/lib/Crud.php');

/**
 * Modelo para registros con CRUD
 */

class Registros_crud extends Crud {

   function __construct(PDO $objPdo, $id = NULL) {

      $sufijo = ( isset($GLOBALS['sufijo_para_modelo']) ) 
         ? $GLOBALS['sufijo_para_modelo']
         : '';

      $this->sql_listado = 'SELECT id
         ,DATE_FORMAT(FROM_UNIXTIME(fecha),"%d/%m/%y %T") as fecha
         ,tipo
         ,mensaje
         ,CONCAT("sesion: ",sesion," ",fichero,":",linea,"\n\n",descripcion) as descripcion  
         FROM '.$sufijo.'registros
      ';

      parent::__construct($objPdo, $id);

      }

   function DefineTableName() {

      $sufijo = ( isset($GLOBALS['sufijo_para_modelo']) ) 
         ? $GLOBALS['sufijo_para_modelo']
         : '';

      return $sufijo.'registros';
      }

   }


?>
