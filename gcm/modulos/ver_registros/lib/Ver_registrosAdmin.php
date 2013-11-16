<?php

/**
 * @file Ver_registrosAdmin.php
 *
 * @category Gcm
 * @package Modulos
 * @subpackage Registro
 * @author    Eduardo Magrané <eduardo mamedu com>
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU/GPL
 * @version   SVN $Id: Ver_registros.php 661 2012-11-02 11:26:56Z eduardo $ 
 * @depends   General
 */

require_once 'Ver_registros.php';

/** 
 * Modulo para presentar los registros de la aplizació
 */

class Ver_registrosAdmin extends Ver_registros {

   /**
    * Visualizar registros
    *
    * Presentamos formulario de registros
    */

   function visualizar() {

      global $gcm;


      $gcm->event->anular('titulo','ver_registros');
      $gcm->event->anular('contenido','ver_registros');
      $gcm->titulo = literal('Registros');

      $this->formulario();

      }

   function formulario($e=FALSE, $args=FALSE) {

      global $gcm;

      if ( !isset($_GET['formato']) || $_GET['formato'] !== 'ajax' ) {
         
         ?>
         <h1 id="heading">Registres de l'aplicació</h1>
         <?php

         }

      require_once (GCM_DIR.'lib/int/registro/lib/RegistroGui.php');

      $filtro = ( isset($_GET['filtro']) ) ? $_GET['filtro'] : FALSE ;

      $reg = new RegistroGui($gcm->reg->conexion, $gcm->sufijo);
      $reg->admin('interno',$filtro);

      }

   /**
    * Enviar email al programador con los mensajes del sistema
    *
    * Utilizamos las variables configurables:
    */

   function envio_registros_mail($e, $args=FALSE) {

      global $gcm;

      // Recopilamos mensajes con fecha mayor a ahora - $periocidad
      $fecha_periocidad = time() - ( $this->periocidad * 60 * 60 );

      $filtro = 'fecha > '.$fecha_periocidad.' ORDER BY id desc';

      $registros = $gcm->reg->ver_registros(NULL,$filtro);

      if ( !is_array($registros) || count($registros) < 1 ) {
         registrar(__FILE__,__LINE__,'Sin registros a enviar');
         return ;
         }

      $resultado  = '<pre>

         0: id
         1: sesion
         2: fecha
         3: tipo
         4: fichero
         5: linea
         6: mensaje
         7: descripcion
         ';
      $resultado .= '<pre>'.depurar($registros).'</pre>';

      // Enviamos email

      $args['cuerpo']  = $resultado;
      $args['asunto']  = 'Registros en '.$gcm->config('admin','Proyecto');
      $args['mail']    = $this->mail_envio;
      $args['nombre_destinatario'] = $this->nombre_envio_email;
      $args['nombre_remitente'] = $this->nombre_remitente;
      $args['mail_remitente'] = $this->mail_remitente;

      registrar(__FILE__,__LINE__,'Enviamos email con los registros','ADMIN');

      $gcm->event->lanzar_accion_modulo('enviomail','enviar_email',$e, $args);
      }

   /**
    * Borrar registros antiguos
    */

   function borrar_registros_antiguos($e, $args=FALSE) {

      global $gcm;

      if ( $this->dias_borrado == 0 ) return;

      $salida = $gcm->reg->borrado_registros_antiguos($this->dias_borrado);

      if ( $salida === FALSE ) {
         registrar(__FILE__,__LINE__,'Error en el borrado de registros','ERROR');
      } else {
         registrar(__FILE__,__LINE__,'Borrado de registros con más de '.$this->dias_borrado.' días','ADMIN');
         }

      }

   }
?>