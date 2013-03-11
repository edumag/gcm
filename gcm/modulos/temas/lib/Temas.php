<?php

/**
 * Gcm - Gestor de contenido mamedu
 *
 * @category Gcm
 * @package Modulos
 * @subpackage Temas
 * @author    Eduardo Magrané
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU/GPL
 * @version   SVN $Id: Temas.php 651 2012-10-17 09:19:07Z eduardo $ 
 */

require_once(GCM_DIR.'lib/int/temas/lib/TemaGcm.php');

require_once(GCM_DIR.'lib/ext/detectar_navegador/browser_class_inc.php');

/** 
 * @brief Sistema para la utilización de temas
 *
 * Con esta clase se puede tener un tema por defecto que recoja los archivos css y html
 * de cada módulo.
 *
 * Permitiendo añadir los ficheros del tema seleccionado que sobreescribiran a los del 
 * tema por defecto.
 *
 * Presentamos un formulario para que pueda modificarse un fichero en concreto que pasara
 * a formar parte del tema que estemos editando.
 *
 * Tener en cuenta que los archivos css admiten código php que nos facilita la modificación
 * de variables que podamos utilizar dentro de los css.
 *
 * Dentro de una carpeta de un tema determinado se espera que hayan las siguientes carpetas:
 *
 * - css:    Archivos css que suplantes a los del tema por defecto.
 * - html:   Archivos html.
 * - iconos: Iconos del tema. No es necesario la extensión.
 * - js:     Archivos javascript.
 * - libjs:  Librerías de javascript como jquery
 *
 * Dentro del archivo html se encuentra la plantilla principal.html que es la plantilla con los
 * bloques correspondientes que seran interpretados por los eventos. Así si tenemos un bloque 
 * {columna} Gcm lanza el evento columna para que los módulos lo rellenen con contenido.
 *
 * css según navegador, se ha implementado un sistema que nos permite tener archivos css para cada
 * navegador especifico, teniendo en cuenta: la plataforma, el navegador y su versión. Dentro de 
 * la carpeta del tema se buscara una subcarpeta con el nombre 'condicion' donde deben estar los 
 * archivos css especificos para el navegador actual. @see coincide_archivo_css 
 *
 *
 * @todo 
 *
 * - Previsualizar
 * - Borrar fichero de tema
 * - Configurar tema por defecto en administrador de temas
 * - Aplicar plantilla() en otros módulos
 * - Metodo icono que nos presente el icono correspondiente o su grafica alternativa si no
 *   lo encuentra.
 * - Estaria bien diferenciar entre archivos de administración y de usuarios
 *
 */

class Temas extends Modulos {

   public $plataforma;                 ///< Plataforma del visitante
   public $navegador;                  ///< Navegador del visitante
   public $version;                    ///< Versión del navegador

   protected $tema;                    ///< Instancia de 'Tema'
   protected $ficheros_xdefecto;       ///< Lista de los ficheros de los módulos
   protected $ficheros_tema;           ///< Ficheros del tema seleccionado
   protected $colores;                 ///< Array de colores del tema

   protected $dir_modulos;             ///< Directorio de módulos xdefecto
   protected $dir_temas = 'temasGcm/'; ///< Directorio de tema de proyecto

   protected $tema_actual;             ///< Nombre del tema a presentar
   protected $dir_tema_actual;         ///< Directorio de tema actual
   protected $fich_colores;            ///< Fichero con el array de los colores

   private $tipos_ficheros = array('css','html','js','img','iconos','libjs');  ///< Tipos de ficheros a buscar

   private $Titulo;       ///< Título de proyecto definido en el módulo metatags
   private $Subtitulo;    ///< Subtítulo de proyecto definido en el módulo metatags

   /**
    * Constructor
    *
    * @todo Si se requiere generar el tema admin se hace
    *
    * - Construimos lista de ficheros
    * - Construimos lista de colores
    * - Instanciamos Tema
    *
    */

