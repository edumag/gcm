<?php

/**
 * @file Autentificacion.php
 * @brief Sistema de autentificación de usuarios.
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

   /** 
    * Lista de acciones con roles permitidos
    *
    * formato del array:
    * @code
    * $acciones[modulo][accion] = array(roles);
    * @endcode
    */ 


   private $acciones;

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

   function __construct(PDO $pdo, $sufijo='au', $acciones = FALSE) {

      $this->pdo = $pdo;
      $this->sufijo = $sufijo;

      $this->acciones = ( $acciones ) ? $acciones : array('admin' => array('administrar' => array('administrador')) ) ;

      // Recogemos configuración de acciones y permisos
      $this->leer_permisos_acciones();

      /* Comprobar existencia de tabla de usuarios */

      if ( ! $this->existe_tabla($this->sufijo.'usuarios')  ) $this->crear_tabla();

      /* Comprobar existencia de al menos un usuarios administratico */

      $sql = 'SELECT COUNT(*) FROM '.$this->sufijo.'usuarios u LEFT JOIN '.$this->sufijo.'r_usuarios_roles rur ON rur.usuarios_id = u.id WHERE rur.roles_id=1';

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
    * Recogemos $acciones de archivo de configuración
    */

   function leer_permisos_acciones(){

      global $gcm;

      // Para recoger los permisos configurados en los módulos
      $acciones = FALSE;

      $fichero_acciones_proyecto = "DATOS/acciones_permisos.php";
      $fichero_acciones          = GCM_DIR."modulos/admin/acciones_permisos.php";

      if ( file_exists($fichero_acciones_proyecto) ) {
         include($fichero_acciones_proyecto);
      } else {
         include($fichero_acciones);
         }

      if ( $acciones ) {
         $this->set_acciones($acciones);
         }

      }

   /**
    * Recibir array con acciones que seran sumadas a las que ya tenemos
    *
    * @param $acciones Array con las $acciones
    */

   function set_acciones($acciones) {
      $this->acciones = array_merge($this->acciones, $acciones);
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

      if ( ! $this->existe_tabla($this->sufijo.'roles') ) {

         $SQL = "CREATE TABLE ".$this->sufijo."roles (
            id  INT PRIMARY KEY,
            nombre varchar(150) NOT NULL,
            descripcion varchar(500) NOT NULL
            )
            ";

         if ( ! $sqlResult = $this->pdo->query($SQL) ) {

            throw new Exception("Error al crear tabla de ".$this->sufijo."roles\n".$pdo_error);
            return FALSE;

            }

         }

      if ( ! $this->existe_tabla($this->sufijo.'r_usuarios_roles') ) {

         $SQL = "CREATE TABLE ".$this->sufijo."r_usuarios_roles (
            usuarios_id int(11) NOT NULL,
            roles_id int(11) NOT NULL,
            PRIMARY KEY (usuarios_id,roles_id)
            )
            ";

         if ( ! $sqlResult = $this->pdo->query($SQL) ) {

            throw new Exception("Error al crear tabla de ".$this->sufijo."r_usuarios_roles\n".$pdo_error);
            return FALSE;

            }

         }

      $SQL="CREATE TABLE ".$this->sufijo."usuarios (
         id INT  PRIMARY KEY AUTO_INCREMENT ,
         usuario CHAR(50) , 
         pass_md5 CHAR(32) ,
         nombre CHAR(50) , 
         apellidos CHAR(50) , 
         fecha_creacion datetime  NOT NULL,
         fecha_modificacion timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ,
         mail CHAR(60) ,
         telefono CHAR(15)
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
    * @param usuario Nombre de usuario
    * @param pass    Contraseña de usuario
    */

   function entrar($usuario, $pass) {

      global $_SESSION;

      $sql = "SELECT id, usuario FROM ".$this->sufijo."usuarios WHERE usuario=? AND pass_md5=? LIMIT 1";
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

      list($id,$usuario) = $retorno[0];

      // Buscamos roles de usuario
      
      $sql = "SELECT r.nombre FROM ".$this->sufijo."roles r LEFT JOIN ".$this->sufijo."r_usuarios_roles rur ON rur.roles_id=r.id WHERE rur.usuarios_id=?";
      $comando = $this->pdo->prepare($sql);

      if ( ! $comando->execute(array($id)) ) {
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

         // Si es un usuario sin rol le adjudicamos el de usuario que es el mínimo.
         $roles[] = 'usuario';

      } else {

         foreach ( $retorno as $rol ) {
            $roles[] = $rol[0];
            }
         }

      /* Cramos sessión para usuario */

      $_SESSION[$this->sufijo.'id'] = $id;
      $_SESSION[$this->sufijo.'usuario'] = $usuario;
      $_SESSION[$this->sufijo.'roles'] = serialize($roles);

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

      if ( ! isset($_SESSION[$this->sufijo.'roles']) ) return FALSE;

      $roles = unserialize($_SESSION[$this->sufijo.'roles']);

      return in_array('administrador',$roles) ;

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
    * @defgroup permisos Permisos 
    *
    * Gestión de permisos de usuarios.
    *
    *
    *
    * @ingroup usuarios
    * @{
    */

   /** 
    * Devolvemos array con roles de usuario
    */

   function roles_usuario() {

      if ( ! isset($_SESSION[$this->sufijo.'roles']) ) return FALSE;

      return unserialize($_SESSION[$this->sufijo.'roles']);

      }

   /**
    * Roles de acción
    *
    * Devolver array con los roles de la acción
    *
    * @param $modulo Módulo
    * @param $accion Acción
    */

   function roles_accion($modulo, $accion) {

      global $gcm;

      if ( isset($this->acciones[$modulo][$accion]) ) return $this->acciones[$modulo][$accion];

      return FALSE;

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

      $roles_usuario = $this->roles_usuario();
      $roles_accion  = $this->roles_accion($modulo,$accion);

      if ( ! $roles_accion ) {
         registrar(__FILE__,__LINE__,'Sin permisos para ['.$modulo.'->'.$accion.'()]','DEBUG');
         if ( GCM_DEBUG ) 
            registrar(__FILE__,__LINE__,'Tiempo de ejecución de '.__CLASS__.'->'.__FUNCTION__.'('.$accion.','.$modulo.'): ('.round(microtime(TRUE)-$tiempo_inicio,4).')'); 
         return FALSE;
         }

      foreach ( $roles_accion as $rol_accion ) {
         if ( in_array($rol_accion, $roles_usuario) ) return TRUE;
         }

      return FALSE;

      }

   /** @} */

   /**
    * salir
    *
    * Cerrar sessión.
    */

   function salir() {

      if ( isset($_SESSION[$this->sufijo.'id'])  ) unset($_SESSION[$this->sufijo.'id']);
      if ( isset($_SESSION[$this->sufijo.'admin'])  ) unset($_SESSION[$this->sufijo.'admin']);
      if ( isset($_SESSION[$this->sufijo.'roles'])  ) unset($_SESSION[$this->sufijo.'roles']);
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

      $sql = "INSERT INTO ".$this->sufijo."usuarios (id, usuario, pass_md5, fecha_creacion, mail) VALUES ";
      $sql .= "(?,?,?,?,?)";
      $comando = $this->pdo->prepare($sql);
      if ( !$comando ) {
         $err = $this->pdo->errorInfo();
         $men_error = $err[2];
         throw new Exception('Error al preparar sql'.$men_error);
         }

      if ( ! $comando->execute(array(1,'admin',md5('admin'),date('Y-m-d H:i'), 'root@localhost') ) ) {
         $err = $this->pdo->errorInfo();
         $men_error = $err[2];
         throw new Exception('Error al insertar registro: '.$men_error);
         }

      $sql = "INSERT INTO ".$this->sufijo."roles VALUES (1,'administrador','El administrador tiene todos los privilegios');";
      $sql .= "INSERT INTO ".$this->sufijo."roles VALUES (2,'usuario','Usuario registrado');";
      $sql .= "INSERT INTO ".$this->sufijo."roles VALUES (3,'editor','Puede añadir su propio contenido');";
      $sql .= "INSERT INTO ".$this->sufijo."roles VALUES (4,'traductor','Puede modificar las traducciones');";

      $comando = $this->pdo->prepare($sql);
      if ( !$comando ) {
         $err = $this->pdo->errorInfo();
         $men_error = $err[2];
         throw new Exception('Error al preparar sql'.$men_error);
         }

      if ( ! $comando->execute() ) {
         $err = $this->pdo->errorInfo();
         $men_error = $err[2];
         throw new Exception('Error al insertar registro: '.$men_error);
         }

      unset($comando);

      $this->insertar_rol_usuario(1,1);
      $this->insertar_rol_usuario(1,2);

      }

   /**
    * Añadir un nuevo rol a usuario
    */

   function insertar_rol_usuario($usuario_id, $rol_id) {

      $sql = "INSERT INTO ".$this->sufijo."r_usuarios_roles VALUES (".$usuario_id.",".$rol_id.")";

      $comando = $this->pdo->exec($sql);

      if ( !$comando ) {
         $err = $this->pdo->errorInfo();
         $men_error = $err[2];
         throw new Exception('Error al wjwcutar sql: '.$sql."\n\n<br />".depurar($err));
         }

      // if ( ! $comando->execute() ) {
      //    $err = $this->pdo->errorInfo();
      //    $men_error = $err[2];
      //    throw new Exception('Error al insertar registro: '.$men_error);
      //    }

      }
   }

/** @} */

?>
