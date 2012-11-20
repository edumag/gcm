<?php

/** login.php
* Presentación de formulario de login
*
* @author Eduardo Magrané
*/

/** formulario_registro
 *
 * Presentar formulario de registro
 *
 * @author Eduardo Magrané
 * @version 1.0
 *
 */

function formulario_registro() {

   if (! logeados() ) {
      $panel = array();
      $panel['titulo'] = literal('Administrar');
      $panel['oculto'] = TRUE;
      $panel['href'] = 'javascript:visualizar(\'login\');';
      $panel['subpanel'] ='login';
      $panel['contenido'] = 
         '<form name="entrada" action="'.gcm_generar_url().'" method="post"> 
         <br />'.literal("Nombre",3).':
         <br /><input type="text" size="10" name="loginPro" id="loginPro" value="" />
         <br />'.literal("Contraseña",3).': 
         <br /><input type="password" size="10" name="passwd" id="passwd" value="" />
         <input type="hidden" name="selected" value="'.$_SERVER['PHP_SELF'].'" />
         <br /><br />
         <input type="submit" value="Entrar" />
         </form>';

      Temas::panel($panel);

      }
   }

/** Comprobar que estamos logeados
*
* @author Eduardo Magrané
* @version 1.0
*
* @param $mensaje En caso negativo enviamos mesaje al usuario TRUE/FALSE
* 
* @return TRUE/NULL
*
*/

function logeados($mensaje=FALSE) {

   global $gcm;

   $proyecto = $gcm->config('admin','Proyecto');
   
   if ( isset($_SESSION["admin-".$proyecto] ) ) { 
      return 1 ;
   } elseif ( isset($_SESSION['root']) ) {
      return 2 ;
   } else {
      if ( $mensaje ) { $this->registra(__FILE__,__LINE__,'Acceso solo para administradores','AVISO'); }
      return NULL ;
   }
}

/** Comprobar que estamos logeados y es superadmin
*
* @author Eduardo Magrané
* @version 1.0
*
* @return TRUE/NULL
*
* @todo Comprobar que es el super administrador
*
*/

function superadmin() {

   global $gcm ;
   
   $proyecto = $gcm->config('admin','Proyecto');
   
   if ( isset($_SESSION["admin-".$proyecto] ) ) { 
      return 1 ;
   } elseif ( isset($_SESSION['root']) ) {
      return 2 ;
   } else {
      return NULL ;
   }
}

?>
