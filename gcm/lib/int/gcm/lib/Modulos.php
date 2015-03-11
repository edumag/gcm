<?php

/**
 * @file      Modulos.php
 * @brief     Clase abstracta para módulos
 *
 * Todos los módulos de gcm deben heredar de está clase para tener todas las funcionalidades
 * disponibles.
 *
 * @see Modulos Definicion de clase
 * @author    Eduardo Magrané 
 *
 * @internal
 * Copyright  Copyright (c) 2010, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 *
 * @defgroup modulos_aplicacion Módulos de la aplicación
 * @{
 */

/* GcmConfig */

require_once(GCM_DIR."lib/int/GcmConfig/lib/GcmConfigFactory.php");

/** 
 * @class Modulos
 * @brief Interface para los módulos
 *
 * Con los metodos que son iguales para todos ellos
 *
 * @version 0.3
 *
 * @todo Crear metodos comunes para tratar con eventos: lanzaEvento(), anularEvento()
 *
 */

abstract class Modulos {

   private static $contador_instancias = -1;     ///< Contador de instancias

   /**
    * Nombre de la clase hija, En caso de llevar el Admin se lo quitamos
    * @see Eventos
    */

   private $nombre_clase = '';

   /**
    * Iniciamón módulos
    *
    * Registramos Inicio de modulo
    */

   function __construct() {

      global $gcm;

      self::$contador_instancias++;
      $this->nombre_clase = str_replace('Admin','',get_class($this));

      if ( $gcm ) {
         $gcm->registra(__FILE__,__LINE__,"Iniciada clase hija::[".$this->nombre_clase.'] Numero de instancia: ['.self::$contador_instancias.']');
         }

      }

   /**
    * anular evento
    *
    * @param $evento
    */

