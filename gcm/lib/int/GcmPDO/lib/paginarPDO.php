<?php

/**
 * @file      paginarPDO.php
 * @brief     Componente para la paginación de PDO
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 *   Created  14/05/10
 *  Revision  SVN $Id: PaginarPDO.php 278 2010-07-13 12:24:14Z eduardo $
 * Copyright  Copyright (c) 2010, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

/** Recogemos clase padre */

require_once(dirname(__FILE__).'/GcmPDO.php');

/**
 * @class PaginarPDO
 * @brief Componente para la paginación de PDO
 */

class PaginarPDO extends GcmPDO {

   public $elementos_pagina;              ///< Número de elementos por pagina
   public $url_base='';                   ///< Url de enlace de botonera
   public $plantilla_resultados;          ///< Plantilla que presenta los resultados
   public $plantilla_botonera;            ///< Plantilla que presenta los resultados
   public $paginas_agrupadas=2;           ///< Número de botones de páginas que se presenta por alante y atras de la actual
   public $sufijo;                        ///< Para diferenciar entre diferentes instancias

   private $inicio;                       ///< Inicio
   private $pagina;                       ///< Número de página
   private $total_de_paginas;             ///< Número total de páginas
   private $elemento_inicio;              ///< Número inicial de elemento
   private $elemento_final;               ///< Número de ultimo elemento
   private $totalpp;                      ///< PENDIENTE

   private $as_orden;                     ///< Alias del campo por el que se ordena
   private $tipo_orden;                   ///< Tipo de orden (asc/desc)

   private static $script_ajax_incluido = FALSE ;  ///< Para saber si ya esta incluido el script para ajax

   /**
    * @param $pdo Instancia de PDO
    * @param $sql sql a paginar
    * @param $sufijo Sufijo para que no choquen cuando hay más de un paginador en una
    *                misma pagina.
    * @param $orden Orden por defecto de la sql
    * @param $sql_relaciones Una sql compleja, puede ser necesario añadir estas relaciones, al 
    *        ordenar las fechas por el alias el orden nos lo hace como si fuera una  cadena, 
    *        así en este caso se hace necesario tener el nombre real del  campo para que las 
    *        ordene como fechas.
    */

   function __construct (PDO $pdo, $sql, $sufijo=FALSE, $elementos_pagina=8, $order = FALSE, $sql_relaciones = FALSE) {

       parent::__construct($pdo, $sql);

      $this->sufijo = ( $sufijo ) ? $sufijo : '';

      $this->elementos_pagina = ( $elementos_pagina ) ? $elementos_pagina : 10 ; 

     // Definimos orden si nos llega por GET o cogemos por defecto

      if ( isset($_GET[$this->sufijo.'orden']) && ! empty($_GET[$this->sufijo.'orden']) ) {

         $this->as_orden = stripslashes($_GET[$this->sufijo.'orden']);

         $this->tipo_orden = ( isset($_GET[$this->sufijo.'tipo_orden']) ) ? $_GET[$this->sufijo.'tipo_orden'] : 'asc';

         // Buscamos si tenemos una relación definida del alias del campo con su nombre real

         if ( $sql_relaciones && array_key_exists($this->as_orden,$sql_relaciones) ) {
            $order = "ORDER BY ".$sql_relaciones[$this->as_orden]." ".$this->tipo_orden;
         } else {
            $order = "ORDER BY `".$this->as_orden."` ".$this->tipo_orden;
            }

      } elseif ( $order ) {

         preg_match_all('/order by (.*?) (.*?)/i', $order, $coincidencias);

         $ordenadox = ( !empty($coincidencias[1][0]) ) ? trim($coincidencias[1][0],',') : FALSE ;

         $this->tipo_orden = ( stripos($order,'desc') !== FALSE ) ? 'desc' : 'asc' ;

         if ( $ordenadox ) {

            // Buscar el alias del campo por el cual se ordena
            // hay que tener cuidado aquí el resultado es un poco 
            // imprevisible.

            if ( preg_match('/'.$ordenadox.'(.?) as (.*)/i',$sql, $coincidencias) && ! empty($coincidencias[2]) ) {

               $this->as_orden = trim($coincidencias[2],',');

            } else {

               $this->as_orden = $ordenadox;

               }

            // Si tenemos una relación del nombre del campo ordenado le indicamos a as_orden
            if ( $sql_relaciones && array_search($this->as_orden,$sql_relaciones) ) {
               $this->as_orden = array_search($this->as_orden,$sql_relaciones);
               }

         } else {
            $this->as_orden = FALSE;
            }

      } else {

         $this->as_orden = FALSE;

         }


      $this->sql = $sql." ".$order;

      // if ( isset($ordenadox) ) echo '<br>ordenadox: '.$ordenadox;
      // echo '<br>as_orden: '.$this->as_orden;
      // echo '<br>tipo_orden: '.$this->tipo_orden;
      // echo "<br>sql: <pre>" ; print_r($this->sql) ; echo "</pre>"; // DEV  

      if (empty($_GET[$this->sufijo."pagina"])) {
         $inicio = 0;
         $this->pagina=1;

      } else {

         $this->pagina = $_GET[$this->sufijo."pagina"];
         $inicio = ($this->pagina - 1) * $this->elementos_pagina;

         }

      $inicio = ( $inicio > 0 ) ? $inicio : 0 ;

      $this->total_de_paginas = ( $this->num_total_registros > 1 ) ? ceil($this->num_total_registros / $this->elementos_pagina) : 1 ;

      $this->sql=$this->sql." limit " . $inicio . "," . $this->elementos_pagina;

      $this->totalpp=ceil($this->total_de_paginas / $this->paginas_agrupadas);
      //$this->elemento_inicio=$this->pagina*$this->paginas_agrupadas-$this->paginas_agrupadas+1;
      //$this->elemento_final=$this->elemento_inicio+$this->paginas_agrupadas-1;
      $this->elemento_inicio=($this->pagina-$this->paginas_agrupadas);
      $this->elemento_final=($this->pagina+$this->paginas_agrupadas);

      if ( $this->elemento_inicio < 1  ) $this->elemento_inicio=1;
      if ( $this->elemento_final > $this->total_de_paginas  ) $this->elemento_final=$this->total_de_paginas;

      // echo "<pre>sql: " ; print_r($this->sql) ; echo "</pre>"; // DEV  
      }

