<?php

/**
 * @file Admin
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 *   Created  23/11/09
 *  Revision  SVN $Id: Admin.php 660 2012-11-01 19:37:28Z eduardo $
 * Copyright  Copyright (c) 2009, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

/**
 * @class Admin
 * @brief Administración de proyectos
 * @version 0.1
 */

class Admin extends Modulos {

   function __construct() {

      global $gcm;

      parent::__construct();

      /* Añadir módulos activos a todos los módulos que encontremos e caso de
       * iniciarse un nuevo proyecto
       */

      // $modulos = glob(GCM_DIR.'modulos/*');

      // echo "<pre>" ; print_r($gcm->modulos_basicos) ; echo "</pre>"; // DEV  "")""
      // foreach ($modulos as $modulo) {
      //    
      //    $m = basename($modulo);
      //    if ( !in_array($m, $gcm->modulos_basicos )  ) {
      //       $config['Módulos activados'][] = $m;
      //       }
      //    }

      }

   /**
    * Validar datos de usuario
    *
    * mail: Pasar por el validador de emails
    * pass: pass1 debe ser igual a pass2
    * nombre: Máximo 100 caracteres, sin código
    *
    * @todo Validar email
    *
    */

   function validar_datos($aDatos) {

      global $gcm;

      permiso(1);

      $vale = TRUE;

      if ( isset($aDatos['usuario']) && stripos($aDatos['usuario'],'javascript')  ) {
         registrar(__FILE__,__LINE__,literal('Este nombre de usuario no es permitido'),'ERROR');
         $vale = NULL;
         }

      if ( isset($aDatos['usuario']) && stripos($aDatos['usuario'],'<?') !== FALSE ) {
         registrar(__FILE__,__LINE__,literal('Este nombre de usuario no es permitido'),'ERROR');
         $vale = NULL;
         }

      if ( isset($aDatos['pass']) && $aDatos['pass'] !=  $aDatos['pass2'] ) {
         registrar(__FILE__,__LINE__,literal('No coinciden las contraseñas'),'ERROR');
         $vale = NULL;
         }

      if ( $vale  ) {

         return $aDatos;

      } else {

         return FALSE;

         }

      }

   /**
    * Borrar usuario
    */

   function borrar_usuario() {

      permiso(1, $usuario_id);

      }

   /**
    * Añadir usuario
    */

   function anyadir_usuario() {

      permiso(10);

      }

   /**
    * Cambio de contraseña
    */

   function cambio_password() {

      global $gcm;

      permiso(1);

      ?>
      <form action="<?=$_SERVER['PHP_SELF'];?>" method="post">
         <?php include($gcm->event->instancias['temas']->ruta('admin','html','form_cambio_pass.html')); ?>
         <input type="hidden" name="a" value="ejecutar_cambio_password" />
         <input type="hidden" name="m" value="admin" />
      </form>
      <?php

   }

   /**
    * Ejecutar cambio de contraseña
    */

   function ejecutar_cambio_password() {

      global $gcm;

      require_once(dirname(__FILE__).'/../modelos/usuarios.php');

      $usuario_id = $gcm->au->logeado();

      permiso(1, $usuario_id);

      $gcm->event->anular('contenido','admin');
      $gcm->event->unico('titulo','admin');
      $gcm->titulo = literal('Cambio de contraseña');

      $usuario = new Usuarios($gcm->pdo_conexion(), $usuario_id);

      $resultado = $this->validar_datos($_POST);

      if ( ! $resultado  ) {
         $this->cambio_password();
         return FALSE;
         }

      $usuario->setPass_md5(md5($resultado['pass']));
      $usuario->save();

      registrar(__FILE__,__LINE__,literal('Usuario modificado'),'AVISO');

      $this->perfil_usuario(NULL);

      }

   /**
    * Modificar usuario
    */

