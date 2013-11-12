<?php

/**
 * @file Ver_registros.php
 *
 * @category Gcm
 * @package Modulos
 * @subpackage Registro
 * @author    Eduardo Magrané <eduardo mamedu com>
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU/GPL
 * @version   SVN $Id: Ver_registros.php 661 2012-11-02 11:26:56Z eduardo $ 
 * @depends   General
 */

$GLOBALS['DIR_BASE'] = Router::$dir;

/** 
 * Modulo para presentar los registros de la aplizació
 */

class Ver_registros extends Modulos {

   // Configurables
   private $nombre_envio_email;   ///< Nombre del destinatario del email con registros
   private $mail_envio;           ///< Mail del destinatario
   private $nombre_remitente;     ///< Nombre del remitente
   private $mail_remitente;       ///< Mail del remitente
   private $periocidad;           ///< Cada cuantas horas enviamos el email
   private $dias_borrado;         ///< Se borran los registros con más de X días de antiguedad

   function __construct() { 

      global $gcm;

      parent::__construct(); 

      $this->nombre_envio_email = $this->config('Nombre envio registros');
      $this->mail_envio         = $this->config('Mail envio registros');        
      $this->nombre_remitente   = $this->config('Remitente');  
      $this->mail_remitente     = $this->config('Mail remitente');    
      $this->periocidad         = $this->config('Periocidad en horas');        
      $this->dias_borrado       = $this->config('Antiguedad de registros a borrar');      
      }

   /** 
    * Presentar caja de avisos 
    *
    * En caso de recibir un número de sesion en GET['mens'] presentamos
    * los avisos de la sesión especificada junto con la actual.
    *
    * Lo mismo si lo recibimos en _SESSION['mens']
    *
    */

   function presentar_caja_de_avisos() {

      global $gcm;

      $tipos = $gcm->reg->registros_sesion();

      if ( $tipos )  {

         foreach ($tipos as $tipo => $registros ) {
            include($gcm->event->instancias['temas']->ruta('ver_registros','html','presentar_avisos.html'));
            }

         }

      }

   /* Si estamos en modo DEBUG presentamos formulario de regitros */

   function debug() {

      global $gcm;

      require_once (GCM_DIR.'lib/int/registro/lib/RegistroGui.php');

      $reg = new RegistroGui($gcm->pdo_conexion(), $gcm->sufijo);

      if ( ! GCM_DEBUG ) {
         /* Añadimos algo de contenido para que no salga aviso en Plantilla de que falta */
         echo '<!-- debug -->';
         return;
         }
         
      /* Añadimos variables del sistema a los registros */

      if ( $_POST )    { registrar(__FILE__,__LINE__,'POST',FALSE,depurar($_POST)); }
      if ( $_GET )     { registrar(__FILE__,__LINE__,'GET',FALSE,depurar($_GET)); }
      if ( $_SESSION ) { registrar(__FILE__,__LINE__,'SSESSION',FALSE,depurar($_SESSION)); }
      registrar(__FILE__,__LINE__,'Constantes',FALSE,depurar(get_defined_constants()));
      registrar(__FILE__,__LINE__,'SERVER',FALSE,depurar($_SERVER));
      registrar(__FILE__,__LINE__,'EVENTOS',FALSE,depurar($gcm->event->eventos));
      registrar(__FILE__,__LINE__,'UBICACIONES',FALSE,depurar($gcm->event->ubicaciones));

      /* Panel de registros */
      ob_start();
      echo '<br />';
      $reg->tabla_registros(NULL,$gcm->reg->registros);
      $salida = ob_get_contents(); 
      ob_end_clean();

      if ( $salida ) {

         require_once(GCM_DIR.'/modulos/editar/lib/Editar.php');

         Temas::panel( array( 'titulo' => literal('Registros',3).' '.literal('de').' '.literal('página actual'), 
                                 'oculto' => TRUE, 
                                 'href' => 'javascript:visualizar(\'ver_registros_'.$gcm->reg->sesion.'\');', 
                                 'subpanel' => 'ver_registros_'.$gcm->reg->sesion, 
                                 'contenido' => $salida) );
         }

      }

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
