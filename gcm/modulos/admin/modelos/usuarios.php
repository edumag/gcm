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
 * Roles
 */

class Roles extends Crud {

   function listado_para_select() {
      return $this->find(NULL,array('id', 'nombre'),'id');
   }


   function DefineTableName() {

      global $gcm;

      return $gcm->au->sufijo.'roles';
      }

}

/**
 * Relacion de usuarios con roles de usuario
 */

class R_usuarios_roles extends Crud {

   function __construct(PDO $objPDO, $id=NULL) {

      global $gcm;

      $this->tipo_tabla = 'combinatoria';

      $this->sql_listado = "
         SELECT CONCAT(rur.usuarios_id,',',rur.roles_id) as id, u.nombre AS usuario, r.nombre AS rol
         FROM ".$gcm->sufijo."r_usuarios_roles rur
         LEFT JOIN ".$gcm->sufijo."usuarios u ON u.id=rur.usuarios_id
         LEFT JOIN ".$gcm->sufijo."roles r    ON r.id=rur.roles_id";

       $this->opciones_array2table = array(
          'presentacion' => 'Array2table',
          'op' => array (
             'ocultar_id'=>TRUE
             ,'url'=>Router::$base.'admin/roles_usuario?gmas_r_usuarios_roles_id='
             , 'eliminar'=>'eliminar'
             , 'enlaces'=> array(
                'url' => array(
                   'campo_enlazado'=>'nombre'
                   ,'titulo_columna'=>'Usuario'
                   ,'base_url'=>Router::$base.'/admin/roles_usuario/'
                   )
                )
             )
          );

         parent::__construct($objPDO, $id);

         }
      
   function DefineTableName() {

      global $gcm;

      return $gcm->au->sufijo.'r_usuarios_roles';
      }

   function DefineRelationMap($pdo) {

      $retorno['usuarios_id,roles_id'] = 'ID';

      return $retorno;

      }

   // function visualizar_registro() {

   //    // echo 'Hola: '.$this->ID;
   //    echo "Rol: ".$this->GetAccessor('Nombre');

   //    }

   }

/** @} */

?>
