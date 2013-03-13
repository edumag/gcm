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

   function cabecera_tabla($sesion) {

      ?>
      <table class='registro' summary='Registros de la aplicación'>
          <thead>
             <tr><th class="sesion" colspan="3">Sesion: <strong><?=presentarFecha($sesion,2);?></strong></th></tr>
             <tr>
             <th>Fecha</th>
             <th>Tipo</th>
             <th width="100%">Mensaje</th>
             </tr>
          </thead>
          <tbody>
      <?php

      }

   /** Formulario para los regisstros */

   function formulario($filtro=NULL) {

      global $gcm;

      $filtro = ( isset($_POST['filtro']) ) ? $_POST['filtro'] : 'sesion='.$gcm->reg->sesion;

      ?>
      <div class="registros_sesion">
      <fieldset>
      <legend><?=literal('Registros',3)?></legend>
      <div class="ayuda">
      Podemos filtrar por id, sesion, fecha, tipo ('ERROR','AVISO','ADMIN','DEBUG'), fichero, mensaje
      <br />
      Ejemplos:
      <ul>
         <li>sesion>100 AND tipo='ERROR' ORDER BY id desc</li>
         <li>mensaje LIKE "%ERROR%"</li>
      </ul> 
      </div>
      <form name='form_ver_registros' action='' method='post' onSubmit='javascript: visualizar_registros(this,"<?=$gcm->reg->sesion?>"); return false;'>
      <fieldset>
      <legend><?php echo literal('Filtro',3);?></legend>
         <input type="text" style='width:98%;' name='filtro' value="<?php echo $filtro; ?>">
      </fieldset>
      <input type='hidden' name='m' value='ver_registros' />
      <input type='hidden' name='a' value='registros_ajax' />
      <input type='hidden' name='formato' value='ajax' />
      <br />
      <br />
      <input type='submit' />
      </form>
      <div id='caja_registro_<?=$gcm->reg->sesion?>'>
      </fieldset>
      <?php
      

      if ( isset($_POST['filtro']) ) { 
         $this->tabla_registros($filtro); 
         }
      
      echo '</div></div>';

      }

   /**
    * Sistema de admnistración de ficheros de registro de la aplicación
    *
    * Presentación de registro con formulario de filtrado
    *
    * @filtro Condición sql para regustor de base de datos
    * @array  En caso de querer presentar los registros que tenemos en el array
    *
    * @package Registro
    * @subpackge Acciones
    */

   function tabla_registros($filtro=NULL, $array=NULL) {

      global $gcm;

      if ( GCM_DEBUG || $gcm->au->logeado()) {

         if ( $array  ) {

            /* Los registros vienen en un array */

            $registros = $array;

         } elseif ( ! $filtro ) {

            $registros = $gcm->reg->ver_registros();

         } else {
            
            $registros = $gcm->reg->ver_registros(NULL,$filtro);

         }

         if ( !is_array($registros) || count($registros) < 1 ) {
            echo "<div class='aviso'>Sin registros</div>";
            return FALSE;
            }

         $conta = 0;
         reset($registros);
         while ( current($registros) ) {
            $registro = current($registros);
            list($id,$sesion,$fecha,$tipo,$fichero,$linea,$mensaje,$descripcion) = $registro;
            $resultado[$conta]['id'] = $id;
            $resultado[$conta]['sesion'] = $sesion;
            $resultado[$conta]['fecha'] = $fecha;
            $resultado[$conta]['tipo'] = $tipo;
            $resultado[$conta]['fichero'] = $fichero;
            $resultado[$conta]['linea'] = $linea;
            $resultado[$conta]['mensaje'] = $mensaje;
            // $resultado[$conta]['descripcion'] = $descripcion;
            $conta++;
            next($registros);
            }

         require_once(GCM_DIR.'lib/int/array2table/lib/Array2table.php');

         //$opciones = array ('ocultar_id' => TRUE, 'fila_unica' => 'mensaje','table_id' => 'table');
         $opciones = array ('ocultar_id' => TRUE,'table_id' => 'table');


         $array2table = new Array2table();
         $array2table->generar_tabla($resultado, $opciones);

         $gcm->add_lib_js('temas', 'jquery.dataTables.js');
         
         return;

      } else {
         return FALSE;
      }
   }

   /**
    * registros_ajax
    *
    * Recogemos valores del formulario para crear el filtro
    */

   function registros_ajax() {

      if (  ! GCM_DEBUG && !permiso(3,NULL,FALSE,FALSE) ) {
         echo '<p class="error">'.literal('Sin permisos').'</p>';
         return FALSE;
         }

      $filtro = ( isset($_GET['filtro']) ) ? $_GET['filtro'] : '';

      $this->tabla_registros($filtro);

      ?>
      <script>
      $(document).ready(function() {
      $('#table').dataTable();
      } );
      </script>
      <?php
      exit();
      }

   /* Si estamos en modo DEBUG presentamos formulario de regitros */

   function debug() {

      global $gcm;

      if ( ! GCM_DEBUG ) {
         /* Añadimos algo de contenido para que no salga aviso en Plantilla de que falta */
         echo '<!-- debug -->';
         return;
         }
         
      $this->javascripts('ver_registros.js');

      /* Añadimos variables del sistema a los registros */

      if ( $_POST ) { $gcm->registra(__FILE__,__LINE__,depurar($_POST,'Post','POST')); }
      if ( $_GET ) { $gcm->registra(__FILE__,__LINE__,depurar($_GET,'Get','GET')); }
      if ( $_SESSION ) { $gcm->registra(__FILE__,__LINE__,depurar($_SESSION,'Session')); }
      //$gcm->registra(__FILE__,__LINE__,depurar(get_defined_constants(),'Constantes'));
      $gcm->registra(__FILE__,__LINE__,depurar($_SERVER,'SERVER'));
      // registrar(__FILE__,__LINE__,depurar($gcm->event->eventos,'Eventos'));
      registrar(__FILE__,__LINE__,depurar($gcm->event->ubicaciones,'Ubicaciones'));

      /* Panel de registros */
      ob_start();
      echo '<br />';
      $this->tabla_registros(NULL,$gcm->reg->registros);
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
    * postcontenido_ajax
    *
    * En caso de llamar a contenido desde ajax se debe reinicializar 
    * paneles con javascript
    */

   function postcontenido_ajax($e, $args) {

      ?>
      <script>
      paneles();
      </script>
      <?php

      }

   /**
    * Visualizar registros
    *
    * Presentamos formulario de registros
    */

   function visualizar() {

      global $gcm;

      $gcm->add_lib_js('temas', 'jquery.dataTables.js');

      $this->javascripts('ver_registros.js');

      $gcm->event->anular('titulo','ver_registros');
      $gcm->event->anular('contenido','ver_registros');
      $gcm->titulo = literal('Registros');

      $this->formulario();

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

      registrar(__FILE__,__LINE__,'Enviamos email con los registros');

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