   function __construct() {

      global $gcm;

      parent::__construct();

      $tema_actual = $gcm->tema;

      // Si nos llega el tema por get lo pasamos a sesión para mantenerlo

      if ( isset($_REQUEST['tema']) ) {
         $tema_actual = $_REQUEST['tema'];
         $_SESSION[$gcm->config('admin','Proyecto').'_tema'] = $tema_actual;
      } elseif ( isset($_SESSION[$gcm->config('admin','Proyecto').'_tema']) ) {
         $tema_actual = $_SESSION[$gcm->config('admin','Proyecto').'_tema'];
         }

      $b = new browser();
      $bb = $b->whatBrowser(); 

      $this->navegador  = $bb['browsertype'];
      $this->version    = $bb['version'];
      $this->plataforma = $bb['platform'];

      $this->Titulo = $gcm->config('metatags','title');
      $this->Subtitulo = $gcm->config('metatags','subject');

      $tema_conf = $this->config('tema_actual');
      //echo "<p>Tema: <b>".$tema_conf."</b></p>";
      $this->tema_actual  = ( $tema_actual ) ? $tema_actual : $tema_conf ;
      $this->dir_tema_actual = ( $this->tema_actual ) ? $this->dir_temas.$this->tema_actual.'/' : FALSE;
      $this->dir_modulos = GCM_DIR.'modulos/';

      registrar(__FILE__,__LINE__,'Tema actual: '.$this->tema_actual);

      /* Lista de colores */

      if ( file_exists($this->dir_tema_actual.'modulos/temas/css/colores.php') ) {
         $this->fich_colores = $this->dir_tema_actual.'modulos/temas/css/colores.php';
      } else {
         $this->fich_colores = $this->dir_modulos.'temas/css/colores.php';
         }

      $this->contruir_lista_colores();

      /* Construimos listado de ficheros de módulos */

      if ( ! is_dir($this->dir_modulos) ) {

         registrar(__FILE__,__LINE__,"Directorio de modulos [".$this->dir_modulos."] no encontrado",'ADMIN');

      } else {
         
         $this->ficheros_xdefecto = $this->buscar_fichero($this->dir_modulos);

         /* Si tenemos módulos del proyecto tambien añadimos */

         if ( is_dir('modulos')  ) {

            $ficheros_modulos_proyecto = $this->buscar_fichero('modulos/');
            foreach ( $this->ficheros_xdefecto as $key => $valor ) {
               if ( isset($ficheros_modulos_proyecto[$key])  ) {
                  $this->ficheros_xdefecto[$key] = array_merge($this->ficheros_xdefecto[$key],$ficheros_modulos_proyecto[$key]);
                  }
               }

            }
         }


      /* Añadimos o modificamos listado de ficheros css si coincide con el css
       * de algún módulo prevalece el del tema
       */

      if ( is_dir($this->dir_tema_actual) ) {

         $this->ficheros_tema = $this->buscar_fichero($this->dir_tema_actual.'modulos/');

         }

      if ( !empty($this->ficheros_tema) ) {

            foreach ( $this->ficheros_xdefecto as $key => $valor ) {
               if ( isset($this->ficheros_tema[$key])  ) {
                  $ficheros[$key] = array_merge($this->ficheros_xdefecto[$key],$this->ficheros_tema[$key]);
               } else {
                  $ficheros[$key] = $this->ficheros_xdefecto[$key];
                  }
               }

      } else {
         $ficheros = $this->ficheros_xdefecto; 
         }

      registrar(__FILE__,__LINE__,'Ficheros del tema: '.depurar($ficheros));
      registrar(__FILE__,__LINE__,'Colores del tema: '.depurar($this->colores));
      registrar(__FILE__,__LINE__,'Sistema: '.$this->plataforma.' Navegador: '.$this->navegador.' Versión: '.$this->version);

      $this->tema = new TemaGcm($ficheros,$this->colores);

      }

   /**
    * Construir lista de colores
    */

   function contruir_lista_colores() {

      include($this->fich_colores);
      if ( isset($colores) ) $this->colores = $colores;

      }

   /**
    * Buscamos ficheros de tema y lo añadimos a listado
    */

   private function buscar_fichero($dir) {

      $ficheros = array();

      foreach ( $this->tipos_ficheros as $tipo) {

         if ( glob($dir.'*/'.$tipo.'/*') ) {

            foreach ( glob($dir.'*/'.$tipo.'/*'.'*') as $fich) {
               $llave = str_replace($dir,'',$fich);
               $ficheros[$tipo][$llave] = $fich; 
               }
            }

         /* Si es tipo iconos miramos tambien en subdirectorios */

         if ( $tipo == 'iconos' && glob($dir.'*/'.$tipo.'/*/*') ) {

            foreach ( glob($dir.'*/'.$tipo.'/*/*'.'*') as $fich) {
               $llave = str_replace($dir,'',$fich);
               $ficheros[$tipo][$llave] = $fich; 
               }
            }


         }

      return $ficheros;

      }

