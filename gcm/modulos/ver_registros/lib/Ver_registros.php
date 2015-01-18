<?php

/**
 * @file Ver_registros.php
 *
 * @category Gcm
 * @package Modulos
 * @subpackage Registro
 * @author    Eduardo Magrané <edu lesolivex com>
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
   protected $nombre_envio_email;   ///< Nombre del destinatario del email con registros
   protected $mail_envio;           ///< Mail del destinatario
   protected $nombre_remitente;     ///< Nombre del remitente
   protected $mail_remitente;       ///< Mail del remitente
   protected $periocidad;           ///< Cada cuantas horas enviamos el email
   protected $dias_borrado;         ///< Se borran los registros con más de X días de antiguedad

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

   }
?>