   protected function anularEvento($evento) {

      global $gcm;

      if ( $gcm && $gcm instanceof Gcm ) {
         registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$evento.') Evento anulado por '.$this->nombre_clase);
         $gcm->event->anular($evento,$this->nombre_clase);
      } else {
         registrar(__FILE__,__LINE__,'Modulos->anularEvento('.$evento.') Gcm no esta definido','ERROR');
         }
      }

   /**
    * Lanzar evento
    *
    * @param $evento
    * @param $args Argumentos para evento
    */

   protected function lanzarEvento($evento, $args='') {

      global $gcm;

      if ( $gcm && $gcm instanceof Gcm ) {
         registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$evento.') Lanzado evento desde '.$this->nombre_clase);
         $gcm->event->lanzarEvento($evento,$args);
      } else {
         registrar(__FILE__,__LINE__,'Modulos->anularEvento('.$evento.','.$args.') Gcm no esta definido','ERROR');
         }
      }

   /**
    * Añadir librerias externas
    *
    * @param $tipo Tipo de fichero puede ser js o css
    * @param $url  Url del fichero
    */

   protected function add_ext_lib($tipo, $url) {

      global $gcm;

      if ( $gcm && $gcm instanceof Gcm ) {
         registrar(__FILE__,__LINE__,'Añadimos librería externa tipo: ['.$tipo.'] url: ['.$url.'] Módulo: ['.$this->nombre_clase.']');
         $gcm->add_ext_lib($tipo,$url);
      } else {
         registrar(__FILE__,__LINE__,'Modulos->add_ext_lib('.$tipo.','.$url.') $gcm no esta definida','ERROR');
         }
      }

   /**
    * Añadir librerias javascript a la lista de Gcm
    *
    * @param $archivo Librería javascript a añadir
    */

   protected function librerias_js($archivo) {

      global $gcm;

      if ( $gcm && $gcm instanceof Gcm ) {
         registrar(__FILE__,__LINE__,'Modulos->librerias_js('.$archivo.') modulo: '.$this->nombre_clase);
         $gcm->add_lib_js($this->nombre_clase,$archivo);
      } else {
         registrar(__FILE__,__LINE__,'Modulos->libreria_javascripts('.$archivo.') $gcm no esta definida','ERROR');
         }
      }

   /**
    * Añadir archivos javascript a la lista de Gcm
    *
    * @param $archivo Archivo javascript a añadir
    */

   protected function javascripts($archivo) {

      global $gcm;

      if ( $gcm && $gcm instanceof Gcm ) {
         $gcm->add_js($this->nombre_clase,$archivo);
         registrar(__FILE__,__LINE__,'Modulos->javas_cripts('.$archivo.') modulo: '.$this->nombre_clase);
      } else {
         registrar(__FILE__,__LINE__,'Modulos->javascripts('.$archivo.') $gcm no esta definida','ERROR');
         }
      }

   /**
    * Añadir o modificar elemento de configuración
    */
   
   protected function config($elemento=FALSE,$valor=NULL) {

      global $gcm;

      if ( $gcm && $gcm instanceof Gcm ) {
         registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$elemento.','.$valor.')');
      } else {
         registrar(__FILE__,__LINE__,
            __CLASS__.'->'.__FUNCTION__.'('.$elemento.','.$valor.') Gcm no esta implementado para Modulo. '.$this->nombre_clase,
            'ERROR');
         return FALSE;
         }

      registrar(__FILE__,__LINE__,
         'Config, modulo: ['.$this->nombre_clase.'] Elemento ['.$elemento.'] valor: ['.$valor.']');

      return $gcm->config($this->nombre_clase,$elemento,$valor);

      }

   /**
    * Configuración de módulos
    */

   public function configuracion($e, $args) {

      global $gcm;

      if ( !permiso('configuracion',lcfirst($this->nombre_clase)) ) {
         registrar(__FILE__,__LINE__,'Sin permisos para configurar','AVISO');
         return FALSE;
         }


      if ( isset($_POST['accion']) && $_POST['accion'] == 'escribir_gcmconfig'  ) {

         /* Nos llega configuración modificada */

         try {

            require_once(GCM_DIR.'lib/int/GcmConfig/lib/GcmConfigGui.php');

            $configuracion = new GcmConfigGui($_POST['archivo']);

            $configuracion->escribir_desde_post();

         } catch (Exception $ex) {
            registrar($ex->getFile(),$ex->getLine(),$ex->getMessage(),'ERROR');
            return FALSE;
            }

         registrar(__FILE__,__LINE__,literal('Configuración guardada',3),'AVISO');

      } else {
         
         /* Presentamos formulario para modificar configuración */

         if ( isset($args['modulo']) ) {
            $modulo = $args['modulo'];
            $men = 'Desde args';
         } elseif ( !empty(Router::$m) ) {
            $modulo = Router::$m;
            $men = 'Desde Router';
         } else {
            $modulo = $this->nombre_clase;
            $men = 'Desde el propio módulo';
            }

         registrar(__FILE__,__LINE__,
            __CLASS__.'->'.__FUNCTION__.'('.$e.','.depurar($args).') '.$men.' ['.$modulo.']');

         $this->anularEvento('titulo');
         $this->anularEvento('contenido');
         $gcm->titulo = literal('Configuración',3).' '.literal('de',3).' '.$modulo;

         $archivo = 'DATOS/configuracion/'.$modulo.'/config.php';

         $config_original = GCM_DIR.'modulos/'.$modulo.'/config/config.php';
         $directorio_descripciones = GCM_DIR.'modulos/'.$modulo.'/config';
         if ( !file_exists($config_original) ) {
           $config_original = 'modulos/'.$modulo.'/config/config.php';
           $directorio_descripciones = 'modulos/'.$modulo.'/config';
          }

         /* Si se pide volver a los valores por defecto, borramos
          * archivo de configuración del proyecto, para que se coja 
          * el del módulo
          */

         if ( isset($_POST['reset']) && file_exists($archivo) ) unlink($archivo);

         if ( !file_exists('DATOS/configuracion')  ) mkdir('DATOS/configuracion');
         if ( !file_exists('DATOS/configuracion/'.$modulo)  ) mkdir('DATOS/configuracion/'.$modulo);

         if ( !file_exists($archivo) ) copy($config_original, $archivo);

         try {

            require_once(GCM_DIR.'lib/int/GcmConfig/lib/GcmConfigGui.php');

            $configuracion = new GcmConfigGui($archivo);

            $args = array();
            $args['eliminar'] = 'si';

            $configuracion->directorio_descripciones($directorio_descripciones);

            $configuracion->formulario($args);

            echo '<br /><br /><form action="" method="post">';
            echo '<input name="reset" type="submit" value="Valores por defecto"/>';
            echo '</form>';

         } catch (Exception $ex) {
            registrar($ex->getFile(),$ex->getLine(),$ex->getMessage(),'ERROR');
            }
         }

      }

   /**
    * Test de módulos
    *
    * Se compara uno con dos si no son iguales
    * presentamos mensaje de error
    *
    * @param $nombre Nombre del test
    * @param $uno Primer parametro
    * @param $dos Segundo parametro Si no existe solo se comprueba que el uno no es FALSE
    */

   protected function ejecuta_test($nombre, $uno, $dos = FALSE) {

      if ( ( ! $dos && $uno ) || ( $uno === $dos ) ) {
         echo '<div class="aviso">'.$this->nombre_clase.' '.$nombre.' bien</div>';
      } else {
         echo '<div class="error">'.$this->nombre_clase.' <b>'.$nombre.'</b>: ';
         if ( is_array($uno) ) {
            echo '<br />'.depurar($uno).'<br />diferente a <br />'.depurar($dos);
         } else {
            echo '['.$uno.'] diferente a ['.$dos.']';
            }
         echo '</div>';
         }

      }

   /**
    * Mensajes administrativos con paneles.
    */

   protected function panel_admin($clave, $numero, $mensaje, $contenido) {

     global $gcm;

     if ( $gcm && $gcm instanceof Gcm ) {
       $gcm->event->instancias['admin']->mensajes_admin[$clave]['num'] = $numero; 
       $gcm->event->instancias['admin']->mensajes_admin[$clave]['mensaje'] = $mensaje; 
       $gcm->event->instancias['admin']->mensajes_admin[$clave]['contenido'] = $contenido; 
     } else {
       registrar(__FILE__,__LINE__,'Error con instancia admin al crear panel admin','ERROR');
     }
   }

   }

/** @} */
?>
