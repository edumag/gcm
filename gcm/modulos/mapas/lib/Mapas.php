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

   public $mapas;
   public $marcadores;

   static $cargado_script = FALSE ;

   /** Constructor */

   function __construct() {

      parent::__construct();

      $this->mapas = $this->config('Mapas');
      $this->marcadores = $this->config('Marcadores');

      }

   function mapa($e,$args=FALSE) {

      global $gcm;

      if ( ! $args ) { registrar(__FILE__,__LINE__,"Es necesario pasar el nombre del mapa a mostrar",'ERROR') ; return FALSE ;}
      
      $mapa_nombre = $args;

      $mapa = $this->mapas[$mapa_nombre];

      foreach ( $this->mapas as $mapa ) {
         if ( $mapa['nombre'] == $mapa_nombre ) continue;
         }

      if ( ! $mapa ) { registrar(__FILE__,__LINE__,"No se ha encontrado un mapa con este nombre [$mapa_nombre]",'ERROR') ; return FALSE ;}

      $latitud        = $mapa['latitud'];
      $longitud       = $mapa['longitud'];
      $tipo           = $mapa['tipo'];
      $zoom           = intval($mapa['zoom']);
      $otras_opciones = $mapa['Otras opciones'];

      foreach ( $this->marcadores as $marcador ) {
         if ( $marcador['mapa'] == $mapa_nombre ) {
            $marcadores[] = $marcador;
            }
         }

      $caja_mapa     = str_replace(' ','_',$mapa_nombre);

      $this->cargar_script();
      ?>
      <div id="<?php echo $caja_mapa ?>" class="mapa" ><?php echo literal('Cargando mapa')?>...</div>
      <div id="<?php echo $caja_mapa ?>_info" class="mapa_info" ></div>
      <script type="text/javascript">

         var mapa = {
            'nombre': '<?php echo $mapa_nombre ?>',
            'latitud': '<?php echo $latitud ?>',
            'longitud': '<?php echo $longitud ?>',
            'tipo': '<?php echo $tipo ?>',
            'zoom': <?php echo $zoom ?>,
            'otras_opciones': '<?php echo $otras_opciones ?>',
         };

         var markers = {
           'marca': [
         <?php foreach ( $marcadores as $marca ) { ?>
             {
               'name': '<?php echo $marca['nombre'] ?>'
               ,'location': [<?php echo $marca['latitud'] ?>, <?php echo $marca['longitud'] ?>]
               ,'contenido': '<?php echo $marca['contenido'] ?>'

             },
         <?php } ?>
           ]
         };

      addLoadEvent(function(){

         inicia_mapa('<?php echo $caja_mapa ?>',mapa, markers);
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