   function gestionar_usuario($usuario_id) {

      global $gcm;

      permiso(1, $usuario_id);

      $presentar_form = TRUE;          ///< Presentamos formulario
      $anyadir_usuario = FALSE;        ///< Si se desea añadir un usuario

      require_once(dirname(__FILE__).'/../modelos/usuarios.php');

      if ( isset($_POST['modificar'] )  ) {

         try {

            $usuario = new Usuarios($gcm->pdo_conexion(), $usuario_id);

            $resultado = $this->validar_datos($_POST);

            if ( $resultado ) {

               $usuario->setUsuario($resultado['usuario']);
               $usuario->setNombre($resultado['nombre']);
               $usuario->setApellidos($resultado['apellidos']);
               $usuario->setMail($resultado['mail']);
               $usuario->setTelefono($resultado['telefono']);
               $usuario->setFecha_modificacion(date('Y-m-d H:i'));
               $usuario->save();

               registrar(__FILE__,__LINE__,literal('Usuario modificado'),'AVISO');

            } else {
               
               $resultado = $_POST;

            }

         } catch (Exception $ex ) {

            registrar($ex->getFile(),$ex->getLine(),$ex->getMessage(),'AVISO');
            return;

            }

      } elseif ( isset($_POST['borrar'] )  ) {

         $usuario = new Usuarios($gcm->pdo_conexion(), $_POST['id']);

         $usuario->MarkForDeletion();
         $presentar_form = FALSE;
         registrar(__FILE__,__LINE__,literal('Usuario borrado'),'AVISO');

      } elseif ( isset($_POST['anyadir'] )  ) {

         $anyadir_usuario = TRUE;

      } elseif ( isset($_POST['insertar'] )  ) {

         $usuario = new Usuarios($gcm->pdo_conexion());

         $resultado = $this->validar_datos($_POST);

         if ( $resultado ) {

            $usuario->setUsuario($resultado['usuario']);
            $usuario->setPass_md5(md5($resultado['pass']));
            $usuario->setNombre($resultado['nombre']);
            $usuario->setApellidos($resultado['apellidos']);
            $usuario->setMail($resultado['mail']);
            $usuario->setTelefono($resultado['telefono']);
            $usuario->save();
            $resultado['id'] = $usuario->ultimo_identificador();

            registrar(__FILE__,__LINE__,literal('Usuario insertado'),'AVISO');

         } else {
            
            $resultado = $_POST;

         }


      } else {
         
         $usuario = new Usuarios($gcm->pdo_conexion(), $usuario_id);

         $resultado['id']     = $usuario->getID();
         $resultado['usuario'] = $usuario->getUsuario();
         $resultado['nombre']   = $usuario->getNombre();
         $resultado['apellidos']   = $usuario->getApellidos();
         $resultado['mail']   = $usuario->getMail();
         $resultado['telefono']   = $usuario->getTelefono();

         }         

      ?>
      <span class="caja">
         <form action="<?=$_SERVER['PHP_SELF'];?>" method="post">
            <?php if ( $presentar_form ) include(dirname(__FILE__).'/../html/form_perfil.html'); ?>
            <br />
            <input type="hidden" name="id" value="<?=$resultado['id']?>" />
            <input type="hidden" name="m" value="admin" />
            <input type="hidden" name="a" value="usuarios" />
            <?php if ( isset($resultado['id'])  ) { ?>
            <input type='submit' name='modificar' value='<?=literal("Modificar")?>' />
            <input type='submit' name='borrar' value='<?=literal("Borrar")?>' />
            <?php } ?>
            <?php if ( $anyadir_usuario ) { ?>
               <input type='submit' name='insertar' value='<?=literal("Añadir")?>' />
            <?php } else { ?>
               <input type='submit' name='anyadir' value='<?=literal("Añadir")?>' />
            <?php } ?>
            <br /><br />
         </form>
         <script language='javascript'>
            document.getElementById('usuario').focus();
         </script>
      </span>
      <?php
      
      }

