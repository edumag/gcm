<?php

/**
 * @file RolesAdmin.php
 * @brief Clase para administración de roles
 */

require_once ( 'Roles.php' );

/**
 * @class RolesAdmin
 * @brief Administrar roles
 */

class RolesAdmin extends Roles {

   /** Constructor */

   function __construct() {

      parent::__construct();

      }

   function editar_rol($rol) {

      global $gcm;

      /* Presentamos formulario para modificar configuración */

      $archivo = 'DATOS/configuracion/roles/roles/'.$rol.'.php';

      /* Si se pide volver a los valores por defecto, borramos
       * archivo de configuración del proyecto, para que se coja 
       * el del módulo
       */

      if ( isset($_POST['reset']) && file_exists($archivo) ) unlink($archivo);

      if ( !file_exists('DATOS/configuracion')  ) mkdir('DATOS/configuracion');
      if ( !file_exists('DATOS/configuracion/roles')  ) mkdir('DATOS/configuracion/roles');
      if ( !file_exists('DATOS/configuracion/roles/roles')  ) mkdir('DATOS/configuracion/roles/roles');

      if ( !file_exists($archivo) ) copy(GCM_DIR.'modulos/roles/config/roles/editor.php', $archivo);

      try {

         require_once(GCM_DIR.'lib/int/GcmConfig/lib/GcmConfigGui.php');

         $configuracion = new GcmConfigGui($archivo);

         $args = array();
         $args['eliminar'] = 'si';
         $args['ampliar_lista'] = 'si';

         // $configuracion->directorio_descripciones(GCM_DIR.'modulos/'.$modulo.'/config');

         $configuracion->formulario($args);

         echo '<br /><br /><form action="" method="post">';
         echo '<input name="reset" type="submit" value="Valores por defecto"/>';
         echo '</form>';

      } catch (Exception $ex) {
         registrar($ex->getFile(),$ex->getLine(),$ex->getMessage(),'ERROR');
         }
      }

   function admin_roles($e, $args = FALSE) {

      global $gcm;

      if ( $e !== 'interno' ) {
         ?>
         <div class="normal">Módulo en fase de pruebas</div>
         <?php
         }

      require_once(GCM_DIR.'lib/int/formato/lib/HTML.php');

      $gcm->event->anular('contenido','roles');
      $gcm->event->unico('titulo','roles');

      if ( isset($_POST['accion']) && $_POST['accion'] == 'escribir_gcmconfig'  ) {

         /* Nos llega configuración modificada */

         try {

            require_once(GCM_DIR.'lib/int/GcmConfig/lib/GcmConfigGui.php');

            $configuracion = new GcmConfigGui($_POST['archivo']);

            $configuracion->directorio_descripciones(dirname(__FILE__).'/../config/roles/descripciones/');
            $configuracion->escribir_desde_post();

         } catch (Exception $ex) {
            registrar($ex->getFile(),$ex->getLine(),$ex->getMessage(),'ERROR');
            return FALSE;
            }

         registrar(__FILE__,__LINE__,literal('Configuración guardada',3),'AVISO');

         }

      if ( isset($_POST['rol']) ) {
         $gcm->titulo = literal('Editar').' '.literal('rol').' '.$_POST['rol'];
         $this->editar_rol($_POST['rol']);
         return;
         }

      if ( isset($_POST['nuevo_rol']) ) {
         $nuevo_rol = $_POST['nuevo_rol'];
         $nuevo_rol_contenido = '<?php'."\n\n";

         ${$nuevo_rol} = array();
         foreach ( $gcm->event->eventos as $evento ) {
            foreach ( $evento as $modulo => $accion ) {
               if ( isset($editor[$modulo]) && in_array(key($accion),$editor[$modulo]) ) continue;
               //${$nuevo_rol}[$modulo][] = key($accion);
               $nuevo_rol_contenido .= "\$${nuevo_rol}['".$modulo."'][] = '".key($accion)."';\n";
               }
            }

         $nuevo_rol_archivo = self::$dir_roles.$nuevo_rol.'.php';
         file_put_contents($nuevo_rol_archivo, $nuevo_rol_contenido);
         registrar(__FILE__,__LINE__,'Nuevo rol ['.$nuevo_rol.'] añadido','AVISO');
         }

      $gcm->titulo = literal('Administrar roles');

      $roles_gcm      = glob(dirname(__FILE__).'/../config/roles/*.php');
      $roles_proyecto = glob(self::$dir_roles.'*.php');

      $roles_unidos = array_merge($roles_gcm, $roles_proyecto);
      $roles = array();
      foreach ( $roles_unidos as $rol_unido ) {
         if ( ! in_array(basename($rol_unido,'.php'),$roles) ) $roles[] = basename($rol_unido,'.php');
         }

      ?>

      <form action="<?php echo Router::$base;?>roles/admin_roles/" method="POST">
      <fieldset>
      <legend  accesskey="s">Editar roles de usuarios</legend>
      <select onchange="this.form.submit();" name="rol">
      <option value=""><?php echo literal('Selecciona un rol a editar'); ?></option>
      <?php foreach ( $roles as $rol ) { ?>
      <option value="<?php echo $rol ?>"><?php echo $rol ?></option>
      <?php } ?>
      </select>
      </fieldset>
      </form>

      <br /><br />

      <form action="<?php echo Router::$base;?>roles/admin_roles/" method="post">
      <fieldset>
      <legend  accesskey="a">Añadir un nuevo rol</legend>
      <br />
      <?php echo HTML::form_text('nuevo_rol', FALSE, array('placeholder' => 'Texto sin espacios', 'pattern' => '[A-Za-z]{4,15}', 'required' )); ?>
      <button type="submit">Enviar </button>
      </fieldset>
      </form>


      <br />
      <br />

      <a class="boton" href="<?php echo Router::$base;?>roles/usuarios/">Administrar roles de usuarios</a>

      <?php

      }