   /**
    * Incluir javascript en html
    */
    
   function incluir_javascript() {

      global $gcm;

      $ficheros  = $gcm->lista_js();
      $librerias = $gcm->lista_lib_js();
      $url_base  = Router::$dir;

      $this->tema->incluir_javascript($ficheros,$librerias,$url_base);

      }

   /**
    * Devolver color
    *
    * @param $capa Capa del color
    * @param $color Identificador del color
    */

   function color($color, $valor=FALSE) { 

      $return = $this->tema->color($color, $valor); 

      // if ( $return == 'red' ) {
      //    // Todo esto no actua, los css utilizan directamente color()
      //    // de temaGcm.
      //    //
      //    // Si devuelve red es que no encontro el color definido.
      //    // Guardamos los colores en caso de estar administrando
      //    // para tener referencia de los que falta definir.
      //    if ( permiso('administrar_temas') ) {
      //       $this->guardar_colores();
      //    } else {
      //       echo 'SIN PERMISOS';
      //       }
      //    }

      return $return;

      }

   /** Construir css */

   function construir_css() { 

      echo "\n/* Tema actual: ".$this->tema_actual." */\n";
      return $this->tema->construir_css(); 
      }

   /** Construir js */

   function construir_js() { return $this->tema->construir_js(); }

   /** Ruta de archivo */

   function ruta($modulo,$tipo,$archivo) { return $this->tema->ruta($modulo, $tipo, $archivo); }

   /** 
    * Presentar pie de página, si tenemos archivo en tema actual 
    * <tema>/modulos/tema/html/pie.html lo incluimos en caso 
    * contrario, contruimos uno con los datos de gcm.
    */

   function pie($e, $args) {

      $pie = $this->ruta('temas','html','pie.html');

      if ( $pie ) {
         include($pie);
      } else {
         return FALSE;
         }

      }

   /**
    * Presentar el titulo de la página si no hay plantilla de título
    * <tema>/modulos/temas/html/titulo.html añadimos titulo de proyecto
    * y subtitulo de la configuración del proyecto.
    */

   function presentar_titulo($e, $args) {

      global $gcm;

      $ruta = $this->ruta('temas','html','titulo.html');

      if ( $ruta ) {
         include($ruta);
      } else {
         registrar(__FILE__,__LINE__,'No tenemos plantilla ['.$ruta.']');
         ?>
         <a href="" title="<?php echo literal('inicio');?>">
            <h1>
               <?php echo literal($this->Titulo,1); ?>
            </h1>
            <h2>
               <?php echo literal($this->Subtitulo,1); ?>
            </h2>
         </a>
         <?php
         }

      }

   /** 
    * panel
    *
    * creación de un panel con funcionalidades de javascript
    *
    * @param panel Array con la información del panel
    *              titulo:        Título del panel
    *              oculto:        TRUE/FALSE Em pieza oculto o visible
    *              href:          Enlace al que apunta
    *              ajax:          javascript a ejecutar al presentar contenido
    *              jajax:         Lanzamos load() de jquery sobre subpanel_visible con la url indicada
    *                             Con una simple url, nos añadira el contenido en el subpanel.
    *                             Prevalece jaxax sobre ajax.
    *              contenido:     string con el contenido del panel
    *              altura_maxima: Altura maxima para el subpanel
    * 
    * @return TRUE/FALSE
    *
    * @todo Creo que se puede evitar tener que definir href 
    */

   static function panel($panel) {

      global $gcm;

      $subpanel_class = ( isset($panel['oculto']) && $panel['oculto'] == TRUE ) ? 'subpanel_oculto' : 'subpanel_visible';
      $enlace = ( isset($panel['href']) ) ? $panel['href'] : '#' ;

      include($gcm->event->instancias['temas']->ruta('temas','html','panel.html'));

      }

   /**
    * Recoger datos de Router para saber si se esta pidiendo
    * algun fichero de temas, proyecto.css o proyecto.js
    *
    */

