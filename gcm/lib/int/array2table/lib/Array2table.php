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
 *
 * @category Gcm
 * @author Eduardo Magrané
 * @version 0.1
 *
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
      self::$base_imagenes = ''              ;
      self::$img_modificar = 'modificar.png' ;
      self::$img_borrar    = 'borrar.png'    ;
      self::$img_ver       = 'ver.png'       ;

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
    *         dir_imag      = 'Url del directorio donde se encuentran los iconos modificar, eliminar, etc...
    * 
    * @param $orden Nombre de la columna por la que se esta ordenando
    * @param $tipo_orden (asc/desc) 
    */

   function generar_tabla($res, $opciones=NULL, $orden = FALSE , $tipo_orden = FALSE) {
      
      $num_registros = count($res);
      $num_columnas  = count($res[0]);

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
      /** Url del directorio de los iconos */
      self::$base_imagenes = ( isset($opciones['dir_img']))    ? $opciones['dir_img']    : self::$base_imagenes ;

      /* Si no se indica el campo identificador cogemos id por defecto */

      $identificador = ( $identificador ) ? $identificador : 'id';

      $simbolo = ( strrpos($url,'?') === FALSE ) ? '?' : '&';        //< Evitar colocar dos veces el interrogante en la url

      ?>
     <table cellpadding="0" cellspacing="0" border="0" id="table" class="tinytable">
         <thead>
            <tr>
      <?php

      if ( $res ) {

         foreach ( $res[0] as $columna => $valor ) {

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

            $url_orden = construir_get( array ($this->sufijo.'orden' => $columna
               , $this->sufijo.'tipo_orden' => $tipo_orden2
               , $this->sufijo.'pagina' => 1)) ;

            echo "\n\t\t\t";
            echo "<th".$clase.">";
            echo "<a href='".htmlspecialchars($url_orden,ENT_QUOTES)."'>";
            echo "<h3>";
            echo $columna;
            echo "</h3>";
            echo "</a>";
            echo "</th>";

            }

         }

      if ( $modificar ) {
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

      if ( $ver ) {
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

      //echo "\n\t\t</tr>";
      echo "\n\t</thead>";
      echo "\n\t<tbody>";

      $fpi = 'evenrow';

      foreach ( $res as $fila ) {          // filas del body de la tabla

         $enlace = NULL;

         echo "\n\t\t<tr class='".$fpi."'>";

         foreach ( $fila as $key_columna => $columna ) {       // columnas

            // Si estamos en la columna que ordena añadimos clase especial

            $clase_columna =  ( $key_columna == $orden ) ? ' class="oddselected" ' : '' ;

            /* Si estamos en la columna id definimos enlaces hacia el elemento */

            if ( $identificador && $key_columna == $identificador && $url ) {

               /* Añadimos identificador a url */
               $enlace = $url.$columna;

               }

            /* Si tenemos columna con % Añadimos efecto visual para representarlo */

            if ( $key_columna == $identificador  && $ocultar_id ) { // id oculto
               echo ''; 

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

         $fpi = ( $fpi == 'evenrow' ) ? 'oddrow' : 'evenrow';

         }

         echo "\n\t</tbody>";
         echo "</table>";

      }

   }

?>
