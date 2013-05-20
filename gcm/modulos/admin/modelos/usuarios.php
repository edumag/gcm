<?php

/**
 * @file      usuarios.php
 * @brief     Modelo para usuarios
 */

require_once(GCM_DIR.'lib/int/databoundobject/lib/Crud.php');

/**
 * @defgroup usuarios Usuarios
 * @{
 */

/**
 * @class Usuarios
 * @brief Modelo para los usuarios de la aplicación.
 * @version 0.1
 */

class Usuarios extends Crud {

   function DefineTableName() {

      global $gcm;

      return $gcm->au->sufijo.'usuarios';
      }

   function __construct(PDO $objPDO, $id=NULL) {

      global $gcm;

      $this->sql_listado = 'SELECT u.id, u.usuario, u.nombre,u.apellidos, fecha_modificacion as modificación FROM '.$gcm->au->sufijo.'usuarios u';

      parent::__construct($objPDO, $id);

      }

   }

/** @} */

?>
