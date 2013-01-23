<?php

/**
* @file gcm_imagen.php
* @brief Funciones para el tratamiento de imagenes
*
* @author Eduardo Magrané
*/


/**
* Copiar imágen a una ubicación
*
* Esta función esta pensada para recoger la imágen del directorio
* temporal despues de ser subida desde un formulario y copiarla
* al directorio de destino indicado o moverla con un nombre diferente
*
* @todo Se podra indicar si queremos una miniatura que en caso de ser afirmativo
* la crearemos en el subdirectorio con el nombre indicado
*
* Se podra indicar si queremos una altura o anchura max que en el caso de tener
* alguno de estos valores definidos, transformaremos la imagen.
*
* Para las miniaturas tambien se puede especificar alto o ancho maximo
*
* @param $imagen Nombre del campo de formulario con la imagen
* @param $destino Carpeta en la que se desea copiar, "/" final incluida
* @param $imagenAlto Alto maximo de la imagen, por defecto ninguno.
* @param $imagenAncho Ancho maximo de la imagen, por defecto ninguno.
* @param $miniatura Si es diferente a no el nombre de la subcarpeta sera el valor de la variable.
* @param $miniaturaAlto Alto maximo de la miniatura, por defecto ninguno.
* @param $miniaturaAncho Ancho maximo de la miniatura, por defecto ninguno.
*/

function gcm_imagen_copiar($imagen, $destino, $imagenAlto='0', $imagenAncho='0', $miniatura='no', $miniaturaAlto='0', $miniaturaAncho='0') {

   global $_FILES;

   $imagenFile      = $imagen['tmp_name'];
   $imagenName      = $imagen['name'];
   $imagenSize      = $imagen['size'];
   $imagenType      = $imagen['type'];
   $imagenError     = $imagen['error'];


   if ( is_dir($destino) ) { // Si es un directorio
      $dirFinal = $destino."/";
      $nombreFinal = $dirFinal.$imagenName ;
   } else { // Si no lo es se guarda con el nombre pasadp
      $dirFinal = $destino;
      $nombreFinal = $dirFinal;
   }

   if ( $imagenError == 4 ) {

      $imagenFile      = null;
      $imagenName      = null;
      $imagenSize      = null;
      $imagenType      = null;

      $mens = "No se subio ningún fichero";
      registrar(__FILE__,__LINE__,$mens,'ERROR');

      return NULL ;

   }

   // *** Control de errores

   if ($imagenError>0){

      switch ($imagenError){
         case 1:
            $mens = "Tamaño excesivo de archivo";
            registrar(__FILE__,__LINE__,$mens,'ERROR');
            echo '<script>alert("Tamaño del archivo excesivo\nMax permitido: '.ini_get('upload_max_filesize').'");</script>';
            return NULL ;
            break;
         case 2:
            $mens = "Tamaño excesivo de archivo";
            registrar(__FILE__,__LINE__,$mens,'ERROR');
            echo '<script>alert("Tamaño del archivo excesivo\nMax permitido: '.ini_get('upload_max_filesize').'");</script>';
            return NULL ;
            break;
         default:
            $mens = "Error al subir la imágen";
            registrar(__FILE__,__LINE__,$mens,'ERROR');
            return NULL ;
            break;
       }
       $mens = "Error al subir la imágen";
       registrar(__FILE__,__LINE__,$mens,'ERROR');
       return NULL ;
   }

   // *** Control de seguridad
   if (!is_uploaded_file($imagenFile)){
     $mens = "Acceso no permitido, posible violación de seguridad";
     registrar(__FILE__,__LINE__,$mens,'ERROR');
     return NULL ;
   }

   // Si es un archivo zip lo descomprimimos
   if ( $imagenType == 'application/zip' ) {

      $zip = new ZipArchive;

      // listar archivos incluidos para procesarlos despues
      if ( $zip->open($imagenFile) === TRUE ) {
         $num_imagenes = $zip->numFiles;
      } else {
         $mens = 'No se pudo abrir el archivo zip: '.$imagenName;
         registrar(__FILE__,__LINE__,$mens,'ERROR');
         echo '<script>alert(\'No se pudo abrir el archivo zip: '.$imagenName.'\');</script>';
         return null ;
      }

      if ( $num_imagenes < 1 ) {
         $mens = $imagenName.' vacio';
         registrar(__FILE__,__LINE__,$mens,'ERROR');
         echo '<script>alert(\'Vacio '.$imagenName.'\');</script>';
         return null ;
      }


      if ( $zip->extractTo($dirFinal) ) {
         echo '<script>parent.resultadoUpload (\'0\', \''.$imagenName.'\');</script>';
      } else {
         $mens = 'Archivo '.$imagenName.' no se pudo descomprimir';
         registrar(__FILE__,__LINE__,$mens,'ERROR');
         echo '<script>parent.resultadoUpload (\'4\', \'No se pudo descomprimir '.$imagenName.'\');</script>';
         return NULL ;
      }

      echo "gcm_imagen_copiar::Numero de imagenes: <b>$num_imagenes</b>";
      if ( $num_imagenes > 0 ) {
         for ($i=0 ; $i!=$num_imagenes; $i++) {
            $filename = $zip->getNameIndex($i);
            $fileinfo = pathinfo($filename);
            $imagen = $dirFinal.$filename ;
            $salida.=$imagen.'\n';

            // Generamos imagen
            generarImagen($imagen, $dirFinal, $imagenAlto, $imagenAncho);

            // Especificar las acciones a realizar.
            $hacerMiniatura   = NULL;

            if ( $miniatura != "" && $miniatura != 'no' ) {
              $hacerMiniatura = TRUE;
              generarImagen($imagen, $miniatura, $miniaturaAlto, $miniaturaAncho);
            }

         }
      }

      echo '<script>parent.resultadoUpload (\'0\', \''.$imagenName.' '.$num_imagenes.' imagenes\\n'.$salida.'\');</script>';

      return TRUE ;

   } else { // Acaba si es un archivo zip

      // Copiar imagen a destino antes de tratar

      if (!copy($imagenFile ,$nombreFinal)) {
        trigger_error('Error al copiar imagen ['.$imagenFile.'] a ['.$nombreFinal.']', E_USER_ERROR);
        return NULL ;
         }

      $imagen = $nombreFinal ;

      // Generamos imagen en el caso de tener Alto o ancho, sino no hace falta
      if ( $imagenAlto != 0 && $imagenAncho != 0 ) {
         generarImagen($imagen, $dirFinal, $imagenAlto, $imagenAncho);
         }

      // Especificar las acciones a realizar.
      $hacerMiniatura   = NULL;

      if ( $miniatura != "" && $miniatura != 'no' ) {
        $hacerMiniatura = TRUE;
        generarImagen($imagen, $miniatura, $miniaturaAlto, $miniaturaAncho);
         }

      return TRUE ;

      } // Acaba sino es un archivo zip

   }

