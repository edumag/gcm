<?php

/**
 * @file AdminAdmin.php
 * @brief Métodos administrativos para el módulo admin
 *
 * @ingroup modulo_admin
 */

require_once(dirname(__FILE__).'/Admin.php');

/**
 * @class AdminAdmin
 * @brief Administración de la administración de proyectos
 */

class AdminAdmin extends Admin {

   function __construct() {
      parent::__construct();
      }

   /**
    * Activamos tema admin para los metodos que lo necesiten
    *
    * @param $e Evento
    * @param $args Argumentos
    */

   function activar_tema_admin($e, $args=FALSE) {

      global $gcm;

      if ( Router::$m && Router::$a == 'configuracion' ) {
         
         registrar(__FILE__,__LINE__,'Seleccionamos tema para administración');
         $gcm->tema = 'admin';

         }

      }
      
   /** 
    * Generamos test administrativos 
    */

   function test() {

      permiso('administrar',TRUE);

      global $gcm;

      $gcm->event->anular('titulo','admin');
      $gcm->event->anular('contenido','admin');

      $gcm->titulo = '<h1>Testeando proyecto</h1>';

      // Recogemos una sección para hacer pruebas y un contenido
      $secciones = glob(Router::$dd.'*/*html');
      if ( ! isset($secciones[0]) ) {
         echo '<div class="aviso">Sin una sección no podemos comprobar</div>';
      } else {

         $contenido = basename($secciones[0]);
         $seccion   = basename(dirname($secciones[0]));

         if ( ! empty($seccion) ) {

            $url = 'buscar/palabra_a_buscar/'.$seccion.'/'.$contenido;
            $router = Router::desglosarUrl($url);

            $this->ejecuta_test('Verificar url: '.$url,$router['url'],$seccion.'/'.$contenido);
            $this->ejecuta_test('Verificar s: '.$seccion,$router['s'],$seccion.'/');
            $this->ejecuta_test('Verificar c: '.$contenido,$router['c'],$contenido);
            $this->ejecuta_test('Verificar dd',$router['dd'],'File/es/');
            $this->ejecuta_test('Verificar ii',$router['ii'],'es');
            $this->ejecuta_test('Verificar a',$router['a'],NULL);
            $this->ejecuta_test('Verificar m',$router['m'],NULL);
            $this->ejecuta_test('Verificar args: palabra_a_buscar',$router['args'],array('palabra_a_buscar'));
            $this->ejecuta_test('Verificar e: buscar',$router['e'],'buscar');
            $this->ejecuta_test('Verificar enlace_relativo', $router['enlace_relativo'],'./');
            $this->ejecuta_test('Verificar mime/type',$router['mime_type'],'text/html');
            $this->ejecuta_test('Verificar formato',$router['formato'],'html');

            if ( GCM_DEBUG ) echo '<pre>DEPURANDO: ' ; print_r($router) ; echo '</pre>'; // exit() ; // DEV  
            }

         }

      $secciones = glob(Router::$dd.'*.html');

      if ( ! isset($secciones[0]) ) {
         echo '<div class="aviso">Sin un contenido creado no podemos comprobar</div>';
      } else {

         $contenido = basename($secciones[0]);

         if ( ! empty($contenido) ) {

            $url = 'ca/ajax/contenidos/borrar/12/28/'.$contenido;
            $router = Router::desglosarUrl($url);

            $this->ejecuta_test('Verficar url: '.$url,$router['url'],$contenido);
            $this->ejecuta_test('Verficar s',$router['s'],'');
            $this->ejecuta_test('Verficar c: '.$contenido,$router['c'],$contenido);
            $this->ejecuta_test('Verficar dd',$router['dd'],'File/es/');
            $this->ejecuta_test('Verficar ii',$router['ii'],'es');
            $this->ejecuta_test('Verficar i',$router['i'],'ca');
            $this->ejecuta_test('Verficar a: borrar',$router['a'],'borrar');
            $this->ejecuta_test('Verficar m: contenidos',$router['m'],'contenidos');
            $this->ejecuta_test('Verficar args: [12,28]',$router['args'],array('12','28'));
            $this->ejecuta_test('Verficar e',$router['e'],NULL);
            $this->ejecuta_test('Verficar enlace_relativo', $router['enlace_relativo'],'./');
            $this->ejecuta_test('Verficar mime/type: text/html',$router['mime_type'],'text/html');
            $this->ejecuta_test('Verficar formato: ajax',$router['formato'],'ajax');

            }

         }

      $url = 'ca/ajax/buscar/literal/';
      $router = Router::desglosarUrl($url);

      $this->ejecuta_test('Verificar url: '.$url,$router['url'],'');
      $this->ejecuta_test('s',$router['s'],'');
      $this->ejecuta_test('c',$router['c'],'');
      $this->ejecuta_test('dd',$router['dd'],'File/es/');
      $this->ejecuta_test('ii',$router['ii'],'es');
      $this->ejecuta_test('i',$router['i'],'ca');
      $this->ejecuta_test('e: buscar',$router['e'],'buscar');
      $this->ejecuta_test('args: literal',$router['args'],array('literal'));

      }