   /** 
    * Presentamos contenido
    *
    * @param $opciones_array2table Opciones que pasamos a array2table
    * @param $presentacion array2table.
    */

   function pagina($opciones_array2table=FALSE, $presentacion = 'Array2table') {

      global $gcm;

      if ( ! $this->resultado  ) $this->resultado();

      // echo "<pre>resultado: " ; print_r($this->resultado()) ; echo "</pre>"; // DEV  
      // echo "<pre>array: " ; print_r($this->to_array()) ; echo "</pre>"; // DEV  
      // echo "<pre>opciones_array2table: " ; print_r($opciones_array2table) ; echo "</pre>"; // DEV  
      // echo "<pre>opciones: " ; print_r($opciones) ; echo "</pre>"; // DEV  

      if ( $this->plantilla_resultados  ) {

         // Plantilla personalizada

         include($this->plantilla_resultados);

      // } elseif ( ! $opciones_array2table ) {

      //    // Utilizamos array2table si no se especifico plantilla

      //    require_once(dirname(__FILE__).'/../../array2table/lib/Array2table.php');

      //    $array2table = new Array2table($this->sufijo);
      //    $array2table->generar_tabla($this->to_array(), $opciones_array2table, $this->as_orden, $this->tipo_orden);

      } else {

         // Presentación 

         require_once(dirname(__FILE__).'/../../array2table/lib/'.$presentacion.'.php');

         $array2table = new $presentacion();
         $array2table->sufijo = $this->sufijo;
         $array2table->generar_tabla($this->to_array(), $opciones_array2table, $this->as_orden, $this->tipo_orden);

         }

      }

   /** 
    * Botonera
    */

   function botonera() {

      // require_once(dirname(__FILE__).'/../../gcm/lib/helpers.php');

      if ( ! $this->resultado  ) $this->resultado();

      $this->url_base = construir_get( array(
         $this->sufijo.'orden' => htmlspecialchars(trim($this->as_orden),ENT_QUOTES)
         , $this->sufijo.'tipo_orden' => $this->tipo_orden
      ));


      /* Mirar si la url ya viene con ? */

      $simbolo = '&';

      if ( ! $this->plantilla_botonera ) {

         include(dirname(__FILE__).'/../html/botonera_paginador.html');

      } else {

         include($this->plantilla_botonera);
         
         }
      }


   /** 
    * Generar página con contenido
    *
    * @param $url_ajax Para indicar a la url que hacemos ajax, ejemplo: '&formato=ajax'
    * @param $opciones_array2table Opciones para generar la tabla con array2table
    * @param $presentacion Clase a utilizar para presentar la tabla, por defecto
    *                      array2table.
    */

   function generar_pagina($url_ajax=FALSE, $opciones_array2table = FALSE, $presentacion = 'Array2table') {

      /* creamos caja con contenido que pueda ser sustituido con ajax */

      $ident = $this->sufijo;
      $div   = $ident.'paginador';

      echo '<div id="'.$div.'">';
      $this->botonera();
      $this->pagina($opciones_array2table, $presentacion);
      $this->botonera();
      echo '</div>';
      if ($this->total_de_paginas > 1) {
         if ( $url_ajax && $url_ajax != '' ) {
            $this->script_ajax();
            
            if ( isset($_REQUEST['formato']) && $_REQUEST['formato'] == 'ajax' ) {
               ?>
               <script>setTimeout('initPaginador("#<?=$div?>","<?=$url_ajax?>")', 1000);</script>
               <?php
            } else {
               ?>
               <script>
                  addLoadEvent(function(){
                     initPaginador("#<?=$div?>","<?=$url_ajax?>");
                  });
               </script>
               <?php
               }
            }
         }
      }

   /**
    * Javascript para botonera con ajax
    *
    * En caso de encontrar enlaces dentro de la cabecera de la tabla tambien
    * le aplicamos el ajax.
    *
    * Esto nos servira pata las tablas ordenadas que funcione el ajax.
    */

   function script_ajax() {

      if ( self::$script_ajax_incluido  ) return ;

      ?>
      <script>
      function initPaginador(div,url_ajax) {
         $(div).find(".botonera_paginador a").click(function() {
            var url = $(this).attr("href") + url_ajax;
            $.get(url,function(data){
               $(div).replaceWith(data);
              });
            return false;
         });
         $(div).find("table th a").click(function() {
            var url = $(this).attr("href") + url_ajax;
            $.get(url,function(data){
               $(div).replaceWith(data);
              });
            return false;
         });
       }
      </script>
      <?php

      self::$script_ajax_incluido = TRUE;

      }

   }
?>