   /** Presentar menu administrativo
    *
    * Creación del menu administrativo para los proyectos
    * 
    * Se genera dinamicamente el menu segun los modulos que tengamos
    * Los modulos se encuentran en el directorio GCM_DIR."modulos/gcm/"
    * y se incluye el archivo menuAdmin.php que tenga cada modulo.
    *
    * - Si estamos logeados los de administración
    * - Si somos root los de root y administración
    *
    * Formato del arreglo con la información del menú::
    *
    *  $menuAdmin['seccion'][title]='Title de la sección';
    *  $menuAdmin['seccion'][link]='Enlace'; // En caso que lo haya.
    *
    * ejemplo de archivo menuAdmin.php::
    *
    *  $menuAdmin['Archivo']['boton']['borrar_documento']['activado'] = ( is_file( Router::get_c() ) ) ? 1 : 0;
    *  $menuAdmin['Archivo']['boton']['borrar_documento']['title']="Borrar documento actual";
    *  $menuAdmin['Archivo']['boton']['borrar_documento']['link']="?e=borrar_documento";
    *
    * @todo Organizar menú, crear sistema que ordene el menú
    *
    * @author Eduardo Magrané
    */

   function presentar_menu_administrativo() {

      global $gcm;

      $menuAdmin = array();

      // Buscamos los modulos de administracion

      $path=GCM_DIR."modulos/";
      $directorio_modulos=dir($path);
      while ($directorio = $directorio_modulos->read()) {
         if ( $directorio[0] !="."  AND is_dir($path.$directorio) ) {
            $fich_final=$path.$directorio.'/menuAdmin.php';
            if (is_file($fich_final)) include($fich_final);
            }
         }

      $path="modulos/";
      if ( file_exists($path) ) {
         $directorio_modulos=dir($path);
         while ($directorio = $directorio_modulos->read()) {
            if ( $directorio[0] !="."  AND is_dir($path.$directorio) ) {
               $fich_final=$path.$directorio.'/menuAdmin.php';
               if (is_file($fich_final)) include($fich_final);
               }
            }
         }

      /* Buscar archivos config en carpetas de módulos en caso de tenerlos se 
       * genera automáticamente una entrada en el menu administrativo
       */

      $archivos_config = glob(GCM_DIR.'modulos/*/config/config.php');
      foreach ( $archivos_config as $archivo ) {

         $array = explode('/',$archivo);
         $modulo = $array[count($array)-3];

         $menuAdmin[literal('Módulos',3)]['boton'][literal($modulo,3)]['activado']= 1;
         $menuAdmin[literal('Módulos',3)]['boton'][literal($modulo,3)]['title']=literal("Configuración de ",3).literal($modulo,3);
         $menuAdmin[literal('Módulos',3)]['boton'][literal($modulo,3)]['link']=Router::$base.$modulo."/configuracion";


         }

      /* Buscar en módulos de proyecto */

      $archivos_config = glob('modulos/*/config/config.php');

      if ( ! empty($archivos_config)  ) {
         foreach ( $archivos_config as $archivo ) {

            $array = explode('/',$archivo);
            $modulo = $array[count($array)-3];

            $menuAdmin[literal('Módulos',3)]['boton'][literal($modulo,3)]['activado']= 1;
            $menuAdmin[literal('Módulos',3)]['boton'][literal($modulo,3)]['title']=literal("Configuración de ",3).literal($modulo,3);
            $menuAdmin[literal('Módulos',3)]['boton'][literal($modulo,3)]['link']="?m=".$modulo."&a=configuracion";


            }
         }

      include($gcm->event->instancias['temas']->ruta('admin','html','menuAdmin.html'));
      }

   /**
    * Incluimos para evitar mensaje de Sin eventos para menuadmin
    */

   function menuadmin_sin_login($e, $args=NULL) {

      }

   /** formulario_registro
    *
    * Presentar formulario de registro
    */

   function panel_login() {

      global $gcm;

      if (! $gcm->au->logeado() ) {
         $panel = array();
         $panel['titulo'] = literal('Administrar');
         $panel['oculto'] = TRUE;
         $panel['contenido'] = 
            '<form name="entrada" action="" method="post"> 
            <br />'.literal("Usuario",3).':
            <br /><input type="text" size="10" name="loginPro" id="loginPro" value="" />
            <br />'.literal("Contraseña",3).': 
            <br /><input type="password" size="10" name="passwd" id="passwd" value="" />
            <br /><br />
            <input type="submit" value="Entrar" />
            </form>';

         Temas::panel($panel);

         }
      }

