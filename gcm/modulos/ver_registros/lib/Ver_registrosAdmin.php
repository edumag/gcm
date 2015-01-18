<?php

/**
 * @file Ver_registrosAdmin.php
 *
 * @category Gcm
 * @package Modulos
 * @subpackage Registro
 * @author    Eduardo Magrané <edu lesolivex com>
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
    * Enviar email al programador con los mensajes del sistema en un archivo
    * csv
    *
    * Utilizamos las variables configurables:
    *
    * @todo Poner exportación a cvs en Registros.php
    */

   function envio_registros_mail($e, $args=FALSE) {

      global $gcm;

      // Recopilamos mensajes con fecha mayor a ahora - $periocidad
      $fecha_periocidad = time() - ( $this->periocidad * 60 * 60 );

      $filtro = 'fecha > '.$fecha_periocidad.' ORDER BY id desc';

      if ( $gcm->reg->conexion->getAttribute(constant("PDO::ATTR_DRIVER_NAME")) == 'sqlite' ) {

         $sql = 'SELECT id
            ,strftime("%d/%m/%Y %H:%M:%S", datetime(fecha,"unixepoch")) as fecha
            ,tipo
            ,mensaje
            , "sesion: " || sesion || " " || fichero || ":" || linea || "\n\n" || descripcion as descripcion  
            FROM '.$gcm->sufijo.'registros
            ';

      } else {
         $sql= 'SELECT id
            ,DATE_FORMAT(FROM_UNIXTIME(fecha),"%d/%m/%y %T") as fecha
            ,tipo
            ,mensaje
            ,CONCAT("sesion: ",sesion," ",fichero,":",linea,"\n\n",descripcion) as descripcion  
            FROM '.$gcm->sufijo.'registros
         ';
         }

      $sql .= 'WHERE '.$filtro;

      require_once GCM_DIR.'lib/int/GcmPDO/lib/GcmPDO.php';
      $export = new GcmPDO($gcm->reg->conexion, $sql);
      $adjunto = $export->to_csv('registros-'.$gcm->config('admin','Proyecto').'-'.date("Y-m-d").'csv', FALSE); 
      
         
      if ( ! $adjunto ) {
         registrar(__FILE__,__LINE__,'Sin registros a enviar');
         return ;
         }

      // Enviamos email

      $args['cuerpo']  = literal('Registros de los últimos'.' '.$this->periocidad.' '.literal('días'));
      $args['asunto']  = 'Registros en '.$gcm->config('admin','Proyecto');
      $args['mail']    = $this->mail_envio;
      $args['nombre_destinatario'] = $this->nombre_envio_email;
      $args['nombre_remitente'] = $this->nombre_remitente;
      $args['mail_remitente'] = $this->mail_remitente;
      $args['adjuntos'] = array($adjunto);

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
