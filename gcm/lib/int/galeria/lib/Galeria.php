<?php

/** 
 * @file Galeria.php
 * Clase Galeria para la gestión de las galerías
 */

if (!defined('GCM_DIR')) {
   echo 'GCM_DIR debe estar definido';
   exit();
   //define('GCM_DIR',dirname(__FILE__).'/../../../../../gcm/');
   }

if ( ! function_exists('literal') ) { function literal($l,$t=0) { echo $l; } }

if ( ! function_exists('registrar') ) {

function registrar($file, $line,$mensaje, $tipo='DEBUG') {

   $_SESSION['msg'][] = $mensaje;
   return;

   ?>
   <div class="<?php echo strtolower($tipo); ?>"><?php echo $mensaje; ?></div>
   <?php

   }
}

require_once(dirname(__FILE__).'/../../../../lib/int/gcm/lib/helpers.php');
require_once(dirname(__FILE__).'/Imatge.php');
require_once(dirname(__FILE__).'/DescripcionesGalerias.php');

/** 
 * @class Galeria
 *
 * Aquest mòdul fa la funció de gestionar tot el relacionat amb les imatges de l'aplicació, 
 * visualització de galeries completes, visualització d'una imatge en concret, inserció, esborrat, etc
 *
 * @warning
 *   El formulario que contendra la galería no debe tener un campo con el nombre submit, para evitar 
 *   interferencias que darían error con javascript.
 *
 * Tenemos un ejemplo donde hacer pruebas en test/GaleriaTest.php
 *
 * @todo Cron deberá borrar tambien los directorios viejos.
 */

class Galeria implements Iterator {

   /**
    * Configuración
    */

   public $config;

   /** 
    * Carpeta definitiva
    *
    * Juntamos la url de las galerías con la de la galería en concreto
    *
    * En caso de ser temporal sera $dir_tmp.session_id.'/'
    */

   public $galeria_url = FALSE;

   /**
    * Podemos crear atributos de galeria para poder utilizarlos en las plantillas de presentación.
    *
    * <pre>
    * $galeria = GaleriaFactory::galeria('galeries_comentaris','Mysql',$idComentari);
    * $galeria->plantilla_presentacio = 'piulades';
    * $galeria->atributs = array('alt' => $vComentari);
    * if ( $galeria->count() > 0 ) {
    *    $galeria->presentaGaleria();
    * }
    * </pre>
    */

   public $atributs;

   /**
    * Identificador de galería
    *
    * la carpeta on estan las imatges
    */

   public $id;

   /** Si no tenim galeria traballem en modo termporal */

   public $temporal = FALSE;

   /** Identifiador unic */

   public $identificador_unic;

   public $connector           = NULL; ///< Funció javascript que rep el numero de miniatura que es va pujar
   public $accio_esborra       = NULL; ///< Funció javascript que rep avis de imatge esborrada

   private $fitxer_js;                 ///< Fitxer amb al javascript necesari per editar galería

   public $imatges;                    ///< Instancias de imatges

   private $puntero;                   ///< Puntero

   protected $loaded = FALSE ;         ///< Para saber si ya se fue a buscar la información

   public $carga_php_general = FALSE ; ///< Torna la ubicació (respecta a GCM_DIR) de un arxiu php general, aqui pot averi les trocadas els css

   public $carga_js  = FALSE ;         ///< Codi javascript per activa la galería individualment

   /**
    * Devuelve la ubicación ( Respecto a GCM_DIR ) del archivo con el javascript necesarios para la presentación, 
    * solo debe cargarse una vez y antes del javascript individual de cada galería.
    */

   public $carga_js_general = FALSE ;

   /** Cargar css TRUE / FALSE */

   public $incluir_css = TRUE ;

   /**
    * Instancia de descripciones
    */

   public $descripcions = FALSE ;

   /**
    * Constructor
    *
    * @param $file_config Archivo con la configuración
    * @param $id Identificador de la galeria, si no tenemos identificador la 
    *            galería es temporal
    */

