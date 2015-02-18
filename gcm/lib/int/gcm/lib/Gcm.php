<?php

/**
 * @file Gcm.php
 * @brief Clase que inicia y conecta todos los elementos del framework
 * @ingroup gcm_lib
 *
 * @author    Eduardo Magrané edu.lesolivex.com
 * licencia: http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU/GPL
 */

/**
 * @defgroup gcm_api Gestión de Contenido Mamedu
 *
 * Frameworks que nos permite acelerar el proceso de construcción de una aplicación web
 *
 * Si deseamos hacer debug podemos definir $debug en fichero index.php del proyecto con la
 * palabra secreta que queramos, se tendra en cuenta a la hora de activar debug dede GET 
 * añadiendo a la url '?palabra_secreta=1'
 *                                                                                                 
 * En caso de activarse el debug se hara sobre toda la sessión hasta no marcar ?palabra_secreta=0 
 * o cerrar la session.
 *                                                                                                 
 * En producción solo se debera comentar la linea en proyecto/index.php y se anula la posibilidad 
 * de debug
 *
 * @ingroup gcm_lib
 * @{
 */

/* Notificar todos los errores de PHP (véase el registro de cambios) */
error_reporting(E_ALL);

/* Iniciamos sesión */
session_start();

/* helpers */
require("helpers.php");

/* Urls amigables */
require("Router.php");

/* Utiles */
require("GUtil.php");

/* Sistema de eventos */
require('Eventos.php');

/* Sistema de plantilla general */
require('Plantilla.php');

/* Solicitudes */
require_once(GCM_DIR.'lib/int/solicitud/lib/Solicitud.php');

/* Registros */
require_once(GCM_DIR.'lib/int/registro/lib/Registro.php');

/* Autentificación de usuarios */
require_once(GCM_DIR."lib/int/autentificacion/lib/Autentificacion.php");

/* Módulos */
require('Modulos.php');

/** Autoload */

function __autoload($clase) {

   global $gcm;

   $clase = strtolower($clase); 
   $fich = GCM_DIR.'modulos/'.$clase.'/lib/'.ucfirst($clase).'.php';

   if ( ! file_exists($fich) ) {

      // Probar con módulos del mismo proyecto
      $fich = 'modulos/'.$clase.'/lib/'.ucfirst($clase).'.php';

      if ( ! file_exists($fich) ) {
         $msg = 'Error al requerir módulo ['.$clase.'] El archivo ['.$fich.'] no existe';
         if ( $gcm ) {
            $gcm->registra(__FILE__,__LINE__,$msg,'ERROR');
         } else {
            die($msg);
            }
         return FALSE;
         }
   
      }

   require_once($fich);

   }

/* Gestionamos los errores */
set_error_handler("Gutil::gestion_errores");

/** 
 * Clase principal
 * 
 * Con la clase gcm interconectamos los eventos con los módulos.
 *
 * Una vez Detectadas las variables generales por Router, lanzamos elevento precarga
 * esto nos permite una acción rapida hacia modulos que utilizan ajax.
 *
 * @ingroup gcm_lib
 */

class Gcm {

   public $pdo    = NULL;          ///< Array con las diferentes conexión a bases de datos
   public $event;                  ///< Instancia de eventos
   public $router = NULL;          ///< Instancia de Router
   public $reg    = NULL;          ///< Instancia de Registro
   public $au     = NULL;          ///< Instancia de Autentificcion

   /**
    * Se permite desde los módulos modificar el tema a presentar, al instanciar Temas
    * mirara si hemos añadido en nombre de un tema, si es así cargara este.
    *
    * La utilidad de este atributo es permitir a los módulos cambiar el tema por defecto
    * al tema admin, ya que el tema por defecto puede no ser adecuado para la administración
    *
    * La forma de aplicarlo es añadiendo un metodo en el módulo que desea presentar el tema admin
    * que sea invocado desde el evento precarga anterior (Con prioridad 1 para asegurarse que sea
    * cogido antes de que Temas contruya los css y javascipts.
    */

