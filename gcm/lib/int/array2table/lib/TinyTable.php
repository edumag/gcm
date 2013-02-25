<?php

/**
 * @file      TinyTable.php
 * @brief     Convertir tabla de Array2Tabla a javascript
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 *   Created  07/05/10
 *  Revision  SVN $Id: Array2table.php 286 2010-07-16 09:02:14Z eduardo $
 * Copyright  Copyright (c) 2010, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

/** Añadimos clase padre */

require_once(dirname(__FILE__).'/Array2table.php');

/** 
 * Añadir javascript a la tabla generada por Array2table
 *
 * @category Gcm
 * @author Eduardo Magrané
 * @version 0.1
 *
 */

class TinyTable extends Array2table {

   private $scripts_cargados;                    ///< Para saber si ya se cargo el script o no.
   private $cargar_script = FALSE;               ///< Cargar nuestro propio script o no

   private $dir_tinytable;                       ///< Directorio de tinytable

   //function __construct($dir_tinytable = FALSE, $cargar_script = TRUE) {
   function __construct($op = FALSE) {

      parent::__construct();

      $this->cargar_script = ( isset($op['cargar_script']) ) ?  $op['cargar_script'] : TRUE ;

      if ( isset($op['dir_tinytable']) ) {
         $this->dir_tinytable = $op['dir_tinytable'];
      } else {
         $this->dir_tinytable = Router::$base.GCM_DIR.'lib/ext/TinyTable/';
         }


      }

   /**
    * Añadimos codigo javascript necesario para el funcionamiento.
    *
    */

   function scripts() {

      if ( ! $this->scripts_cargados  ) {

         $this->scripts_cargados = TRUE;

         ?>
         <script type="text/javascript" src="<?=$this->dir_tinytable;?>script.js"></script>
         <?php

         }

      }

   /**
    * Generar tabla.
    *
    * - Si un campo se llama "%" se vera de forma grafica su contenido.
    *
    * - Los enlaces de las acciones se pasaran mediante $url.$identificador&accion=modificar
    *   Si se desea cambiar 'accion' por otra palabra se puede especificar en opciones.
    *
    * 
    * @param $res Array con el contenido
    *        Formato del array $resultado[0][columna]=valor
    *
    * @param $opciones Array con las diferentes opciones
    *         identificador = '<nombre columna que hace de identificador>', por defecto 'id'.
    *         ver           = '<nombre de la acción para entrar en detalles de un registro>'
    *         modificar     = '<nombre de la acción para modificar>', es necesario tener identificador
    *         eliminar      = '<nombre de la acción para eliminar registro>', Es necesario identificador
    *         ocultar_id    = TRUE/FALSE Ocultar columna de identificador.
    *         url           = 'Url para enlaces de registro'
    *         accion        = 'Nombre de variable GET que se pasara en url, por defecto 'accion'
    * 
    */

   function generar_tabla($res, $opciones=FALSE, $orden = FALSE , $tipo_orden = FALSE) {
      
      ?>
      <div id="tablewrapper">
         <div id="tableheader">
            <div class="search">
                   <select id="columns" onchange="sorter.search('query')"></select>
                   <input type="text" id="query" onkeyup="sorter.search('query')" />
               </div> <!-- search -->
               <span class="details">
               <div>Records <span id="startrecord"></span>-<span id="endrecord"></span> of <span id="totalrecords"></span></div>
               <div><a href="javascript:sorter.reset()">reset</a></div>
            </span>
           </div>

      <?php

      if ( $opciones ) {
         $opciones = array_merge(array( 'table_id' => 'table'), $opciones);
      } else {
         $opciones = array( 'table_id' => 'table');
         }

      parent::generar_tabla($res, $opciones);

      $this->pie_tabla();

      }

   /** Pie de tabla */

   function pie_tabla() {

      ?>

        <div id="tablefooter">
          <div id="tablenav">
            	<div>
               <img src="<?=$this->dir_tinytable?>images/first.gif" width="16" height="16" alt="First Page" onclick="sorter.move(-1,true)" />
                    <img src="<?=$this->dir_tinytable?>images/previous.gif" width="16" height="16" alt="First Page" onclick="sorter.move(-1)" />
                    <img src="<?=$this->dir_tinytable?>images/next.gif" width="16" height="16" alt="First Page" onclick="sorter.move(1)" />
                    <img src="<?=$this->dir_tinytable?>images/last.gif" width="16" height="16" alt="Last Page" onclick="sorter.move(1,true)" />
                </div>
                <div>
                	<select id="pagedropdown"></select>
				</div>
                <div>
                	<a href="javascript:sorter.showall()">view all</a>
                </div>
            </div>
			<div id="tablelocation">
            	<div>
                    <select onchange="sorter.size(this.value)">
                    <option value="5">5</option>
                        <option value="10" selected="selected">10</option>
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                    <span>Entries Per Page</span>
                </div>
                <div class="page">Page <span id="currentpage"></span> of <span id="totalpages"></span></div>
            </div>
        </div>
      </div>

      <?php $this->scripts(); ?>

      <?php if ( $this->cargar_script ) { ?>

      <script type="text/javascript">
         var sorter = new TINY.table.sorter('sorter','table',{
            headclass:'head', // Header Class //
            ascclass:'asc', // Ascending Class //
            descclass:'desc', // Descending Class //
            evenclass:'evenrow', // Even Row Class //
            oddclass:'oddrow', // Odd Row Class //
            evenselclass:'evenselected', // Even Selected Column Class //
            oddselclass:'oddselected', // Odd Selected Column Class //
            paginate:true, // Paginate? (true or false) //
            size:20, // Initial Page Size //
            colddid:'columns', // Columns Dropdown ID (optional) //
            currentid:'currentpage', // Current Page ID (optional) //
            totalid:'totalpages', // Current Page ID (optional) //
            startingrecid:'startrecord', // Starting Record ID (optional) //
            endingrecid:'endrecord', // Ending Record ID (optional) //
            totalrecid:'totalrecords', // Total Records ID (optional) //
            hoverid:'selectedrow', // Hover Row ID (optional) //
            pageddid:'pagedropdown', // Page Dropdown ID (optional) //
            navid:'tablenav', // Table Navigation ID (optional) //
            sortcolumn:1, // Index of Initial Column to Sort (optional) //
            sortdir:1, // Sort Direction (1 or -1) //
            <?php

      /* Totals */

      //sum:[8], // Index of Columns to Sum (optional) //
      //avg:[6,7,8,9], // Index of Columns to Average (optional) //

      if ( isset($sum) ) { echo "\n\t".'sum:['.$sum.'],'; }
      if ( isset($avg) ) { echo "\n\t".'avg:['.$avg.'],'; }

      /*
      columns:[{index:7, format:'%', decimals:1},{index:8, format:'$', decimals:0}], // Sorted Column Settings (optional) //
       */

      ?>
      init:true // Init Now? (true or false) //
      });
      </script>

      <?php } ?>

      <br /><br />
      <?php

      }

   }

?>