   /**
    * Cerrar cron
    *
    * Tras ejecutar el evento cron y todos los módulos hayan realizado el
    * trabajo, mostramos los avisos en formato texto y salimos.
    *
    * @param $e Evento
    * @param $args Argumentos
    */

   function cerrar_cron($e = FALSE, $args = FALSE) {

      global $gcm;

      // Recogemos avisos
      $tipos = $gcm->reg->registros_sesion();

      if ( $tipos )  {

         foreach ($tipos as $tipo => $registros ) {
            echo '# '.$tipo;
            echo "\n";
            echo "\n";
            foreach ( $registros as $reg ) {
               echo $reg[2];
               echo "\n";
               }
            }

         }

      exit();

      }

   /**
    * Ejecutar métodos test de los módulos
    *
    * Buscamos en todos los módulos si hay un metodo test en tal caso se lanza
    *
    * @param $e Evento
    * @param $args Argumentos
    */

   function ejecutar_tests_modulos($e = FALSE, $args = FALSE) {

      global $gcm;

      $modulos_activados = array_merge($gcm->modulos_basicos,$gcm->config('admin','Módulos activados'));

      // Buscar instancias de módulos para ejecutar sus tests

      echo '<tr /><h2>Módulos de gcm</h2>';

      foreach ( $modulos_activados as $modulo ) {

         $dir_modulo = GCM_DIR.'modulos/'.$modulo;

         if ( ! is_dir($dir_modulo) ) continue;

         $clase = ucfirst($modulo);

         $hay_admin =  ( file_exists($dir_modulo.'/lib/'.$clase.'Admin.php') ) ? TRUE : FALSE ;


         if ( $hay_admin ) {
            $clase = ucfirst($modulo).'Admin';
            require_once($dir_modulo.'/lib/'.$clase.'.php');
            $instancia = new  $clase();
         } else {
            require_once($dir_modulo.'/lib/'.$clase.'.php');
            $instancia = new  $clase();
            }

         if (method_exists($instancia, 'test') ) {
            echo '<br /><h3>'.$modulo.'</h3><br />';
            $instancia->test();
         } elseif ( GCM_DEBUG ) {
            echo '<br /><h3>'.$modulo.'</h3><br />';
            echo 'Sin tests asociados';
            }
         }

      // Buscar instancias de módulos del proyecto

      echo '<tr /><h2>Módulos de proyecto</h2>';

      foreach ( glob('modulos/*') as $dir_modulo ) {

         if ( ! is_dir($dir_modulo) ) continue;

         $modulo = basename($dir_modulo);
         $clase = ucfirst($modulo);
         require_once($dir_modulo.'/lib/'.$clase.'.php');
         $instancia = new  $clase();
         echo '<br /><h3>'.$modulo.'</h3><br />';
         if (method_exists($instancia, 'test') && $modulo != 'admin' ) {
            $instancia->test();
         } else {
            echo 'Sin tests asociados';
            }
         }

      }