/**
* Generar imagen
*
* Generamos una imagen a partir de una origen con las dimensiones y ubicación pasados
* como argumento.
*
* @author Eduardo Magrané
* @version 1.0
*
* @param $imagen imagen origen
* @param $destino Carpeta de destino
* @param $alto Alto maximo de la imagen
* @param $ancho Ancho maximo de la imagen
* @param $minuatura Por defecto se crea una miniatura en directorio nuevo.
*
* @return TRUE/NULL
*
*/

function generarImagen($imagen, $destino, $alto, $ancho, $miniatura='si') {

   $dirFinal = comprobar_barra($destino);

   // Cogemos los datos de la imagen
   $vDatosImg = getimagesize($imagen);
   $imagenName = basename($imagen);

   if (!$vDatosImg) {
      $mens = literal('Error con las datos de la imagen',3);
      registrar(__FILE__,__LINE__,$mens,'ERROR');
      return NULL ;
      }

   $imgAncho      = $vDatosImg[0];
   $imgAlto       = $vDatosImg[1];

   // Recogemos el Tipo Mime
   if ( isset($vDatosImg['mime']) ) {
      $sTipo = $vDatosImg['mime'];
   } elseif ( isset($vDatosImg[2]) ) {
      $sTipo = image_type_to_mime_type($vDatosImg[2]);
   } elseif ( isset($iTipo) ) {
      $sTipo = image_type_to_mime_type($iTipo);
   } else {
      $mens = literal('Error con datos mime de la imagen',3);
      registrar(__FILE__,__LINE__,$mens,'ERROR');
      return NULL ;
   }

   // Especificar las acciones a realizar.
   $cambiarMiniatura = NULL;

   if ( $ancho < $imgAncho ) {
     $cambiarMiniatura=TRUE;
   } elseif ( $alto < $imgAlto) {
     $cambiarMiniatura=TRUE;
   }

   // Creamos directorio de miniaturas con el nombre del directorio que
   // nos han pasado si se pide miniatura

   if ( $miniatura == 'si' ) {
      if ( ! is_dir($dirFinal) ) {
        if ( ! mkdir(comprobar_barra($dirFinal,'eliminar')) ) {
           $mens = literal('Error al crear directorio destino',3).'['.$dirFinal.']';
           registrar(__FILE__,__LINE__,$mens,'ERROR');
           return NULL ;
           }
         }
      }

   // Si hay que cambiar el tamaño se buscan los datos
   if ( $cambiarMiniatura ) {

     switch($sTipo){
         case "image/gif":
             $fuente = imagecreatefromgif($imagen) ;
             break;
         case "image/jpeg":
             $fuente = imagecreatefromjpeg($imagen) ;
             break;
         case "image/png":
             $fuente = imagecreatefrompng($imagen) ;
             break;
         default:
             break;
     }

     if ( !$fuente ) {
        registrar(__FILE__,__LINE__,literal('Error con datos mime',3).'['.$sTipo.']','ERROR');
        return NULL ;
     }

     /// Comprobar que la imágen no pase ni alto maximo ni el ancho definido
     if ( $ancho > 0 )  {
        $anchoFinal = $ancho;
        $altoFinal = $imgAlto * $ancho / $imgAncho ;
        // comprobar altoFinal no sea mayor al especificado
        if ( $altoFinal > $alto ) {
           $altoFinal = $alto;
           $anchoFinal = $imgAncho * $alto / $imgAlto ;
        }
     } elseif ( $alto > 0 ) {
        $altoFinal = $alto;
        $anchoFinal = $imgAncho * $alto / $imgAlto ;
        if ( $anchoFinal > $ancho ) {
           $anchoFinal = $ancho ;
           $altoFinal = $imgAlto * $ancho / $imgAncho ;
        }
     }

     $imagen= ImageCreateTrueColor($anchoFinal, $altoFinal)
       or $imagen=ImageCreate($anchoFinal, $altoFinal);

     ImageCopyResized($imagen,$fuente,0,0,0,0,$anchoFinal,$altoFinal,$imgAncho,$imgAlto);
     // Un tercer parametro podemos definir la calidad
     // que es de 0 a 100 siendo 100 la max calidad.
     // Por defecto es 75.
     // imagejpeg($imagen,$userImagen);

     // Se pinta la imagen según el tipo
      switch($sTipo){
          case "image/gif":
              imagegif($imagen,$dirFinal.$imagenName);
              break;
          case "image/jpeg":
              imagejpeg($imagen,$dirFinal.$imagenName, 100);
              break;
          case "image/png":
              imagepng($imagen,$dirFinal.$imagenName);
              break;
          default:
              registrar(__FILE__,__LINE__,literal('Tipo de imagen no soportada',3).' ['.$sTipo.']','ERROR');
              return NULL ;
              break;
     }

   } else { // si no hay que cambiar el tamaño

      if (!copy($imagen ,comprobar_barra($dirFinal).$imagenName)) {
         registrar(__FILE__,__LINE__,literal('Fallo al generar imagen',3),'ERROR');
         trigger_error("Error al copiar imagen", E_USER_ERROR);

         return NULL ;
         }

      } // ACABA if $cambiarMiniatura

   } // Acaba generarMiniatura