   /**
    * Administrar roles de usuarios
    *
    * @todo procesar texto de readme para presentarlo
    */

   function usuarios($e, $args=FALSE){

      global $gcm;

      $gcm->event->anular('contenido','roles');
      $gcm->event->unico('titulo','roles');
      $gcm->titulo = literal('Roles de usuarios');

      require GCM_DIR.'lib/ext/parsedown-master/Parsedown.php';
      $ayuda = file_get_contents(dirname(__FILE__).'/../usuario.md');   
      $ayuda = Parsedown::instance()->parse($ayuda);
      echo '<div class="ayuda">'; echo $ayuda ; echo '</div>';

      // Archivo con los roles de usuario del proyecto
      $archivo = self::$dir_roles.'../usuarios.php';

      ?>
      <div class="normal">Módulo en fase de pruebas</div>
      <?php

      if ( isset($_POST['contenido_usuarios']) ) {

         $salida = @eval('?>'.$_POST['contenido_usuarios']);

         if( $salida == '' ) {
            file_put_contents($archivo,$_POST['contenido_usuarios']);
            registrar(__FILE__,__LINE__,'Archivo con roles de usuario guardado','AVISO');
         } else {
            registrar(__FILE__,__LINE__,'Error al procesar archivo, intentalo de nuevo','ERROR');
            }
   
         }

      require_once(GCM_DIR.'lib/int/formato/lib/HTML.php');

      if ( file_exists($archivo) ) {
         $contenido = file_get_contents($archivo);
      } else {
         $contenido = file_get_contents(dirname(__FILE__).'/../config/usuarios.php');
         }

      ?>
      <form action="" method="POST">
      <fieldset>
      <legend  accesskey="r">Adjudicar roles a usuarios</legend>
      <?php $lineas=contabilizar_saltos_linea($contenido); ?>
      <?php echo HTML::form_text('contenido_usuarios', $contenido, array('maxlength' => 2000 , 'required','class'=>'editor_codigo','rows'=>$lineas )); ?>
      <input name="usuarios_roles" type="submit" value="Enviar" />
      </fieldset>
      </form>

      <br /><br />
      <?php

      $this->admin_roles('interno');

      }

   }

?>