   /**
    * Administrar conexiones entre eventos y módulos
    */

   function configurar_conexiones($e, $args=NULL) {

      global $gcm;

      permiso('administrar',TRUE);

      if ( isset($_REQUEST['eVisualizar']) ) return ;

      $gcm->tema = 'admin';
      $gcm->plantilla = 'administrando.html';

      $gcm->event->anular('titulo','admin');
      $gcm->event->anular('contenido','admin');

      $gcm->titulo = 'Visualizar conexiones';

      $url = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/?eVisualizar';

      echo '<p id="ver_plantilla"><a id="boton_ver_plantilla" class="boton" href="?eVisualizar=1" onclick="ver_plantilla();return false;">Ver plantilla</a></p>';
      echo '<p id="ver_lista"><a id="boton_ver_lista" class="boton" href="?formato=ajax&m=admin&a=lista_eventos" onclick="ver_lista();return false;">Ver listado</a></p>';

      ?>
      <script>
         function ver_lista() {
            
            var contenedor = $('p#ver_lista');
            var url = $('a#boton_ver_lista').attr("href");  // alert(url);
            contenedor.html("Cargando...");

            $.get(url,function(data){
               contenedor.replaceWith(data);
              });
            return false;
            }
         function ver_plantilla() {
            
            var contenedor = $('p#ver_plantilla');
            var url = $('a#boton_ver_plantilla').attr("href");  // alert(url);
            contenedor.html("Cargando...");

            $.get(url,function(data){
               contenedor.replaceWith(data);
              });
            return false;
            }
      </script>
      <?php

   }

   /**
    * Listar eventos para editar
    */

   function lista_eventos($e, $args=FALSE) {

      global $gcm;

      ?>
      <ul>Eventos
      <?php foreach ( $gcm->event->eventos as $evento => $modulos ) { ?>
         <ul><?php echo $evento ?>
         <?php foreach ( $modulos as $modulo => $accion)  { ?>
            <?php foreach ($accion as $p ) { $prioridad = key($p); $args = ( isset($p[0]) ) ? $p[0] : FALSE; } ?>
            <li>
               <a href="<?php echo Router::$base ?>/admin/editar_conexion?md=<?php echo $modulo ?>" title="Editar eventos de módulo">
                  <?php echo $modulo ?> -> <?php echo key($accion) ?> <?php echo $prioridad ?> <?php echo $args ?>
               </a>
            </li>
         <?php } ?>
         </ul>
      <?php } ?>
      </ul>
      <?php
      }

   /**
    * Editar conexión
    *
    * @param  $e Evento
    * @param  $args Argumentos
    * @return TRUE/FALSE
    **/
   