   public $tema   = FALSE;

   /**
    * Array de variables en memoria que se requiera tener globalizadas sin
    * tener que utilizar las de sesión.
    *
    * uso:
    * <pre>
    * $gcm->memoria[nombre_seccion_borrada] = 'Sección borrada';
    * </pre>
    */

   public $memoria = array();

   /**
    * lista de contenidos seleccionados que nos permite generar acciones desde los módulos
    */

   public $seleccionado;

   /**
    * Contenido de la página despues de ser procesado por la plantilla
    */

   public $salida = '';

   /**
    * Sufijo para tables de base de datos
    */

   public $sufijo = '';

   /** 
    * Array con el contenido generado por los eventos lanzados desde la plantilla general
    *
    * El contenido generado por los eventos de plantilla (cabecera, contenido, columna,etc...)
    * es guardado en array privado en Gcm y se podra acceder a él o modificarlo con metodos, 
    * get_cabecera() o set_cabecera('<b>hola</b>').
    *
    * Con esto conseguimos un sistema más claro a la hora que desde los módulos se requiera 
    * modificar u obtener el contenido ya generado para la plantilla.
    *
    * Al final Gcm procesa la plantilla con el contenido que se ha obtenido de procesar los 
    * diferentes eventos de la misma.
    *
    * Desde un módulo podemos modificar contenido de la siguiente manera:
    *
    * <pre>
    * $gcm->titulo = 'Cambiando nombre de seccción';
    * </pre>
    */

   private $contenidos = array();

   /**
    * Array con los archivos javascript necesarios para la aplicación
    */

   public $javascripts = array();

   /**
    * Array con las librerias javascript necesarios para la aplicación
    */

   public $librerias_javascript = array();

   /**
    * Array con las librerias externas necesarios para la aplicación
    */

   public $librerias_externas = array();

   /** 
    * Para posibles comprobaciones, permitimos elegir no recoger la configuración
    * de los eventos desde el proyecto sino los estipulados por defecto de cada módulo
    *
    * @see Eventos
    */

   private $eventos_proyecto = TRUE ;

   /**
    * Cache para los valores de configuración de los módulos
    */

   private $config;

   /**
    * Listado de modulos basicos para el funcionamiento de la aplicación
    *
    * El resto de módulos se debe activar desde el módulo admin
    */

   public $modulos_basicos = array(
      'contenidos',
      'editar',
      'metatags',
      'idiomas',
      'literales',
      'menu',
      'imagenes',
      'admin',
      'enviomail',
      'temas',
      'ver_registros'
      );

   /**
    * Modo: 'view' o 'admin'
    */

   public $modo = 'view';

   /**
    * Constructor
    *
    * Iniciamos sistema de registros
    *
    * @see Registros
    */

   function __construct() {

      /* Debug */

      $palabra_secreta_para_debug = $this->config('admin','Palabra secreta para depurar');

      if ( isset($palabra_secreta_para_debug) ) { 
         if ( isset($_GET[$palabra_secreta_para_debug]) && $_GET[$palabra_secreta_para_debug] == 1 ) {
            DEFINE('GCM_DEBUG', TRUE);
            $_SESSION[$palabra_secreta_para_debug] = 1;
         } elseif ( isset($palabra_secreta_para_debug) && isset($_GET[$palabra_secreta_para_debug]) && $_GET[$palabra_secreta_para_debug] != 1 ) {
            DEFINE('GCM_DEBUG', FALSE);
            if ( isset($_SESSION[$palabra_secreta_para_debug])  ) $_SESSION[$palabra_secreta_para_debug] = 0;
         } elseif ( isset($_SESSION[$palabra_secreta_para_debug]) && $_SESSION[$palabra_secreta_para_debug] == 1 ) {
            DEFINE('GCM_DEBUG', TRUE);
         } elseif ( isset($_SESSION[$palabra_secreta_para_debug]) && $_SESSION[$palabra_secreta_para_debug] != 1 ) {
            DEFINE('GCM_DEBUG', FALSE);
            if ( isset($_SESSION[$palabra_secreta_para_debug])  ) $_SESSION[$palabra_secreta_para_debug] = 0;
         } else {
            DEFINE('GCM_DEBUG', FALSE);
            }
      } else {
         DEFINE('GCM_DEBUG',FALSE);
         if ( isset($_SESSION[$palabra_secreta_para_debug])  ) $_SESSION[$palabra_secreta_para_debug] = 0;
         }

      $this->sufijo = $this->config('admin','Sufijo para base de datos');

      $bd_registros = $this->config('admin','bd_conexion');

      // Si trabajamos con sqlite creamos archivos diferentes para registros para
      // evitar archivos demasiado grandes en directorio log/

      if ( strpos($bd_registros,'sqlite') !== FALSE || $bd_registros == '' ) {  

         if ( ! is_dir('log') ) mkdir('log');
         $this->reg = new Registro('log',$this->sufijo);

      } else {
         
         $this->reg = new Registro($this->pdo_conexion('registros'),$this->sufijo);

         }

      $this->registra(__FILE__,__LINE__,'Gcm->construct: REQUEST_URI: '.$_SERVER['REQUEST_URI'],'NORMAL');

      }