/** Listamos imagenes
 *
 * Crear una lista de las imagenes de un directorio
 *
 * @param path Directorio donde buscar imagenes
 * @param tipo tipop de salida
 *
 */

function gcm_listaImagenes($path, $tipo=1) {

   global $gcm;

   $altoMaxMiniatura = $gcm->config('imagenes','altoMaxMiniatura');
   $anchoMaxMiniatura = $gcm->config('imagenes','anchoMaxMiniatura');

   $extensiones = array("jpg", "jpeg", "JPG", "JPEG", "GIF", "gif", "png", "PNG", "tiff", "TIFF");

   $base_imagenes='';

   if ( ! is_dir($path) ) {
      registrar(__FILE__,__LINE__,$path.' '.literal('No es un directorio',3),'ERROR');
      return NULL;
   }

   /// Secciones posibles para editor de imagenes en ajax
   $otrasSecciones = array() ;
   // La primera sección de imagenes que listamos para ajax es la actual
   $uri = explode("/",$path);
   $ultimo = ( $uri[count($uri)-1] ) ? $uri[count($uri)-1] : $uri[count($uri)-2];
   unset($uri[count($uri)-1]);
   $arriba = implode("/",$uri);

   // Listado de otras secciones, que podamos presentar si queremos ver imagenes de otra seccion
   //if ( "" != $ultimo && $ultimo !== "Image" ) {
   if ( $ultimo !== $gcm->config('idiomas','Idioma por defecto') ) {
      $otrasSecciones[] = array ("actual", literal($ultimo));
      $otrasSecciones[]= array (literal($arriba),"..");
   } else {
      $otrasSecciones[] = array ("actual", literal('inicio'));
   //   $otrasSecciones[]= array ("Image/",literal("inicio"));
   }

   // Añadimos barra en caso de no haberla
   $path = comprobar_barra($path);

   $directorio = dir($path);

    while ($archivo = $directorio->read()) {

       $file = $path.$archivo ;
       // Si es un directorio lo añadimos a otrasSecciones
       if ( $archivo != ".svn" && is_dir($file) && $archivo != ".miniaturas" && $file != $path  && $archivo != "." && $archivo != ".." ) {
          $otrasSecciones[]= array ($file,literal($archivo));
       }
       $fileInfo = pathinfo($file);
       $ext = ( ! empty($fileInfo['extension']) ) ? $fileInfo['extension'] : NULL ;

       if ( in_array($ext,$extensiones) ) {
          // Comprobar que existe la miniatura
          $min=$path.'.miniaturas/'.$archivo;
          if ( ! is_file($min) ) {
             if (! generarImagen($file, $path.'.miniaturas', $altoMaxMiniatura, $anchoMaxMiniatura) ) {
                registrar(__FILE__,__LINE__,literal('Error al generar imagen',3).' ['.$file.'] en ['.$path.'.miniaturas]','ERROR');
             }
          }
         /// @todo Añadir width, heigth, size, exif al array
         /// @todo Utilizar libreria nueva para buscar datos de imagenes
         //$exif = @exif_read_data($path.'/'.$archivo, 0, true);
         $exif = '';
         $i_size = getfilesize(filesize($path.'/'.$archivo));
         $i_area = getimagesize($path.'/'.$archivo);
         $i_width = $i_area[0];
         $i_height = $i_area[1];
         $lista[]=array($archivo, $base_imagenes.$path.$archivo, $base_imagenes.$path.'.miniaturas/'.$archivo, $i_width, $i_height, $i_size, $exif) ;
       }

    }

    $directorio->close();

    if ( $tipo == 1 ) {                    // escribir archivo para tiny

       $num = count($lista);
       if ( $num > 0 ) {

          $archivo = $path."/.listaImg.js";
          registrar(__FILE__,__LINE__,'Archivo de lista de img, '.$archivo);
         // escribir archivo de javascript para tener una lista de imagenes
         if (!$file = fopen($archivo, "w")) {
            registrar(__FILE__,__LINE__,'No se puede abrir el archivo para escribir','ERROR');
            return FALSE;
         }
         fputs($file, "var tinyMCEImageList = new Array (\n");

          for ($i=0 ; $i!=$num-1; $i++) {
             fputs($file, "[\"mini/".$lista[$i][0]."\",\"".$lista[$i][2]."\"],\n");
             fputs($file, "[\"".$lista[$i][0]."\",\"".$lista[$i][1]."\"],\n");
          }
          fputs($file, "[\"mini/".$lista[$i][0]."\",\"".$lista[$i][2]."\"],\n");
          fputs($file, "[\"".$lista[$i][0]."\",\"".$lista[$i][1]."\"]");

         fputs($file, ");\n");
         fclose($file);
         return TRUE;

       }

    } elseif ( $tipo == 3 ) {              // Salida para modulo de imagenes ajax
       $devolver = array ();
       $devolver['imgs'] = ( isset($lista) ) ? $lista : NULL;
       $devolver['sec']= $otrasSecciones;
       print json_encode($devolver);

    } elseif ( $tipo == 4 ) {              // Incluimos listado en una variable llamada lista, para javascript
       echo "\nvar lista = ".json_encode($lista);

    } else {                               // Salida para ajax
       print json_encode($lista);
    }

   }

