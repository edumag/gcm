<?php

/**
 * @file      Comentarios.php
 * @brief     Módulo para comentarios
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 *   Created  04/02/11
 *  Revision  SVN $Id: Comentarios.php 650 2012-10-04 07:17:48Z eduardo $
 * Copyright  Copyright (c) 2011, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

/** 
 * @class Comentarios
 * @brief Este módulo nos permite que los usuarios entren comentarios
 *
 * @todo Funciona la comprobación de usuario con sesión pero faltaria la comprobación de un usuario registrado
 *       que añade comentario o modifica.
 * @todo Evitar spam comprobando enlaces en texto de comentario
 * @todo Comprobar email en caso de que queramos hacerlo
 * @todo Configurar módulo número que se presentan etc...
 * @todo Modiicar el campo contenido, si hay evento de cambio de nombre de un contenido
 *
 * @ingroup modulo_comentarios
 */

class Comentarios extends Modulos {

   protected $pdo     = NULL;                        ///< Instancia a la base de datos
   protected $prefijo = NULL;                        ///< Prefijo para la tabla
   protected $tabla   = NULL;                        ///< Nombre de la tabla con prefijo
   protected $tipo_base_datos ;                      ///< Tipo de base de datos ( mysql o sqlite )

   private $moderacion;                              ///< Activación de moderación de comentarios TRUE/FALSE

   /**
    * Saber si hemos cargado ya el javascript del editor web
    */

   private $cargado_javascript = FALSE;

   /** Constructor */

   function __construct() {

      global $gcm;

      $this->pdo     = $gcm->pdo_conexion();
      $this->prefijo = $gcm->sufijo;
      $this->tabla   = $this->prefijo.'comentarios';

      $this->tipo_base_datos = $this->pdo->getAttribute(constant("PDO::ATTR_DRIVER_NAME"));

      /** Comprobar la existencia de la tabla comentarios, sino la creamos */

      $SQL = 'SELECT COUNT(*) FROM '.$this->tabla;

      if ( !$this->pdo )  { return FALSE; }

      try {

         $sth = $this->pdo->prepare($SQL);
         $sth->execute(); 

      } catch (Exception $ex) {

            registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'() Creamos tabla comentarios');
            $this->generarTablaComentarios($this->pdo);

         }

      parent::__construct();

