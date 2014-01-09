<?php
/**
 * @file      Menu
 * @category  Modulos
 * @author    Eduardo Magrané <eduardo mamedu com>
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU/GPL
 * @version   SVN $Id: Menu.php 634 2012-07-19 14:15:30Z eduardo $ 
 */

/**
 * Esta clase compondrá un menú a partir del
 * contenido de un directorio, ofreciendo varios
 * estilos:
 *
 * 1 -> Menú en la parte superior en forma de pestañas
 * 2 -> Menú como secuencia de enlaces vertical al margen
 * 3 -> Menú como lista desplegable de opciones
 *
 * Se busca los literales de los nombres de los archivos, y si encontramos 
 * literales con el sufijo literal_title lo añadimos al title del enlace.
 *
 * @author Eduardo Magrané
 * @version 1.0
 */

class Menu extends Modulos {

   public $descartar;                          ///< Archivos o directorios a descartar, configurable

   /** Matrices con el texto de las opciones y los enlaces */
   public $sOpciones, $sEnlaces;

   /** Matriz asociativa con opciones y enlaces */
   public $sMenu;

   /** Presentar secciones si/no */

   public $SECCIONES;

   /** Pesos de los items del menú */

   static $pesos_menu = array() ;

   /** Opciones generales del menú */

   public $opciones_generales = array();

   /**
    * El constructor necesita como argumento el directorio
    * del contenido y el filtro si deseamos filtrar.
    *
    * @param $sDirectorio  Directorio a presentar
    * @param $filtro       Filtro para archivos
    * @param $SECCIONES    Presentamos subsecciones TRUE/FALSE
    */

   function __construct($sDirectorio=NULL, $filtro="no", $SECCIONES=true) {

      global $gcm ;

      parent::__construct() ;

      $this->descartar = $this->config('descartar');

      // Recogemos pesos de los items del menú de la configuración

      $pesos_configuracion = $this->config('orden_presentacion');

      if ( $pesos_configuracion ) {

         foreach ( $pesos_configuracion as $pesos ) {

            $array = explode('@',$pesos);
            self::$pesos_menu[$array[0]] = $array[1];

            }

         }

      // Creamos las matrices
      $this->sOpciones = array();
      $this->sEnlaces = array();
      $this->sTitles  = array();
      $this->sMenu = array();
      $this->sDirectorio = $sDirectorio;
      $this->SECCIONES = $SECCIONES ;

      // Comprobar si $sDirectorio tiene una / al final sino hay que añadirle
      if ( isset($sDirectorio) && substr($sDirectorio, -1, 1) != '/' ) {
         $this->sDirectorio=$sDirectorio.'/';
         }

      /// Leemos el contenido del directorio
      if ( file_exists($this->sDirectorio) && $directorio = dir($this->sDirectorio) ) {

         while ($archivos = $directorio->read()) {

            // Comprobar descartados

            if ( ! empty($this->descartar) ) {

               $descartar = FALSE;
               foreach ( $this->descartar as $descartado ) {
                  if ( strpos($archivos,$descartado) !== FALSE ) {
                     registrar(__FILE__,__LINE__,'Descartado: '.$archivos. ' coincide con '.$descartado);
                     $descartar = TRUE;
                     }
                  }

               if ( $descartar ) {
                  continue;
                  }
               }

            $item=$this->sDirectorio.$archivos;

            // Descartamos ., .. e index.html
            // Descartar cualquier archivo o directorio que comience por '.'

            if ( $archivos{0} != "." && $archivos != "index.html" ) {
               // Si hay filtro solo listamos los que coinciden
               if ($filtro <> "no") {

                  if (!substr_count($archivos,$filtro)==0 || is_dir($item)){

                     /* Directorios primero */

                     if ( is_dir($item)  ) {
                        $ElementosDir[] = $archivos;
                     } else {
                        $Elementos[] = $archivos;
                        }
                  }

               } else {

                  if ( is_dir($item)  ) {
                     $ElementosDir[] = $archivos;
                  } else {
                     $Elementos[] = $archivos;
                     }
                  }
               }
            }

      } else {

         $gcm->registra(__FILE__, __LINE__, 'No se puede abrir directorio:'.$this->sDirectorio,'DEBUG');

         return;
      }

      if ( !empty($ElementosDir)  ) {
         if (empty($Elementos)) {
            $Elementos = $ElementosDir;
         } else {
            $Elementos = array_merge($ElementosDir,$Elementos);
            }
         }

      // Recorremos los elementos que son directorios
      // Comprobar que hay algún elemento
      if (empty($Elementos)) {
         return;
      }

      // Ordenamos campos por su peso
      uasort($Elementos, array("Menu", 'ordenar_por_peso'));

      foreach($Elementos as $Elemento) {
         // Extrayendo el texto de la opción y el enlace
         if (is_dir($this->sDirectorio.$Elemento) ) {
            // Si no queremos seciones salimos
            if ( $SECCIONES==false) { break ; }
            $dirReplace=str_replace('File/'.Router::$ii.'/','',$this->sDirectorio.$Elemento);
            //$Enlace = $PHP_SELF."?s=".$dirReplace;
            // utilizamos el sistema de rewrite de apache
            $Enlace = $dirReplace.'/';
         } else {
            $dirReplace=str_replace('File/'.Router::$ii.'/','',$this->sDirectorio);
            // $Enlace = $PHP_SELF."?s=".$dirReplace."&c=".$Elemento;
            // utilizamos el sistema de rewrite de apache
            $Enlace = $dirReplace.$Elemento;
         }
         $Opcion = $Elemento;
         // Si no hay literal mostramos su nombre original
         $Literal= literal($Elemento);
         $Title  = literal($Elemento.'_title');
         if ( $Title == $Elemento.'_title' ) {
            $Title = '';
         }

         // Los guardamos en las matrices simples
         $this->sOpciones[] = $Opcion;
         $this->sLiteral[]  = $Literal;
         $this->sEnlaces[] = $Enlace;
         $this->sTitles[]  = $Title;

         // y en la matriz asociativa
         $this->sMenu[$Opcion] = $Enlace;
      }

   }