   function __construct($file_config=FALSE, $id=FALSE) {

      if ( ! $file_config ) {
         $msg = "Error hay que definir archivo con configuración";
         registrar(__FILE__,__LINE__,$msg,'ERROR');
         }
      if ( ! file_exists($file_config) ) {
         $msg = "Error no existe archivo de configuración [$file_config]";
         registrar(__FILE__,__LINE__,$msg,'ERROR');
         }

      include($file_config);

      /**
       * Configuración por defecto
       */

      $this->config['amplaria_max']            = 600;                  ///< Amplària maxima 600px per defecta
      $this->config['altura_max']              = 400;                  ///< Altura max 400px por defecta
      $this->config['amplada_presentacio']     = 150;                  ///< Amplada de miniatura
      $this->config['altura_presentacio']      = 180;                  ///< Altura de miniatura
      $this->config['limit_imatges']           = 5;                    ///< Limit de imatges 
      $this->config['grandaria_max']           = FALSE;                ///< Grandaría max de la imatge (pes) 
      $this->config['nom_div_galeria']         = 'galeriaDIV';         ///< Per a diferenciar entre galeries 
      $this->config['plantilla_presentacio']   = FALSE;                ///< Plantilla que agafem per presentar la galeria
      $this->config['plantilla_edita_imatge']  = FALSE;                ///< Plantilla per presentar imatge al editar
      $this->config['descripcions_config']     = FALSE ;               ///< Instancia de DescripcionesGaleria
      $this->config['dir_gal']                 = FALSE ;               ///< Directorio de las galerías
      $this->config['dir_base']                = FALSE ;               ///< Directorio base respecto a html
      $this->config['dir_mod']                 = GCM_DIR.'lib/int/galeria/' ; ///< Directorio html del módulo
      $this->config['dir_tmp']                 = GCM_DIR.'tmp/' ;      ///< Directorio temporal donde podamos escribir, para las imagenes temporales
      $this->config['dir_gcm']                 = GCM_DIR ;             ///< Directorio de GCM
      $this->config['tipos_permisos']          = 0644;                 ///< Permisos para las imágenes

      if ( $galeria_config ) $this->config = array_merge($this->config, $galeria_config);

      if ( $this->config['descripcions_config'] ) {
         $this->descripcions = new DescripcionesGalerias($this->config['descripcions_config']['tabla']
            ,$id
            ,$this->config['descripcions_config']['config']
            ,$galeria_pdo
            );
         $this->descripcions->load();
         }
      $this->puntero = 0;
      $this->id = $id;
      $this->fitxer_js = dirname(__FILE__).'/../js/galeria_js.php';
      $this->imatges = array();
      $this->temporal = ( $this->id ) ? FALSE : TRUE ;

      // @todo comprobar si es necesario con los nuevos cambios
      $this->identificador_unic = ( $id ) ? $this->config['dir_gal'].'_'.$id : $this->config['dir_gal'];

      if ( $this->temporal ) {
         $this->galeria_url = $this->config['dir_tmp'].session_id().'/';
         
      } else {
         $this->galeria_url = $this->config['dir_gal'].$this->id.'/';
         }

      // Comprobaciones

      if ( !file_exists($this->dir_tmp) ) 
         registrar(__FILE__,__LINE__,"No se puede acceder a carpeta temporal [".$this->dir_tmp."]",'ERROR');


      $_SESSION['galeria']['config']  = $file_config;
      $_SESSION['galeria']['id']      = $id;

      }

   /**
    * Recollir identificadors de imatges per $this->imatges
    */

