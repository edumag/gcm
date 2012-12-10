<?php

/**
 * @file      Contenidos.php
 * @brief     Clase abstracta para ContenidosFile y ContenidosPDO
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 *   Created  15/04/10
 *  Revision  SVN $Id: Contenidos.php 373 2010-10-08 14:41:09Z eduardo $
 * Copyright  Copyright (c) 2010, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

if ( defined('GCM_DIR') === FALSE ) define('GCM_DIR',dirname(__FILE__).'/../../../');

/** Utiles de Gcm */

require_once(GCM_DIR.'lib/int/gcm/lib/GUtil.php');

/** Contenidos
 *
 * Clase para manejar el contenido
 *
 * @package Contenidos Gestión de contenido
 * @author Eduardo Magrané
 * @version 0.1
 * 
 */

abstract class ContenidosAbstract extends Modulos {

   public $descartar;                          ///< Archivos o directorios a descartar

   function __construct() { 

      parent::__construct(); 

      $this->descartar = $this->config('descartar');

      }

   /** guardar el contenido */
   abstract function guardar_contenido($url, $contenido);

   /** devolver contenido de documento */
   abstract function getContenido($ruta);

   /** devolver titulo de contenido */
   abstract function devolver_titulo($url=NULL);

   /** Verificar sección */
   abstract function verificar_seccion($url);

   /** Crear un array con el contenido de una sección */
   abstract function seccion_matriz($seccion);

   /** Ejecutar nueva sección */
   abstract function crear_nueva_seccion($ruta);

   /** Verificar contenido */
   abstract function verificar_contenido($ruta);

   /** Borrar contenido */
   abstract function borrar_contenido($ruta);

   /** Renombrar sección */
   abstract function renombrar_seccion($ruta_origen, $ruta_destino);

   /** Devolver fecha de modificación de contenido */
   abstract function getFechaActualizacion($ruta);

   /**
    * Guardar como 
    *
    * Presentamos formulario de sección, formulario de nombre con boton
    * de 'guardar' y de 'borrador'.
    *
    * @param $e evento que llama al metodo
    * @param $args Argumentos con formato "num=7&seccion=".Router::$s."&formato=1"
    */

   function guardar_como($e, $args) {

      registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$e.','.$args.')');

      global $gcm;

      $gcm->event->anular('contenido','contenidos');
      $gcm->event->anular('titulo','contenidos');
      $gcm->titulo = literal('Guardar como',3);

      $contenido = $this->devolver_contenido($gcm->seleccionado[0]);
      $titulo = $this->devolver_titulo($gcm->seleccionado);

