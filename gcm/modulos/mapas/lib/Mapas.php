<?php

/**
 * @file mapas.php
 * @brief Presentar mapa y marcadores configurados
 *
 * @package Modulos
 */

/**
 * @class mapas
 * @brief Slideshow de imágenes
 *
 * @code
 * $gcm->event->lanzar_accion_modulo('mapas','mapa','evento_mapa');
 * @endcode
 */

class Mapas extends Modulos {

   public $marcadores;

   static $cargado_script = FALSE ;

   /** Constructor */

   function __construct() {

      parent::__construct();

      // $this->marcadores = $this->config('Marcadores');

      }

   function mapa($e,$args=FALSE) {

      global $gcm;

      $caja_mapa     = 'caja_mapa';
      $mapa_latitud  = '42.522724';
      $mapa_longitud = '3.01712';

      echo 'PRESENTANDO MAPA';

      $this->cargar_script();
      ?>
      <div id="<?php echo $caja_mapa ?>" class="mapa" >MAPA</div>
      <div id="<?php echo $caja_mapa ?>_info" class="mapa_info" >MAPA INFO</div>
      <script type="text/javascript">

         var markers = {
           'countries': [
             {
               'name': 'La Svizra',
               'location': [46.818188, 8.227512]
             },
             {
               'name': 'España',
               'location': [40.463667, -3.74922]
             }
           ]
         };

      addLoadEvent(function(){

         inicia_mapa('caja_mapa',markers);
         }); 
      </script>
      <?php

      }

   /**
    * Añadimos los javascript necesarios para la carga de mapas.
    */

   function cargar_script() {

      if ( self::$cargado_script ) return ;
      ?>
      <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
      <script type="text/javascript" src="<?php echo Router::$base ?><?php echo GCM_DIR ?>modulos/mapas/js/mapas.js"></script>
      <?php
      self::$cargado_script = TRUE;

      }

   /**
    * Administración de mapas
    *
    * @param $e Evento que lo llama
    * @param $args Argumentos
    */

   function admin($e, $args) {

      global $gcm;

      require(GCM_DIR.'lib/int/GcmConfig/lib/Config.php');

      $dir_datos = "DATOS/configuracion/mapas/";

      // Buscar archivos de mapas
      $mapas_conf = glob($dir_datos.'mapa*.php');
      if ( $mapas_conf ) {
         foreach ( $mapas_conf as $mapa_conf ) {
            echo "<br>Mapa: ".$mapa_conf;
            }
      }

      // permitir seleccionar
      // nuevo mapa
      $nuevo = new Config($dir_datos.'mapa1.php');
      // Editar existente
      // Guardar mapa


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

   }

?>