   public function load() {

      if ( $this->loaded ) return TRUE;

      $extensions_imatges[] = 'image/jpeg';
      $extensions_imatges[] = 'image/gif';
      $extensions_imatges[] = 'image/png';

      $this->imatges = array();

      // Si no existe la carpeta es que aun no hay galería
      $url = $this->galeria_url;
      
      if ( !file_exists($url) ) {
         return;
         }

      $archivos = glob($this->galeria_url.'/*');
      if ( $archivos && !empty($archivos) ) {
        foreach ( $archivos as $img) {
           if ( esImagen($img) ) {
              $imatge = new Imatge(basename($img), $this->config, $this->id);
              $imatge->load();
              $this->addImatge($imatge);
              }
           }
        }

      $this->loaded = TRUE;

      }

   /**
    * Eliminar galeria de la bse de dades
    */

   function esborraGaleria() {

      $dir = $this->galeria_url;

      if ( is_dir($dir) ) {
         rmdir_recursivo($dir);
         }
      $nom_galeria_session = 'galeria_imatges_'.$this->dir_gal;
      // unset($_SESSION[$nom_galeria_session]);            // esborrem galeria de sessio

      if ( $this->descripcions ) $this->descripcions->esborrar($this->id);

      }

   public function addImatge (Imatge $imatge) {

      // Comprovar que no hem arribat al màxim 

      if ( count($this->imatges) <= $this->limit_imatges ) {
         $this->imatges[] = $imatge;
      } else {
         return FALSE;
         }

      }

   public function current () {
      if (! $this->valid()) return false;
      if (empty($this->imatges[$this->puntero])) return array();
      return $this->imatges[$this->puntero];
      }

   public function key() {
      $this->load();
      if ( $this->descripcions ) $this->descripcions->load();
      return $this->puntero;
      }

   public function next() {
      $this->load();
      if ( $this->descripcions ) $this->descripcions->load();
      return ++ $this->puntero;
      }

   public function rewind() {
      $this->load();
      if ( $this->descripcions ) $this->descripcions->load();
      $this->puntero = 0;
      }

   public function valid() {
      $this->load();
      if ( $this->descripcions ) $this->descripcions->load();
      return ( isset($this->imatges[$this->puntero]) ) ? TRUE : FALSE ;
      }

   public function count() {
      $this->load();
      return sizeof($this->imatges);
      }

   /** 
    * caixa amb l'input per pujar imatge
    *
    * Creamos variable de sesión con la instancia de Galeria
    *
    * @param $size_input_file grandària d'input d'imatge
    * @param $imatge Nom de imatge tempoaral
    */

   function caixa_input($size_input_file = '31', $imatge=NULL) {

      $this->load();
      if ( $this->descripcions ) $this->descripcions->load();

      include_once($this->fitxer_js);

      if ( $this->grandaria_max ) { ?>
      <input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $this->grandaria_max;?>" />
      <?php } ?>

      <input type="hidden" name="contador" id="contador_<?php echo $this->identificador_unic?>" value="<?php echo $this->count() ?>" />
      <?php

      require(dirname(__FILE__).'/plupload/caixa_input.php');

      if ( $this->incluir_css ) {

         ?>
         <style>
         <?php include(dirname(__FILE__).'/../css/galeria.css.php'); ?>
         </style>
         <?php

         }

      return;
      }

   /** Caixa amb missatges de error */

   function caixa_missatges() {

      ?>   
      <div id="messageBoardDIV" style="display:none;">
         <div>&nbsp;</div>
         <div class="genButton"><a href="javascript:hideMessageBoard();">ok</a></div>	
      </div>
      <?php
         
      }

   /** 
    * Presentem caixa amb les imatges
    *
    * Guardamos una instanca de Galeria en sessión que nos permitira
    * borrar o editar
    *
    * @param $displayHash Array con contenido de formularios @see Solicitud
    */

   function caixa_galeria($displayHash=FALSE) {

      echo '<div id="caixa_galeria">';

      if ( $this->count() > 0 ) {

         for($i=0;$i<$this->count();$i++){
            $this->presentaImatgeEditar($this->imatges[$i],$i+1, $displayHash);
            } 	
         }

      echo '</div>';

      }

