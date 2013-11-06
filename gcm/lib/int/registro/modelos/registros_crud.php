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

   function DefineTableName() {

      $sufijo = ( isset($GLOBALS['sufijo_para_modelo']) ) 
         ? $GLOBALS['sufijo_para_modelo']
         : '';

      return $sufijo.'registros';
      }

   }


?>
