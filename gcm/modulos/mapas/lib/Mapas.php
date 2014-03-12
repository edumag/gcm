<?php

/**
 * @file Mapas.php
 * @brief Presentar mapa y marcadores configurados
 *
 * @package Modulos
 */

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

class Mapas extends Modulos {

   public $mapas;         //< Listado de mapas
   public $marcadores;    //< Listado de marcadores

   static $cargado_script = FALSE ; //< Para saber si ya tenemos cargadas las librerías javascript

   /** Constructor */

   function __construct() {

      parent::__construct();

      $this->mapas = $this->config('Mapas');
      $this->marcadores = $this->config('Marcadores');

      }

   /**
    * Presentar mapa
    *
    * @paaram $e Evento que lo llama
    * @paaram $args Argumentos, por defecto en $args obtendremos el nombre del mapa a mostrar
    */

   function mapa($e,$args=FALSE) {

      global $gcm;

      $gcm->add_lib_js('temas', 'jquery.modal.min.js');

      if ( ! $args ) { registrar(__FILE__,__LINE__,"Es necesario pasar el nombre del mapa a mostrar",'ERROR') ; return FALSE ;}
      
      $mapa_nombre = $args;

      $mapa_encontrado = FALSE ;
      foreach ( $this->mapas as $mapa ) {
         if ( $mapa['nombre'] == $mapa_nombre ) {
            $mapa_encontrado = TRUE;
            continue;
            }
         }

      if ( ! $mapa_encontrado ) { registrar(__FILE__,__LINE__,"No se ha encontrado un mapa con este nombre [$mapa_nombre]",'ERROR') ; return FALSE ;}

      $latitud        = $mapa['latitud'];
      $longitud       = $mapa['longitud'];
      // $tipo           = $mapa['tipo'];
      $zoom           = intval($mapa['zoom']);
      $otras_opciones = $mapa['Otras opciones'];

      if ( $this->marcadores ) {
         foreach ( $this->marcadores as $marcador ) {
            if ( $marcador['mapa'] == $mapa_nombre ) {
               $marcadores[] = $marcador;
               }
            }

         }

      $caja_mapa     = str_replace(' ','_',$mapa_nombre);

      $this->cargar_script();
      ?>
      <div id="<?php echo $caja_mapa ?>" class="mapa" ><?php echo literal('Cargando mapa')?>...</div>
      <div id="<?php echo $caja_mapa ?>_info_default" class="mapa_info" ></div>
      <script type="text/javascript">

         // 'tipo': '<?php echo $tipo ?>',

         var mapa = {
            'nombre': '<?php echo $mapa_nombre ?>',
            'latitud': '<?php echo $latitud ?>',
            'longitud': '<?php echo $longitud ?>',
            'zoom': <?php echo $zoom ?>,
            'otras_opciones': '<?php echo $otras_opciones ?>',
         };


         var markers = {
      <?php if ( $marcadores ) { ?>
           'marca': [
         <?php foreach ( $marcadores as $marca ) { ?>
             {
               'name': '<?php echo $marca['nombre'] ?>'
               ,'location': [<?php echo $marca['latitud'] ?>, <?php echo $marca['longitud'] ?>]
               ,'contenido': '<?php echo preg_replace("/[\n|\r|\n\r]/", ' ', $marca['contenido']);?>'
               ,'icon': '<?php echo Router::$base.$gcm->event->instancias['temas']->ruta('mapas','iconos',$marca['icono'])?>'

             },
         <?php } ?>
           ]
         <?php } ?>
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

   }

?>
