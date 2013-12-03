<?php

/**
 * Gcm - Gestor de contenido mamedu
 *
 * @category Gcm
 * @package Modulos
 * @subpackage enviomail
 * @author    Eduardo Magrané <eduardo mamedu com>
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU/GPL
 * @version   SVN $Id: Enviomail.php 660 2012-11-01 19:37:28Z eduardo $ 
 */

include_once(GCM_DIR.'lib/ext/phpMailer/class.phpmailer.php');

/** funciones para el envio de emails desde proyecto
 *
 * Estas funciones nos facilitaran el envio de emails en formato
 * html con el contenido de cada proyecto.
 *
 * @author Eduardo Magrané
 *
 */

class Enviomail extends Modulos {

   /** enviaMailHtml
    *
    * Creamos el formato del email en base a la plantilla
    *
    * @param asunto Asunto del mail
    * @param contenidoMail Cuerpo del mail
    * @param nombreDestinatario Nombre del destinatario
    * @param emailDestinatario
    * @param nombreRemite
    * @param emailRemitente
    * @param ocultos Array con los pares mail nombre de las direcciones que queramos enviar de forma oculta
    * @param html true/false
    *
    * @author Eduardo Magrané
    * @version 1.0
    *
    */

   function enviaMailHtml($asunto,$contenidoMail,$nombreDestinatario,$emailDestinatario,$nombreRemite,$emailRemitente,$ocultos=NULL,$html='true', $adjuntos=FALSE){

      global $gcm;

      $proyecto = $gcm->config('admin','Proyecto');

      if ( $html == 'true' ) {
      
         $plantilla['url_absoluta'] = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['REQUEST_URI']).'/';
         $plantilla['titulo'] = literal($proyecto,1);
         $plantilla['fecha'] = presentarFecha(time());
         $plantilla['contenido'] = $contenidoMail;

         $contenido = get_include_contents($gcm->event->instancias['temas']->ruta('enviomail','html','mail.html'),$plantilla);

      } else {
         
         $contenido = $contenidoMail;
      }