      $this->moderacion = $this->config('moderación');

   }

   /** Últimos comentarios
    *
    * Presentar los últimos comentarios
    *
    * @author Eduardo Magrané
    *
    */

   function ultimos($e, $args=NULL) {

      global $gcm;

      $num_items_df = 5;

      $parametros = recoger_parametros($args);

      $num = ( isset($parametros['num']) ) ? $parametros['num'] : $num_items_df ;

      if ( !$this->pdo )  { return FALSE; }

      require_once(GCM_DIR.'lib/int/GcmPDO/lib/paginarPDO.php');

      $SQL  = "SELECT fecha_creacion, nombre, url, contenido, comentario, activado, usuarios_id FROM ".$this->tabla;
      if ( $this->moderacion && ! permiso('moderacion_comentarios') ) 
         $SQL .= " WHERE ( activado = 1 OR usuarios_id = '".session_id()."')";

      $SQL .= " ORDER BY fecha_creacion desc";

      $pd = new PaginarPDO($this->pdo, $SQL, 'ult_', $num);

      if ( $pd->validar() ) {

         ob_start();
         $pd->plantilla_resultados=dirname(__FILE__).'/../html/ultimos.html';
         $pd->pagina();
         $salida = ob_get_contents();
         ob_end_clean();
         if ( $salida  ) {
            $panel = array();
            $panel['titulo'] = literal('Últimos comentarios');
            $panel['contenido'] =$salida;
            Temas::panel($panel);
            }


         return;
         }

      }

   /**
    * Comprobar permisos
    *
    * - Si tiene permisos para moderar TRUE
    * - Si es el autor del comentario TRUE
    * - Si es anonimo, pero en su sesion queda reflejada la autoria TRUE
    * - Si no FALSE
    */

   function comprobar_permisos($autor_id=FALSE) {

      global $gcm;

      if ( permiso('moderar_comentarios') ) return TRUE ;

      if ( $autor_id && $autor_id == $gcm->au->id() ) return TRUE;

      if ( $autor_id && $autor_id == sesion('autor_comentario') ) return TRUE;

      return FALSE ;

      }

   /** Acciones del modulo comentarios
    * 
    * Listamos comentarios si los hay y presentamos formulario para añadir uno nuevo
    */

   function presentar_comentarios($e, $args=NULL) {

      global $gcm;

      $num_items_df = 5;

      $parametros = recoger_parametros($args);

      $num = ( isset($parametros['num']) ) ? $parametros['num'] : $num_items_df ;

      // Añadimos javascript para eliminar o borrar comentarios
      $this->javascripts('comentarios.js');

      require_once(GCM_DIR.'lib/int/GcmPDO/lib/paginarPDO.php');

      $SQL  = "SELECT id, fecha_creacion, nombre, url, contenido, comentario, activado, usuarios_id 
         FROM ".$this->tabla." WHERE url='".Router::$url."'";

      // Con moderación activada mostramos solo activos o del usuario de sesión

      if ( $this->moderacion && ! permiso('moderacion_comentarios') ) 
         $SQL .= " AND ( activado = 1 OR usuarios_id = '".session_id()."')";

      $SQL .= " ORDER BY fecha_creacion desc";

      $pd = new PaginarPDO($this->pdo, $SQL, 'comentarios_');

      if ( $pd->validar() ) {

         $pd->elementos_pagina=$num;
         $pd->plantilla_resultados=dirname(__FILE__).'/../html/listado.html';
         if ( Router::$formato != 'ajax'  ) echo '<h2>Comentarios</h2>';
         $pd->generar_pagina('&m=comentarios&a=presentar_comentarios&formato=ajax');
         }

   }

   /**
    * Presentamos formulario para añadir comentarios, solo
    * si tenemos contenido
    */

   function formulario($e, $args=NULL) {

      global $gcm;

      /* si no esta cargado el javascript de editorweb lo cargamos */

      if ( !$this->cargado_javascript && Router::$formato != 'ajax') {
         $this->cargado_javascript = TRUE;
         echo '<script type="javascript" src="'.Router::$dir.GCM_DIR.'lib/ext/tiny_mce/tiny_mce.js"></script>';
         }

      include_once(GCM_DIR.'lib/int/captcha/captcha.php');

      $id = $args;
      $accion = ($id) ? 'ejecutar_modificar_comentario' : 'verificar_entrada';

      if ( Router::$c && Router::$c !== 'index.html' ) {


         $usuario = FALSE;
         $mail    = FALSE;
         $texto   = FALSE;

         if ( $e == 'modificar_comentario' ) {
            
            require_once(dirname(__FILE__).'/../modelos/comentarios_dbo.php');

            $comentario = new Comentarios_dbo($this->pdo, $id);
            $usuario = $comentario->getNombre();
            $mail    = $comentario->getMail();
            $texto   = $comentario->getComentario();
            $id_user = $comentario->getUsuarios_id();

            if ( ! permiso('moderar_comentarios') && $id_user != session_id() ) return ;

            $titulo = literal('Modificar comentario');
            $ocultar = FALSE;

         } else {
            
            $titulo = literal('Añadir comentario');
            $ocultar = TRUE;

            }

         ob_start();
         echo "<div id='form_com_".Router::$url."' class='form_comentario' style='label: Añadir comentario;'>\n";
         include($gcm->event->instancias['temas']->ruta('comentarios','html','form_comentario.html'));
         echo "</div>\n";

         /* Si el formato es ajax tenemmos que iniciar editor web para textarea */

         if ( Router::$formato == 'ajax'  ) {
            ?>
            <script type="text/javascript">
            <?php include($gcm->event->instancias['temas']->ruta('comentarios','js','editorweb.js')); ?>
            </script>
            <?php
         } else {
            ?>
            <script language="javascript" type="text/javascript" 
            src="<?php echo GCM_DIR_P ?>lib/ext/tiny_mce/tiny_mce.js"></script>
            <?php
            $this->javascripts('editorweb.js');
            }

         $salida = ob_get_contents();
         ob_end_clean();

         $panel = array();
         $panel['titulo']    = $titulo;
         $panel['contenido'] = $salida;
         $panel['oculto']    = $ocultar;
         Temas::panel($panel);

         if ( Router::$formato == 'ajax'  ) {
            ?>
            <script type="text/javascript">
            $(".subpanel_oculto").hide(3000);
            paneles();
            </script>
            <?php
            }
      }
   }

   /**
    * Generar la tabla comentarios
    */

   function generarTablaComentarios() {

      global $gcm;

      switch($this->tipo_base_datos) {

         case 'sqlite':

            $mens="creamos la tabla de comentarios";
            registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'() '.$mens);
            $SQL="CREATE TABLE ".$this->tabla." (
               id INTEGER PRIMARY KEY ,
               url CHAR(150) ,
               fecha_creacion TIME NOT NULL DEFAULT CURRENT_TIMESTAMP ,
               nombre CHAR(60) ,
               mail CHAR(60) ,
               contenido CHAR(100),
               comentario CHAR(500),
               activado INTEGER,
               padre INTEGER,
               usuarios_id CHAR(40)
               )";

            if ( ! $sqlResult = $this->pdo->query($SQL) ) {

               registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'() ERROR al crear tabla '.$this->tabla.' '.$pdo_error,'ERROR');
               return FALSE;
            } else {

               registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'() Se creo la tabla '.$this->tabla );
               return TRUE;

               }
            break;

         case 'mysql':

            $mens="creamos la tabla de comentarios";
            registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'() '.$mens);
            $SQL="CREATE TABLE ".$this->tabla." (
               id MEDIUMINT NOT NULL AUTO_INCREMENT ,
               url CHAR(150) ,
               fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
               nombre CHAR(60) ,
               mail CHAR(60) ,
               contenido CHAR(100),
               comentario TEXT,
               activado TINYINT,
               padre MEDIUMINT,
               usuarios_id CHAR(40),
               PRIMARY KEY (id)
               )";

            if ( ! $sqlResult = $this->pdo->query($SQL) ) {

               registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'() ERROR al crear tabla '.$this->tabla.' '.$pdo_error,'ERROR');
               return FALSE;
            } else {

               registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'() Se creo la tabla '.$this->tabla );
               return TRUE;

               }
            break;

         default:
            registrar(__FILE__,__LINE__,'El driver ['.$driver.'] para la base de datos no está soportado','ERROR');
            break;

         }

      }

   /**
    * Validar datos de entrada para comentarios
    *
    * @param $aDatos Array con los datos
    */

   function validar_datos($aDatos) {

      $pasa_comentarios = TRUE;

      /** Tags aceptados en los comentarios */
      $tags_aceptados = '<br><p><u>,<li>,<ul>,<em>,<strike>,<strong>,<blockquote>,<ol>';

      if ( empty($aDatos['texto'])  ) {
         $pasa_comentarios = FALSE;
         $mens = literal('Campo de comentario vacio',3);
         registrar(__FILE__,__LINE__,$mens,'AVISO');
         }

      $aDatos['texto'] = strip_tags($aDatos['texto'], $tags_aceptados);

      if ( empty($aDatos['texto'])  ) {
         $pasa_comentarios = FALSE;
         $mens = literal('Campo de comentario con etiquetas no permitidas',3);
         registrar(__FILE__,__LINE__,$mens,'AVISO');
         }

      if ( empty($aDatos['usuario']) || ! isset($aDatos['usuario']) ) {
         $pasa_comentarios = FALSE;
         $mens=literal("Se necesita un nombre de usuario");
         registrar(__FILE__,__LINE__,$mens,'AVISO');
         }

      if ( empty($aDatos['texto']) || ! isset($aDatos['texto']) ) {
         $pasa_comentarios = FALSE;
         $mens=literal("No hay contenido en el comentario");
         registrar(__FILE__,__LINE__,$mens,'AVISO');
         }

      if ( ! $pasa_comentarios ) return FALSE;
         
      return $aDatos;

      }

   /**
    * Insertar comentario a la base de datos
    */

   function verificar_entrada($e, $args=NULL) {

      global $gcm;

      /* Acciones ajax */

      /* Acciones para formulario comentarios */

      $mens="Añadir nuevo comentario";
      registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$e.','.depurar($args).') '.$mens);

      /* Evitar spam */

      include_once(GCM_DIR.'lib/int/captcha/captcha.php');

      if ( ! verificarCaptcha(4) ) {
         $mens = literal('No se pudo añadir comentario. ');
         $mens .= literal('Si no estas haciendo spam puedes enviar un correo al autor');
         registrar(__FILE__,__LINE__,$mens,'AVISO');
         return FALSE;
         }

      require_once(dirname(__FILE__).'/../modelos/comentarios_dbo.php');

      $comentario = new Comentarios_dbo($this->pdo);

      $resultado = $this->validar_datos($_POST);

      if ( $resultado ) {

         $comentario->setNombre($resultado['usuario']);
         $comentario->setMail($resultado['mail']);
         $comentario->setUrl(Router::$s.Router::$c);
         $comentario->setContenido(str_replace('.html','',Router::$c));
         $comentario->setComentario($resultado['texto']);
         if ( $gcm->au->id() ) {
            $comentario->setActivado(1);
            $comentario->setUsuarios_id($gcm->au->id());
         } else {
            $comentario->setUsuarios_id(session_id());
            }
         $comentario->save();
         $resultado['id'] = $comentario->ultimo_identificador();

         registrar(__FILE__,__LINE__,literal('Comentario insertado'),'AVISO');

         /** Enviamos email */

         $args['cuerpo']  = "<h1>".literal(Router::$c)."</h1>";
         $args['cuerpo']  = "\n";
         $args['cuerpo'] .= "\n<br />Usuario: <b>".$resultado['usuario']."</b>";
         $args['cuerpo'] .= "\n<br />Mail: <b>".$resultado['mail']."</b>";
         $args['cuerpo'] .= "\n<br />Texto: ";
         $args['cuerpo'] .= "\n<b>".$resultado['texto']."</b>";
         $args['cuerpo'] .= "\n<br />";
         $args['cuerpo'] .= "\n<a href='".$_SERVER['HTTP_REFERER']."'>Ir a entrada</a>";
         $args['cuerpo'] .= "\n<br />";
         $args['cuerpo'] .= "\n<br /><em>Ip de usuario: ".mostrar_ip()."</em>";

         $args['asunto'] = 'Comentario añadido';

         $gcm->event->lanzar_accion_modulo('enviomail','enviar_email','comentario_añadido',$args);
         $gcm->event->lanzar_accion_modulo('cache_http','borrar','comentario_insertado',Router::$url);

         }

      }

   /**
    * Eliminar comentario
    */

   function eliminar($e, $args=NULL) {

   
      if ( ! permiso('moderar_comentarios') && $fila->usuarios_id != session_id() ) return ;

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
      $comentario->MarkForDeletion();
      $gcm->event->lanzar_accion_modulo('cache_http','borrar','comentario_eliminado',Router::$s.Router::$c);
      if ( Router::$formato == 'ajax' ) {
         echo $id;
         exit();
      } else {
         registrar(__FILE__,__LINE__,literal('Comentario borrado'),'AVISO');
         }

      }

   /**
    * Modificar comentario
    */

   function modificar($e, $args=NULL) {

      global $gcm;

      $id = $args;
      $this->formulario('modificar_comentario',$id);

      }

   /**
    * Ejecutar Modificar comentario
    */

   function ejecutar_modificar_comentario($e, $args=NULL) {

      global $gcm;

      $mens="Modificar comentario";
      registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$e.','.depurar($args).') '.$mens);

      require_once(dirname(__FILE__).'/../modelos/comentarios_dbo.php');

      $resultado = $this->validar_datos($_POST);

      if ( isset($resultado['id']) ) {

         $comentario = new Comentarios_dbo($this->pdo, $resultado['id']);

         if ( ! permiso('moderar_comentarios') && $comentario->getUsuarios_id() != session_id() ) {
            registrar(__FILE__,__LINE__,'Acción no permitida','ERROR');
            return ;
            }

         if ( $resultado ) {

            $comentario->setNombre($resultado['usuario']);
            $comentario->setMail($resultado['mail']);
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


   }

?>