   /** 
    * Editar Imatge
    *
    * @param $imatge Objecta d'Imatge
    * @param $i      Nombre de miniatura
    * @param $displayHash Array con contenido de formularios @see Solicitud
    */

   function presentaImatgeEditar(Imatge $imatge,$i, $displayHash=FALSE) {

      $imatgeId = $imatge->getId();

      if ( $this->temporal ) {

         // permisos

         chmod($this->galeria_url.$imatgeId,$this->tipos_permisos);

         $href = $this->dir_base.$this->dir_gcm.'tmp/'.session_id().'/'.$imatgeId;

      } else {

         $href = $imatge->getMiniaturaSrc();
         }

      if ( $this->plantilla_edita_imatge ) {
         include($this->plantilla_edita_imatge);
      } else {
         include(dirname(__FILE__).'/../html/miniatura_edit.phtml');
         }

      if ( $this->incluir_css ) {

         ?>
         <style>
         <?php include(dirname(__FILE__).'/../css/galeria.css.php'); ?>
         </style>
         <?php

         }

      }

   public function carga_php_general() {
      $include_php='lib/int/galeria/presentacions/'.$this->plantilla_presentacio.'/include.php';
      if ( file_exists(GCM_DIR.$include_php) ) $this->carga_php_general = $include_php;
      return $this->carga_php_general;
      }

   public function carga_js_general() {
     if ( ! $this->plantilla_presentacio ) return FALSE;
      $include_config=dirname(__FILE__).'/../presentacions/'.$this->plantilla_presentacio.'/config.php';
      if ( file_exists($include_config) ) {
         include($include_config);
         if ( ! isset($carga_js_general) ) {
            registrar(__FILE__,__LINE__,"Error con configuración de ".$this->plantilla_presentacio,'ERROR');
         } else {
            $this->carga_js_general = $carga_js_general;
            return $this->carga_js_general;
            }
      } else {
         registrar(__FILE__,__LINE__,"Falta archivo config de presentación de ".$this->plantilla_presentacio,'ERROR');
         }
      return FALSE;
      }

   /**
    * Presentem galeria
    */

   function presentaGaleria(){

      $this->load();
      if ( $this->descripcions ) $this->descripcions->load();

      // Si no tenim imatges sortim
      if ( !$this->count() > 0 ) return;

      if ( $this->incluir_css ) {

         ?>
         <style>
         <?php include(dirname(__FILE__).'/../css/galeria.css.php'); ?>
         </style>
         <?php

         }

      // Si tenim una plantilla seleccionada la apliquem

      if ( $this->plantilla_presentacio ) {
         include (dirname(__FILE__).'/../presentacions/'.$this->plantilla_presentacio.'/trepa.phtml');
         return;
         }

      ?>
      <!-- Comença el cos de galeria Galeria -->

      <div id="<?php echo $this->nom_div_galeria ?>">
         <?php
         if ( $this->count() > 0 ) {
            for ($i=0;$i<$this->count();$i++){
               $this->presentarImatge($this->imatges[$i],$i);
               }
            }
         ?>

      </div>
      <?php
   }

   /** Presentar imatge
    *
    * @param $imatge  Objecta d'imatge
    * @param $idThumb Nombre de miniatura
    */

   function presentarImatge(Imatge $imatge, $idThumb) {

      include(dirname(__FILE__).'/../html/miniatura_veure.phtml');

      }

   /**
    * Guardar imatges
    *
    * Si pasamos un identificador para guardar las imágenes definitivas
    * la galería pasa a no estar en estado temporal.
    *
    * @param $id Identificador del element al que pertany la galeria
    */

