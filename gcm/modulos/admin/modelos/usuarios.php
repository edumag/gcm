<?php

/**
 * @file      usuarios.php
 * @brief     Modelo para usuarios
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 *   Created  05/05/10
 *  Revision  SVN $Id: usuarios.php 650 2012-10-04 07:17:48Z eduardo $
 * Copyright  Copyright (c) 2010, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

/** DataBoundObject */

require_once(GCM_DIR.'lib/int/databoundobject/lib/Crud.php');

/**
 * @class Usuarios.php
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

      $this->evento_guardar = 'rol_minimo';

      parent::__construct($objPDO, $id);

      }

   /**
    * Añadimos el rol de 'usuario' al insertar un nuevo usuario
    *
    * @param $id Identificador de nuevo usuario
    */

   function rol_minimo($id) {

      global $gcm;

      $gcm->au->insertar_rol_usuario($id,2);

      registrar(__FILE__,__LINE__,'Añadimos rol "usuario"','AVISO');

      }

   }

/**
 * Roles de usuario
 */

class Roles extends Crud {

   }

?>