   function editar_conexion($e,$args=NULL) {
   
      global $gcm;

      $diff_usuario = FALSE;   ///< ¿Hay diferencias entre el archivo por defecto?
      $diff_admin   = FALSE;   ///< ¿Hay diferencias entre el archivo por defecto?

      $modulo = $_GET['md'];

      $gcm->event->anular('titulo','admin');
      $gcm->event->anular('contenido','admin');

      $gcm->titulo = 'Conexiones de '.$modulo;

      $fichero_usuario = 'DATOS/eventos/'.$modulo.'/eventos_usuario.php'; 
      $fichero_admin   = 'DATOS/eventos/'.$modulo.'/eventos_admin.php'; 

      $fichero_usuario_modulo = $gcm->event->ubicaciones[$modulo].'/eventos_usuario.php';
      $fichero_admin_modulo   = $gcm->event->ubicaciones[$modulo].'/eventos_admin.php';

      $contenido_usuario_modulo = FALSE;
      $contenido_admin_modulo   = FALSE;

      if ( file_exists($fichero_usuario) ) {
         $contenido_usuario = file_get_contents($fichero_usuario);
         $contenido_usuario_modulo = file_get_contents($fichero_usuario_modulo);
         if ( $contenido_usuario != $contenido_usuario_modulo ) $diff_usuario = TRUE;
      } else {
         $contenido_usuario = file_get_contents($fichero_usuario_modulo);
         }

      if ( file_exists($fichero_admin) ) {
         $contenido_admin = file_get_contents($fichero_admin);
         $contenido_admin_modulo = file_get_contents($fichero_admin_modulo);
         if ( $contenido_admin != $contenido_admin_modulo) $diff_admin = TRUE;
      } else {
         if ( file_exists($fichero_admin_modulo) ) {
            $contenido_admin = file_get_contents($fichero_admin_modulo);
            } else {
               $contenido_admin = FALSE;
               }
         }

      ?>
      <form action="<? echo $_SERVER['SCRIPT_NAME'] ?>" method="post">
      <fieldset>
      <legend  accesskey="s">Eventos para usuario</legend>
      <?php if ( ! $diff_usuario ) echo '<div class="aviso">Sin diferencias con la versión por defecto</div>'; ?>
      <textarea name="contenido" style="width:100%;height:300px"><?=$contenido_usuario?></textarea>
      <br /><br />
      <input type="hidden" name="m" value="admin">
      <input type="hidden" name="a" value="modificar_conexion">
      <input type="hidden" name="modulo" value="<?=$modulo?>">
      <input type="hidden" name="tipo" value="usuario">
      <fieldset>
      <input type='submit' value='<?=literal('Guardar')?>' />
      </fieldset>
      <?php
      if ( $contenido_usuario != $contenido_usuario_modulo ) {
         ?>
         <br />Contenido predeterminado del módulo
         <textarea style="width:100%;height:300px"><?=$contenido_usuario_modulo?></textarea>
         <?php
         }
      ?>
      </fieldset>
      </form>
      <?php

      if ( $contenido_admin ) {
         

         ?>
         <form action="<? echo $_SERVER['SCRIPT_NAME'] ?>" method="post">
         <fieldset>
         <legend  accesskey="s">Eventos para administración</legend>
         <?php if ( ! $diff_admin ) echo '<div class="aviso">Sin diferencias con la versión por defecto</div>'; ?>
         <textarea name="contenido" style="width:100%;height:300px"><?=$contenido_admin?></textarea>
         <br /><br />
         <input type="hidden" name="m" value="admin">
         <input type="hidden" name="a" value="modificar_conexion">
         <input type="hidden" name="modulo" value="<?=$modulo?>">
         <input type="hidden" name="tipo" value="admin">
         <fieldset>
         <input type='submit' value='<?=literal('Guardar')?>' />
         </fieldset>
         <?php
         if ( $contenido_admin != $contenido_admin_modulo ) {
            ?>
            <br />Contenido predeterminado del módulo
            <textarea style="width:100%;height:300px"><?=$contenido_admin_modulo?></textarea>
            <?php
            }
            ?>
         </fieldset>
         </form>
         <?php
         }

      }

   /**
    * Guardar modificaciones realizadas en archivo de eventos
    *
    * @param  $e Evento
    * @param  $args Argumentos
    * @return TRUE/FALSE
    **/
   
