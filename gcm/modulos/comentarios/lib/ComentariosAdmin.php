<?php

/** Comentarios.php
 *
 * Modulo Comentarios
 *
 */

require_once(dirname(__FILE__).'/Comentarios.php');

/** Comentarios
 *
 * Este módulo nos permite que los usuarios entren comentarios, los mismos se guardaran en
 * una carpeta oculta de la sección correspondiente.
 *
 */

class ComentariosAdmin extends Comentarios {

   /** Constructor */

   function __construct() {

      parent::__construct();

      }


   /**
    * Eliminar comentario
    */

   function eliminar($e, $args=NULL) {

      permiso(8);
   
      global $gcm;

      require_once(dirname(__FILE__).'/../modelos/comentarios_dbo.php');

      if ( !empty($args) ) {
         $id = $args ;
      } elseif ( !empty(Router::$args) ) {
         $id = Router::$args;
      } else {
         registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$e.','.depurar($args).') Sin identificaor no se puede borrar comentario','ERROR');
         echo "FALSE";
         exit();
         }

      $comentario = new Comentarios_dbo($this->pdo, $id);
      $gcm->event->lanzar_accion_modulo('cache_http','borrar','comentario_eliminado',$id);

      $comentario->MarkForDeletion();
      registrar(__FILE__,__LINE__,literal('Comentario borrado'),'AVISO');
      if ( Router::$formato == 'ajax' ) {
         echo $id;
         exit();
         }

      }

   /**
    * Modificar comentario
    */

   function modificar($e, $args=NULL) {

      permiso(8);
   
      global $gcm;

      $id = $args;
      $this->formulario('modificar_comentario',$id);

      }

   /**
    * Ejecutar Modificar comentario
    */

   function ejecutar_modificar_comentario($e, $args=NULL) {

      permiso(8);
   
      global $gcm;

      $mens="Modificar comentario";
      registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$e.','.depurar($args).') '.$mens);

      require_once(dirname(__FILE__).'/../modelos/comentarios_dbo.php');

      $resultado = $this->validar_datos($_POST);

      if ( isset($resultado['id']) ) {

         $comentario = new Comentarios_dbo($this->pdo, $resultado['id']);

         if ( $resultado ) {

            $comentario->setFecha(time());
            $comentario->setNombre($resultado['usuario']);
            $comentario->setMail($resultado['mail']);
            $comentario->setUrl(Router::$s.Router::$c);
            $comentario->setContenido(str_replace('.html','',Router::$c));
            $comentario->setComentario($resultado['texto']);
            $comentario->save();

            registrar(__FILE__,__LINE__,literal('Comentario Modificado'),'AVISO');

            $gcm->event->lanzar_accion_modulo('cache_http','borrar','comentario_modificado',Router::$url);
            }

      } else {
         
         $mens=literal("Sin Identificador no se puede borrar comentario",3);
         registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$e.','.depurar($args).') '.$mens);

         }


      }

   /**
    * Listar comentarios
    */

   function listar($e, $args) {

      global $gcm;

      permiso(8);

      $gcm->event->anular('titulo','contenido');
      $gcm->event->anular('contenido','contenido');

      if ( isset($_GET['accion']) && $_GET['accion'] == 'eliminar' ) {
         $this->eliminar('interno',$_GET['comentario']);
         }

      $gcm->titulo = literal('Listado de comentarios',3);

      require_once(GCM_DIR.'lib/int/array2table/lib/TinyTable.php');
      require_once(GCM_DIR.'lib/int/GcmPDO/lib/GcmPDO.php');

      $condicion = ( $args['id'] ) ? " AND c.id=".$args['id'] : '';
      // $campo =  ( ! $id_proyecto ) ? " p.nombre as Proyecto," : '';

      $sql = "SELECT c.id, c.url, c.fecha, c.nombre, c.mail, c.contenido, c.comentario  
         FROM ".$this->tabla." c ORDER BY c.fecha desc";

      $gcmpdo = new GcmPDO($this->pdo, $sql);
      $array = $gcmpdo->to_array();
      $opciones = array (
         'url'=>'?comentario='
         , 'identificador'=>'id'
         , 'modificar'=>'modificar'
         , 'eliminar'=>'eliminar'
         , 'ver'=>'ver'
         , 'accion'=>'accion'
         );


      if ( $gcmpdo->validar() ) {
         $array2table = new TinyTable();
         $array2table->generar_tabla($array, $opciones);
      } else {
         echo literal('No hay comentarios para listar');
         }

      return;

      }
   }