   /**
    * Ordenar elementos si se empecifico orden desde config
    */

   static function ordenar_por_peso($a, $b) {

      $aPeso = ( isset(self::$pesos_menu[$a]) ) ? self::$pesos_menu[$a] : 0 ;
      $bPeso = ( isset(self::$pesos_menu[$b]) ) ? self::$pesos_menu[$b] : 0 ;

      if ($aPeso == $bPeso) return 1;
      return ($aPeso < $bPeso) ? -1 : 1;
      }

   /**
    * Este método es al que hay que llamar para agregar
    * el menú a la página
    *
    * Tipos de menu:
    * 0 - Menu paginas
    * 1 - Barra de navegación
    * 2 - Menu desplegable
    * 3 - Menu paginas (Menu principal)
    *
    */

   function InsertaMenu($sActual, $iTipoMenu) {

      // Comprobamos que hay contenido
      if (empty($this->sOpciones)) {
         return;
      }
      // Dependiendo del tipo de menú
      switch($iTipoMenu) {
         // invocamos a un método u otro
      case 0: $this->MenuPaginas($sActual, "no");
         break;

      case 1: $this->MenuBarra($sActual);
         break;

      case 2: $this->MenuDesplegable($sActual);
         break;

      case 3: $this->MenuPaginas($sActual, "si");
         break;

      case 4: $this->MenuBotones($sActual, "si");
         break;

      default: // Tipo incorrecto
         echo "Tipo no disponible";
      }
   }