   function modificar_conexion($e,$args=NULL) {
   
      global $gcm;

      permiso('administrar',TRUE);

      $modulo    = $_POST['modulo'];
      $tipo      = $_POST['tipo'];
      $contenido = stripcslashes($_POST['contenido']);

      $fichero = 'DATOS/eventos/'.$modulo.'/eventos_'.$tipo.'.php'; 

      $gcm->event->anular('titulo','admin');
      $gcm->event->anular('contenido','admin');

      $gcm->titulo = 'Guardar '.$modulo.' tipo: '.$tipo;

      if ( file_put_contents($fichero, $contenido) ) {
         echo '<p class="ok">'.$fichero.' modifcado</p>';
      } else {
         echo 'No se pudo realizar actualización';
         }

      }
   
   /** 
    * Usuarios
    *
    * Formulario para administrar los usuarios
    *
    * @todo Hacer de campo admin booleano para tipos_campo en formulario
    *
    * @param $e Evento que lo llama
    * @param $args Argumentos
    */

   function usuarios($e, $args) {

      global $gcm;

      if ( permiso('administrar','admin') ) {

         $gcm->event->anular('contenido','admin');
         $gcm->event->anular('titulo','admin');
         $gcm->titulo = literal('Administración de usuarios');

         require_once(dirname(__FILE__).'/../modelos/usuarios.php');
         $usuarios = new Usuarios($gcm->pdo_conexion());
         // $usuarios->url_formulario = "?m=admin&a=usuarios";
         $usuarios->administrar(FALSE,FALSE,FALSE,TRUE);

         return;

      } else {

         $this->perfil_usuario('interno');

         }

      }

   /**
    * Presentar información de servidor
    *
    * @param $e Evento que lo llama
    * @param $args Argumentos
    */

   function infoserver($e,$args) {

      global $gcm;

      permiso('administrar',TRUE);

      $gcm->event->anular('titulo','admin');
      $gcm->event->anular('contenido','admin');

      $gcm->titulo = "Información de servidor";

      ?>
      <style type="text/css">
      #phpinfo {}
      #phpinfo pre {}
      #phpinfo a:link {}
      #phpinfo a:hover {}
      #phpinfo table {}
      #phpinfo .center {}
      #phpinfo .center table {}
      #phpinfo .center th {}
      #phpinfo td, th {}
      #phpinfo h1 {}
      #phpinfo h2 {}
      #phpinfo .p {}
      #phpinfo .e {}
      #phpinfo .h {}
      #phpinfo .v {}
      #phpinfo .vr {}
      #phpinfo img {}
      #phpinfo hr {}
      </style>

      <div id="phpinfo">
      <?php

      ob_start () ;
      phpinfo () ;
      $pinfo = ob_get_contents () ;
      ob_end_clean () ;

      // the name attribute "module_Zend Optimizer" of an anker-tag is not xhtml valide, so replace it with "module_Zend_Optimizer"
      echo ( str_replace ( "module_Zend Optimizer", "module_Zend_Optimizer", preg_replace ( '%^.*<body>(.*)</body>.*$%ms', '$1', $pinfo ) ) ) ;

      ?>
      </div>
      <?php
      }

   /**
    * @brief  perfil_usuario
    *
    * Mostrar información de usuario
    *
    * @todo Validar datos que nos llegan
    *
    * @param $e Evento que lo llama.
    * @param $args Identificador de usuario a mostrar, sino pasamos niinguno sera el logeado.
    */

   function perfil_usuario($e, $args=FALSE) {

      global $gcm;

      $usuario_id = ( $args ) ? $args : $gcm->au->logeado();

      if ( $usuario_id ) {

         $gcm->event->anular('contenido','admin');
         $gcm->event->unico('titulo','admin');
         $gcm->titulo = literal('Perfil de usuario');

         $this->gestionar_usuario($usuario_id);

         $this->cambio_password();

         }

      }

