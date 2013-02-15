<?php

/**
 * @file Formato.php
 * @brief Generador de código html, csv, json, etc...
 */

/**
 * Clase Formato para mostrar contenido en diferentes formatos
 */

class Formato {

   /**
    * Prestar lista recursiva.
    *
    * Opciones:
    *
    * - item_visible:    Item que queremos que este visible por defecto.
    * - seleccionable:   Se presentan botones para seleccionar    
    * - checkqued:       Item seleccionado por defecto
    * - multiple:        Se permite multiple selección        
    * - plantilla:       Plantilla personalizada.
    */

   static function lista_recursiva($datos, $opciones = FALSE, $formato = 'html') {

      $op = array($item_visible      = FALSE
                , $seleccionable     = FALSE
                , $checkqued         = FALSE
                , $multiple          = FALSE
                , $plantilla         = FALSE
                );

      if ( $opciones ) $op = array_merge($op,$opciones);

      // Comprobar descartados
      // Añadimos a los descartados por configuración el que se desea ocultar

      $items_a_descartar = $this->descartar ;
      if ( $ocultar ) $items_a_descartar[] = $ocultar;

      if ( ($items_a_descartar) ) {

         $descartar = FALSE;
         foreach ( $items_a_descartar as $descartado ) {
            if ( strpos($path,$descartado) !== FALSE ) {
               registrar(__FILE__,__LINE__,'Descartado: '.$path. ' coincide con '.$descartado);
               $descartar = TRUE;
               }
            }
         foreach ( $this->filtro_secciones as $descartado ) {
            if ( strpos($path,$descartado) !== FALSE ) {
               registrar(__FILE__,__LINE__,'Descartado: '.$path. ' coincide con '.$descartado);
               $descartar = TRUE;
               }
            }

         if ( $descartar ) {
            return;
            }
         }

      if ( $ocultar ) $ocultar = comprobar_barra($ocultar,'eliminar');
      $path = comprobar_barra($path,'eliminar');
      $nombre_campo = 'seleccionado[]';

      if ( $multiple ) {
         $tipo_campo   = 'checkbox';
      } else {
         $tipo_campo   = 'radio';
         }

      // Si $checqued viene con barra se la quitamos
      if ( $checkqued ) {
         $checkqued = comprobar_barra($checkqued,'eliminar');
      }

      $ver='NO';

      $dir_por_defecto='File/'.Router::$ii;
      $d = dir($path);
      $HAY="NO";                                          //< Para saber si hay subdirectorios
      $subsecciones = array();
      $documentos = array();
      while($entry=$d->read()) {
         // descartamos directorios ocultos de linux
         if (is_dir($path."/".$entry) && $entry{0} != "." ) {
            $HAY="SI";
            $subsecciones[]=$path."/".$entry;
         } elseif ( $verDocumentos && $entry{0} != "." ) { // contenido html
            $documentos[$path][]=$entry;
            }

         }
      $d->close();

      // Si tenemos un path para seleccionar por defecto
      if ( $path_visible ) {
         $path1 = explode('/',$d->path);
         $path2 = explode('/',$path_visible);
       
         // Determinar si se oculta sección o se mantiene abierta por estar en el
         // camino del path seleccionado.
         if ( in_array($path1[count($path1)-1],$path2) )  {
            $ver='SI';
         }
         }

      echo "\n<ul>";

      // Boton de radio
      if (  ( $ver == 'SI' ) || $d->path == $dir_por_defecto) {

         if ( $d->path == $checkqued ) { // Si es el que queremos tener seleccionado
            echo " <input checked type='".$tipo_campo."' name='".$nombre_campo."' value='", htmlentities($d->path), "' />";
         } elseif ( $d->path == $ocultar ) { // No se debe mostrar
            echo "Descartado";
         } else {
            echo " <input type='".$tipo_campo."' name='".$nombre_campo."' value='", htmlentities($d->path), "' />";
            }

      } else {
         echo " <input type='".$tipo_campo."' name='".$nombre_campo."' value='", htmlentities($d->path), "' />";
         }

      // Contenido
      if ( $HAY == "SI" || $verDocumentos) {
         echo "<a href='#' class='interrogante' onclick=\"visualizar('",htmlentities($d->path),"'); return false;\" >";
         echo basename($d->path);
         echo "</a>";
      } else {
         echo basename($d->path);
         }
      if ( ( $ver == 'SI' )  || $d->path == $dir_por_defecto ) {
         echo "<div id='",$d->path,"' class='toggle'>";
      } else {
         echo "<div id='",$d->path,"' class='toggle' style='display: none' >";
         }

      // Si queremos ver documentos los presentamos
      if ( $verDocumentos ) {
         if ( ! empty($documentos[$d->path]) && count($documentos[$d->path]) > 0 ) {
            foreach( $documentos[$d->path] as $doc ) {
               echo '<li>';
               if ( GUtil::tipo_de_archivo($d->path.'/'.$doc) == 'text/html' ) {
                  echo '<span class="datos_fichero_html">';
               } else {
                  echo '<span class="datos_fichero">';
               }
               echo "<input type='".$tipo_campo."' name='".$nombre_campo."' ";
               echo 'value="'.htmlentities($d->path."/".$doc).'" />';
               // Segun tipo documento mostramos
               if ( esImagen($d->path.'/'.$doc) )  {
                  echo "<img align='center' width='50px' src='".htmlentities($d->path."/".$doc)."' />";
               }
               echo '<a title="'.literal('Visualizar').'" href="?edit=no&url='.htmlentities($d->path.'/'.$doc).'">';
               echo $doc;
               echo "</a>";
               // Solo ponemos link para editar si son paginas html
               if ( GUtil::tipo_de_archivo($d->path.'/'.$doc) == 'text/html' ) {
                  echo '<a title="'.literal('Editar').'" href="?e=editar_contenido&url='.htmlentities($d->path.'/'.$doc).'"> [#]</a>';
               }
               echo '<span class="detalles_fichero">';
               echo ' ['.presentarBytes(filesize($d->path.'/'.$doc)).',  '.presentarFecha(filemtime($d->path.'/'.$doc),2).']';
               echo '</span>';
               echo '</span>';
               echo '</li>';
               }
            }
         }

      foreach($subsecciones as $x) {
         $this->mostrarSecciones($x, $path_visible, $verDocumentos, $seleccionable, $filtro, $recursivo, $checkqued, $multiple, $ocultar);
         }

      echo "</div>";
      echo "\n</ul>";
      }

   }

?>
