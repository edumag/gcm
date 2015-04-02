<?php

/**
 * @file    Idiomas.php
 * @brief   Módulo para la manipulación de literales
 * @ingroup Idiomas
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 * Copyright  Copyright (c) 2009, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

//require_once(GCM_DIR.'lib/int/gcm/lib/helpers.php');

/** 
 * Idiomas
 *
 * para la administración de los idiomas
 *
 * Se presentan los idiomas disponibles, se puede cambiar la imagen correspondiente
 * a cada idioma y eliminar un idioma, en tal caso se debe presentar las carpetas
 * que contienen el contenido perteneciente a ese idioma y confirmar el borrado del mismo.
 *
 * Se crea un select con los idiomas disponibles y se da la posibilidad de cambiar
 *
 * Se presenta formulario para la entrada de un nuevo idioma, en tal caso:
 *
 * - se creara una nueva carpeta en el directorio Files con el código del idioma y en copiasSeguridad 
 * - se añade el literal del nuevo idioma en DATOS/idiomas/idiomas.php
 * - se crear archivo de literales LG_<idioma>.php
 * - se añade imagen al directorio.
 *
 * @author Eduardo Magrané
 * @ingroup Idiomas
 */

class IdiomasCore {

   /**
    * Definir el nombre del proyecto nos permite guardar el idioma
    * actual del usuario en sesión y poder trabajar con diferentes
    * proyectos sin que se chafen unos a otros.
    */

   private $proyecto;                        

   private $idiomas_activados;   ///< Idioma activados
   private $idiomaxdefecto;      ///< Idioma por defecto
   private $dir_idiomas;         ///< Directorio para los archivos de idiomas

   private $idioma_actual;       ///< Idioma actual

   public $literales_faltantes = FALSE ; ///< Literales faltantes en idioma actual.

   /**
    * Constructor
    *
    * @param $aConfiguracion Array con la configuración:
    *        - proyecto = 'nombre del proyecto'
    *        - dir_idiomas = directorio donde se encuentran los archivos con los literales
    *        - idiomaxdefecto = idioma por defecto del proyecto
    *        - idiomas_activados = Idiomas activados
    *
    */

   function __construct($aConfiguracion) {

      if ( ! isset($aConfiguracion) ) {
         trigger_error("Es necesario definir las variables de configuracion", E_USER_ERROR);
         }

      /** Array global con los literales del idioma actual */

      $GLOBALS['LG'] = array();

      foreach ( $aConfiguracion as $key => $valor ) {
         $this->$key = $valor;
         }

      $this->proyecto = ( isset($this->proyecto) ) ? $this->proyecto : 'default';

      if ( ! isset($this->idiomas_activados) ) {
         trigger_error("Es necesario definir idiomas_activados dentro del array de configuracion", E_USER_ERROR);
         }

      if ( ! isset($this->idiomaxdefecto) ) {
         trigger_error("Es necesario definir idiomaxdefecto dentro del array de configuracion", E_USER_ERROR);
         }

      if ( ! isset($this->dir_idiomas) ) {
         trigger_error("Es necesario definir dir_idiomas dentro del array de configuracion", E_USER_ERROR);
         }

      $this->idioma_actual();

      }

   /**
    * Detectar idioma actual de usuario
    */

   function idioma_actual() { $this->idioma_actual = Router::$i ; return Router::$i ; }

   /**
    * Instalación
    *
    * Creamos los directorios necesarios para el funcionamiento del módulo
    */

   function instalacion() {
      if ( ! file_exists($this->dir_idiomas) ) {
         return mkdir_recursivo($this->dir_idiomas);
         }
      return;
      }

   /**
    * Seleccionamos idioma con el que se presenta la página
    *
    * Este método detecta idioma actual y genera el array de los 
    * literales
    *
    * El idioma puede venir por GET o POST ['idioma'] o puede estar
    * en la variable de sessión $_SESSION[$this->proyecto."-idioma"]
    *
    * @note Es importante que se ejecute este método al inicio, para comenzar sabiendo
    *       antes de nada el idioma que se desea.
    *
    */

   public function seleccion_idioma() {

      global $LG, $GCM_LG;

      // Incluimos literales del propio gcm

      $this->incluir_literales(GCM_DIR.'DATOS/idiomas/GCM_LG_'.$this->idiomaxdefecto.'.php', $this->idiomaxdefecto, 'GCM_LG_');

      if ( $this->idioma_actual != $this->idiomaxdefecto ) {
         $this->incluir_literales(GCM_DIR.'DATOS/idiomas/GCM_LG_'.$this->idioma_actual.'.php', $this->idioma_actual, 'GCM_LG_');
         }

      // Añadimos literales del idioma predeterminado
      
      $this->incluir_literales($this->dir_idiomas."LG_".$this->idiomaxdefecto.".php", $this->idiomaxdefecto);

      // Si el idioma actual no es el predeterminado lo añadimos chafando los literales predeterminados
      // por los del idioma actual, pero sin perder los literales faltantes del idioma actual ya que quedaran
      // los predeterminados

      if ( $this->idioma_actual != $this->idiomaxdefecto ) {
         $this->incluir_literales($this->dir_idiomas."LG_".$this->idioma_actual.".php", $this->idioma_actual);
         }

      }

