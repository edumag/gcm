<?php

/**
 * @file Autentificacion.php
 * @brief Sistema de autentificación de usuarios.
 * @ingroup autentificacion
 *
 * Sistema basado en roles de usuario.
 * 
 * @author    Eduardo Magrané 
 *
 * @internal
 * Copyright  Copyright (c) 2009, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

/**
 * @defgroup autentificacion Autentificación
 * @ingroup autentificacion
 * @ingroup usuarios
 * @{
 */

/** @brief Componente Autentificacion
 *
 * Mecanismo para la autentificación de usuarios.
 *
 */

class Autentificacion {

   /** Conexión con base de datos  */

   private $pdo;

   /** Sufijo para diferenciar entre proyectos */

   public $sufijo;

   /** constructor
    *
    * Llenamos el array eventos con la información de los archivos
    * de eventos de cada modulo
    *
    * @param $pdo      Conexión con base de datos
    * @param $sufijo   Para diferenciar entre sesiones de diferentes proyectos
    * @param $acciones Por defecto administrar para administrador
    *
    */

   function __construct(PDO $pdo, $sufijo='au') {

      $this->pdo = $pdo;
      $this->sufijo = $sufijo;

      /* Comprobar existencia de tabla de usuarios */

      if ( ! $this->existe_tabla($this->sufijo.'usuarios')  ) $this->crear_tabla();

      /* Comprobar existencia de al menos un usuarios administratico */

      $sql = 'SELECT COUNT(*) FROM '.$this->sufijo.'usuarios u WHERE u.admin=1';

      if ( ! $sth = $this->pdo->prepare($sql) ) return;

      if (!$sth->execute()) return;

      $arRow = $sth->fetch();

      foreach($arRow as $key => $value) {
         $num_admnistradores = $value;
         }

      if ( $num_admnistradores < 1  ) 
         $this->crear_admin_defecto();

      }

   /**
    * Existe tabla
    */

   function existe_tabla($tabla) {

      try {

         $sql = 'SELECT COUNT(*) FROM '.$tabla;

         if ( ! $sth = $this->pdo->prepare($sql) ) {

            registrar(__FILE__,__LINE__, 'No existe tabla para usuarios la creamos','AVISO');
            $this->crear_tabla();

         } else {

            if (!$sth->execute()) {

               $this->crear_tabla();

               }

            }
      } catch (Exception $ex) {

         return FALSE;

         }

      return TRUE;

      }

   /**
    * crear_tabla
    *
    * En caso de que no exista aun la tabla de la base de datos hay que crearla.
    *
    */

   function crear_tabla() {

      global $gcm;

      if ( $this->pdo->getAttribute(constant("PDO::ATTR_DRIVER_NAME")) == 'sqlite' ) {
         $autoincrement = '';
      } else {
         $autoincrement = 'AUTO_INCREMENT';
         }

      $SQL="CREATE TABLE ".$this->sufijo."usuarios (
         id INT  PRIMARY KEY $autoincrement ,
         usuario CHAR(50) , 
         pass_md5 CHAR(32) ,
         nombre CHAR(50) , 
         apellidos CHAR(50) , 
         fecha_creacion datetime  NOT NULL,
         fecha_modificacion timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ,
         mail CHAR(60) ,
         telefono CHAR(15),
         admin tinyint(1) NOT NULL DEFAULT '0'
         )";

      if ( ! $sqlResult = $this->pdo->query($SQL) ) {

         throw new Exception("Error al crear tabla de ".$this->sufijo."usuarios\n".$pdo_error);
         return FALSE;

         }