/**
* Borrar imagen
*
* @version 1.0
*
* @param url url de la imágen
*
* @return true/false
*
*/

function gcm_borrarImagen($url) {

   if ( ! is_file($url) ) {
      // La imagén no se encuentra
      // Buscar imagen con la dirección relativa, dejamos solo de File/es para alante
      $url = ereg_replace('.*'.$dd,$dd,$url);
      if ( ! is_file($url) ) {
         $error=literal($dd."La imágen no existe").'::'.$url;
         registrar(__FILE__,__LINE__,$error,'ERROR');
         return NULL ;
      }
   }

   if ( ! unlink($url) ) {
      registrar(__FILE__,__LINE__,literal('No se pudo borrar el fichero',3).' ['.$url.']','ERROR');
   } else {
      // Si hay miniatura la borramos tambien.
      $min = dirname($url).'/.miniaturas/'.basename($url);
      if ( is_file($min) ) {
         if ( ! unlink($min) ) {
            registrar(__FILE__,__LINE__,literal('No se borrar el fichero',3).' ['.$min.']','ERROR');
            return null ;
         } else {
            // actualizamos lista del directorio
            gcm_listaImagenes(dirname($url));
            return TRUE;
         }
      } else {
         // actualizamos lista del directorio
         gcm_listaImagenes(dirname($url));
         return TRUE;
      }
   }

   }

