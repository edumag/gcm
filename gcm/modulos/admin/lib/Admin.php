<?php

/**
 * @file Admin.php
 * @brief Módulo Admin
 * 
 * @ingroup modulo_admin
 * @author    Eduardo Magrané 
 *
 * @internal
 * Copyright  Copyright (c) 2009, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 *
 */

/**
 * @class Admin
 * @brief Administración de proyectos
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
    * @param $aDatos Array con los datos a validar
    */

   function validar_datos($aDatos) {

      global $gcm;

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
    *
    * @param $e Evento que lo llama
    * @param $args Argumentos
    */

   function formulario_registro($e, $args=NULL) {

      global $gcm;

      if (! $gcm->au->logeado() ) {

         $gcm->event->anular('contenido','admin');
         $gcm->event->unico('titulo','admin');
         $gcm->titulo = literal('Formulario de entrada');


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
    * Comprobar que el administrador no es aun el por defecto
    *
    * @param $e Evento que lo llama
    * @param $args Argumentos
    */

   function confirmar_configuracion($e, $args) {

      global $gcm;

      $usuario_id = $gcm->au->logeado();

      require_once(dirname(__FILE__).'/../modelos/usuarios.php');

      try {

         $usuario = new Usuarios($gcm->pdo_conexion(), $usuario_id);

         if ( $usuario->getUsuario() == 'admin' && $usuario->getPass_md5() == md5('admin') ) {

            $aviso = literal("Es necesario modificar los datos del administrador por seguridad");

            if ( empty($_POST['pass_md5']) ) registrar(__FILE__,__LINE__,$aviso,'AVISO');

            $usuarios = new Usuarios($gcm->pdo_conexion(),1);
            $usuarios->administrar(FALSE,FALSE,FALSE,TRUE,'editar');
            // $usuarios->generar_formulario();
            // $usuarios->botones_acciones('editar');


            }

      } catch (Exception $ex) {

         registrar($ex->getFile(),$ex->getLine(),$ex->getMessage());

         /* No tenemos usuario */

         registrar(__FILE__,__LINE__,literal('Sin usuario salimos'),'AVISO');

         return;
         }
      }

   /**
    * Ejecutar métodos cron de los módulos
    *
    * Buscamos en todos los módulos si hay un metodo cron en tal caso se lanza
    *
    * @param $e Evento que lo llama
    * @param $args Argumentos
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

   /**
    * Procesar shortcodes de contenido
    *
    */

   function shortcode($e,$args=FALSE) {

      global $gcm;

      $etiqueta_inicio = '{S{';
      $etiqueta_final  = '}}';

      $buffer = $gcm->contenido;

      while ( strpos($buffer, $etiqueta_inicio) !== false ) {

         $pos1 = NULL;
         $pos2 = NULL;
         $archivo  = NULL;
         $remplazar = NULL;
         $archivo = NULL;

         $pos1 = strpos($buffer, $etiqueta_inicio);
         $pos2 = strpos($buffer, $etiqueta_final, $pos1);
         $remplazar = substr($buffer, $pos1, $pos2 - $pos1 + 2);
         $etiqueta = str_replace($etiqueta_inicio,'',$remplazar);
         $etiqueta = str_replace($etiqueta_final,'',$etiqueta);

         if ( $pos1 && $pos2 && $etiqueta && $remplazar ) {

            ob_start();
            list($modulo,$accion,$args) = explode(',',$etiqueta);
            $gcm->event->lanzar_accion_modulo($modulo,$accion,'shortcode',$args);
            $etiqueta = ob_get_contents();
            ob_end_clean();

            $buffer = str_replace($remplazar,$etiqueta,$buffer);

            }

         }

      $gcm->contenido=$buffer;
      }

   /**
    * Enviamos cabecera de error
    */

   function cabecera_error($e, $args=FALSE) {

      global $gcm;

      registrar(__FILE__,__LINE__,"Se ha producido un error, creamos cabeceras de error");
      
      header("Status: 404 Not Found");

      }

   }

?>
