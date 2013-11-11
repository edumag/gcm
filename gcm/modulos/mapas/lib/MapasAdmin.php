<?php

/**
 * @file Mapas.php
 * @brief Presentar mapa y marcadores configurados
 *
 * @package Modulos
 */

require_once dirname(__FILE__).'/Mapas.php';

/**
 * @class Mapas
 * @brief Creación de mapas con marcadores.
 *
 * @code
 * $gcm->event->lanzar_accion_modulo('mapas','mapa','evento_mapa');
 * @endcode
 *
 * @ingroup modulo_mapas
 */

class MapasAdmin extends Mapas {

   /** Constructor */

   function __construct() { parent::__construct(); }


   /**
    * Administración de mapas
    *
    * @param $e Evento que lo llama
    * @param $args Argumentos
    */

   function configuracion($e, $args=FALSE) {

      global $gcm;

      // Añadimos panel con iconos seleccionables
      $gcm->event->accion2evento('columna','mapas','seleccionar_iconos',3);

      parent::configuracion($e, $args);

      }

   /**
    * Instalación del módulo
    *
    * @param $e Evento que lo llama
    * @param $args Argumentos
    */

   function instalacion($e, $args) {
      
      global $gcm;

      }

   /**
    * Mostrar iconos para poder ser seleccionados
    */

   function seleccionar_iconos($e, $args=FALSE) {

      global $gcm;

      // Router::$base.$gcm->event->instancias['temas']->ruta('mapas','iconos',$marca['icono'])

      $iconos = glob(dirname(__FILE__).'/../iconos/*');

      ob_start(); 
      echo '<div id="panel_iconos">';
      foreach ( $iconos as $ico ) {

         $basename = basename($ico);

         ?>
         <span class="seleccionar_icono">
            <img src="<?php echo Router::$base.$gcm->event->instancias['temas']->ruta('mapas','iconos',$basename); ?>" alt="<?php echo $basename; ?>"/>
         </span>
         <?php

         }
      echo '</div>';
      $salida = ob_get_contents() ; ob_end_clean();

      $panel = array();
      $panel['titulo']    = literal('Iconos',3);
      $panel['oculto']    = FALSE;
      $panel['subpanel']  ='subpanel_iconos';
      // $panel['jajax']      = "?formato=ajax&m=literales&a=devolverLiterales"; 
      $panel['contenido'] = $salida; 
         
      Temas::panel($panel);


      // @todo Añadir javascript para al clicar en un icono añadirlo al textarea

      $this->javascripts('incluir_iconos.js');

      }

   }

?>