      include ($gcm->event->instancias['temas']->ruta('contenidos','html','form_guardar_como.html'));

      }

   /**
    * Borrar
    *
    * Comprobar que es lo que se desea borrar y presentar lista con
    * el contenido a borrar con boton de confirmación que nos llevara
    * al evento 'ejecutar_borrado'
    *
    * @param $e evento que llama al metodo
    * @param $args Argumentos con formato "num=7&seccion=".Router::$s."&formato=1"
    */

   function borrar($e, $args) {

      registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$e.','.$args.')');

      global $GCM_LG, $gcm ;

      $gcm->event->anular('contenido','contenidos');
      $gcm->titulo = literal('Confirmar lista de contenidos a borrar');
      $gcm->event->anular('titulo','contenidos');

      /* Relación de cosas afectadas por el borrado de una sección:
       *
       * buscar todo el contenido afectado por el borrado incluyendo el de otros idiomas.
       */

      $contenidos_borrar = $this->seccion_matriz($gcm->seleccionado);

      if ( empty($contenidos_borrar)  ) {

         registrar(__FILE__,__LINE__,'No existe contenido seleccionado para borrar','AVISO');
         return FALSE;
         }

      include ($gcm->event->instancias['temas']->ruta('contenidos','html','form_borrar.html'));

      }

   /**
    * editar
    *
    * Presentamos formulario para editar contenido.
    *
    * @param $e evento que llama al metodo
    * @param $args Argumentos con formato "num=7&seccion=".Router::$s."&formato=1"
    */

   function editar($e, $args) {

      registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$e.','.$args.')');

      global $gcm;

      $gcm->event->anular('contenido','contenidos');

      $contenido = $this->devolver_contenido();

      switch($e) {

         case 'traducir':
            $titulo = literal($this->devolver_titulo());
            $evento = 'ejecutar_traducir';
            $boton  = 'Traducir';
            break;
         default:
            $evento = 'actualizar_contenido';
            $boton  = 'Guardar';
            break;
         }

      include ($gcm->event->instancias['temas']->ruta('contenidos','html','form_editar.html'));

      }

   /**
    * Mover o cambiar de nombre
    *
    * Presentamos formulario de secciones y formulario para el titulo.
    * con boton de evento 'ejecutar_mover'.
    *
    * Añadimos en el formularios campo oculto _POST['original'] con url 
    * de contenido original que se mueve para poderlo utilizar en los módulos.
    *
    * @param $e evento que llama al metodo
    * @param $args Argumentos con formato "num=7&seccion=".Router::$s."&formato=1"
    */

   function mover($e, $args) {

      registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$e.','.$args.')');

      global $gcm;

      $contenido = $this->devolver_contenido($gcm->seleccionado[0]);

      $titulo = literal($this->devolver_titulo($gcm->seleccionado[0]));

      $gcm->router->inicia($gcm->seleccionado[0]);

      $gcm->event->anular('contenido','contenidos');
      $gcm->event->anular('titulo','contenidos');
      $gcm->titulo = $titulo;

      include ($gcm->event->instancias['temas']->ruta('contenidos','html','form_mover.html'));

      }

   /**
    * Contenido nuevo
    *
    * Presentar formulario de secciones, de título, de contenido y boton de 'ejecutar_nuevo'
    *
    * @param $e evento que llama al metodo
    * @param $args Argumentos con formato "num=7&seccion=".Router::$s."&formato=1"
    */

   function nuevo($e, $args) {

      registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$e.','.$args.')');

      global $gcm;

      $gcm->event->anular('contenido','contenidos');

      $titulo = '';
      $contenido = '';

      include ($gcm->event->instancias['temas']->ruta('contenidos','html','form_nuevo.html'));

      }

   /**
    * Presentar contenido
    *
    * @param $e evento que llama al metodo
    * @param $args Argumentos con formato "num=7&seccion=".Router::$s."&formato=1"
    */

   function contenido($e, $args) {

      registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$e.','.$args.')');

      global $gcm;

      /* Si se pide en otro idioma que no es el predeterminado y no lo tenemos */

      if ( Router::$sin_traduccion ) {
         registrar(__FILE__,__LINE__,literal('Contenido pendiente de traducción',3),'AVISO');
         }

      $contenido = $this->devolver_contenido(Router::$f);

      // si el formato es ajax deberiamos convertir los enlaces relativos en absolutos

      if ( Router::$formato == 'ajax'  ) {

         $base = dirname(Router::$url).'/';

         $buffer = $contenido;

         $patron = '/src=["\'](.*?)["\']/';
         $sustitucion = 'src="'.$base.'${1}"';
         $buffer = preg_replace($patron, $sustitucion, $buffer,-1,$numero);

         registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$e.','.depurar($args).') Numero de sustituciones: '.$numero );
         $gcm->contenido=$buffer;

      } else {
         echo $contenido;
         }

      }

   /**
    * Sin Contenido
    *
    * @param $e evento que llama al metodo
    * @param $args Argumentos con formato "num=7&seccion=".Router::$s."&formato=1"
    */

   function sin_contenido($e, $args) {

      registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$e.','.$args.')');

      global $gcm;

      $gcm->titulo = literal('Sin Contenido',3);

      $ruta = $gcm->event->instancias['temas']->ruta('temas','html','sin_contenido.html');

      registrar(__FILE__,__LINE__,literal('Contenido ['.Router::$s.Router::$c.'] no encontrado',3),'ERROR');
      return;

      if ( $ruta ) {
         include($ruta);
      } else {
         registrar(__FILE__,__LINE__,literal('Contenido no encontrado',3),'AVISO');
         }
      
      }

   /**
    * Sin Sección
    *
    * @param $e evento que llama al metodo
    * @param $args Argumentos con formato "num=7&seccion=".Router::$s."&formato=1"
    */

   function sin_seccion($e, $args) {

      registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$e.','.$args.')');

      global $gcm;

      // Comprobar que tenemos los directorios base creados

      echo "Router::dd: ".Router::$dd;
      if ( file_exists(Router::$dd) ) {
         $gcm->titulo = literal('Sin sección',3);
         registrar(__FILE__,__LINE__,literal('Sección ['.Router::$s.'] no encontrada',3),'ERROR');
      } else {
         registrar(__FILE__,__LINE__,literal('Creamos directorio para contenido ['.Router::$dd.']',3),'ERROR');
         mkdir_recursivo(Router::$dd);
         }

      return;

      $ruta = $gcm->event->instancias['temas']->ruta('temas','html','sin_seccion.html');

      if ( $ruta ) {
         include($ruta);
      } else {
         registrar(__FILE__,__LINE__,literal('Sección no encontrada',3),'AVISO');
         }
      
      }

   /**
    * Presentar titulo
    *
    * @param $e evento que llama al metodo
    * @param $args Argumentos con formato "num=7&seccion=".Router::$s."&formato=1"
    */

   function titulo($e, $args) {

      registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$e.','.$args.')');

      $this->titulo_articulo();
      }

   /**
    * camino, presentar recorrido desde el inicio hasta el documento actual 
    * con los enlaces a cada sección
    *
    * @param $e evento que llama al metodo
    * @param $args Argumentos con formato "num=7&seccion=".Router::$s."&formato=1"
    */

   function camino($e , $args=NULL) {

      registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$e.','.$args.')');

      global $gcm;

      // $camino = ( $url  ) ? $url : Router::$s;
      $camino = explode('/',Router::$s);

      include ($gcm->event->instancias['temas']->ruta('contenidos','html','camino.html'));

      }

   /**
    * Crear una nueva sección
    *
    * Presentamos formularios para seleccionar sección y el de titulo de sección
    * y boton de 'ejecutar_nueva_seccion'
    *
    * @param $e evento que llama al metodo
    * @param $args Argumentos con formato "num=7&seccion=".Router::$s."&formato=1"
    */

   function nueva_seccion($e, $args) {

      registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$e.','.$args.')');

      global $gcm;

      $gcm->event->anular('contenido','contenidos');
      $gcm->event->anular('titulo','contenidos');
      $gcm->titulo = literal('Nueva sección');

      include ($gcm->event->instancias['temas']->ruta('contenidos','html','form_nueva_seccion.html'));

      }

   /**
    * Mover o renombrar sección
    *
    * Se presenta formulario para selecccionar donde se mueve la sección y formulario
    * de nombre de sección. Con boton de accion=ejecutar_mover_seccion
    *
    * @param $e evento que llama al metodo
    * @param $args Argumentos con formato "num=7&seccion=".Router::$s."&formato=1"
    */

   function mover_seccion($e, $args) {

      registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$e.','.$args.')');

      global $gcm;

      $gcm->event->anular('contenido','contenidos');
      $gcm->event->anular('titulo','contenidos');
      $gcm->titulo = literal('Mover o renombrar sección');

      $titulo_seccion = comprobar_barra(basename(Router::$s),'eliminar');
      $titulo_seccion = literal($titulo_seccion);
      $seccion_seleccionada = comprobar_barra(Router::$dd.str_replace($titulo_seccion,'',Router::$s));

      include ($gcm->event->instancias['temas']->ruta('contenidos','html','form_mover_seccion.html'));

      }

   /**
    * enrutar
    *
    * Una vez realizados todas las acciones sobre cambios en el contenido
    * nos vamos a la pagina nueva.
    *
    * Por ejemplo en caso de un cambio de nombre al recargar la página daria un error
    * por no encontrar el contenido. Con este sistema permitimos que haya dos pasos:
    *
    * 1) el cambio de nombre del contenido y
    * 2) la recarga de la página para que vaya al nuevo contenido.
    *
    * Entre el paso 1 y 2 los módulos pueden realizar sus acciones pertinentes.
    *
    * Con este sistema evitamos que al recargar la página se repitan acciones indeseadas.
    *
    * @param $e     Evento que recibimos de Eventos
    * @param $args  Argumentos posibles
    */

   function enrutar($e, $args) {

      global $gcm;

      registrar(__FILE__,__LINE__,'Enrutar desde evento: ['.$e.']');

      /* SI estamos en debug no enrutamos para poder tener a mono los mensajes 
       * de la aplicación 
       */

      if ( GCM_DEBUG ) {
         //registrar(__FILE__,__LINE__,'Enrutar desde evento: ['.$e.'] Desde debug no enrutamos <a href="'.Router::$dir.Router::$url.'?mens='.$gcm->reg->sesion.'">[enrutar]</a>','ADMIN');
         echo '<div class="aviso">Enrutar desde evento: ['.$e.'] Desde debug no enrutamos <a href="'.Router::$dir.Router::$url.'?mens='.$gcm->reg->sesion.'">[enrutar]</a></div>';
         return;
         }


      switch ($e) {

         case 'postejecutar_borrar':

            $url = Router::$s.Router::$c;

            /* Si la pagina actual ha sido una de las borradas subimos arriba */

            while ( ! file_exists($url) ) {
               if ( $url == '' || $url == '/'  ) {
                  $url=Router::$s;
                  break;
                  }
               $secs = explode('/',$url);
               if ( isset($secs[count($secs)-1])  ) {
                  unset($secs[count($secs)-1]);
               } else {
                  $url = Router::$s;
                  break;
                  }
               $url = implode('/',$secs);
               }

            $url = str_replace(Router::$d,'',$url);

            $gcm->router->inicia($url);

            header ("Location:".Router::$dir.Router::$url);
            exit();
            break;

         default:
            header ("Location:".Router::$dir.Router::$url);
            exit();
            break;

         }
      }

   /** Actualizar contenido
    *
    * Guardar documento 
    *
    * @todo Filtrar POST
    *
    * @param $e     Evento que recibimos de Eventos
    * @param $args  Argumentos posibles
    */

   function guardar($e, $args) {

      global $gcm;

      registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$e.','.depurar($args).')');

      $contenido = $_POST['areaTexto'];

      switch($e) {


         case 'ejecutar_traducir':

            $this->crear_nueva_seccion(Router::$d.Router::$s);

            $titulo = $_POST['titulo'];
            $nombre_contenido = $this->devolver_titulo();
            $destino = Router::$d.Router::$s.$nombre_contenido;
            if ( substr_count($destino,".html") == 0) $destino .=  ".html";
            $mens = literal('Contenido traducido',3);
            break;

         case 'guardar_como_borrador':

            $titulo = $_POST['titulo'];
            $nombre_contenido = GUtil::textoplano($titulo);
            $contenido = $_POST['areaTexto'];
            $destino = $gcm->seleccionado[0].'/'.$nombre_contenido;
            if ( substr_count($destino,".html") == 0) $destino =  str_replace('.html','',$destino);
            if ( substr_count($destino,".btml") == 0) $destino .=  ".btml";
            $mens = literal('Borrador añadido',3);
            break;

         case 'ejecutar_nuevo':

            $titulo = $_POST['titulo'];
            $nombre_contenido = GUtil::textoplano($_POST['titulo']);
            $destino = $_POST['seleccionado'][0].'/'.$nombre_contenido;
            if ( substr_count($destino,".html") == 0) $destino .=  ".html";

            /* Nuevo: verificar que no haya contenido existente */

            if ( $this->verificar_contenido($destino)  ) {
               $this->existe_contenido($contenido, $destino);
               return;
               }
            $mens = literal('Contenido añadido',3);
            break;

         case 'actualizar_contenido':

            $destino = $gcm->seleccionado[0];
            $mens = literal('Contenido actualizado',3);
            break;

         default:

            $destino = Router::$enlace_relativo.Router::$d.Router::$url;
            if ( substr_count($destino,".html") == 0) $destino .=  ".html";
            $mens = literal('Contenido guardado',3);
            break;

         }

      $gcm->event->anular('titulo','contenidos');
      $gcm->event->anular('contenido','contenidos');
      $gcm->titulo = ( isset($titulo) ) ? stripslashes($titulo) : literal('Guardar contenido',3);
      $gcm->contenido = stripslashes($contenido);

      if ( ! $this->guardar_contenido($destino, $contenido) ) {

         registrar(__FILE__,__LINE__,
            literal('Error').' '.literal('guardando contenido').': '.$destino,'ERROR');
      } else {
         registrar(__FILE__,__LINE__,$mens,'AVISO');

         /* Si tenemos un nuevo literal lo añadimos */

         if ( isset($titulo) && !empty($titulo) && isset($nombre_contenido) ) {
            literal($nombre_contenido,1,$titulo);
            }

         }

      $gcm->router->inicia($destino);

      }

   /**
    * Si se encuentra contenido con el mismo nombre a la hora de guardar
    * esta función presguntara que se desea hacer, cambiar el nombre, sobrescribir, etc...
    *
    * @todo Presentar secciones para poder seleccionar otra donde guardar.
    *
    * @author Eduardo Magrané
    * @version 1.0
    * @param $contenido a grabar
    * @param $f Nombre del archivo
    *
    */

   function existe_contenido($contenido, $f){

      global $gcm;

      $titulo = literal($this->devolver_titulo($f));
      $contenido = limpiarContenido($contenido);

      $gcm->event->anular('contenido','contenidos');
      $gcm->event->anular('titulo','contenidos');
      $gcm->titulo = $titulo;

      include ($gcm->event->instancias['temas']->ruta('contenidos','html','form_existe_contenido.html'));

      }

   /** ver todo
    *
    * Presentamos todo el contenido en forma de arbol para facilitar la 
    * administración del mismo.
    *
    * @param $e     Evento que recibimos de Eventos
    * @param $args  Argumentos posibles
    */

   function vertodo($e, $args=NULL) {

      global $gcm;

      $gcm->event->anular('contenido','contenidos');
      $gcm->event->anular('titulo','contenidos');
      $gcm->titulo = literal('Listado completo de contenido',3);

      $path              = "File";
      $accion            = "editar_doc";
      $path_seleccionado = Router::$d.Router::$s;

      include ($gcm->event->instancias['temas']->ruta('contenidos','html','vertodo.html'));

      }

   /** 
    * Ejecutar el cambio de nombre de contenido o de ubicación
    *
    * @param $e     Evento que recibimos de Eventos
    * @param $args  Argumentos posibles
    */

   function ejecutar_mover($e, $args=NULL) {

      global $gcm;

      registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$e.','.depurar($args).')');

      switch($e) {

         case 'publicar_borrador':

            $origen = Router::$f;
            $destino = str_replace('.btml','.html',$origen);
            $titulo_contenido = str_replace('.btml','',Router::$c);
            $mens = literal('Borrador publicado',3);

            break;

         default:
            $origen = $_POST['origen'];
            $titulo = $_POST['titulo'];
            $titulo_contenido = GUtil::textoplano($_POST['titulo']);
            $destino = $gcm->seleccionado[0].'/'.$titulo_contenido;
            if ( substr_count($destino,".html") == 0) $destino .=  ".html";
            $mens = literal('Contenido renombrado o movido',3);
            break;
         }

      if ( $this->mover_contenido($origen, $destino) ) {
         registrar(__FILE__,__LINE__,$mens,'AVISO');
         literal($titulo_contenido,1,$titulo);
         $gcm->memoria['destino'] = $destino;
         $gcm->memoria['origen'] = $origen;
         $gcm->memoria['titulo_contenido'] = $titulo_contenido;
         $gcm->router->inicia($destino);
      } else {
         return FALSE;
         }

      }

   /** 
    * Ejecutar mover sección 
    *
    * Guardamos en $gcm->memoria['seccion_movida'] la sección movida
    * para que otros módulos puedan tenerla en cuenta.
    *
    * @param $e     Evento que recibimos de Eventos
    * @param $args  Argumentos posibles
    */

   function ejecutar_mover_seccion($e, $args=NULL) {

      registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$e.','.depurar($args).')');

      global $gcm;

      $gcm->event->anular('contenido','contenidos');
      $gcm->event->anular('titulo','contenidos');
      $gcm->titulo = literal('Renombrando o moviendo sección',3);

      if ( count($gcm->seleccionado) > 1 ) {
         registrar(__FILE__,__LINE__,'Solo se puede seleccionar una sección, cogemos la primera como válida','AVISO');
         }

      $literal = $_POST['titulo_seccion'];
      $nombre_seccion = GUtil::textoplano($literal);
      $seccion_nueva = ( $gcm->seleccionado[0] ) ? $gcm->seleccionado[0] : Router::$dd.Router::$s; 
      $seccion_nueva = comprobar_barra($seccion_nueva).$nombre_seccion;
      $ruta_origen = Router::$dd.Router::$s;
      $gcm->memoria['seccion_movida'] = Router::$s;

      if ( $this->renombrar_seccion($ruta_origen,$seccion_nueva) ) {
         registrar(__FILE__,__LINE__,'Cambio de nombre realizado','AVISO');
         literal($nombre_seccion,1,$literal);
      } else {
         registrar(__FILE__,__LINE__,'Error al renombrar sección','ERROR');
         return FALSE;
         }

      $gcm->router->inicia($seccion_nueva);

      }

   /** 
    * Ejecutar nueva sección 
    *
    * @param $e     Evento que recibimos de Eventos
    * @param $args  Argumentos posibles
    */

   function ejecutar_nueva_seccion($e, $args=NULL) {

      registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$e.','.depurar($args).')');

      global $gcm;

      $gcm->event->anular('contenido','contenidos');
      $gcm->event->anular('titulo','contenidos');
      $gcm->titulo = literal('Nueva sección');

      $seccion_madre = $gcm->seleccionado[0];
      $seccion_nueva = GUtil::textoplano($_POST['titulo_seccion']);
      $literal_nuevo = $_POST['titulo_seccion'];
      $ruta_nueva_seccion = comprobar_barra($seccion_madre).$seccion_nueva;

      if ( $this->crear_nueva_seccion($ruta_nueva_seccion) ) {
         $mens="Nueva sección creada [".literal($seccion_nueva,1,$literal_nuevo).']';
         registrar(__FILE__,__LINE__,$mens,'AVISO');
         $gcm->router->inicia($ruta_nueva_seccion);
      } else {
         return FALSE;
         }

      }

   /**
    * Ejecutar borrado de contenido seleccionado
    *
    * La lista de archivos a borrar puede ser o $_POST['archivo'] o un array desde $_POST['seccion']
    *
    * @param $e     Evento que recibimos de Eventos
    * @param $args  Argumentos posibles
    */

   function ejecutar_borrar($e, $args=NULL) {

      global $gcm;

      $gcm->event->anular('contenido','contenidos');
      $gcm->event->anular('titulo','contenidos');
      $gcm->titulo = literal('Borrado de contenido',3);

      foreach ( $gcm->seleccionado as $item ) {

         if ( is_dir($item) ) {

            if ( !$this->eliminarDirectorio($item) ) {
               trigger_error(literal('Directorio').': '.$item.' '.literal('No se pudo borrar'), E_USER_ERROR);
               }

         } else {
            
            /* Comprobamos que exista ya que puede haber sido borrado al borrarse el 
             * directorio
             */

            if ( !$this->verificar_contenido($item) || !$this->borrar_contenido($item) ) {
               if ( count($gcm->seleccionado) == 1 && !$this->verificar_contenido($item) ) {
                  registrar(__FILE__,__LINE__,literal("No existe contenido a borrar",3),'AVISO');
                  }
               }

            }
         }

      array_lista($gcm->seleccionado,NULL,FALSE);
      registrar(__FILE__,__LINE__,literal("Contenido borrado",3),'AVISO');
      }

   /**
    * Metodo activado para el evento contenido
    *
    * @param $e     Evento que recibimos de Eventos
    * @param $args  Argumentos posibles
    */

   function devolver_contenido($url=NULL) {

      global $gcm;

      $url = ( $url ) ? $url : Router::$f;

      /* Comprobar sección */

      if ( ! $this->verificar_seccion(Router::$dd.Router::$s) ) {                                     
         registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$url.') No existe el documento lanzamos evento sincontenido');
         $gcm->event->lanzarEvento('sin_seccion');
         return FALSE;
         }

      /* Comprobar contenido */

      if ( Router::$c && Router::$c != 'index.html' && ! $this->verificar_contenido(Router::$dd.Router::$s.Router::$c) ) {
         registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$url.') No existe el documento lanzamos evento sincontenido');
         $gcm->event->lanzarEvento('sin_contenido');
         return FALSE;
         }

      /* Comprobar existencia de archivo */


      if ( $this->verificar_contenido($url) ) {

         return $this->getContenido($url);

      } elseif ( Router::$c == 'index.html' ) {

         registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$url.') Estamos en uns sección sin index.html, lanzamos contenido_dinamico');
         $gcm->event->lanzarEvento('contenido_dinamico');

      } else {

         registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$url.') No existe fichero ['.$url.']');
         return FALSE;

         }

      }

   /**
    * Título de artículo
    */

   function titulo_articulo() {

      global $gcm;

      $esBorrador = FALSE;

      if ( Router::$c == 'index.html' || ! Router::$c ) {
         $titulo=Router::$estamos;
      } else {

         if ( stripos(Router::$c,'.html') ) {
            $titulo=str_replace('.html','',Router::$c);
         } elseif ( stripos(Router::$c,'.btml') ) {
            $titulo=str_replace('.btml','',Router::$c);
            }

         try {
            if ($this->verificar_contenido(Router::$f) !== FALSE) {
               $fecha = presentarFecha($this->getFechaActualizacion(Router::$f),1,'unix');
            } else {
               throw new Exception('El contendo no existe [ '.Router::$f.' ]');
            }
         } catch (Exception $ex) {
            registrar(__FILE__,__LINE__,'No se pudo recoger fecha: '.$ex->getMessage(),'ADMIN');
            }
         }

      $titulo = ( isset($titulo) ) ? literal($titulo,1) : NULL;

      if ( $gcm->au->logeado() ) {

         if ( Router::$esBorrador ) $titulo = '<b title="'.literal('Borrador').'">(B)</b> '.$titulo;

         }

      include(dirname(__FILE__).'/../html/titulo_articulo.html');

      }



   }

?>