   /**
    * Incluir literales a variable global LG del archivo especificado
    *
    * @param $file Archivo con los literales
    * @param $idioma Idioma de los literales
    * @param $prefijo_array Prefijo del nombre del array que contiene los literales,
    *                       por defecto es LG_ pero en el caso de los de gcm es GCM_LG_
    */

   private function incluir_literales($file, $idioma, $prefijo_array = 'LG_') {

      global $LG, $GCM_LG;

      if ( file_exists($file) ) {
         include($file);
         if ( isset(${$prefijo_array.$idioma}) ) {
            foreach ( ${$prefijo_array.$idioma} as $key => $literal ) {
               if ( $prefijo_array == 'LG_' ) {
                 if ( ! empty($literal) ) {
                   $LG[$key] = $literal;
                 } else {
                   $this->literales_faltantes[] = $key;
                 }
               } else {
                 if ( ! empty($literal) ) $GCM_LG[$key] = $literal;
                 }
               }
            }
         }

      }

   /**
    * Devolver idioma por defecto
    */

   function getIdiomaxdefecto() {
      return ( isset($this->idiomaxdefecto) ) ? $this->idiomaxdefecto : FALSE;
      }

   /**
    * Devolver idioma actual
    */

   function getIdioma_actual() {
      return ( isset($this->idioma_actual) ) ? $this->idioma_actual : FALSE;
      }

   /**
    * Devolver directorio de idiomas
    */

   function getDir_idiomas() {
      return ( isset($this->dir_idiomas) ) ? $this->dir_idiomas : FALSE;
      }

   /** Devolver idiomas activados */

   function getIdiomasActivados() {
      return $this->idiomas_activados;
      }

   /** 
    * Formulario para selección de idioma
    */

   function selector_idiomas() {

      global $gcm;

      // Solo se presenta selecctor si hay más de un idioma activo
      if ( count($this->idiomas_activados) > 1 ) {

         ?>
         <script type="text/javascript">
            function validaSelect(theList){ theList.form.submit(); }
         </script>
         <?php

         echo "\n<div id='idiomas'>"	;
         echo "\n<form action='#'>";
         echo "<select name='idioma' onChange='validaSelect(this);' >";
         foreach($this->idiomas_activados as $idioma) {
            if ($idioma!=$_SESSION[$this->proyecto."-idioma"]) {
               echo "<option value='".$idioma."' >".literal($idioma)."</option>";
            } else {
               echo "<option selected value='".$idioma."' >".literal($idioma)."</option>";
               }
            }
         echo "</select>\n</form>";
         echo "\n</div> <!-- ACABA idiomas -->";

         }
      }

   /**
    * Listado de idiomas con banderas
    *
    * Si no encontramos las banderas en el directorio de idiomas del proyecto
    * las cogemos del módulo de idiomas.
    *
    * @param $plantilla Archivo con la plantilla para las banderas
    */

   function banderas($plantilla = FALSE) {

      global $gcm;

      if ( ! $plantilla ) {

         // Solo se presenta selecctor si hay más de un idioma activo
         if ( count($this->idiomas_activados) > 1 ) {
            echo '<div id="banderas">';
            foreach($this->idiomas_activados as $idioma) {

               $bandera = ( file_exists($this->dir_idiomas.''.$idioma.'.png') ) ? $this->dir_idiomas.''.$idioma.'.png' : Router::$base.GCM_DIR.'lib/int/idiomas/img/'.$idioma.'.png' ;

               if ($idioma != $_SESSION[$this->proyecto."-idioma"]) {
                  echo '<a href="'.Router::$base.$idioma.'/'.Router::$s.Router::$c.'" ><img alt="'.$idioma.'" src="'.$bandera.'" /></a> ';
               } else {
                  echo '<a class="idioma_activado" href="'.Router::$base.$idioma.'/'.Router::$s.Router::$c.'" ><img alt="'.$idioma.'" src="'.$bandera.'" /></a> ';
                  }
               }
            echo '</div>';
            }
      } else {
         include($plantilla);
         }
      }

   /**
    * Listado de idiomas
    *
    * @param $plantilla Archivo con la plantilla
    */

   function lista_idiomas($plantilla = FALSE) {

      global $gcm;

      if ( ! $plantilla ) {

         // Solo se presenta selecctor si hay más de un idioma activo
         if ( count($this->idiomas_activados) > 1 ) {
            echo '<ul id="lista_idiomas">';
            foreach($this->idiomas_activados as $idioma) {

               if ($idioma != $_SESSION[$this->proyecto."-idioma"]) {
                  ?>
                  <li class="idioma_off"><a href="<?php echo Router::$base.$idioma.'/'.Router::$s.Router::$c;?>" ><?php echo literal($idioma); ?></a></li>
                  <?php
               } else {
                  ?>
                  <li class="idioma_on"><a class="idioma_activado" href="<?php echo Router::$base.$idioma.'/'.Router::$s.Router::$c;?>" ><?php echo literal($idioma);?></a></li>
                  <?php
                  }
               }
            echo '</ul>';
            }
      } else {
         include($plantilla);
         }
      }

   }
?>