      $this->crear_admin_defecto();

      }

   /**
    * entrar
    *
    * Se pide autentificación para usuario, hay que comprobar que este registrado.
    *
    * @param $usuario  Nombre de usuario
    * @param $pass     Contraseña de usuario
    */

   function entrar($usuario, $pass) {

     global $_SESSION;

     /**
      * Minutos que deseamos que se guarde la sesión.
      * 60 minutos * 24 horas * 30 días
      */
     $minuts_mante_sesio_recorda = ( 60 * 24 * 30 );

     $sql = "SELECT id, usuario, admin FROM ".$this->sufijo."usuarios WHERE usuario=? AND pass_md5=? LIMIT 1";
     $comando = $this->pdo->prepare($sql);
     if ( ! $comando->execute(array($usuario, md5($pass)) ) ) {
       $err = $this->pdo->errorInfo();
       $men_error = $err[2];
       throw new Exception('Error al insertar registro: '.$men_error);
     }

     $retorno = $comando->fetchAll(PDO::FETCH_NUM);

     if (!$comando) {

       $err = $this->pdo->errorInfo();
       $men_error = $err[2];
       throw new Exception('Error ejecutando query: '.$men_error. ' sql: '.$consulta);
       return FALSE;

     } elseif ( count($retorno) < 1 ) {

       registrar(__FILE__,__LINE__,'Usuario o contraseña incorrecta','AVISO');

       return FALSE;

     }

     list($id,$usuario, $admin) = $retorno[0];

     /* Cramos sessión para usuario */

     $_SESSION[$this->sufijo.'id'] = $id;
     $_SESSION[$this->sufijo.'usuario'] = $usuario;
     if ( $admin == 1 ) {
       $_SESSION[$this->sufijo.'admin'] = $admin;
     } else {
       if ( isset($_SESSION['admin']) ) unset($_SESSION['admin']);
     }

     if (array_key_exists('remember',$_POST)) {
       ini_set('session.cookie_lifetime', 60 * $minuts_mante_sesio_recorda );
       session_regenerate_id(TRUE);
     }

     return TRUE;

   }

   /**
    * logeado
    *
    * Comprobar que se esta logeado
    */

   function logeado() {

       global $_SESSION;

       return ( isset($_SESSION[$this->sufijo.'id']) ) ? $_SESSION[$this->sufijo.'id'] : FALSE ;

      }

   /**
    * es_admin
    *
    * Comprobar si tiene permisos de administración
    *
    * @return TRUE/FALSE
    */

   function es_admin() {

      if ( ! isset($_SESSION[$this->sufijo.'admin']) ) return FALSE;

      return TRUE;

      }

   /** Identificador de usuario
    *
    * Devolvemos el identificador de usuario actual
    *
    */

   function id() {
      return ( isset($_SESSION[$this->sufijo.'id']) ) ? $_SESSION[$this->sufijo.'id']: FALSE ;
      }


   /**
    * Comprobar permiso para usuario segun accion a realizar
    *
    * @param $accion Accion a realizar, por defecto 'administrar'
    * @param $modulo Módulo al que pertenece la acción, por defecto 'admin'
    *
    */

   function permiso($accion = 'administrar', $modulo = 'admin') {

      global $gcm;

      if ( GCM_DEBUG ) $tiempo_inicio = microtime(TRUE);

      $usuario_id    = $this->id();

      // Si no hay identificador de usuario salimos

      if ( ! $usuario_id ) return FALSE;

      // Si el usuario de sesión es administrador no hace falta seguir

      if ( $gcm->au->es_admin() ) return TRUE;

      // Si el módulo Roles está activado, comprobamos permisos con él

      if ( in_array('roles',$gcm->event->modulos_activados) ) {

         if ( Roles::comprobar_permisos($modulo, $accion) ) return TRUE;

         }


      return FALSE;

      }

   /**
    * salir
    *
    * Cerrar sessión.
    */

   function salir() {

      if ( isset($_SESSION[$this->sufijo.'id'])  ) unset($_SESSION[$this->sufijo.'id']);
      if ( isset($_SESSION[$this->sufijo.'admin'])  ) unset($_SESSION[$this->sufijo.'admin']);
      if ( isset($_SESSION[$this->sufijo.'usuario'])  ) unset($_SESSION[$this->sufijo.'usuario']);
      session_destroy();
      return TRUE;

      }

   /**
    * Crear administrador por defecto
    *
    * usuario:  admin
    * password: admin
    */

   function crear_admin_defecto() {

      /* Insertar un administrador por defecto */

      $sql = "INSERT INTO ".$this->sufijo."usuarios (id, usuario, pass_md5, fecha_creacion, mail, admin) VALUES ";
      $sql .= "(?,?,?,?,?,?)";
      $comando = $this->pdo->prepare($sql);
      if ( !$comando ) {
         $err = $this->pdo->errorInfo();
         $men_error = $err[2];
         throw new Exception('Error al preparar sql'.$men_error);
         }

      if ( ! $comando->execute(array(1,'admin',md5('admin'),date('Y-m-d H:i'), 'root@localhost',1) ) ) {
         $err = $this->pdo->errorInfo();
         $men_error = $err[2];
         throw new Exception('Error al insertar registro: '.$men_error);
         }

      }

   }

/** @} */

?>
