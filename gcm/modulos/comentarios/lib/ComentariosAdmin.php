<?php

/** 
 * @file ComentariosAdmin.php
 * @brief Administración para los comentarios
 */

require_once(dirname(__FILE__).'/Comentarios.php');

/** 
 * @class ComentariosAdmin
 * @brief Métodos administrativos para comentarios
 *
 * @ingroup modulo_comentarios
 */

class ComentariosAdmin extends Comentarios {

   /** Constructor */

   function __construct() {

      parent::__construct();

      }

   /**
    * Listar comentarios para administración
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

      $sql = 'SELECT c.id,c. fecha_creacion `fecha creación`, 
         c.url, c.contenido , 
         c.nombre, c.mail, c.comentario  
         FROM '.$this->tabla.' c ORDER BY c.fecha_creacion desc';

      $gcmpdo = new GcmPDO($this->pdo, $sql);
      $array = $gcmpdo->to_array();
      $opciones = array ('url'=>'?comentario='
         , 'Identificador'=>'id'
         , 'table_id'=>'commentarios'
         , 'ver'=>'ver'
         , 'modificar'=>'modificar'
         , 'eliminar'=>'eliminar'
         , 'ocultar_id'=>TRUE
         , 'accion'=>'accion'
         , 'fila_unica'=>'comentario'
         , 'enlaces'=> array('url' => array('campo_enlazado'=>'contenido'
                                           ,'titulo_columna'=>'Contenido'
                                           ,'base_url'=>Router::$base
                                        )
                            )
         );


      if ( $gcmpdo->validar() ) {
         $array2table = new TinyTable();
         $array2table->generar_tabla($array, $opciones);
      } else {
         echo literal('No hay comentarios para listar');
         }

      return;

      }

   /**
    * Activación de un comentarios
    */

   function activar($e, $args) {

      if ( ! permiso('moderar_comentarios') ) return ;
   
      global $gcm;

      require_once(dirname(__FILE__).'/../modelos/comentarios_dbo.php');

      if ( !empty($args) && is_array($args) ) {
         $id = $args[0] ;
      } elseif ( !empty($args) && ! is_array($args) ) {
         $id = $args ;
      } elseif ( !empty(Router::$args) ) {
         $id = Router::$args[0];
      } else {
         registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$e.','.depurar($args).') Sin identificaor no se puede borrar comentario','ERROR');
         echo "FALSE";
         exit();
         }

      $comentario = new Comentarios_dbo($this->pdo, $id);
      $comentario->setActivado(1);
      $comentario->save();

      $gcm->event->lanzar_accion_modulo('cache_http','borrar','comentario_activado',Router::$s.Router::$c);
      if ( Router::$formato == 'ajax' ) {
         echo $id;
         exit();
      } else {
         registrar(__FILE__,__LINE__,literal('comentario').' '.literal('activado'),'AVISO');
         }

      }

   }