   function guardar($id=FALSE) {

      if ( $id ) $this->id = $id;

      $this->load();

      if ( $this->descripcions ) $this->descripcions->guardar($this->id); 

      $this->galeria_url = $this->config['dir_gal'].$this->id.'/';

      // Si la galeria esta en estado temporal se guardan las imagenes a directorio
      // definitivo, en caso contrario no se hace nada, ya que se suben al momento.

      if ( !$this->temporal ) return TRUE ;

      foreach ( $this->imatges as $imatge ) {

         $imatge->guardar($this->config, $this->id);

         }

      $this->temporal = FALSE ;

      unset($_SESSION['galeria']);            // esborrem galeria de sessio

      return TRUE;
      }

   /**
    * Esborra imatge de galeria
    *
    * Si no existeix la imatge entenem que estem volent canviar una imatge
    * de la base de dades per altra temporal. Per tant la imatge no es podrà
    * esborrar ja que si ho fem esborrem tot el registre.
    *
    * @param $id      Identificador d'imatge
    * @param $idThumb Nombre de miniatura
    */

   function esborrarImatge($id,$idThumb) {

      $src = $this->galeria_url.$id;

      if ( $this->temporal ) {
         
         if ( file_exists($src)) {
            if ( unlink($src) ) {
               echo $idThumb;
            } else {
               echo "No s'ha pogut esborrar la imatge temporal"; // FALTA LITERAL
               }
         } else {
            echo $idThumb;
         }

      } else {

         $imatge = new Imatge($id, $this->config, $this->id);
         if ( $imatge->borrar() === FALSE ) {
            return FALSE;
         } else {
            echo $idThumb;
            }

         }

      $imatges = $this->imatges;
      $this->imatges = array();

      foreach ( $imatges as $imatge ) {
         if ( ! empty($imatge) && ( $imatge->id != $id ) ) $this->imatges[] = $imatge;
         }

      // if ( isset($_SESSION[$this->dir_gal]) ) {
      //    $_SESSION[$this->dir_gal]['config']  = $this->config;
      //    $_SESSION[$this->dir_gal]['imatges'] = $this->imatges;
      //    }

      }

   function accion($accion) {

      // Definir acción a realizar
      
      switch ($_REQUEST['galeria_accion']) {

         case 'actualizar':
            include('actualizar_galeria.php');
            exit();
            break;
         
         case 'subir':
            include('pujar_imatge.php');
            exit();
            break;
         
         case 'esborra':
            include('esborra_imatge.php');
            exit();
            break;
         
         default:
            die(literal("Acción no definida"));
            // code...
            break;
         }
      }

   /**
    * Presentación de galeria para su modificación
    *
    * @param $displayHash Array con contenido de formularios @see Solicitud
    */

   function formulario($displayHash=FALSE) {

      // Añadimos caja de presentación de las imagenes
      $this->caixa_galeria($displayHash);
      
      // Añadimos caja de mensajes de la galería
      $this->caixa_missatges();

      // Añadimos input para insertar imagen nueva
      $this->caixa_input();

      }

   function __toString() {

      $temporal = ( $this->temporal ) ? 'TRUE' : 'FALSE';

      $salida  = '<br />id: '.$this->id;
      $salida .= '<br />temporal: '.$temporal ;
      $salida .= '<br />galeria_url: '.$this->galeria_url;
      $salida .= '<br />config: '.print_r($this->config,1);
      $salida .= "<br />Imagenes: ";

      foreach ( $this as $imatge ) {
         $salida .= print_r($imatge,1);
         }

      return $salida;

      }

   function __get($variable_configuracion) {

      if ( isset($this->config[$variable_configuracion]) ) {
         return $this->config[$variable_configuracion];
         }

      registrar(__FILE__,__LINE__,"Error [$variable_configuracion] no es una variable de configuración",'ERROR');
      
      return FALSE;
      }

   function __set($variable_configuracion, $valor) {

      if ( isset($this->config[$variable_configuracion]) ) {
         $this->config[$variable_configuracion] = $valor ;
         return;
         }

      registrar(__FILE__,__LINE__,
         "Error $variable_configuracion no es una variable de configuración, no se puede añadir valor [".$valor."]",'ERROR');
      
      }

   }

?>