   /**
    * Este método generaría el menú en forma de páginas
    * accesibles desde una pestaña
    *
    * @param $sActual Sección actual
    * @param $principal (T/F) Si es el menu principal hay que colocar el
    *                   elemento inicio y descartar los que no son directorios
    * @param $imagenes (T/F) Crear el menú con las imágenes de la sección 
    *                  menu_on.gif y menu_off.gif
    */

   function MenuPaginas($sActual, $principal, $imagenes = FALSE) {

      // Preparamos la tabla
      echo '<table id="menu" cellspacing="0"><tr>';

      if ( isset($this->opciones_generales['ocultar_inicio']) && $this->opciones_generales['ocultar_inicio'] == 1 ) {

         if ($principal=="si") {

            if("inicio" == $sActual) {

                  if ( $imagenes ) {

                     echo '<td id="menuOff"><a href="'.Router::$base.'" title="'.literal('inicio_title').'" ><img src="'.$this->boton_seccion('', TRUE).'" alt="'.literal('inicio').'" /></a></td>';	 
                  } else {
                     echo '<td id="menuOff"><a href="'.Router::$base.'" title="'.literal('inicio_title').'" >'.literal('inicio').'</a></td>';	 
                     }

            } else {

               if ( $imagenes ) {
                  echo '<td><a href="'.Router::$base.'" title="'.literal('inicio_title').'" ><img src="'.$this->boton_seccion('').'" alt="'.literal('inicio').'" /></a></td>';	 
               } else {
                  echo '<td><a href="'.Router::$base.'" title="'.literal('inicio_title').'" >'.literal('inicio').'</a></td>';	 
                  }
               }
            }
         }

      // nos situamos al inicio de la matriz de enlaces
      reset($this->sEnlaces);
      reset($this->sTitles);
      reset($this->sLiteral);

      // y obtenemos el primero de ellos
      $Enlace = current($this->sEnlaces);
      $Title  = current($this->sTitles);
      $Literal  = current($this->sLiteral);

      // Vamos recorriendo las opciones existentes
      foreach($this->sOpciones as $Opcion) {

         if ( is_dir(Router::$dd.$Enlace) ) {

            // si es la opción actual
            if($Opcion == $sActual) {

               // la mostramos con un fondo distinto
               if ( $imagenes ) {
                  echo '<td id="menuOff"><a href="'.Router::$base.$Enlace.'" title="'.literal($Title,1).'" ><img src="'.$this->boton_seccion($Enlace, TRUE).'" alt="'.$Literal.'" /></a></td>';	 
               } else {
                  echo '<td id="menuOff"><a href="'.Router::$base.$Enlace.'" title="'.literal($Title,1).'" >'.$Literal.'</td>';
                  }

            } else {

               if ( $imagenes ) {
                  echo '<td id="'.$Opcion.'"><a href="'.Router::$base.$Enlace.'" title="'.literal($Title,1).'" ><img src="'.$this->boton_seccion($Enlace).'" alt="'.$Literal.'" /></a></td>';	 
               } else {
                  echo '<td id="'.$Opcion.'" ><a href="'.Router::$base.$Enlace.'" title="'.literal($Title,1).'" >'.$Literal.'</a></td>';
                  }

               }

         }

         // Obtenemos el enlace siguiente
         $Enlace = next($this->sEnlaces);
         $Title  = next($this->sTitles);
         $Literal  = next($this->sLiteral);

      }


      echo '</tr></table>'; // Cerramos la tabla
   }


   /// Este método genera la barra de navegacíon

