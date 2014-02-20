<?php

/**
 * @file html.php
 * @brief Clase estatica para generar html
 * @ingroup formato
 */

/**
 * HTML
 * @ingroup formato
 */

class HTML {

   /**
    * Escapamos la cadena para poder ser insertada como un valor dentro de un
    * atributo
    *
    * @param $text Texto a escapar
    */

   static function esc_atr($text) {
      return htmlspecialchars($text, ENT_QUOTES);
      }

   /**
    * Devolver los atributos en una cadena
    *
    * @param $atributos Array con la lista de los atributos y sus valores.
    */

   static function get_atributos($atributos=FALSE) {

      $salida = FALSE;

      if ( ! $atributos ) return FALSE;

      foreach ( $atributos as $atributo => $valor ) {

         if ( $atributo == 'required' ) {
            $salida .= ' required';
         } else {
            $salida .= ' '.$atributo.'="'.self::esc_atr($valor).'"'; 
            }

         }
      return $salida;

      }

   /**
    * Campo de texto
    *
    * Si el tamaño máximo del texto permitido no supera los 150 caracteres
    * se presenta como un imput text si lo supera se presenta como textarea
    *
    * @param $name   Nombre del campo
    * @param $valor  Valor
    * @param $atr    Atributos para el input o textarea
    * @param $limite Si el atributo maxlength sobrepasa este limite presentamos
    *        un textarea en vez de un input text, puede definirse como 0 para
    *        generar un textarea sin limite
    */

   static function form_text($name, $valor=FALSE, $atr=FALSE, $limite=150) {

      $maxlength = ( isset($atr['maxlength']) ) ? $atr['maxlength'] : FALSE ;
      $size = ( isset($atr['size']) ) ? $atr['size'] : FALSE ;

      $textarea = ( $limite == 0 || ( $maxlength && $maxlength > $limite ) ) ? TRUE : FALSE ;

      $salida = '';

      if ( $textarea ) { // textarea
         $salida .= '<textarea ';
      } else {                                // input text
         $salida .= '<input type="text" ';
         }

      $salida .= ' name="'.$name.'"';
      $salida .= ' id="'.$name.'"';

      // Atributos

      $salida .= self::get_atributos($atr);

      if ( $textarea ) { // textarea

         $salida .= '>';

         if ( $valor ) $salida .=  $valor;

         $salida .= '</textarea>';

      } else {                                // input text

         if ( $valor ) $salida .=  ' value="'.self::esc_atr($valor).'" ';

         $salida .= '>';
      
         }

      return $salida;

      }

   /**
    * Enum
    */

   static function form_enum($name, $valor=FALSE, $atr=FALSE, $opciones) {

      $salida = '';

      foreach ( $opciones as $opcion ) {
      
         $salida .= '<p><input type="radio" ';

         $salida .= ' name="'.$name.'"';
         $salida .= ' value="'.$opcion.'"';

         // Atributos

         $salida .= self::get_atributos($atr);

         if ( $valor == $opcion ) $salida .=  ' checked ';

         $salida .= '/>'.literal($name.'_'.$opcion,1).'</p>';

         }

      return $salida;

      }


   /**
    * Booleano
    */

   static function form_bool($name, $valor=FALSE, $atr=FALSE) {

      $salida = '';

      $salida .= '<input type="checkbox" ';

      $salida .= ' name="'.$name.'"';
      $salida .= ' id="'.$name.'"';

      // Atributos

      $salida .= self::get_atributos($atr);

      if ( $valor ) $salida .=  ' checked ';

         $salida .= '/>';


      return $salida;

      }

   /**
    * Añadir un campo oculto
    */

   static function form_hidden($nombre, $valor=FALSE, $atributos=FALSE) {

      return '<input type="hidden" name="'.self::esc_atr($nombre).'" value="'.self::esc_atr($valor).'" '.self::get_atributos($atributos).' />';

      }

   /**
    * Campo password
    */

   static function form_pass($nombre, $valor=FALSE, $atributos=FALSE) {

      return '<input type="password" name="'.self::esc_atr($nombre).'" value="'.self::esc_atr($valor).'" '.self::get_atributos($atributos).' />';

      }

   /**
    * Devolvemos cadena con el select construido
    *
    * @param $nombre Nombre para el select
    * @param $opciones Array con las opciones cada una con un array identificador y valor
    * @param $valor_seleccionado Valor establecido
    * @param $primera_opcion Por si deseamos añadir un texto en el select como primera opción
    * @param $readonly Solo lectura
    */

   static function form_select($nombre, $opciones, $valor_seleccionado=FALSE, $primera_opcion=FALSE, $readonly = FALSE) {

      $disabled = ( $readonly ) ? 'disabled' : '' ;

      $salida = '
         <select name="'.$nombre.'" id="'.$nombre.'" '.$disabled.'>
         ';

      if ( $primera_opcion ) {
         $salida .= '
            <option>'.$primera_opcion.'</option>
            <option></option>
            ';
         }

      foreach ( $opciones as $res) {
         $count=0;
         foreach ( $res as $contenido ) {
            if ( $count == 0 ) $identificador = $contenido;
            if ( $count == 1 ) $nombre = $contenido;
            $count++;
            }

         $salida .= '
            <option value="'.$identificador.'" 
            ';

         if ( $identificador == $valor_seleccionado ) $salida .= ' selected ';

         $salida .= '>'.$nombre.'</option>';

         }

      $salida .= '</select>';

      return $salida;

      }

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
    *
    * @param $datos    Array con los datos
    * @param $opciones Array con las opciones
    */

   static function lista_recursiva($datos, $opciones = FALSE) {

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