   function inspeccionar_router($e,$args) {

      global $gcm;

      $this->javascripts('general.js');
      $this->javascripts('paneles.js');
      $this->librerias_js('jquery.js');

      if ( Router::$c == 'proyectos.css' ) {
         header('Content-Type: text/css');
         $tema_mostrar = ( empty($_GET['t']) ) ? $this->tema_actual : $_GET['t'];
         // echo $this->construir_css();
         // exit();
         // No funciona al hacer cache
         $gcm->salida = $this->construir_css();
         $gcm->salir();
         }

      if ( Router::$c == 'proyecto.js' ) {
         header('Content-Type: text/javascript');
         $gcm->salida = $this->construir_js();
         $gcm->salir();
         }

      }

   /**
    * Devolver ruta de icono
    *
    * @param $icono Nombre de icono sin extensión
    */
    
   function icono($icono) {

      $retorno = $this->tema->icono($icono);

      if ( $retorno ) return $retorno;

      registrar(__FILE__,__LINE__,'No se encontro icono ['.$icono.']',
         'ADMIN');
      return FALSE;

      }

   /**
    * Ocultar contenido desde título
    */
               
   function ocultar_contenido_desde_titulo($e,$args=NULL) {

      global $gcm;

      $this->javascripts('ocultar_contenido_desde_titulo.js');

      }

    /**
     * incluir_javascript_heads
     *
     * Añadimos javascript en los heads de la plantilla
     *
     * Buscamos si tenemos archivos javascript especifico de navegador, si
     * es así lo añadimos a parte.
     */

    function incluir_javascript_head($e, $args=NULL) {

       echo "\n<script>\n";
       $f = $this->ruta('temas','js','javascript_head.js');
       include ($f);
       echo "\n".'</script>';

      }

    /**
     * incluir_css_heads
     *
     * Añadimos css en los heads de la plantilla
     *
     * Buscamos si tenemos archivos css especifico de navegador en la carpeta
     * 'condicion' del tema actual, si es así lo añadimos a parte.
     *
     * @see coincide_archivo_css
     */

    function incluir_css_head($e, $args=NULL) {

       registrar(__FILE__,__LINE__,'Tema actual: '.$this->tema_actual);

       echo "\n".'<style type="text/css" media="screen, projection">';
       echo "\n".'   @import "proyectos.css?tema='.$this->tema_actual.'&u='.Router::$d.Router::$s.Router::$c.'";';
       echo "\n".'</style>';

       // Archivos condicionales

       if ( ! file_exists($this->dir_tema_actual.'condicion') ) {
          return;
         }

      $archivos = glob($this->dir_tema_actual.'condicion/*');

      if ( empty($archivos) ) return;

      $archivos = array_filter($archivos, array($this,"coincide_archivo_css"));

      if ( empty($archivos) ) return;

      foreach ( $archivos as $archivo ) {
         echo "\n".'<style type="text/css" media="screen, projection">';
         echo "\n".'   @import "'.$archivo.'";';
         echo "\n".'</style>';
         }

      }

   /**
   * Buscamos si los archivos css de condiciones, entran dentro de la plataforma,
   * el navegador y versión actual
   *
   * El mecanismo se basa en archivos css con nomenclatura:
   *
   * [plataforma]_[navegador]_[version].css
   *
   * Puede añadirse un all para especificar todas, ejemplo:
   *
   * - Linux_all_all.css      Para todos los que vengan de linux.
   * - all_Safari_all.css     Navegadores safari
   * - all_Firefox_3.css      Firefox version 3
   *
   * @todo Implementar comparación de versión
   *
   * @param $nombre_archivo    Nombre del archivo
   * @param $plataforma        Plataforma en la que estamos
   * @param $navegador         Navegador actual
   * @param $version           Versión del navegador
   *
   * @return TRUE/FALSE
   */

   function coincide_archivo_css($archivo) {

      $nombre_archivo = basename($archivo);

      if ( !preg_match('/(.*)_(.*)_(.*).css/i',$nombre_archivo,$datos) ) {
         registrar(__FILE__,__LINE__,'Nombre de archivo mal formado: '.$nombre_archivo,'ERROR');
         return FALSE;
         }

      if ( $this->plataforma != $datos[1]  && $datos[1] != 'all' ) {
         return FALSE;
         }

      if ( $this->navegador != $datos[2]  && $datos[2] != 'all' ) {
         return FALSE;
         }

      registrar(__FILE__,__LINE__,'Cargamos archivo css segun navegador: '.$nombre_archivo);

      return TRUE;

      }

   /**
    * Devolver el nombre del tema actual
    */
   
   function getTema() { return $this->tema_actual; }
   
   }
?>