   function MenuBarra($sActual) {

      global $gcm;

      // sActual es toda la lista de secciones hay que dividirla
      if ( $sActual ) {

         $aSecciones = explode('/',$sActual);
         $sActual = ( $aSecciones[count($aSecciones)-1] ) ?  $aSecciones[count($aSecciones)-1] :  $aSecciones[count($aSecciones)-2];
         $seccion=literal($sActual,1);

      } else {
         
         $aSecciones = array();
         $seccion=literal('inicio',1);

      }

      $aURL = explode('/',$_SERVER['REQUEST_URI']);

      echo "\n<ul>";
      foreach($this->sMenu as $Opcion => $Enlace) {

         $aEnlace = explode('/',$Enlace);

         // Limpiar $Opcion de .html
         $Literal=str_replace('.html','',$Opcion);

         if ( in_array($Opcion,$aSecciones) ) {

            echo "\n<li>";
            echo "<a class='m_on' href='".Router::$enlace_relativo.Router::$dir.$Enlace."' >";
            echo '<img src="'.$gcm->event->instancias['temas']->icono('-').'" alt="-"/>';
            echo " </a>";
            echo "<a href='".Router::$enlace_relativo.Router::$dir.$Enlace."'>";
            echo literal($Literal,1);
            echo '</a></li>';
            $submenu[$Opcion] = new Menu($this->sDirectorio.$Opcion.'/', '.html', true);
            $submenu[$Opcion]->InsertaMenu(Router::$s,1);

         } else {
            // si en un archivo y no una sección
            if ( substr($Enlace, -5) == '.html' ) {
               if ( str_replace('%20',' ',$aURL[count($aURL)-1])  == $aEnlace[count($aEnlace)-1]) {
                  echo "\n<li class='listaOff' >".literal($Literal,1).'</li>';
               } else {
                  echo "\n<li><a href='".Router::$enlace_relativo.Router::$dir.$Enlace."'>".literal($Literal,1).'</a></li>';
               }
            } else {
               echo "\n<li>";
               echo "<a class='m_off' href='".Router::$enlace_relativo.Router::$dir.$Enlace."'>";
               echo '<img src="'.$gcm->event->instancias['temas']->icono('+').'" alt="+"/>';
               echo " </a>";
               echo "<a href='".Router::$enlace_relativo.Router::$dir.$Enlace."'>".literal($Literal,1);
               echo '</a></li>';
               }
            }
         }

      echo "\n</ul>";

   }

   /// Este método produce el menú desplegable

   function MenuDesplegable($sActual) {

      // Generamos el formulario con la lista desplegable,
      // redireccionando a la página correspondiente a la
      // opción elegida
      echo '<form action="">'.
         '<select name="Menu" onChange="top.location.href='.
         'this.form.Menu.value'.
         '">';

      // Vamos añadiendo las opciones a la lista
      foreach($this->sMenu as $Opcion => $Enlace)
         if($Opcion == $sActual)
            echo '<option value="'.$Enlace.
               '" selected>'.literal($Opcion,1).'</option>';
         else
            echo '<option value="'.$Enlace.'">'.literal($Opcion,1).'</option>';

      echo '</select></form>'; // cerramos el formulario
   }


   /** Menú principal con imágenes 
    *
    * Presentamos menú principal con las imágenes que encontramos dentro de cada sección
    *
    * - menu_on.gif: Para cuando estamos en ella
    * - menu_off.gif: Para cuando no estamos.
    *
    * En caso de no encontrar una imagen se presenta nombre de sección
    */

   function menu_principal_img() {

      echo "\n<div id='menu_principal'>";

      // Solo buscamos contenido en el idioma predeterminado
      $menuPrincipal = new Menu("File/".Router::$ii."/");
      $menuPrincipal->InsertaMenu(Router::$estamos,4);

      echo "\n</div> <!-- Acaba menu_principal -->";

      }

   /** Menú principal 
    *
    * Presentamos menú principal
    *
    */

   function menu_principal($e, $args=FALSE) {


      $this->opciones_generales = array_merge($this->opciones_generales, recoger_parametros($args));

      /**
       * Presentar el menu de las secciones principales
       */

      echo "\n<div id='menu_principal'>";

      // Solo buscamos contenido en el idioma predeterminado
      $menuPrincipal = new Menu("File/".Router::$ii."/");
      $menuPrincipal->InsertaMenu(Router::$estamos,3);

      echo "\n</div> <!-- Acaba menu_principal -->";

      }

