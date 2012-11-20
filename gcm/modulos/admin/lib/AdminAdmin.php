<?php

/**
 * @file AdminAdmin
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 *   Created  23/11/09
 *  Revision  SVN $Id: Admin.php 478 2011-02-28 08:31:31Z eduardo $
 * Copyright  Copyright (c) 2009, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

require_once(dirname(__FILE__).'/Admin.php');

/**
 * @class AdminAdmin
 * @brief Administración de proyectos
 * @version 0.1
 */

class AdminAdmin extends Admin {

   function __construct() {
      parent::__construct();
      }

   /**
    * Activamos tema admin para los metodos que lo necesiten
    */

   function precarga() {

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

      permiso(2);

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

            echo '<br />'.$url.'<br />';

            $this->ejecuta_test('Verificar url',$router['url'],$seccion.'/'.$contenido);
            $this->ejecuta_test('Verificar s',$router['s'],$seccion.'/');
            $this->ejecuta_test('Verificar c',$router['c'],$contenido);
            $this->ejecuta_test('Verificar dd',$router['dd'],'File/es/');
            $this->ejecuta_test('Verificar d',$router['d'],'File/es/');
            $this->ejecuta_test('Verificar ii',$router['ii'],'es');
            $this->ejecuta_test('Verificar i',$router['i'],'es');
            $this->ejecuta_test('Verificar a',$router['a'],NULL);
            $this->ejecuta_test('Verificar m',$router['m'],NULL);
            $this->ejecuta_test('Verificar args',$router['args'],array('palabra_a_buscar'));
            $this->ejecuta_test('Verificar e',$router['e'],'buscar');
            $this->ejecuta_test('Verificar enlace_relativo', $router['enlace_relativo'],'./');
            $this->ejecuta_test('Verificar mime/type',$router['mime_type'],'text/html');
            $this->ejecuta_test('Verificar formato',$router['formato'],'html');

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

            echo '<br />'.$url.'<br />';

            $this->ejecuta_test('Verficar url',$router['url'],$contenido);
            $this->ejecuta_test('Verficar s',$router['s'],'');
            $this->ejecuta_test('Verficar c',$router['c'],$contenido);
            $this->ejecuta_test('Verficar dd',$router['dd'],'File/es/');
            $this->ejecuta_test('Verficar d',$router['d'],'File/es/');
            $this->ejecuta_test('Verficar ii',$router['ii'],'es');
            $this->ejecuta_test('Verficar i',$router['i'],'ca');
            $this->ejecuta_test('Verficar a',$router['a'],'borrar');
            $this->ejecuta_test('Verficar m',$router['m'],'contenidos');
            $this->ejecuta_test('Verficar args',$router['args'],array('12','28'));
            $this->ejecuta_test('Verficar e',$router['e'],NULL);
            $this->ejecuta_test('Verficar enlace_relativo', $router['enlace_relativo'],'./');
            $this->ejecuta_test('Verficar mime/type',$router['mime_type'],'text/html');
            $this->ejecuta_test('Verficar formato',$router['formato'],'ajax');

            }

         }

      $url = 'ca/ajax/buscar/literal/';
      $router = Router::desglosarUrl($url);

      echo '<p>'.$url.'</p>';

      $this->ejecuta_test('Verificar url',$router['url'],'');
      $this->ejecuta_test('Verificar s',$router['s'],'');
      $this->ejecuta_test('Verificar c',$router['c'],'');
      $this->ejecuta_test('Verificar dd',$router['dd'],'File/es/');
      $this->ejecuta_test('Verificar d',$router['d'],'File/es/');
      $this->ejecuta_test('Verificar ii',$router['ii'],'es');
      $this->ejecuta_test('Verificar i',$router['i'],'ca');
      $this->ejecuta_test('Verificar a',$router['e'],'buscar');
      $this->ejecuta_test('Verificar args',$router['args'],array('literal'));

      }

   /**
    * Ejecutar métodos test de los módulos
    *
    * Buscamos en todos los módulos si hay un metodo test en tal caso se lanza
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

      permiso(8);

      $gcm->tema = 'admin';
      $gcm->plantilla = 'administrando.html';

      $gcm->event->anular('titulo','admin');
      $gcm->event->anular('contenido','admin');

      $gcm->titulo = 'Visualizar conexiones';

      $url = 'http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/?eVisualizar';

      echo '<p id="ver_plantilla"><a id="boton_ver_plantilla" class="boton" href="?eVisualizar=1" onclick="ver_plantilla();return false;">Ver plantilla</a></p>';

      ?>
      <script>
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
    * Editar conexión
    *
    * @param  $e Evento
    * @param  $args Argumentos
    * @return TRUE/FALSE
    **/
   
   function editar_conexion($e,$args=NULL) {
   
      global $gcm;

      permiso(8);

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

      if ( ! $diff_usuario ) echo '<p class="aviso">Sin diferencias con la versión por defecto</p>';

      ?>
      <form action="<? echo $_SERVER['SCRIPT_NAME'] ?>" method="post">
      <fieldset>
      <legend  accesskey="s">Eventos para usuario</legend>
      <textarea name="contenido" style="width:100%;height:300px"><?=$contenido_usuario?></textarea>
      <input type="hidden" name="m" value="admin">
      <input type="hidden" name="a" value="modificar_conexion">
      <input type="hidden" name="modulo" value="<?=$modulo?>">
      <input type="hidden" name="tipo" value="usuario">
      <input type='submit' value='<?=literal('Guardar')?>' />
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
         
         if ( ! $diff_admin ) echo '<p class="aviso">Sin diferencias con la versión por defecto</p>';
         ?>
         <form action="<? echo $_SERVER['SCRIPT_NAME'] ?>" method="post">
         <fieldset>
         <legend  accesskey="s">Eventos para administración</legend>
         <textarea name="contenido" style="width:100%;height:300px"><?=$contenido_admin?></textarea>
         <input type="hidden" name="m" value="admin">
         <input type="hidden" name="a" value="modificar_conexion">
         <input type="hidden" name="modulo" value="<?=$modulo?>">
         <input type="hidden" name="tipo" value="admin">
         <input type='submit' value='<?=literal('Guardar')?>' />
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

      permiso(8);

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
   
   }
?>