   /**
    * Mostrar formulario de registro 
    *
    * Para evento registro
    */

   function formulario_registro($e, $args=NULL) {

      global $gcm;

      if (! $gcm->au->logeado() ) {
         $formulario = '<form name="entrada" action="" method="post"> 
               <br />'.literal("Usuario",3).':
               <br /><input type="text" size="10" name="loginPro" id="loginPro" value="" />
               <br />'.literal("Contraseña",3).': 
               <br /><input type="password" size="10" name="passwd" id="passwd" value="" />
               <br /><br />
               <input type="submit" value="Entrar" />
               </form>';

         echo $formulario;
         }

      }

   /**
    * @brief  perfil_usuario
    *
    * Mostrar información de usuario
    *
    * @todo Validar datos que nos llegan
    *
    * @param $e Evento que lo llama.
    * @param $args Identificador de usuario a mostrar, sino pasamos niinguno sera el logeado.
    */

   function perfil_usuario($e, $args=NULL) {

      global $gcm;

      $usuario_id = ( $args ) ? $args : $gcm->au->logeado();

      permiso(1, $usuario_id);

      $gcm->event->anular('contenido','admin');
      $gcm->event->unico('titulo','admin');
      $gcm->titulo = literal('Perfil de usuario');

      $this->gestionar_usuario($usuario_id);

      $this->cambio_password();

      }

   /**
    * Listar usuarios para administrar
    */

   function listar_usuarios() {

      permiso(10);

      }

   /**
    * Comprobar que el administrador no es aun el por defecto
    *
    * @todo Enrutar a pagina de sin privilegios
    */

   function confirmar_configuracion($e, $args) {

      global $gcm;

      $usuario_id = $gcm->au->logeado();

      require_once(dirname(__FILE__).'/../modelos/usuarios.php');

      try {

         $usuario = new Usuarios($gcm->pdo_conexion(), $usuario_id);

         if ( $usuario->getUsuario() == 'admin' && $usuario->getPass_md5() == md5('admin') ) {

            $aviso = literal("Es necesario modificar los datos del administrador por seguridad");

            registrar(__FILE__,__LINE__,$aviso,'AVISO');

            $this->anularEvento('contenido','admin');
            $this->anularEvento('contenido_dinamico','admin');
            $this->anularEvento('titulo','admin');
            $gcm->titulo = literal('Configurar proyecto');

            $this->gestionar_usuario($usuario_id);

            }

      } catch (Exception $ex) {

         registrar($ex->getFile(),$ex->getLine(),$ex->getMessage());

         /* No tenemos usuario */

         registrar(__FILE__,__LINE__,literal('Sin usuario salimos'),'AVISO');

         return;
         }
      }

   /** 
    * Usuarios
    *
    * Formulario para administrar los usuarios
    *
    * @todo Seleccionar los campos que deben salir y paginarlos
    */

   function usuarios($e, $args) {

      global $gcm;

      permiso(NULL,TRUE);

      $gcm->event->anular('contenido','admin');
      $gcm->event->anular('titulo','admin');
      $gcm->titulo = literal('Usuarios');

      if ( isset($_GET['id'])  ) {
         $usuario_id = $_GET['id'];
      } elseif ( isset($_POST['id']) ) {
         $usuario_id = $_POST['id'];
      } else {
         $usuario_id = $gcm->au->id();
         }

      $this->gestionar_usuario($usuario_id);

      require_once(dirname(__FILE__).'/../modelos/usuarios.php');
      require_once(GCM_DIR.'lib/int/array2table/lib/Array2table.php');

      $usuarios = new Usuarios($gcm->pdo_conexion());

      $usuarios->listado();
      return;
      $arUsuarios = $usuarios->find();

      $numUsuarios = count($arUsuarios);

      if ( $numUsuarios > 0 ) {
         $array2table = new Array2table();
         $array2table->generar_tabla($arUsuarios, array('url'=>'?m=admin&a=usuarios&id='));
         }

      return;

      }