   /**
    * Menu para la barra de navegación
    */

   function barra_navegacion() {

      global $gcm;

      $this->javascripts('menu.js');

      ob_start();
      echo '<a href="'.$_SERVER['PHP_SELF'].'" >'.literal('inicio').'</a>';
      echo '<div id="barraNavegacion">';
      $barraNavegacion = new Menu("File/".Router::$ii."/", '.html', true);
      $barraNavegacion->InsertaMenu(Router::$s,1);
      echo '</div>';
      $contenido = ob_get_contents();
      ob_end_clean(); 


      $panel = array();
      $panel['titulo'] = literal('Menú',3);
      $panel['contenido'] =$contenido;

      Temas::panel($panel);

      }

   function menu_ajax() {

      global $gcm;

      ob_end_clean();

      // coger la última sección
      $secciones = explode('/',comprobar_barra(Router::$s,'eliminar'));
      $seccion = $secciones[count($secciones)-1];
      echo "\n<li>";
      echo "<a class='m_on' href='".Router::$s."' >";
      echo '<img src="'.$gcm->event->instancias['temas']->icono('-').'" alt="-"/>';
      echo " </a>";
      echo "<a href='".Router::$s."'>";
      echo literal($seccion,1);
      echo '</a></li>';
      $submenu = new Menu(Router::$dd.Router::$s, '.html', true);
      $submenu->InsertaMenu(Router::$s,1);
      //salir();
      exit();

      }

   function menu_ajax_off() {

      global $gcm;

      ob_end_clean();
      // coger la última sección
      $secciones = explode('/',comprobar_barra(Router::$s,'eliminar'));
      $seccion = $secciones[count($secciones)-1];
      //echo "\n<li>";
      echo "<a class='m_off' href='".Router::$s."' >";
      echo '<img src="'.$gcm->event->instancias['temas']->icono('+').'" alt="+"/>';
      echo " </a>";
      //salir();
      exit();
      }

   /**
    * Buscamos las imágenes que hacen de botones para el menu principal
    *
    * Las imáges pueden estar en gif o png.
    *
    * Si no encontramos imágenes del idioma especificado las cogemos del por defecto.
    *
    * @param $seccion Sección del boton
    * @param $on Activado o desativado, por defecto desactivado
    */

   function boton_seccion($seccion, $on = FALSE) {

      $estado = ( $on ) ? 'on' : 'off';

      $img = Router::$d. $seccion.'menu_'.$estado.'.png'; if ( file_exists($img) ) return Router::$base.$img;
      $img = Router::$d. $seccion.'menu_'.$estado.'.gif'; if ( file_exists($img) ) return Router::$base.$img;
      $img = Router::$dd.$seccion.'menu_'.$estado.'.png'; if ( file_exists($img) ) return Router::$base.$img;
      $img = Router::$dd.$seccion.'menu_'.$estado.'.gif'; if ( file_exists($img) ) return Router::$base.$img;

      $seccion_ = ($seccion)?$seccion:'inicio';
      registrar(__FILE__,__LINE__,'No se encontro boton de sección para '.$seccion_,'ADMIN');
      }

   /**
    * Este método genera el menú principal con botones y presenta un submenu
    * al pasar el cursor sobre él.
    *
    * Las imágenes de los botones de cada sección estaran en su directorio
    * correspondiente. menu_on.gif y menu_off.gif o en formato png.
    *
    * @param $sActual Sección actual
    */

