<?php

/**
 * @file roles.php
 * @brief Clase Roles
 *
 * @package Modulos
 */

/**
 * @class Roles
 * @brief Si se activan los roles se comprobara que el usuario tiene
 *        permisos para ejecutar las acciones administrativas.
 */

class Roles extends Modulos {

   static $dir_roles = 'DATOS/configuracion/roles/roles/';

   /** 
    * Roles del usuario
    *
    * Estos roles se encuentran en modulos/roles/config/usuarios.php
    * y los definidos por el propio proyecto en: Datos/configuracion/roles/usuarios.php
    *
    * formato del array:
    * @code
    * $usuarios[usuario][] = rol;
    * @endcode
    */ 

   static $roles = array();

   /**
    * Acciones, recogemos las acciones de los módulos que se le permiten al usuario
    * por los roles que tiene.
    *
    * Estos roles se encuentran en modulos/roles/config/roles/
    * y los definidos por el propio proyecto en: Datos/configuracion/roles/roles/
    *
    * formato del array:
    * @code
    * $rol[módulo][] = acción;
    * @endcode
    */

   static $acciones = array();

   /** Constructor */

   function __construct() {

      parent::__construct();

      // Recogemos configuración de acciones y permisos
      $this->leer_permisos_acciones();

      }

   static function comprobar_permisos($m, $a) {

      global $gcm;

      registrar(__FILE__,__LINE__,"Comrpobar permisos m:$m a:$a dir:".self::$dir_roles);
      
      self::leer_permisos_acciones();

      if ( ! self::$roles ) {
         registrar(__FILE__,__LINE__,'Sin rol definido','DEBUG');
         return FALSE;
         }

      if ( isset(self::$acciones[$m]) && in_array($a, self::$acciones[$m]) ) return TRUE;

      return FALSE;

      }

   /**
    * @defgroup roles Roles
    *
    * Gestión de roles de usuarios.
    *
    *
    *
    * @ingroup usuarios
    * @{
    */

   /**
    * Recogemos $acciones de archivo de configuración
    *
    * y rellenamos roles de usuario en sesión
    *
    */

   static function leer_permisos_acciones(){

      global $gcm;

      if ( ! empty(self::$acciones) && ! empty(self::$roles) ) return;

      $usuario = $_SESSION[$gcm->sufijo.'usuario'];

      // Roles de usuario

      if ( empty(self::$roles) ) {
         if ( file_exists('DATOS/configuracion/roles/usuarios.php') ) {
            include('DATOS/configuracion/roles/usuarios.php');
         } elseif (file_exists(dirname(__FILE__).'/../config/usuarios.php')) {
            include(dirname(__FILE__).'/../config/usuarios.php');
         } else {
            registrar(__FILE__,__LINE__,"No se encontro archivo de usuarios",'ERROR');
            return FALSE;
            }

         if ( ! isset($usuarios) ) {
            registrar(__FILE__,__LINE__,"No hay usuarios configurados",'ERROR');
            return FALSE;
            }

         if ( isset($usuarios[$usuario]) ) {
            self::$roles = $usuarios[$usuario];
         } else {
            registrar(__FILE__,__LINE__,"Usuario [$usuario] sin roles definidos");
            return FALSE;
            }
         }

      // Guardar las acciones permitidas del usuario.

      if ( empty(self::$acciones) ) {
         foreach ( self::$roles as $rol ) {
            if ( file_exists(self::$dir_roles.$rol.'.php') ) {
               include(self::$dir_roles.$rol.'.php');
               self::$acciones = array_merge(self::$acciones,${$rol});
               }
            }
         }

      // echo "<pre>Roles: " ; print_r(self::$roles) ; echo "</pre>"; // DEV  
      // echo "<pre>Acciones: " ; print_r(self::$acciones) ; echo "</pre>"; // DEV  
      }

   /** @} */

   }

?>