/**
*
* ver imágenes como en una galería
*
* @todo Esta función ha sido sustituida por javascript, pendiente de borrado
*
* @author Eduardo Magrané
* @version 1.0
*
* @param $path Sección que deseamos ver.
*
* @return true/null
*
*/

function gcm_verImagenes($path = "Image") {

   global $gcm;

   $extensiones = array("jpg", "jpeg", "JPG", "JPEG", "GIF", "gif", "png", "PNG", "tiff", "TIFF");

   if ( ! is_dir($path) ) {
      registrar(__FILE__,__LINE__,$path.', '.literal('No es un directorio',3),'ERROR');
      return NULL;
   }
   $directorio=dir($path);

    while ($archivo = $directorio->read()) {

       $file = $path."/".$archivo ;
       $fileInfo = pathinfo($file);
       $ext = $fileInfo['extension'];

       if ( in_array($ext,$extensiones) ) {
          // Comprobar que existe la miniatura
          $min=$path.'/.miniaturas/'.$archivo;
          /// @todo comparar tambien que son de la misma fecha
          if ( ! is_file($min) ) {
             generarImagen($file, $path.'/.miniaturas', $altoMaxMiniatura, $anchoMaxMiniatura);
          }
          // Información sobre la imágen
         $exif = exif_read_data($path.'/'.$archivo, 0, true);
         $lista[]=array($archivo, $path."/".$archivo, $min, $exif) ;
       }

    }

    $directorio->close();

    $num = count($lista);
    echo "Numero de imagenes: <b>$num</b>";
    if ( $num > 0 ) {

       echo "\n<div id='thumbnails'>";
       for ($i=0 ; $i!=$num; $i++) {
          $i_size = getfilesize(filesize($lista[$i][1]));
          $i_area = getimagesize($lista[$i][1]);
          $i_width = $i_area[0];
          $i_height = $i_area[1];
          echo "\n<p class='thumb' ><tt>";
          echo "<a rel='thumbnail' onclick=\"javascript:open('",$lista[$i][1],"','",$lista[$i][0],"','toolbar=no,scrollbars=no,width=",$i_width + 20 ,",height=",$i_height + 20,"')\" >";
          echo "<img id='thumb_",$i,"' src='",$lista[$i][2],"' ";
          echo "alt='Imagen' />  </a>";
          echo "<br /><span class='idim'>[",$i_width,"x",$i_height,"] ",$i_size;
          if ( count($lista[$i][3]) > 1 ) {
             echo " [<a onclick='visualizar(\"exif_",$i,"\");visualizar(\"thumb_",$i,"\")' >exif</a>]";
             echo "</span>";
             echo "<br/>  <span id='exif_",$i,"' class='isize' style='display: none'><p>";
             foreach ($lista[$i][3] as $key => $section) {
                 foreach ($section as $name => $val) {
                     echo "<b>$key.$name:</b> $val<br />\n";
                 }
             }
             echo "</p></span></tt></p>";
          } else {
             echo "</span></tt></p>";
          }
       }
       echo "\n</div> <!-- Acaba thumbnails -->";
      return TRUE;

    } else {
       return TRUE;
    }

   }

/**
*
* Devuelve el peso de la imágen
*
* @author www.teayudo.cl
*
* @param $size Peso de la imágen en bytes
*
* @return peso
*
*/

function getfilesize($size) { // gets file size for each image

   $units = array(' B', ' KB', ' MB', ' GB', ' TB');
   for ($i = 0; $size > 1024; $i++) { $size /= 1024; }
   return round($size, 2).$units[$i];
   }

?>