   /** Presentar menu administrativo
    *
    * Creación del menu administrativo para los proyectos
    * 
    * Se genera dinamicamente el menu segun los modulos que tengamos
    * Los modulos se encuentran en el directorio GCM_DIR."modulos/gcm/"
    * y se incluye el archivo menuAdmin.php que tenga cada modulo.
    *
    * - Si estamos logeados los de administración
    * - Si somos root los de root y administración
    *
    * Formato del arreglo con la información del menú::
    *
    *  $menuAdmin['seccion'][title]='Title de la sección';
    *  $menuAdmin['seccion'][link]='Enlace'; // En caso que lo haya.
    *
    * ejemplo de archivo menuAdmin.php::
    *
    *  $menuAdmin['Archivo']['boton']['borrar_documento']['activado'] = ( is_file( Router::get_c() ) ) ? 1 : 0;
    *  $menuAdmin['Archivo']['boton']['borrar_documento']['title']="Borrar documento actual";
    *  $menuAdmin['Archivo']['boton']['borrar_documento']['link']="?e=borrar_documento";
    *
    */

   function presentar_menu_administrativo() {

      global $gcm;

      $menuAdmin = array();

      // Buscamos los modulos de administracion

      $path=GCM_DIR."modulos/";
      $directorio_modulos=dir($path);
      while ($directorio = $directorio_modulos->read()) {
         if ( $directorio[0] !="."  AND is_dir($path.$directorio) ) {
            $fich_final=$path.$directorio.'/menuAdmin.php';
            if (is_file($fich_final)) include($fich_final);
            }
         }

      $path="modulos/";
      if ( file_exists($path) ) {
         $directorio_modulos=dir($path);
         while ($directorio = $directorio_modulos->read()) {
            if ( $directorio[0] !="."  AND is_dir($path.$directorio) ) {
               $fich_final=$path.$directorio.'/menuAdmin.php';
               if (is_file($fich_final)) include($fich_final);
               }
            }
         }

      /* Buscar archivos config en carpetas de módulos en caso de tenerlos se 
       * genera automáticamente una entrada en el menu administrativo
       */

      $archivos_config = glob(GCM_DIR.'modulos/*/config/config.php');
      foreach ( $archivos_config as $archivo ) {

         $array = explode('/',$archivo);
         $modulo = $array[count($array)-3];

         if ( permiso('configuracion',$modulo) ) {
            $menuAdmin['Configuración']['boton'][literal($modulo,3)]['activado']= 1;
            $menuAdmin['Configuración']['boton'][literal($modulo,3)]['title']=literal("Configuración de ",3).literal($modulo,3);
            $menuAdmin['Configuración']['boton'][literal($modulo,3)]['link']=Router::$base.$modulo."/configuracion";
            }


         }

      /* Buscar en módulos de proyecto */

      $archivos_config = glob('modulos/*/config/config.php');

      if ( ! empty($archivos_config)  ) {
         foreach ( $archivos_config as $archivo ) {

            $array = explode('/',$archivo);
            $modulo = $array[count($array)-3];

            if ( permiso('configuracion',$modulo) ) {
               $menuAdmin['Configuración']['boton'][literal($modulo,3)]['activado']= 1;
               $menuAdmin['Configuración']['boton'][literal($modulo,3)]['title']=literal("Configuración de ",3).literal($modulo,3);
               $menuAdmin['Configuración']['boton'][literal($modulo,3)]['link']="?m=".$modulo."&a=configuracion";
               }


            }
         }

      // Ordenamos según peso de la sección del menú
      uasort($menuAdmin, 'ordenar_por_peso');

      include($gcm->event->instancias['temas']->ruta('admin','html','menuAdmin.html'));
      }

   /**
    * Modificar usuario
    */