   /**
    * Presentar acciones sin realizarlas
    */

   function eventos_sin_accion($e, $args) {

      global $gcm;


      }

   /**
    * Presentar información de servidor
    */

   function infoserver($e,$args) {

      global $gcm;

      permiso(5);

      $gcm->event->anular('titulo','admin');
      $gcm->event->anular('contenido','admin');

      $gcm->titulo = "Información de servidor";

      ?>
      <style type="text/css">
      #phpinfo {}
      #phpinfo pre {}
      #phpinfo a:link {}
      #phpinfo a:hover {}
      #phpinfo table {}
      #phpinfo .center {}
      #phpinfo .center table {}
      #phpinfo .center th {}
      #phpinfo td, th {}
      #phpinfo h1 {}
      #phpinfo h2 {}
      #phpinfo .p {}
      #phpinfo .e {}
      #phpinfo .h {}
      #phpinfo .v {}
      #phpinfo .vr {}
      #phpinfo img {}
      #phpinfo hr {}
      </style>

      <div id="phpinfo">
      <?php

      ob_start () ;
      phpinfo () ;
      $pinfo = ob_get_contents () ;
      ob_end_clean () ;

      // the name attribute "module_Zend Optimizer" of an anker-tag is not xhtml valide, so replace it with "module_Zend_Optimizer"
      echo ( str_replace ( "module_Zend Optimizer", "module_Zend_Optimizer", preg_replace ( '%^.*<body>(.*)</body>.*$%ms', '$1', $pinfo ) ) ) ;

      ?>
      </div>
      <?php
      }

   /**
    * Ejecutar métodos cron de los módulos
    *
    * Buscamos en todos los módulos si hay un metodo cron en tal caso se lanza
    */

   function ejecutar_cron_modulos($e = FALSE, $args = FALSE) {

      global $gcm;

      $modulos_activados = array_merge($gcm->modulos_basicos,$gcm->config('admin','Módulos activados'));

      // Buscar instancias de módulos para ejecutar sus tests

      echo '<tr /><h2>Módulos de gcm</h2>';

      foreach ( $modulos_activados as $modulo ) {

         $dir_modulo = GCM_DIR.'modulos/'.$modulo;

         if ( ! is_dir($dir_modulo) ) continue;

         $clase = ucfirst($modulo);

         $hay_admin =  ( file_exists($dir_modulo.'/lib/'.$clase.'Admin.php') ) ? TRUE : FALSE ;


         if ( $hay_admin ) {
            $clase = ucfirst($modulo).'Admin';
            require_once($dir_modulo.'/lib/'.$clase.'.php');
            $instancia = new  $clase();
         } else {
            require_once($dir_modulo.'/lib/'.$clase.'.php');
            $instancia = new  $clase();
            }

         if (method_exists($instancia, 'cron') ) {
            echo '<br /><h3>'.$modulo.'</h3><br />';
            $instancia->cron();
         } elseif ( GCM_DEBUG ) {
            echo '<br /><h3>'.$modulo.'</h3><br />';
            echo 'Sin método cron';
            }
         }

      // Buscar instancias de módulos del proyecto

      echo '<tr /><h2>Módulos de proyecto</h2>';

      foreach ( glob('modulos/*') as $dir_modulo ) {

         if ( ! is_dir($dir_modulo) ) continue;

         $modulo = basename($dir_modulo);
         $clase = ucfirst($modulo);
         require_once($dir_modulo.'/lib/'.$clase.'.php');
         $instancia = new  $clase();
         echo '<br /><h3>'.$modulo.'</h3><br />';
         if (method_exists($instancia, 'cron') && $modulo != 'admin' ) {
            $instancia->cron();
         } else {
            echo 'Sin método cron';
            }
         }

      }

   }

?>
