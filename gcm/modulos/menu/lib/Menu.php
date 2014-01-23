<?php

/**
 * @file Menu.php
 * @brief Creación de menús
 *
 * @ingroup modulo_menu
 * @author    Eduardo Magrané 
 *
 * @internal
 * Copyright  Copyright (c) 2011, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

/**
 * @class Menu
 * @brief Presentamos menús
 *
 * Esta clase compondrá un menú a partir del
 * contenido de un directorio, ofreciendo varios
 * estilos:
 *
 * 1 -> Menú principal con tabla para la parte superior
 * 2 -> Menú como secuencia de enlaces vertical al margen
 * 3 -> Menú como lista desplegable de opciones
 *
 * Se busca los literales de los nombres de los archivos, y si encontramos 
 * literales con el sufijo literal_title lo añadimos al title del enlace.
 *
 */

class Menu extends Modulos {

   static $pesos_menu   = FALSE   ; ///< Pesos de los items del menú
   static $descartar    = FALSE   ; ///< Archivos o directorios a descartar, configurable

   public $opciones     = array() ; ///< Opciones generales del menú
   public $actual                 ; ///< elemento en el que nos encontramos
   public $base_enlace            ; ///< Base del enlace de los elementos

   private $elementos   = FALSE   ; ///< Lista de elementos del menú
   private $dir_contenido         ; ///< Directorio con el contenido 
   private $filtro                ; ///< Filtro para los contenidos

   /**
    * El constructor necesita como argumento el directorio
    * del contenido y el filtro si deseamos filtrar.
    *
    * @param $sDirectorio  Directorio a presentar
    * @param $filtro       Filtro para archivos
    * @param $SECCIONES    Presentamos subsecciones TRUE/FALSE
    */

   function __construct($dir_contenido = FALSE, $filtro = FALSE ) {

      global $gcm;

      parent::__construct();

      // Recogemos pesos de los items del menú de la configuración

      $pesos_menu = $this->config('orden_presentacion');

      if ( !self::$descartar  ) self::$descartar  = $this->config('descartar');

      $this->dir_contenido   = ( $dir_contenido ) ? $dir_contenido : Router::$dd ;
      $this->base_enlace     = '' ;
      $this->filtro          = ( $filtro ) ? $filtro : '.html'    ;

      $aSecciones = explode('/',Router::$d.Router::$s);

      $this->actual = ( $aSecciones[count($aSecciones)-1] ) 
         ?  $aSecciones[count($aSecciones)-1] 
         :  $aSecciones[count($aSecciones)-2];

      if ( $pesos_menu ) {

         foreach ( $pesos_menu as $pesos ) {

            $array = explode('@',$pesos);
            self::$pesos_menu[$array[0]] = $array[1];

            }

         }

      }

   /**
    * Ordenar elementos si se especifico orden desde config
    */

   static function ordenar_por_peso($a, $b) {

      $aPeso = ( isset(self::$pesos_menu[$a]) ) ? self::$pesos_menu[$a] : 0 ;
      $bPeso = ( isset(self::$pesos_menu[$b]) ) ? self::$pesos_menu[$b] : 0 ;

      if ($aPeso == $bPeso) return 1;
      return ($aPeso < $bPeso) ? -1 : 1;
      }

   /**
    * Devolver lista de elementos del menú a partir de un directorio
    */

   function buscar_elementos($directorio=FALSE) {

      $directorios = array();
      $contenidos = array();

      if ( $directorio ) $directorio = comprobar_barra($directorio);

      if ( ! $directorio ) {
         $directorio = $this->dir_contenido ;
      } else {
         if ( $this->dir_contenido != $directorio ) {
            $this->base_enlace = $directorio;
            $directorio = $this->dir_contenido.$directorio;
            }
         }

      if ( $directorio ) {
         if ( ! is_dir($directorio) ) {
            registrar(__FILE__,__LINE__,"Si no es un directorio no se puede buscar elementos [$directorio]","ADMIN");
            registrar(__FILE__,__LINE__,"Error construyendo menú sobre [$directorio]","ERROR");
            return FALSE;
            }
         }

      $items = glob($directorio.'*');

      // Recorremos directorio para recoger solo documentos html o secciones (directorios)
      foreach ( $items as $key => $el ) {

         $nombre = basename($el);

         if ( is_dir($el) ) {
            // $this->buscar_elementos($el);
            if ( $this->validar($nombre, FALSE) ) $directorios[] = $nombre ;
         } else {
            if ( $this->validar($nombre) ) $contenidos[] = $nombre ;
            }
         }

      return array_merge($directorios, $contenidos);
      }
      
   /**
    * Validar elemento de menu
    *
    * @param $elemento Elemento a validar
    * @param $filtro Aplicar filtro o no, en caso de las secciones especificar 
    *                que no.
    */