   function gestionar_usuario($usuario_id) {

      global $gcm;

      // Si no tenemos permisos, tenemos que ser el usuario que edita
      if ( ! permiso() && $usuario_id != $gcm->au->logeado()) return ;


      $presentar_form = TRUE;          ///< Presentamos formulario
      $anyadir_usuario = FALSE;        ///< Si se desea añadir un usuario

      require_once(dirname(__FILE__).'/../modelos/usuarios.php');

      if ( isset($_POST['modificar'] )  ) {

         try {

            $usuario = new Usuarios($gcm->pdo_conexion(), $usuario_id);

            $resultado = $this->validar_datos($_POST);

            if ( $resultado ) {

               $usuario->setUsuario($resultado['usuario']);
               $usuario->setNombre($resultado['nombre']);
               $usuario->setApellidos($resultado['apellidos']);
               $usuario->setMail($resultado['mail']);
               $usuario->setTelefono($resultado['telefono']);
               $usuario->setFecha_modificacion(date('Y-m-d H:i'));
               $usuario->save();

               registrar(__FILE__,__LINE__,literal('Usuario modificado'),'AVISO');

            } else {
               
               $resultado = $_POST;

            }

         } catch (Exception $ex ) {

            registrar($ex->getFile(),$ex->getLine(),$ex->getMessage(),'AVISO');
            return;

            }

      } elseif ( isset($_POST['borrar'] )  ) {

         $usuario = new Usuarios($gcm->pdo_conexion(), $_POST['id']);

         $usuario->MarkForDeletion();
         $presentar_form = FALSE;
         registrar(__FILE__,__LINE__,literal('Usuario borrado'),'AVISO');

      } elseif ( isset($_POST['anyadir'] )  ) {

         $anyadir_usuario = TRUE;

      } elseif ( isset($_POST['insertar'] )  ) {

         $usuario = new Usuarios($gcm->pdo_conexion());

         $resultado = $this->validar_datos($_POST);

         if ( $resultado ) {

            $usuario->setUsuario($resultado['usuario']);
            $usuario->setPass_md5(md5($resultado['pass']));
            $usuario->setNombre($resultado['nombre']);
            $usuario->setApellidos($resultado['apellidos']);
            $usuario->setMail($resultado['mail']);
            $usuario->setTelefono($resultado['telefono']);
            $usuario->save();
            $resultado['id'] = $usuario->ultimo_identificador();

            registrar(__FILE__,__LINE__,literal('Usuario insertado'),'AVISO');

         } else {
            
            $resultado = $_POST;

         }


      } else {
         
         $usuario = new Usuarios($gcm->pdo_conexion(), $usuario_id);

         $resultado['id']     = $usuario->getID();
         $resultado['usuario'] = $usuario->getUsuario();
         $resultado['nombre']   = $usuario->getNombre();
         $resultado['apellidos']   = $usuario->getApellidos();
         $resultado['mail']   = $usuario->getMail();
         $resultado['telefono']   = $usuario->getTelefono();

         }         

      ?>
      <span class="caja">
         <form action="<?=$_SERVER['PHP_SELF'];?>" method="post">
            <?php if ( $presentar_form ) include(dirname(__FILE__).'/../html/form_perfil.html'); ?>
            <br />
            <input type="hidden" name="id" value="<?=$resultado['id']?>" />
            <input type="hidden" name="m" value="admin" />
            <input type="hidden" name="a" value="usuarios" />
            <?php if ( isset($resultado['id'])  ) { ?>
            <input type='submit' name='modificar' value='<?=literal("Modificar")?>' />
            <input type='submit' name='borrar' value='<?=literal("Borrar")?>' />
            <?php } ?>
            <?php if ( $anyadir_usuario ) { ?>
               <input type='submit' name='insertar' value='<?=literal("Añadir")?>' />
            <?php } else { ?>
               <input type='submit' name='anyadir' value='<?=literal("Añadir")?>' />
            <?php } ?>
            <br /><br />
         </form>
         <script language='javascript'>
            document.getElementById('usuario').focus();
         </script>
      </span>
      <?php
      
      }

   /**
    * Ejecutar cambio de contraseña
    */