   function MenuBotones($sActual, $principal) {

      echo '<ul id="menu_botones_horizontal">';

      // Botón de inicio

      $title_inicio = ( literal('inicio_title') != 'inicio_title' ) ? literal('inicio_title') : '' ;
      if("inicio" == $sActual || empty(Router::$s) ) {

         echo '<li id="menuOff">';
         echo '<a href="'.Router::$base.'" title="'.$title_inicio.'" ><img src="'.$this->boton_seccion('', TRUE).'" alt="'.literal('inicio').'" /></a>';

      } else {

         echo '<li>';
         echo '<a href="'.Router::$base.'" title="'.$title_inicio.'" ><img src="'.$this->boton_seccion('').'" alt="'.literal('inicio').'" /></a>';	 

         }

      // Presentar subtitulos con paginas html de inicio

      $salida = FALSE;
      $ficheros_seccion = glob(Router::$dd.'*.html');
      $contenidos = array();
      foreach ( $ficheros_seccion as $file ) {
         $contenidos[] = basename($file,'.html');
         }
      uasort($contenidos, array("Menu", 'ordenar_por_peso'));
      
      foreach ( $contenidos as $nombre ) {

         $literal = literal($nombre,1);
         $enlace  = Router::$base.$nombre.'.html';
         $clase   = ( Router::$c == $nombre.'.html' ) ? 'ma_on' : 'ma_off';

         if ( $nombre != 'index' ) {

            $salida .= '<li class="'.$clase.'">';
            if ( Router::$c != $nombre.'.html' ) $salida .= '<a href="'.$enlace.'">';
            $salida .= $literal;
            if ( Router::$c != $nombre.'.html' ) $salida .= '</a>';
            $salida .= '</li><br />';

            }

         }

      if ( $salida ) echo '<ul>'.$salida.'</ul>';
      echo '</li>';	 

      // Botones de secciones

      // nos situamos al inicio de la matriz de enlaces
      reset($this->sEnlaces);
      reset($this->sTitles);
      reset($this->sLiteral);

      // y obtenemos el primero de ellos
      $Enlace = current($this->sEnlaces);
      $Title  = current($this->sTitles);
      $Literal  = current($this->sLiteral);

      // Vamos recorriendo las opciones existentes
      foreach($this->sOpciones as $Opcion) {

         if ( is_dir(Router::$dd.$Enlace) ) {

            // si es la opción actual
            if($Opcion == $sActual) {

               echo '<li id="menuOff">';
               echo '<a href="'.Router::$base.$Enlace.'" title="'.$Title.'" ><img src="'.$this->boton_seccion($Enlace, TRUE).'" alt="'.$Literal.'" /></a>';	 

            } else {

               echo '<li id="'.$Opcion.'">';
               echo '<a href="'.Router::$base.$Enlace.'" title="'.$Title.'" ><img src="'.$this->boton_seccion($Enlace).'" alt="'.$Literal.'" /></a>';	 

               }

      // Presentar subtitulos con paginas html de sección

      $salida = FALSE;
      $ficheros_seccion = glob(Router::$dd.$Enlace.'*.html');
      $contenidos = array();
      foreach ( $ficheros_seccion as $file ) {
         $contenidos[] = basename($file,'.html');
         }
      uasort($contenidos, array("Menu", 'ordenar_por_peso'));
      
      foreach ( $contenidos as $nombre ) {

         $literal = literal($nombre,1);
         $enlace  = Router::$base.$Enlace.$nombre.'.html';
         $clase   = ( Router::$c == $nombre.'.html' ) ? 'ma_on' : 'ma_off';

         if ( $nombre != 'index' ) {

            $salida .= '<li class="'.$clase.'">';
            if ( Router::$c != $nombre.'.html' ) $salida .= '<a href="'.$enlace.'">';
            $salida .= $literal;
            if ( Router::$c != $nombre.'.html' ) $salida .= '</a>';
            $salida .= '</li><br />';

            }

         }

      if ( $salida ) echo '<ul>'.$salida.'</ul>';
      echo '</li>';	 

         }

         // Obtenemos el enlace siguiente
         $Enlace = next($this->sEnlaces);
         $Title  = next($this->sTitles);
         $Literal  = next($this->sLiteral);

      }


      if ($principal=="si") echo '</ul>';


      }


   }

?>
