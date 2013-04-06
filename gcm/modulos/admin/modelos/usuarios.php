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

   function DefineTableName() {

      global $gcm;

      return $gcm->au->sufijo.'r_usuarios_roles';
      }

   function DefineRelationMap($pdo) {

      $retorno['usuarios_id'] = 'ID';
      $retorno['roles_id'] = 'Roles_id';

      return $retorno;

      }

   // function visualizar_registro() {

   //    // echo 'Hola: '.$this->ID;
   //    echo "Rol: ".$this->GetAccessor('Nombre');

   //    }

   }

/** @} */

?>