      if ( $this->procesaEnvioMail($asunto,$contenido,$nombreDestinatario,$emailDestinatario,$nombreRemite,$emailRemitente,$ocultos,$html,$adjuntos) ) {
         return TRUE;
      } else {
         return FALSE;
        }
      }

   /** procesaEnvioMail 
    *
    * enviamos email ya con formato establecido desde enviaMailHtml
    *
    * @param asunto
    * @param contenidoMail
    * @param nombreDestinatario
    * @param emailDestinatario
    * @param nombreRemite
    * @param emailRemitente
    * @param ocultos
    * @param html true/false
    * @param $adjuntos Array con ficheros adjuntos array('ruta_fichero','ruta_otro_fichero')
    *
    * @author Eduardo Magrané
    * @version 1.0
    *
    */

   function procesaEnvioMail($asunto,$contenidoMail,$nombreDestinatario,$emailDestinatario,$nombreRemite,$emailRemitente,$ocultos=NULL, $html='true', $adjuntos=FALSE){

      global $gcm;

      // Idioma de sessión o el predeterminado del proyecto
      $idioma = ($_SESSION[$gcm->config('admin','Proyecto')."-idioma"]) ? $_SESSION[$gcm->config('admin','Proyecto')."-idioma"] : LG_PRE;

      // Si el formato es html y tenemos imagenes en contenido, hay que transformar su src para que phpMailer las
      // proceso cmo adjuntos automaticamente.

      if ( $html == 'true' )  {

         $contenidos = explode(' ',$contenidoMail);
         $contenido = '';
         foreach ( $contenidos as $palabra ) {
            if ( stripos($palabra,'src') !== FALSE ) {
               $contenido .=  ereg_replace( "src=('|\")(.*)File/(.*)('|\")", "src=\"File/\\3\"", $palabra );
               $contenido .= ' ';
            } elseif ( stripos($palabra,'href') !== FALSE  ){
               $contenido .= preg_replace("/href=('|\")(.*)File\/(.*)('|\")/", "href=\"File/\3\"", $palabra );
               $contenido .= ' ';
            } else {
               $contenido .= $palabra.' ';
               }
            }

      } else {

         $contenido = $contenidoMail;
         
      }

      $mail = new PHPMailer();
      //$mail->IsSendmail(); // Envio mediante sendmail no funciona en servidor
      $mail->SetLanguage($idioma);
      $mail->CharSet = "UTF-8";
      $mail->From = $emailRemitente;
      $mail->FromName = $nombreRemite;
      $mail->Subject = $asunto;
      $mail->AddAddress($emailDestinatario,$nombreDestinatario);
      if ( $html == 'true' )  {
         $mail->IsHTML($html);
         $mail->AltBody = literal('Para ver el email, necesita compatibilidad con html');
         $mail->MsgHTML($contenido);
      } else {
         $mail->Body = $contenido;
      }

      // Si tenemos destinatarios ocultos los incluimos
      if ( $ocultos ) {
         foreach( $ocultos as $email => $nom ) {
            $mail->AddBCC($email, $nom);
            }
         }

      // Adjuntos
      if ( $adjuntos ) {
         foreach ( $adjuntos as $adjunto ) {
            $mail->AddAttachment($adjunto, basename($adjunto));
            }
         }

      if(!$mail->Send()) {
         trigger_error(literal('Error').' '.literal('enviando email').': '.$mail->ErrorInfo, E_USER_ERROR);
         return FALSE;
      } else {
         $mens =  'Email enviado a ['.$emailDestinatario.']';   
         $mens .= 'De              ['.$emailRemitente.']';   
         $mens .= 'Asunto          ['.$asunto.']';   
         registrar(__FILE__,__LINE__,$mens);
         return TRUE;
         }

   }

   /** Formulario */

   function formulario() {
      ob_start();
      include(dirname(__FILE__).'/../html/form_enviomail.html');
      $salida = ob_get_contents();
      ob_end_clean();

      $panel = array();
      $panel['titulo'] = literal('Informe de errores',3);
      $panel['oculto'] = TRUE;
      $panel['contenido'] =$salida;

      Temas::panel($panel);



      }

   /** Envio de email
    *
    * @todo Configurar email local o sevidor
   */

   function enviar_email_error() {
   
      global $gcm;

      registrar(__FILE__,__LINE__,'Enviando mail de usuario con error');

      /* Comprobar que recibimos datos de formulario */

      if ( !$gcm->au->logeado() ) {

         registrar(__FILE__,__LINE__,'No se puede enviar informe sin ser administrador','AVISO');

      } else {

         // Mirar si estamos en local o en servidor para enviar a uno u otro sitio
         if ( $_SERVER['HTTP_HOST'] == 'localhost' ) {
            $mail_dev = 'eduardo@localhost';
         } else {
            $mail_dev = 'eduardo@mamedu.com';
            }

         $salida = var_export($_SESSION,TRUE);

         if ( !empty($_POST['comentario_usuario']) ) {
            $contenido_mail .=  "\n".'------------------';
            $contenido_mail  =  "\n".'Comentario usuario';
            $contenido_mail .=  "\n".'------------------';
            $contenido_mail .= "\n";
            $contenido_mail .= "\n".strip_tags($_POST['comentario_usuario']);
            $contenido_mail .= "\n";
            }

         $contenido_mail .= "\n".$salida;

         if ( $this->enviaMailHtml('Error en gcm', $contenido_mail, 'Eduardo', $mail_dev, 'Robot de gcm', 'admin@mamedu.com',NULL,'false') ) {

            registrar(__FILE__,__LINE__,literal('El email a sido enviado',3).' '.literal('Gracias',3),'AVISO');
            return TRUE;
         } else {
            return FALSE; 
            }

         }
      
      }

   /** Envio de email */

   function enviar_email($e, $args) {
   
      global $gcm;

      registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$e.','.depurar($args).') ');

      if ( isset($args['mail']) ) {
         $mail = $args['mail'];
      } else {
         // Mirar si estamos en local o en servidor para enviar a uno u otro sitio
         if ( $_SERVER['HTTP_HOST'] == 'localhost' ) {
            $mail = 'eduardo@mamedu.com';
         } else {
            $mail = 'eduardo@mamedu.com';
            }
         }

      if ( !isset($args['cuerpo']) ) {
         registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$e.','.depurar($args).') 
            Sin contenido para enviar','ERROR');
         return FALSE;
         }

      if ( isset($args['nombre_destinatario']) ) {
         $nombre_destinatario = $args['nombre_destinatario'];
      } else {
         $nombre_destinatario = 'Eduardo';
         }

      if ( isset($args['nombre_remitente']) ) {
         $nombre_remitente = $args['nombre_remitente'];
      } else {
         $nombre_remitente = 'Eduardo';
         }

      if ( isset($args['mail_remitente']) ) {
         $mail_remitente = $args['mail_remitente'];
      } else {
         $mail_remitente = 'eduardo@mamedu.com';
         }


      $contenido_mail =  $args['cuerpo'];
      $asunto_mail    =  $args['asunto'];

      if ( isset($args['adjuntos']) ) {
         $adjuntos = $args['adjuntos'];
      } else {
         $adjuntos = FALSE;
         }

      if ( $this->enviaMailHtml($asunto_mail, $contenido_mail, $nombre_destinatario, $mail, $nombre_remitente, $mail_remitente,NULL,TRUE, $adjuntos) ) {

         return TRUE;
      } else {
         return FALSE; 
         }

      }

   }
?>