   /**
    * Configuración para gcm
    *
    * Si se nos pide una variable desde un módulo vamos a buscarla
    * en el archivo de configuración del proyecto, 
    * proyecto/DATOS/configuracion/[modulo]/config.php en caso de no existir
    * el archivo la cogemos del config.php del mismo módulo.
    *
    * Si se nos da un valor para una variable, siempre la colocamos en el 
    * archivo de configuración del proyecto.
    *
    * El archivo llevara la variable $config[variable][valor];
    *
    * Se utiliza para gestionar las variables 'GcmConfig'
    *
    * Para acelerar el proceso guardamos en 
    * $this->config[modulo][nombre variable] = valor los resultados, así
    * obtenemos un cache para los mismos 
    *
    * @param $modulo Módulo
    * @param $nombre_variable Nombre de la variable
    * @param $valor Valor para la variable
    *
    * @return En caso de pedir valor de variable: False en caso de no encontrar o su valor
    *         En caso de darnos un valor TRUE o FALSE dependiendo del exito de la operación
    */

   function config($modulo, $nombre_variable, $valor=FALSE) {

      require_once(GCM_DIR.'lib/int/GcmConfig/lib/GcmConfigFactory.php');

      if ( ! $modulo ) {
         registrar(__FILE__,__LINE__,"Error sin modulo definido",'ERROR');
         return FALSE;
         }
         
      // Nombre de módulo con primera letra en minusculas
      $modulo = strtolower($modulo[0]).substr($modulo,1);

      // Si se pide un valor y ya lo tenemos recogido lo devolvemos
      if ( !$valor && isset($this->config[$modulo][$nombre_variable]) ) return $this->config[$modulo][$nombre_variable];

      $dir_configuracion_proyecto = 'DATOS/configuracion/';

      if ( $valor && !file_exists($dir_configuracion_proyecto) ) mkdir($dir_configuracion_proyecto);

      $dir_configuracion_proyecto_modulo = $dir_configuracion_proyecto.$modulo.'/';

      if ( $valor && !file_exists($dir_configuracion_proyecto_modulo) ) 
         mkdir($dir_configuracion_proyecto_modulo);

      $archivo_configuracion = $dir_configuracion_proyecto_modulo.'config.php';

      $this->registra(__FILE__,__LINE__,
         'Config, modulo: ['.$modulo.'] valor: ['.$valor.'] 
         Archivo de configuración ['.$archivo_configuracion.']');

      if ( $valor ) {                  // Guardar valor de variable

         $arr = GcmConfigFactory::GetGcmConfig($archivo_configuracion);

         $arr->set($nombre_variable,$valor);
         $arr->guardar_variables();
         $this->config[$modulo][$nombre_variable] = $valor;

      } else {                         // Devoler valor de variable

         if ( !file_exists($archivo_configuracion) ) $archivo_configuracion = GCM_DIR.'modulos/'.$modulo.'/config/config.php';

         if ( !file_exists($archivo_configuracion) ) {
            $this->registra(__FILE__,__LINE__
               ,'No tenemos el archivo ['.$archivo_configuracion.'] de configuración del módulo ['.$modulo.']','ERROR');
            return FALSE;
            }

         $arr = GcmConfigFactory::GetGcmConfig($archivo_configuracion);
         $valor = $arr->get($nombre_variable);
         $this->config[$modulo][$nombre_variable] = $valor;
         return $valor;

         }

      }

   /**
    * Contruir lista de seleccionado
    *
    * la preferencia a la hora de construir la lista sera:
    *
    * - args, argumentos directos al metodo
    * - GET
    * - POST
    * - Router, contenido actual.
    */

   function construir_seleccionado($args=NULL) {

      registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$args.')');

      $parametros = recoger_parametros($args);

      if ( isset($parametros['url']) ) {

         $mens = 'Seleccionados viene por $args';
         $this->seleccionado = array($parametros['url']);

      } elseif ( isset ( $_GET['seleccionado']) ) {

         $mens = 'Seleccionados viene por _GET';
         $this->seleccionado = $_GET['seleccionado'];
         
      } elseif ( isset ( $_POST['seleccionado']) ) {

         $mens = 'Seleccionados viene por _POST';
         $this->seleccionado = $_POST['seleccionado'];

      } elseif ( isset ( $_POST['f']) ) {

         $mens = 'Seleccionados viene por _POST[$f]';
         $this->seleccionado = array($_POST['f']);

      } else {

         $mens = 'Seleccionados viene por defecto archivo actual';
         $this->seleccionado = array(Router::$d.Router::$s.Router::$c);

         }

      if ( isset($mens) ) registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$args.') '.$mens);
      registrar(__FILE__,__LINE__,depurar($this->seleccionado,'Seleccionado'));
      }

   /**
    * Añadir librerias javascript a la aplicación
    *
    * @param $modulo Módulo que lo reclama
    * @param $archivo Archivo javascript
    */

   function add_lib_js($modulo, $archivo) {
      if ( in_array($modulo.":".$archivo, $this->librerias_javascript) ) {
         registrar(__FILE__,__LINE__,'Temas->javascripts('.$modulo.','.$archivo.') Añadido anteriormente');
         return;
         }
      $this->librerias_javascript[] = $modulo.":".$archivo;
      }

   /**
    * Añadir librerias externas a la aplicación
    *
    * @param $tipo Tipo de fichero puede ser js o css
    * @param $url  Url del fichero
    */

   function add_ext_lib($tipo, $url) {

      if ( in_array($tipo.":".$url, $this->librerias_externas) ) {
         registrar(__FILE__,__LINE__,$tipo.','.$url.') Añadido anteriormente');
         return;
         }

      $this->librerias_externas[] = $tipo.":".$url;
      registrar(__FILE__,__LINE__,"Contenido de librerias_externas",'DEBUG',depurar($this->librerias_externas));
      
      }

   /**
    * Añadir archivos javascript a la aplicación
    *
    * @param $modulo Módulo que lo reclama
    * @param $archivo Archivo javascript
    */

   function add_js($modulo, $archivo) {
      if ( in_array($modulo.":".$archivo, $this->javascripts) ) {
         registrar(__FILE__,__LINE__,'Temas->javascripts('.$modulo.','.$archivo.') Añadido anteriormente');
         return;
         }
      $this->javascripts[] = $modulo.":".$archivo;
      }

   /** 
    * Devolver conexión con base de datos en caso de existir o crearla
    * segun $BD que se nos pasa.
    *
    * @todo Si no tenemos una base de datos, suponemos que estamos en una instalación 
    *       nueva, lanzamos evento instalacion_nueva para que se configure y a 
    *       continuación lanzar el evento instalación.
    *       Esto permite que primero se configure para saber por ejemplo si se quiere 
    *       utilizar un servidor de base de datos o se utiliza sqlite, que en tal 
    *       caso se debera crear directorios a la hora de instalar.
    *
    * @author Eduardo Magrané
    * @version 1.0
    *
    * @param $conexion Conexión que se desea 
    *
    * @return Instancia de pdo
    *
    */

   function pdo_conexion($conexion='principal') {

      // Si ya tenemos conexión la devolvemos
      if ( isset($this->pdo[$conexion]) ) {
         return $this->pdo[$conexion];
         }

      // Ubicación de la aplicación, por defecto local
      $ubicacion = 'local';

      // Comprobamos configuración de proyecto en archivo modulos/admin/config.php

      $bd_conexion = $this->config('admin','bd_conexion');
      $bd_usuario = $this->config('admin','bd_usuario');
      $bd_pass = $this->config('admin','bd_pass');

      $driver = explode(':',$bd_conexion);
      $motor = $driver[0];
      $archivo = $driver[1];

      if ( $motor == "sqlite" ) {

         /* Comprobar directorio de la base de datos */

         $dir = dirname($archivo);

         if ( ! mkdir_recursivo($dir) ) {
            registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$e.','.depurar($args).') No se pudo crear el directorio para la base de datos ['.$dir.']','ERROR');
            exit();
            }

         }

      $this->registra(__FILE__,__LINE__,'Creamos nueva conexión a base de datos','DEBUG');

      try {

         if ( $motor == "sqlite" ) {

            $this->pdo[$conexion] = new PDO($bd_conexion);

         } elseif ( $motor == 'mysql' ) {

            $this->pdo[$conexion] = new PDO($bd_conexion, $bd_usuario,$bd_pass,
               array(PDO::MYSQL_ATTR_INIT_COMMAND =>  "SET NAMES utf8"));

         } else {

            $this->pdo[$conexion] = new PDO($bd_conexion, $bd_usuario, $bd_pass);

            }

         $this->pdo[$conexion]->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
         $this->pdo[$conexion]->setAttribute(PDO::ATTR_PERSISTENT, TRUE);

      } catch (Exception $ex) {

         echo "<p class='error'>";
         echo "Error con la conexión a base de datos";
         echo "<p>Configure correctamente el archivo del módulo admin</p>";
         if ( GCM_DEBUG  ) {
            echo '<br />';
            echo $ex->getMessage();
            echo '<br />';
            echo $ex->getFile(),'@',$ex->getLine();
         }
         echo "</p>";

         exit();
         registrar($ex->getFile(),$ex->getLine,$ex->getMessage(),'ERROR');

         }

      return $this->pdo[$conexion];

      }

   /**
    * Recuperar valores sobre elementos de contenidos
    */

   function __get($elemento) {

      if ( isset($this->contenidos[$elemento] )  ) {
         registrar(__FILE__,__LINE__,'Gcm->__get('.$elemento.')','DEBUG',$this->contenidos[$elemento]);
         return $this->contenidos[$elemento];
         }

      registrar(__FILE__,__LINE__,'Gcm->__get('.$elemento.'): No esta definido');
      return FALSE;
      }

   /**
    * Recuperar valores sobre elementos de contenidos
    */

   function __set($elemento, $valor) {

      registrar(__FILE__,__LINE__,'Gcm->__set('.$elemento.')','DEBUG',$valor);
      $this->contenidos[$elemento] = $valor;

      }

   /** Comprobación de un elemento de contenidos */

   function __isset($elemento) {

      return isset($this->contenidos[$elemento]);

      }

   /** Registra las acciones y avisos de la aplicación */

   function registra($fichero,$linea,$mensaje, $tipo='DEBUG', $descripcion = NULL) {

      if ( $this->reg instanceof Registro ) {
         $this->reg->registra($fichero,$linea,$mensaje, $tipo, $descripcion);
      } else {
         // registrar(__FILE__,__LINE__,"No hay instancia de Registro",'ERROR');
         }
      }

   /**
    * Este metodo es utilizado por la clase Plantilla.php.
    *
    * Guardamos en contenidos el resultado de ejecutar los eventos con el 
    * mismo nombre que los elementos de la plantilla.
    *
    * El resultado se suma en cado de que ya tengamos contenido en ellos
    * Y se devuelve el resultado.
    *
    * En caso de que el evento sea el titulo se añadira un div con class
    * titulo_principal.
    */

   function procesaContenido($evento) {

      $this->registra(__FILE__,__LINE__,'Gcm->procesaContenido('.$evento.')');

      ob_start();

      try {

         $this->event->lanzarEvento($evento);

      } catch (Exception $e) {

         $this->registra(__FILE__,__LINE__,'Gcm->procesaContenido: Error en evento ['.$evento.'] '.$e->getMessage(),'DEBUG');
         }

      $this->contenidos[$evento] = ( isset($this->contenidos[$evento]) ) ? $this->contenidos[$evento].ob_get_contents() : ob_get_contents();

      ob_end_clean();

      // Aun sin tener contenido procesamos para evitar que se muestre {contenido}
      // if ( empty($this->contenidos[$evento])  ) {
      //    registrar(__FILE__,__LINE__,'Gcm->procesaContenido: Evento ['.$evento.'] sin contenido');
      //    return FALSE;
      //    }

      $salida  = PHP_EOL.'<!-- '.$evento.' -->'.PHP_EOL;
      if ( $evento == 'titulo' ) {
         $salida .= '<div class="titulo_principal">'.$this->contenidos[$evento].'</div>';
      } else {
         $salida .= $this->contenidos[$evento];
         }
      $salida .= PHP_EOL.'<!-- / '.$evento.' -->'.PHP_EOL;
      return $salida;

      }

   /** Metodo de salida */

   function salir() {

      registrar(__FILE__,__LINE__,'Gcm->salir()');

      if ( $this->event->verificar_evento('postcarga') ) $this->event->lanzarEvento('postcarga');

      /* Si el cliente lo acepta le enviamos la página comprimida */

      // if ( !$this->au->logeado() && strstr($_SERVER['HTTP_ACCEPT_ENCODING'], 'gzip') && !GCM_DEBUG ) {
      //    header('Content-Encoding: gzip');
      //    echo gzencode($this->salida,9);
      //    exit();
      //    }

      echo $this->salida;
      exit();

      }

   /** 
    * Presentar página 
    *
    * Procesamos plantilla html del tema actual (modulos/temas/html/principal.html)
    *
    */

   function presentar_pagina() {

      $plantilla_url = $this->event->instancias['temas']->ruta('temas','html','principal.html');

      if ( !$plantilla_url ) {
         $plantilla_url = $this->tema->ruta('temas','html','principal.html');
         }

      registrar(__FILE__,__LINE__,"Gcm->presentar_pagina: Procesamos plantilla html [".$plantilla_url."]");

      $plantilla = new Plantilla($plantilla_url, $this);
      $pagina = $plantilla->procesaPlantilla();

      return $pagina;

      }

   /** 
    * Iniciamos aplicación 
    *
    * - Comprobamos instalación
    * - Cargamos módulo de autentificación
    * - Definimos tema que se debe mostrar
    * - Guardamos salida en buffer
    * - Comprobamos si se desea salir de sesión
    * - Comprobamos si se desea entrar en sesión
    * - Recogemos eventos por defecto
    * - Lanzamos evento precarga
    * - Si hay módulo definido generamos acción
    * - Si tenemos un evento definido lo lanzamos
    * - Se guarda en $this->contenidos['contenido'] la salida generada
    * - Si el formato es ajax salimos, sino presentamos página
    */

   function inicia() {

      /* Comprobar instalación */

      $this->au = new Autentificacion($this->pdo_conexion(), $this->sufijo);

      /* Si no estamos debugeando o logeados mostramos tambien los mensajes tipo ADMIN */

      if ( GCM_DEBUG || $this->au->logeado() ) $this->reg->nivel('ADMIN');

      /* Comprobar administrador que nos llega */

      if ( isset($_POST['loginPro']) ){
         if ( $this->au->entrar($_POST["loginPro"],$_POST["passwd"]) ) {
            $this->registra(__FILE__,__LINE__,'gcm->inicia: Usuario [ '.$_POST['loginPro'].' ] registrado');
            }
         }

      /* ¿Forzar a coger los eventos por defecto? */

      if ( $this->au->logeado() ) {
         $this->eventos_proyecto = ( isset($_GET['eGcm']) ) ? FALSE : TRUE ;
         $visualizar = ( isset($_GET['eVisualizar']) ) ? TRUE : FALSE ;
         $this->event = new Eventos(GCM_DIR.'modulos/',TRUE,$this->eventos_proyecto,$visualizar);
      } else {
         $this->event = new Eventos(GCM_DIR.'modulos/',FALSE);
         }

      $this->router = Router::getInstance();
      $this->router->inicia();

      /** 
       * Ubicación de la carpeta de gcm desde proyecto
       */

      DEFINE('GCM_DIR_P',Router::$dir.GCM_DIR);

      $this->contenidos['contenido'] = '';     //< Contenido central

      $this->construir_seleccionado();

      // Definimos modo
     $var_administrando = 'administrando_'.$this->config('admin','Proyecto');

     // Comprobamos si se desea modo admin
     if ( isset($_GET['administrando']) 
          && $_GET['administrando'] == 1 
        ) {

       $_SESSION[$var_administrando] = 1 ;
      }

     // Limpiamos variable de sesión administrando
     if ( ( ! $this->au->logeado() && isset($_SESSION[$var_administrando]) )
       || ( isset($_SESSION[$var_administrando]) && isset($_GET['administrando']) && $_GET['administrando'] == 0 ) 
     ) {
       unset($_SESSION[$var_administrando]);
        }

     if ( isset($_SESSION[$var_administrando]) ) {
       $this->modo = 'admin';
     } else {
       $this->modo = 'view';
     }

      /* Guardamos salida en buffer */
      ob_start();

      /* Definimos el tema a mostrar sino se hizo ya desde precarga */

      //$this->tema = new Temas();

      $this->registra(__FILE__,__LINE__,'gcm->inicia: Url:'.$_SERVER['REQUEST_URI']);

      $this->event->lanzarEvento('precarga');

      registrar(__FILE__,__LINE__,'Tema actual despues de salir de precarga: '.$this->event->instancias['temas']->getTema());

      /* Si tenemos un módulo definido lo cargamos */

      if ( Router::$m ) {
         $m = Router::$m;
         $a = Router::$a;
         $this->registra(__FILE__,__LINE__,'gcm->inicia: Recibimos petición de módulo ['.$m.'] con acción ['.$a.']','DEBUG');
         if ( Router::$formato == 'ajax' ) {
            $this->event->lanzar_accion_modulo($m,$a);
            $salida = ob_get_contents(); ob_end_clean();
            $this->salida = $salida;
            $this->salir();
         } else {
            $this->event->lanzar_accion_modulo($m,$a);
            }
         }

      /** Si tenemos un evento definido **/

      if ( Router::$e ) {
         $this->registra(__FILE__,__LINE__,"gcm->inicia: Tenemos un evento definido[".Router::$e.']');
         try {
            $this->event->lanzarEvento(Router::$e);
         } catch (Exception $ex) {
            $this->registra($ex->getFile(),$ex->getLine(),"gcm->inicia: Error al lanzar evento: ".$ex->getMessage(),'ERROR');
            }
         if ( Router::$formato == 'ajax' ) {
            $salida = ob_get_contents(); ob_end_clean();
            $this->salida = $this->contenido;
            $this->salida .= $salida;
            $this->salir();
            }
         }

      /* Guardamos en un contenidos['contenido'] la salida producida hasta ahora */

      $this->contenidos['contenido'] .= ob_get_contents(); ob_end_clean();

      if ( Router::$formato == 'ajax'  ) {
         $this->salir();
         }

      $this->salida = $this->presentar_pagina();

      $this->salir();

      }

   }

/** @} */
?>
