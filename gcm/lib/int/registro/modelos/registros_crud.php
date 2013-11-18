<?php

/**
 * @file registros.php
 * @brief Modelo para registros
 * @ingroup registro
 */

require_once(GCM_DIR.'lib/int/databoundobject/lib/Crud.php');

/**
 * Modelo para registros con CRUD
 * @ingroup registro
 */

class Registros_crud extends Crud {

   function __construct(PDO $objPdo, $id = NULL) {

      $sufijo = ( isset($GLOBALS['sufijo_para_modelo']) ) 
         ? $GLOBALS['sufijo_para_modelo']
         : '';

      if ( $objPdo->getAttribute(constant("PDO::ATTR_DRIVER_NAME")) == 'sqlite' ) {

         $this->sql_listado = 'SELECT id
            ,strftime("%d/%m/%Y %H:%M:%S", datetime(fecha,"unixepoch")) as fecha
            ,tipo
            ,mensaje
            , "sesion: " || sesion || " " || fichero || ":" || linea || "\n\n" || descripcion as descripcion  
            FROM '.$sufijo.'registros
            ';

      } else {

         $this->sql_listado = 'SELECT id
            ,DATE_FORMAT(FROM_UNIXTIME(fecha),"%d/%m/%y %T") as fecha
            ,tipo
            ,mensaje
            ,CONCAT("sesion: ",sesion," ",fichero,":",linea,"\n\n",descripcion) as descripcion  
            FROM '.$sufijo.'registros
         ';

         }
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
