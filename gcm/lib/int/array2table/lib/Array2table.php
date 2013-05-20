<?php

/**
 * @file      Array2table.php
 * @brief     Pasar contenido de un array a tabla
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 *   Created  07/05/10
 *  Revision  SVN $Id: Array2table.php 650 2012-10-04 07:17:48Z eduardo $
 * Copyright  Copyright (c) 2010, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

/** 
 * Transformar contenido array en tabla html
 */

class Array2table {

   public static $base_imagenes ;                //< Directorio de imágenes 
   public static $img_modificar ;                //< Imagen para modificación
   public static $img_borrar    ;                //< Imagen para borrado
   public static $img_ver       ;                //< Imagen para visualizar detalles
   public $sufijo               ;                //< sufijo para añadir a los enlaces

   /**
    * Constructor
    *
    * @param $base_imagenes Directorio donde se encuentran las imágenes desde html 
    *                  
    */

   function __construct($sufijo='') {

      $this->sufijo        = $sufijo         ;
      self::$img_modificar = 'modificar.png' ;
      self::$img_borrar    = 'borrar.png'    ;
      self::$img_ver       = 'ver.png'       ;
      self::$base_imagenes = Router::$base.GCM_DIR.'lib/int/array2table/img/';

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
    * @param $opciones Array con las diferentes opciones:
    *
    *     identificador  Nombre columna que hace de identificador>', por defecto 'id'.
    *     ver            Nombre de la acción para entrar en detalles de un registro>'
    *     modificar      Nombre de la acción para modificar>', es necesario tener identificador
    *     eliminar       Nombre de la acción para eliminar registro>', Es necesario identificador
    *     ocultar_id     TRUE/FALSE Ocultar columna de identificador.
    *     url            Url para enlaces de registro'
    *     accion         Nombre de variable GET que se pasara en url, por defecto 'accion'
    *     dir_imag       Url del directorio donde se encuentran los iconos modificar, eliminar, etc...
    *     enlaces        Array con el contenido de los campos que contienen un enlace.
    *
    *                    Estructura de ejemplo:
    *
    *                    array('url' => array(                            // Nombre del campo con el enlace
    *                                   'campo_enlazado'=>'contenido'     // Nombre del campo que se mostrara en el enlace
    *                                  ,'titulo_columna'=>'Contenido'     // Titulo de la columna
    *                                  ,'base_url'=>Router::$base         // Base de la url 
    *                                    )
    *                                  )
    *        Importaante:
    *           Para que funcione correctamente el campo url debe llegar primero desde la sql que el campo al que enlaza
    *
    *     $fila_unica   Campo que deseamos que se muestre en una sola fila de la tabla, util para cuando 
    *                   es un campo con mucho texto, sin necesidad de ordenarse por él.
    * 
    * @param $orden Nombre de la columna por la que se esta ordenando
    * @param $tipo_orden (asc/desc) 
    */

   function generar_tabla($res, $opciones=NULL, $orden = FALSE , $tipo_orden = FALSE) {
      
      $num_registros = count($res);
      $num_columnas  = count($res[0]);

      /** Se presenta en una sola fila, para permitir gran cantidad de datos */

      $salida_fila_unica = FALSE ;

      /** Identificador htnl de la tabla, añadiremos id="$table_id" Para distinguirlas*/
      $table_id      = ( isset($opciones['table_id']))      ? $opciones['table_id']      : FALSE ;

      /** url base para enlaces */
      $url           = ( isset($opciones['url']))           ? $opciones['url']           : FALSE ;

      /** Nombre de variable get que lleva la acción */
      $accion        = ( isset($opciones['accion']))        ? $opciones['accion']        : FALSE ;

      /** identificador base para enlaces */
      $identificador = ( isset($opciones['identificador'])) ? $opciones['identificador'] : FALSE ;

      /** ver base para enlaces */
      $ver           = ( isset($opciones['ver']))           ? $opciones['ver']           : FALSE ;

      /** modificar base para enlaces */
      $modificar     = ( isset($opciones['modificar']))     ? $opciones['modificar']     : FALSE ;

      /** eliminar base para enlaces */
      $eliminar      = ( isset($opciones['eliminar']))      ? $opciones['eliminar']      : FALSE ;

      /** ocultar_id base para enlaces */
      $ocultar_id    = ( isset($opciones['ocultar_id']))    ? $opciones['ocultar_id']    : FALSE ;

      /** Generar colgroup en la cabecera de la tabla */
      $colgroup    = ( isset($opciones['colgroup']))    ? $opciones['colgroup']    : FALSE ;

      /** Url del directorio de los iconos */
      self::$base_imagenes = ( isset($opciones['dir_img']))    ? $opciones['dir_img']    : self::$base_imagenes ;

      /** Enlaces definidos desde opciones */
      $enlaces = ( isset($opciones['enlaces']))    ? $opciones['enlaces']    : FALSE ;

      /** Campos que se desean mostrar en una sola fila */
      $fila_unica = ( isset($opciones['fila_unica']))    ? $opciones['fila_unica']    : FALSE ;

      /* Si no se indica el campo identificador cogemos id por defecto */

      $identificador = ( $identificador ) ? $identificador : 'id';

      $simbolo = ( strrpos($url,'?') === FALSE ) ? '?' : '&';        //< Evitar colocar dos veces el interrogante en la url

      ?>
         <table cellpadding="0" cellspacing="0" border="0" <?php if ( $table_id ) echo 'id="'.$table_id.'"';?> class="dataTable">

         <?php // Elementos colgroup nos permite modificar presentación por columnas ?>

         <?php if ( $colgroup ) { ?>

            <colgroup>
            <?php foreach ( $res[0] as $columna => $valor ) { ?>
            <?php if ( $columna == $identificador  && $ocultar_id ) continue; ?>
            <col id="col_<?php echo htmlspecialchars($columna,ENT_QUOTES);?>"> 
            <?php } ?>
            </colgroup>

         <?php } ?>

         <thead>
            <tr>
      <?php

      if ( $res ) {

         foreach ( $res[0] as $columna => $valor ) {         // Encabezado de tabla

            $columna = trim($columna);

            /* Columna de identificador sin ordenación

            if ( $columna == $identificador  ) {
               $clase=" class='nosort'";
            } else {
               $clase=NULL;
               }

             */

            if ( $columna == $identificador  && $ocultar_id ) continue;

            $clase = ( $columna == $orden ) ? ' class="'.$tipo_orden.'" ' : ' class="head" ';

            if ( $columna == $orden ) {

               $tipo_orden2 = ( $tipo_orden == 'asc' ) ? 'desc' : 'asc';
            } else {
               $tipo_orden2 = 'asc';
               }

            $url_orden = construir_get( array ($this->sufijo.'orden' => trim($columna)
               , $this->sufijo.'tipo_orden' => trim($tipo_orden2)
               , $this->sufijo.'pagina' => 1)) ;

            if ( $enlaces && isset($enlaces[$columna]) ) {
               $titulo_columna = $enlaces[$columna]['titulo_columna'];
               $campo_enlazado = $enlaces[$columna]['campo_enlazado'];
               $enlaces_iniciados[] = $campo_enlazado;
            } elseif ( isset($enlaces_iniciados) && in_array($columna, $enlaces_iniciados) ) {
               $num_columnas--;
               continue;
            } elseif ( isset($fila_unica) && $fila_unica == $columna ) {
               // Si es un campo que se desea presentar en una sola linea de la tabla
               // no se debe mostrar su cabecera
               $num_columnas--;
               continue;
            } else {
               $titulo_columna = $columna;
               }

            echo "\n\t\t\t";
            echo "<th".$clase.">";
            if ( $url ) echo "<a href='".htmlspecialchars($url_orden,ENT_QUOTES)."'>";
            echo "<h3>";
            echo $titulo_columna;
            echo "</h3>";
            if ( $url ) echo "</a>";
            echo "</th>";

            }

         }

      if ( $ver ) {
         $num_columnas++;
         ?>
         <th>
            <h3>
               <img src='<?=self::$base_imagenes.self::$img_ver?>'
               width='16' height='16' border='0' title='Visualizar' 
               alt='[*]' />
            </h3>
         </th>
         <?php
         }

      if ( $modificar ) {
         $num_columnas++;
         ?>
         <th>
            <h3>
               <img src='<?=self::$base_imagenes.self::$img_modificar?>'
               width='16' height='16' border='0' title='Modificar' 
               alt='[#]' />
            </h3>
         </th>
         <?php
         }

      if ( $eliminar ) {
         $num_columnas++;
         ?>
         <th>
            <h3>
               <img src='<?=self::$base_imagenes.self::$img_borrar?>'
               width='16' height='16' border='0' title='Eliminar' 
               alt='[-]' />
            </h3>
         </th>
         <?php
         }

      //echo "\n\t\t</tr>";
      echo "\n\t</thead>";
      echo "\n\t<tbody>";

      $fpi = 'evenrow';

      foreach ( $res as $fila ) {          // filas del body de la tabla

         $enlace = FALSE;

         echo "\n\t\t<tr class='".$fpi."'>";

         $col = 0;
         foreach ( $fila as $key_columna => $columna ) {       // columnas

            $col++;
            // Si estamos en la columna que ordena añadimos clase especial

            $clase_columna =  ( $key_columna == $orden ) ? ' class="col_'.$col.' oddselected" ' : ' class="col_'.$col.'"' ;

            /* Si estamos en la columna id definimos enlaces hacia el elemento */

            if ( $identificador && $key_columna == $identificador && $url ) {

               /* Añadimos identificador a url */
               $enlace = $url.$columna;

               }

            /* Si tenemos columna con % Añadimos efecto visual para representarlo */

            if ( $key_columna == $identificador  && $ocultar_id ) { // id oculto
               $col--;
               echo ''; 

            } elseif ( $enlaces && isset($enlaces[$key_columna]) ) {
               $DATO=trim($columna);
               $campo_enlazado = $enlaces[$key_columna]['campo_enlazado'];
               $enlaces_creados[$campo_enlazado]['inicio'] = sprintf("\n\t\t\t<td %s><a href='%s%s'>",$clase_columna,$enlaces[$key_columna]['base_url'],$DATO);
               if ( isset($enlaces_creados[$campo_enlazado]['inicio']) && isset($enlaces_creados[$campo_enlazado]['final']) ) 
                  echo $enlaces_creados[$campo_enlazado]['inicio'].$enlaces_creados[$campo_enlazado]['final'];

            } elseif ( isset($enlaces_creados[$key_columna]) ) {
               $DATO=trim($columna);
               $clave_enlace = $key_columna;
               $enlaces_creados[$clave_enlace]['final'] = sprintf("%s</a></td>",$DATO);
               if ( isset($enlaces_creados[$clave_enlace]['inicio']) && isset($enlaces_creados[$clave_enlace]['final']) ) 
                  echo $enlaces_creados[$clave_enlace]['inicio'].$enlaces_creados[$clave_enlace]['final'];

            } elseif ( isset($fila_unica) && $fila_unica == $key_columna ) {
               $DATO=trim($columna);
               $salida_fila_unica .= sprintf("\n\t\t<tr><td colspan='%s' %s>%s</td></tr>",$num_columnas,$clase_columna,$DATO);
               
            } elseif ($key_columna == "img" ) {
               $DATO=trim($columna);
               printf("\n\t\t\t<td".$clase_columna."> <img width='60px' src='%s' /></td>",$DATO);
               
            } elseif ($key_columna == "%") {
               $DATO=trim($columna);
               printf("\n\t\t\t<td".$clase_columna." style='border:0;'> <div style='background-color: #5db02d; width: \
               %spx'>%s</div></td>",$DATO,$DATO,$DATO);
               
            } else {

               if ( $enlace ) {
                  echo "\n\t\t\t<td".$clase_columna.">";
                  echo "<a href='".$enlace."'>";
                  echo $columna;
                  echo "</a></td> " ;
               } else {
                  echo "\n\t\t\t<td".$clase_columna.">".$columna."</td> " ;
                  }

               }

            }

         if ($enlace)  {

            if ( $modificar ) {
               echo "\n\t\t\t<td><a href='$enlace&$accion=".$modificar."'>
               <img src='".self::$base_imagenes.self::$img_modificar."'
               width='16' height='16' border='0' title='Modificar' 
               alt='[#]' ></a></td>";	
               }

            if ( $eliminar ) {
               echo "\n\t\t\t<td><a href='$enlace&$accion=$eliminar'>
               <img src='".self::$base_imagenes.self::$img_borrar."'
               width='16' height='16' border='0' title='Eliminar' 
               alt='[-]' ></a></td>";
               }

            if ( $ver ) {
               echo "\n\t\t\t<td><a href='$enlace&$accion=ver'>
               <img src='".self::$base_imagenes.self::$img_ver."' 
               width='16' height='16' border='0' title='ver' 
               alt='[*]' ></a></td>";
               }

            $enlace = NULL;
               
            } 

         echo "\n\t\t</tr>";

         $enlaces_creados = array();
         if ( $salida_fila_unica ) echo $salida_fila_unica;
         $salida_fila_unica = '';

         $fpi = ( $fpi == 'evenrow' ) ? 'oddrow' : 'evenrow';

         }

         echo "\n\t</tbody>";
         echo "</table>";

      }

   }

?>