   function validar($elemento, $filtro = TRUE) {

      // Comprobar descartados

      if ( ! empty(self::$descartar) ) {

         $descartar = FALSE;
         foreach ( self::$descartar as $descartado ) {
            if ( strpos($elemento,$descartado) !== FALSE ) {
               registrar(__FILE__,__LINE__,'Descartado: '.$elemento. ' coincide con '.$descartado);
               return FALSE;
               }
            }
         }

      if ( $elemento{0} == "." || $elemento == "index.html" || $elemento == "thumbnail"  ) return FALSE ;

      // Si hay filtro solo añadimos los que coinciden
      if ( $filtro && $this->filtro && substr_count($elemento,$this->filtro) == FALSE ) {
         return FALSE;
         }

      return TRUE;

      }

   /**
    * Insertar menú
    *
    * @param $tipo      Tipo de menu a insertar
    * @param $seccion   Sección a buscar contenido
    * @param $seccion   Sección en la que nos encontramos
    */

   function inserta_menu($tipo = 'principal', $seccion = '', $preseccion = '', $base_ajax='') {

      global $gcm;

      $seccion    = ( ! empty($seccion) )    ? comprobar_barra($seccion)    : '' ;
      $preseccion = ( ! empty($preseccion) ) ? comprobar_barra($preseccion) : '' ;
      $base_ajax  = ( ! empty($base_ajax) )  ? comprobar_barra($base_ajax)  : '' ;

      $elementos = $this->buscar_elementos($preseccion.$seccion);

      //$plantilla = dirname(__FILE__).'/../html/'.$tipo.'.phtml' ;
      // include ($plantilla);

      include ($gcm->event->instancias['temas']->ruta('menu','html',$tipo.'.phtml'));


      }

   /** Menú principal 
    *
    * Presentamos menú principal
    *
    */

   function menu_principal($e, $args=FALSE) {

      global $gcm;

      $this->opciones = array_merge($this->opciones, recoger_parametros($args));

      include ($gcm->event->instancias['temas']->ruta('menu','html','menu_switch.phtml'));

      ?>
      <div id='menu_principal'>
      <?php $this->inserta_menu();?>
      </div> <!-- Acaba menu_principal -->
      <?php

      }

   /**
    * Menu para la barra de navegación
    */

   function barra_navegacion($e, $args=FALSE) {

      global $gcm;

      $seccion = '';

      ob_start();

      /** @todo Desactivamos ajax hasta asegurarnos que funciona bien */
      // $this->javascripts('menu.js');

      echo '<a href="'.$_SERVER['PHP_SELF'].'" >'.literal('inicio').'</a>';
      echo '<div id="barraNavegacion">';

      $this->inserta_menu('navegacion',$seccion);

      echo '</div>';


      $contenido = ob_get_contents();
      ob_end_clean(); 

      $panel = array();
      $panel['titulo'] = literal('Menú',3);
      $panel['contenido'] =$contenido;

      Temas::panel($panel);

      }

   function menu_ajax_on() {

      global $gcm;

      // $args = Router::$args;
      // if ( isset($_GET['url']) ) $seccion = $_GET['url'];
      $seccion = Router::$s;
      $url = ( isset($_GET['url'] ) ) ? $_GET['url'] : '' ;
      $url = preg_replace('/^\.\/|\/\.\/$/', '', $url);
      $seccion = preg_replace('/^\.\/|\/\.\/$/', '', $seccion);
      $base = comprobar_barra(str_replace($seccion,'',comprobar_barra($url)));
      $base = preg_replace('/^\.\/|\/\.\/$/', '', $base);
      $base = rtrim($base,'/').'/'; 
      $this->base_enlace = $base ;
      // $seccion = implode('/',$args);
      // echo '<pre>seccion: ' ; print_r($seccion) ; echo '</pre>'; // exit() ; // DEV  
      // echo '<pre>url: ' ; print_r($url) ; echo '</pre>'; // exit() ; // DEV  
      // echo '<pre>base: ' ; print_r($base) ; echo '</pre>'; // exit() ; // DEV  
      // echo '<pre>Router: ' ; print_r(Router::$args) ; echo '</pre>'; // exit() ; // DEV  
      // echo '<pre>absoluta: ' ; print_r(Router::$base_absoluta) ; echo '</pre>'; // exit() ; // DEV  
      // echo '<pre>SERVER: ' ; print_r($_SERVER) ; echo '</pre>'; // exit() ; // DEV  

      ?>
         <li>
            <a class='m_off' href='<?php echo $url;?>'>
               <img src="<?php echo $gcm->event->instancias['temas']->icono('-')?>" alt="-"/>
            </a>
            <a href='<?php echo $url;?>'>
               <?php echo literal(basename($seccion),1);?>
            </a>
            <?php $this->inserta_menu('navegacion',$seccion,'',$base); ?>
         </li>
      <?php
      exit();

      }
   
   function menu_ajax_off() {

      global $gcm;
   
      $url = ( isset($_GET['url'] ) ) ? $_GET['url'] : '' ;
      $seccion = basename($url);


?>
   <li>
      <a class='m_on' href='<?php echo $url;?>'>
         <img src="<?php echo $gcm->event->instancias['temas']->icono('-')?>" alt="+"/>
      </a>
      <a href='<?php echo $url;?>'>
         <?php echo literal(basename($seccion),1);?>
      </a>
   </li>
<?php

      exit();
      }

   }

?>