   function ejecutar_cambio_password() {

      global $gcm;

      $usuario_id = is_numeric($_POST['id'] );

      // Si no tenemos permisos, tenemos que ser el usuario que edita
      if ( ! permiso() && $usuario_id != $gcm->au->logeado()) return ;

      require_once(dirname(__FILE__).'/../modelos/usuarios.php');

      $gcm->event->anular('contenido','admin');
      $gcm->event->unico('titulo','admin');
      $gcm->titulo = literal('Cambio de contraseña');

      $usuario = new Usuarios($gcm->pdo_conexion(), $usuario_id);

      $resultado = $this->validar_datos($_POST);

      if ( ! $resultado  ) {
         $this->cambio_password();
         return FALSE;
         }

      $usuario->setPass_md5(md5($resultado['pass']));
      $usuario->save();

      registrar(__FILE__,__LINE__,literal('Usuario modificado'),'AVISO');

      $this->perfil_usuario(NULL);

      }

   /**
    * Cambio de contraseña
    */

   function cambio_password($usuario_id=FALSE) {

      global $gcm;

      // Si no tenemos permisos, tenemos que ser el usuario que edita
      if ( ! permiso() ) {
         $usuario_id = $gcm->au->logeado();
         }

      if ( ! $usuario_id ) return ;

      ?>
      <form action="<?=$_SERVER['PHP_SELF'];?>" method="post">
         <?php include($gcm->event->instancias['temas']->ruta('admin','html','form_cambio_pass.html')); ?>
         <input type="hidden" name="a" value="ejecutar_cambio_password" />
         <input type="hidden" name="m" value="admin" />
         <input type="hidden" name="id" value="<?php echo $usuario_id; ?>" />
      </form>
      <?php

      }

   /**
    * Caja con información de depuración
    */

   function caja_info_dev($e, $args) {

      if ( ! permiso('caja_info_dev', 'admin') ) return FALSE;

      global $gcm;

      ob_start();
      $gcm->event->lanzarEvento('contenido_caja_info_dev');
      $salida = ob_get_contents(); ob_end_clean();

      if ( $salida != "" ) { 

         $panel = array();
         $panel['titulo'] = literal('Información de módulos');
         $panel['oculto'] = TRUE;
         $panel['contenido'] = $salida;
         Temas::panel($panel);
         }
      }

   /**
    * Información para depurar aplicación
    */

   function contenido_caja_info_dev($e, $args=FALSE) {

      ?>
      <h3>Admin</h3>
      <ul>
      <?php

      if ( GCM_DEBUG ) {
         ?>
         <li>
         <a href="<?php echo modificarGet('debug','0');?>" title="<?php echo literal('Desactivar depuración'); ?>">
            <?php echo literal('Depuración activada');?>
         </a>
         </li>
         <?php
      } else {
         ?>
         <li>
         <a href="<?php echo modificarGet('debug','1');?>" title="<?php echo literal('Activar depuración'); ?>">
            <?php echo literal('Depuración desactivada');?>
         </a>
         </li>
         <?php
         }

      ?>
      <li>
      <a href="<?php echo modificarGet('eGcm','1');?>" title="<?php echo literal('Forzar a recoger los eventos por defecto'); ?>">
         <?php echo literal('Eventos por defecto');?>
      </a>
      </li>
      <li>
      <a href="?e=test" title="<?php echo literal('Forzar a recoger los eventos por defecto'); ?>">
         <?php echo literal('Lanzar test');?>
      </a>
      </li>
      <li>
      <a href="?e=cron" title="<?php echo literal('Forzar a recoger los eventos por defecto'); ?>">
         <?php echo literal('Lanzar cron');?>
      </a>
      </li>

      </ul>
      <?php

      }

   /**
    * Cerrar sesión
    * 
    */
   
    function cerrar_sesion($e, $args=FALSE) {
   
      global $gcm;
   
      registrar(__FILE__,__LINE__,'cerramos sesión');

      $gcm->au->salir();
      header ("Location:".Router::$dir.Router::$s.Router::$c);
      exit();

      }
   
   }
?>
