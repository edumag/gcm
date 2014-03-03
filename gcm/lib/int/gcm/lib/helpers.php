<?php

/**
 * @file      helpers.php
 * @brief     Funciones para facilitar la programación de módulos para gcm
 * @ingroup gcm_lib
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 * Copyright  Copyright (c) 2010, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

if( ! function_exists("literal") ) {

/** literal()
*
* Devolver literal o en su defecto retornamos la cadena enviada, por defecto solo busca literales del proyecto
* pero con nivel 2 buscal primero en proyecto y si no en literales de GCM, con nivel 3 solo en GCM.
*
* Si especificamos el tipo de literal que queremos, de proyecto o de gcm, y no es
* encontrado se añadirá al archivo correspondiente, aunque fuera con valor vacío,
* esto nos permitirá tener reflejado los literales que se están pidiendo y que no
* tenemos añadidos.
*
* Permitimos que esta función se aplique de otra manera en diferentes proyectos, por ello
* comprobamos antes de crearla su existencia.
*
* @author Eduardo Magrané
* @version 1.0
*
* @param literal cadena asociada a un literal
* @param nivel 1:literal de proyecto, 2:literal de proyecto o gcm, 3:solo de gcm
* @param Valor nuevo para elemento
*
* @return literal
*
*/

function literal($literal, $nivel=2, $valor=NULL) {

   global $LG, $GCM_LG, $gcm;

   if ( $gcm ) {
      $proyecto = $gcm->config('admin','Proyecto');
   } else {
      $Proyecto = 'SinProyecto';
   }

   $literal = html_entity_decode($literal,ENT_NOQUOTES,'UTF-8');

   if ( empty($literal) || $literal == ' ' || $literal == '' ) {
      return FALSE;
   }

   switch ($nivel) {

      case 1:

         if ( !$valor && isset($LG[$literal]) && $LG[$literal] != "" ) {
            return $LG[$literal] ;
            }

         if ( $valor ) {

            $mens = 'Nuevo literal ['.$literal.'] de proyecto con valor ['.$valor.']';

         } elseif ( is_array($LG) && ! @array_key_exists  ( $literal , $LG  ) ) {

            /* Si se pide un literal que no tenemos lo añadimos al archivo vacío
             * para tener constancia de ello.
             */

            $mens = 'Nuevo literal de proyecto ['.$literal.']';
            $valor = '';

         } else {
            return $literal;
            }

         $idioma_actual = $_SESSION[$proyecto.'-idioma'];
         $file=$gcm->config('idiomas','Directorio idiomas')."LG_".$idioma_actual.".php";

         require_once(GCM_DIR."lib/int/GcmConfig/lib/GcmConfigFactory.php");

         $arr = GcmConfigFactory::GetGcmConfig($file);
         $arr->set($literal,$valor);
         $arr->guardar_variables();

         registrar(__FILE__,__LINE__,$mens);

         $LG[$literal] = '';

         return $literal;

         break;

      case 2:

         if ( isset($LG[$literal]) && $LG[$literal] != "" ) {
            return $LG[$literal] ;
         } elseif ( isset($GMC_LG[$literal]) && $GCM_LG[$literal] != "" )  {
            return $GMC_LG[$literal];
         } else {
            // No se encontro literal ni de proyecto ni de gcm
            // Añadimos literal al proyecto para poder ser añadido
            // En caso de no estar ya en el array.
            return $literal;
            }
         break;

      case 3:

         if ( !$valor && isset($GCM_LG[$literal]) && !empty($GCM_LG[$literal])  && $GCM_LG[$literal] !== '' ) {
            return $GCM_LG[$literal] ;
            }

         if ( $valor ) {

            // @todo Guardar lista de literales faltantes para la columna
            $mens = 'Nuevo literal ['.$literal.'] de Gcm con valor ['.$valor.']';

         } elseif ( ! array_key_exists  ( $literal  , $GCM_LG  ) ) {

            $mens = 'Nuevo literal de Gcm ['.$literal.']';
            $valor = '';

         } else {
            return $literal;
            }

         $idioma_actual = $_SESSION[$proyecto.'-idioma'];
         $file=GCM_DIR."DATOS/idiomas/GCM_LG_".$idioma_actual.".php";

         require_once(GCM_DIR."lib/int/GcmConfig/lib/GcmConfigFactory.php");

         $arr = GcmConfigFactory::GetGcmConfig($file);
         $arr->set($literal,$valor);
         $arr->guardar_variables();

         registrar(__FILE__,__LINE__,$mens);

         /* Añadimos a $GCM_LG para que haya constancia inmediata */

         $GCM_LG[$literal]='';

         return $literal;

         break;
      }
   return FALSE;
   }

}

if ( ! function_exists("registrar") ) {

/** registrar 
 *
 * Registramos mensajes de la aplicación
 *
 * @param $fichero Fichero que genera el mensaje
 * @param $linea Donde se genera el mensaje
 * @param $mensaje Mensaje
 * @param $tipo Tipo de mensaje (ADMIN,ERROR,AVISO,DEBUG)
 *
 */

function registrar($fichero,$linea,$mensaje,$tipo='DEBUG',$descripcion=FALSE) {

   global $gcm;

   /* Si existe una instancia de gcm la utilizamos sino creamos una */

   if ( !isset($gcm) ) {
      echo '<div class="error">';
      echo 'No hay instancia de gcm mostramos mensaje directamente';
      echo '<br />Mensaje tipo: '.$tipo;
      echo '<br />'.$fichero,':'.$linea;
      echo '<pre>';
      echo $mensaje;
      echo '</pre>';
      echo '</div>';
      // exit();
      // require_once(GCM_DIR.'lib/int/registro/RegistroFactory.php');
      // $registro = RegistroFactory::getRegistro();
      // $registro->registra($fichero,$linea,$mensaje,$tipo);
   } else {

      $usuario = ( isset($_SESSION[$gcm->sufijo.'usuario']) && $_SESSION[$gcm->sufijo.'usuario'] ) 
         ? $_SESSION[$gcm->sufijo.'usuario'] 
         : 'Anonimo' ;

      $descripcion_extra = "Usuario: $usuario IP: ".mostrar_ip();

      if ( isset($_SERVER['REQUEST_URI']) ) $descripcion_extra .= "\n\nREQUEST_URI: ".$_SERVER['REQUEST_URI'];

      $descripcion_extra .= $descripcion;

      $gcm->registra($fichero,$linea,$mensaje,$tipo, $descripcion_extra);
      }

   }

}

/** comprobar_barra()
 *
 * Comprobamos que la url de un directoria viene con la barra,
 * en caso contrario la añadimos. O si se pide eliminar al reves 
 * la quitamos
 *
 * @param url URL de la sección o directorio
 * @param accion Puede ser anyadir o eliminar barra por defecto se añade
 *
 * @return TRUE/FALSE
 *
 * @author Eduardo Magrané
 * @version 1.0
 *
 */

function comprobar_barra($url, $accion='anyadir') {

   $url = str_replace('//','/',$url);

   if ($url) {

      if ( $accion == 'eliminar' )  {
         if ( $url{strlen($url)-1} == '/' ) {
            return substr($url,0, -1);
         } else {
            return $url;
            }
      } else {
         if ( $url{strlen($url)-1} == '/' ) {
            return $url;
         } else {
            return $url.'/';
            }
         }
      }

   }

/** esImagen
 *
 * Comprobar si es una imagen
 *
 * @author Eduardo Magrané
 * @version 1.0
 *
 * @param $archivo Direccion del archivo
 *
 * @return TRUE/FALSE
 *
 */

function esImagen($archivo) {

   $extensiones = array("jpg", "jpeg", "JPG", "JPEG", "GIF", "gif", "png", "PNG", "tiff", "TIFF");

   // Buscar la extensión del archivo
    $fileInfo = pathinfo($archivo);
    $ext = ( isset($fileInfo['extension']) ) ? $fileInfo['extension'] : NULL ;

   // Mirar si esta dentro del array de extensiones
    if ( in_array($ext,$extensiones) ) {
       return TRUE;
    } else {
       return FALSE;
      }

   }

if ( ! function_exists('depurar') ) {

/** Formatemaos salida de array */

function depurar($var, $titulo=NULL, $nivel=0) {

   if ( $titulo ) {
      $titulo = "[".$titulo."]\n";
   } else {
      $titulo = NULL;
      }

   $string = '';
   $tabulador = '';

   if ( $nivel > 0 ) $tabulador = str_repeat("\t",$nivel);

   if ($titulo) $string .= $tabulador.$titulo;

   $nivel++;

   if (is_array($var)) {

      foreach($var as $key => $value) {

         if (is_array($value)) {
             $string .= "\n".$tabulador.$key.': ';
             $string .= depurar($value, false, $nivel);
         } elseif(gettype($value) == 'object') {
             $string .= "\n".$tabulador.$key.': ';
             $string .= "Object of class " . get_class($value);
         } else {
             $string .= "\n".$tabulador.$key.': ';
             $string .= "$value" ;
            }

         }

   } elseif(gettype($var) == 'object') {
      $string .= "Object of class " . get_class($var);
   } else {
      $string .= "$var" ;
      }

   return $string;
   }

}

/**
 * Recogemos los argumentos entregados a una función
 * y devolvemos un array con par valor.
 *
 * @param $parametros cadena con los argumentos en formato tipo url 'var1=valor1&var2=valor2'
 * @return array
 */

function recoger_parametros($parametros){

   $retorno = array();

   if ( isset( $parametros ) ) {

      if ( !is_array($parametros) ) {

         if ( substr_count($parametros,'&') > 0 ) {
            $datos = explode('&',$parametros);
         } else {
            $datos = array($parametros);
            }

      } else {
         $datos = $parametros;
         }

      foreach ($datos as $parametro) {
         if ( substr_count($parametro,'=') > 0 ) {
            list($var,$val) = explode('=',$parametro);
            $retorno[$var] = $val;
            }
         }

      }
	return $retorno;
   }

/** presentarBytes
 *
 * Presentamos bytes segun tamaño
 *
 * @todo Si el tamaño del archivo es de más de 1M presentar por Megas no por Kb
 *
 * @author Eduardo Magrané
 * @version 1.0
 *
 * @param bytes
 *
 * @return cadena formateada
 *
 */

function presentarBytes($bytes) {

   $Kb = sprintf("%.1f", $bytes / 1000);

   return $Kb.'Kb';

   }

/** presentarFecha
 *
 * Presentamos fechas formateada y con idioma en uso
 *
 * @author Eduardo Magrané
 * @version 1.0
 *
 * @param $time tiempo unix
 * @param $formato_salida Formato de salida 
 *                        1- 08 de May, 2008
 *                        2- 08 de May, 2008 23.34
 *                        3- 08 May
 * @param $formato_entrada Formatos de entrada (por defecto unix):
 *                         - unix
 *                         - mysql   datatime
 *                         - sqlite  se espera unix
 *                         - deducir Intentaremos deducir cual es
 *
 * @return fecha formateada
 *
 */

function presentarFecha($time, $formato_salida=1, $formato_entrada='unix') {

   if ( $formato_entrada == 'sqlite' ) $formato_entrada = 'unix';

   if ( $formato_entrada == 'deducir' ) {

      if ( is_int($time) ) {
         $formato_entrada = 'unix';
      } else {
         registrar(__FILE__,__LINE__, "No se pudo dedicir el formato de la fecha [".$time."]",'ADMIN');
         }
      }

   if ( $formato_entrada != 'unix' ) {

      switch($formato_entrada) {

      case 'mysql':
         $time = strtotime( date($time) );
         break;
      default:
         $time = strtotime( date($time) );
         break;
         }
      }

   $mes = date('F', $time);
   $dia = date('d', $time);
   $anyo = date('Y', $time);
   $hora = date('H', $time);
   $minutos = date('i', $time);

   $mes = literal($mes);

   switch ($formato_salida) {

       case 2:
          return $dia.' de '.$mes.', '.$anyo. ' '.$hora.':'.$minutos;
          break;

       case 3:
          return $dia.' de '.$mes;
          break;

       default:
         return $dia.' de '.$mes.', '.$anyo;
         break;

      }

   }

/**
*
* Añadir una variable _GET a la url actual
*
* @deprecated Por constriutGet()
*
* @param $var Variable que se quiere añadir o modificar
* @param $valor Valor de variable que se quiere añadir o modificar
*
* @return url
*
*/

function modificarGet($var, $valor) {

   $salida = FALSE;

   if ( $valor ) $salida .= $var."=".$valor;

   foreach($_GET as $key => $val ) {
      if ( $key !== $var ) {
         // Descartamos url, ya viene con la dirección
         if ( $key != "url" ) {
            $salida .= "&".$key."=".$val;
         }
      }
   }
   
   return ( $salida ) ? '?'.$salida : FALSE ;

   }

/**
 * Generamos url segun las variables de GET que ya tenemos
 * añadiendo las que pasamos.
 *
 * @param $variables Array con las variables y sus valores a añadir 
 *         formato: array ( 'orden' => id,  'tipo_orden' => desc)
 */

function construir_get ($variables=FALSE) {

   if ( isset($_GET) && ( $variables ) && is_array($variables) ) {
      $resultado = array_merge( $_GET, $variables );
   } elseif ( isset($_GET) && ( ( ! $variables ) || ( ! is_array($variables) ) ) ) {
      $resultado = $_GET;
   } elseif ( ! isset($_GET) && ( $variables && is_array($variables) ) ) {
      $resultado = $variables;
   } else {
      return FALSE;
      }

   $simbolo = '?';
   $salida = '';
 
   foreach ( $resultado as $variable => $valor) {

      $salida .= $simbolo.$variable."=".$valor;
      $simbolo = '&';
      }

   return $salida;

   }

if( ! function_exists("permiso") ) {

/**
 * Determinar si tiene un rol con los permisos especificados para la acción.
 *
 * Comprobamos si la función ya existe, de esta manera permitimos con facilidad
 * implementar otro sistema de verificación de permisos diferente.
 *
 * @param $accion     Acción a realizar
 * @param $modulo     Módulo que realiza la acción
 * @param $salir En caso de no tener permisos enrutamos
 * @param $mensaje Presentar mensaje en caso de no tener permisos T/F
 *
 * @return TRUE/FALSE
 *
 * @ingroup permisos
 */

function permiso($accion='administrar', $modulo = 'admin', $salir=FALSE, $mensaje=FALSE) {

   global $gcm;

   if ( $gcm->au->permiso($accion, $modulo) ) return TRUE;

   if ( $salir  ) {
      registrar(__FILE__,__LINE__,'Se necesitan permisos para esta acción','ERROR');
      registrar(__FILE__,__LINE__,"Recargamos página por falta de permisos");
      header('location:'.Router::$base.Router::$s.Router::$c);
      exit();
   } elseif ( $mensaje ) {
      registrar(__FILE__,__LINE__,'Se necesitan permisos para esta acción','ERROR');
      }

   return FALSE;

   }

}

/**
* Limpiamos el texto preparandolo para ser guardado o presentado
*
* @author Eduardo Magrané
* @version 1.0
*
* @param contenido texto a limpiar
*
* @return texto limpio
*
*/

function limpiarContenido($contenido){
	$contenido= preg_replace( '/\\\"/', '\"', $contenido);
	$contenido= preg_replace( "/\\\'/", "'", $contenido);
	$contenido= preg_replace( "/\'/", "'", $contenido);
	$contenido = stripslashes($contenido);
	return $contenido;
}

/**
 * convertir array en lista
 *
 * Convertir un array en una lista html
 *
 * @author Eduardo Magrané
 * @version 1.0
 *
 * @param contenido Array con el contenido
 * @param nivel Parametro interno para poder controlar el nivel de profundidad 
 *        en el que nos encontramos
 * @param form Añadir checkbox para seleccionar T/F
 *
 * @return true/false
 * @see seccion_array
 *
 */

function array_lista($contenido, $nivel=0, $form=FALSE) {

   if ( empty($contenido)  ) return FALSE;

   $formato_form = '<input checked type="checkbox" name="seleccionado[]" value="%s" /> ';

   echo "\n".str_repeat("\t",$nivel)."<ul>";
   foreach ( $contenido as $key => $val ) {
      if ( is_array($val) ) {
         echo "\n".str_repeat("\t",$nivel);
         if ( $form ) printf($formato_form,$key);
         echo "<b>$key</b>";
         array_lista($val, ++$nivel, $form);
      } else {
         if ( empty($val)  ) {
            echo "\n\t".str_repeat("\t",$nivel)."<li>";
            if ( $form ) printf($formato_form,$key);
            echo $key,'</li>';
         } else {
            echo "\n\t".str_repeat("\t",$nivel)."<li>";
            if ( $form ) printf($formato_form,$val);
            echo $val,'</li>';
            }
      }
   }
   echo "\n".str_repeat("\t",$nivel)."</ul>";
   }

/**
 * unserialize sin problemas
 */

function mb_unserialize($serial_str) { 
   $out = preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'", $serial_str); 
   return unserialize($out); 
   } 

/**
 * Devolver la salida de un include
 *
 * @param $filename Archivo a incluir
 * @param $datos Datos para la plantilla
 */

function get_include_contents($filename,$datos=NULL) {

   global $gcm;

   if (is_file($filename)) {

      ob_start();
      include $filename;
      $contents = ob_get_contents();
      ob_end_clean();
      return $contents;
      }

    return false;

   }

/** Extreure ip de sol·licitud */

function mostrar_ip() {

   if( isset($_SERVER['HTTP_X_FORWARDED_FOR']) ) {

      $client_ip =
         ( !empty($_SERVER['REMOTE_ADDR']) ) ?
         $_SERVER['REMOTE_ADDR']
         :
         ( ( !empty($_ENV['REMOTE_ADDR']) ) ?
         $_ENV['REMOTE_ADDR']
         :
         "unknown" );

      // los proxys van añadiendo al final de esta cabecera
      // las direcciones ip que van "ocultando". Para localizar la ip real
      // del usuario se comienza a mirar por el principio hasta encontrar
      // una dirección ip que no sea del rango privado. En caso de no
      // encontrarse ninguna se toma como valor el REMOTE_ADDR

      $entries = explode('[, ]', $_SERVER['HTTP_X_FORWARDED_FOR']);

      reset($entries);
      while (list(, $entry) = each($entries)) {

         $entry = trim($entry);
         if ( preg_match("/^([0-9]+.[0-9]+.[0-9]+.[0-9]+)/", $entry, $ip_list) ) {
            // http://www.faqs.org/rfcs/rfc1918.html
            $private_ip = array(
               '/^0./',
               '/^127.0.0.1/',
               '/^192.168..*/',
               '/^172.((1[6-9])|(2[0-9])|(3[0-1]))..*/',
               '/^10..*/');

            $found_ip = preg_replace($private_ip, $client_ip, $ip_list[1]);

            if ($client_ip != $found_ip) {
               $client_ip = $found_ip;
               break;
               }
            }
         }

   } else {

      $client_ip =
         ( !empty($_SERVER['REMOTE_ADDR']) ) ?
         $_SERVER['REMOTE_ADDR']
         :
         ( ( !empty($_ENV['REMOTE_ADDR']) ) ?
         $_ENV['REMOTE_ADDR']
         :
         "unknown" );
      }

   return $client_ip;

   }

/**
 * Creación de un directorio y sus subdirectorios si es necesario
 *
 * @param $ruta Ruta del directorio a crear
 */

function mkdir_recursivo($ruta) {

   $directorios = explode('/',$ruta);
   $dir = '';
   for ( $i=0;$i<=count($directorios)-1;$i++ ) {
      $dir=$dir.$directorios[$i];
      if ( ! is_dir($dir) ) {
         if ( ! @mkdir( $dir) ) {
            $mens = 'Debe crearse el directorio de los idiomas con los literales en: ['.$dir.'] con los permisos adecuados' ;
            registrar(__FILE__,__LINE__,$mens,'ERROR');
            return FALSE;
            }
         }
      $dir=$dir.'/';
      }

   return TRUE;
   }

/**
 * Comprobar existencia de tabla
 *
 * @param $pdo Conexión a base de datos
 * @param $tabla Nombre de la tabla
 */

function existe_tabla($pdo, $tabla) {

   try {

      $sql = 'SELECT COUNT(*) FROM '.$tabla;

      if ( ! $sth = $pdo->prepare($sql) ) {

         return FALSE;

      } else {

         if (!$sth->execute()) {

            return FALSE;

            }

         }

   } catch (Exception $ex) {

      return FALSE;

      }

   return TRUE;

   }

/**
 * Borrar tabla
 *
 * @param $pdo Instancia de PDO
 * @param $tabla Nombre de tabla a borrar
 */

function borrar_tabla($pdo, $tabla) {

   if ( existe_tabla($pdo,$tabla)  ) {
      $sql="drop table ".$tabla;
      $sqlResult = $pdo->query($sql);
      }

   }

/**
 * Borrar contenido de tabla
 *
 * @param $pdo Instancia de PDO
 * @param $tabla Nombre de tabla a borrar
 */

function borrar_contenido_tabla($pdo, $tabla) {

   if ( existe_tabla($pdo,$tabla)  ) {
      $sql="delete from ".$tabla;
      $sqlResult = $pdo->query($sql);
      }

   }

/**
 * Glob recursiva ()
 *
 * Http://php.net/glob
 *
 * @author HM2K <hm2k@php.net>
 *
 * @param $pattern El modelo pasa a glob ()
 * @param $flags Las banderas pasa a glob ()
 * @param $path El camino de la exploración
 * @return mixtos Una serie de archivos en la ruta dada coinciden con el patrón.
 */

function rglob($pattern='*', $flags = 0, $path=false) {

   if (!$path) { $path=dirname($pattern).DIRECTORY_SEPARATOR; }
   $pattern=basename($pattern);
   $paths=glob($path.'*', GLOB_MARK|GLOB_ONLYDIR|GLOB_NOSORT);
   $files=glob($path.$pattern, $flags);
   if ( !is_array($files) ) { $files = array(); }
   if ( empty($paths) ) return FALSE; 
   foreach ($paths as $path) {
      $mas = rglob($pattern, $flags, $path);
      if ( $mas && !empty($mas) && is_array($mas) ) { $files=array_merge($files,$mas); }
      }
   return $files;
   }

/**
 * Devolver el contenido de un directorio en un array
 *
 * Devolvemos en un array con todo el contenido de forma recursiva que contenga un directorio
 * Las claves del array seran los directorios, y los archivos normales items del mismo.
 *
 * @param path Ubicación del directorio
 * @param descartados Array con lista de descartados
 */

function dir_array($path, $descartados=NULL) {

   global $gcm;

   $contenido = FALSE;

   if ( !is_dir($path) ) {
      registrar(__FILE__,__LINE__,"ERROR::$path No es un directorio",'ERROR');
      return FALSE;
      }

   $directorio=dir($path);

   while ($archivo = $directorio->read()) {

      if ($archivo !="." && $archivo != ".." && $archivo != ".svn" ) {

         $file = $path."/".$archivo;

         // Comprobar descartados

         if ( isset($descartados) ) {

            $descartar = FALSE;
            foreach ( $descartados as $descartado ) {
               if ( strpos($archivo,$descartado) !== FALSE ) {
                  registrar(__FILE__,__LINE__,'Descartado: '.$archivo. ' coincide con '.$descartado);
                  $descartar = TRUE;
                  }
               }

            if ( $descartar ) {
               return;
               }
            }

         if ( is_dir($path."/".$archivo) ) {
            $directorios[]=$path."/".$archivo;
         } elseif ( is_file($path."/".$archivo) ) {
            $ficheros[]=$path."/".$archivo ;
            }

         }

      }

   if ( isset($ficheros) && is_array($ficheros) ) {

      foreach ( $ficheros as $f ) {

            $contenido[] = $f ;

            }

         }

   if ( isset($directorios) && is_array($directorios) ) {

      foreach  ( $directorios as $d ) {

            $contenido[$d]=dir_array($d,$descartados);

            }

         }

    $directorio->close();

   return $contenido;

   }


/**
 * Borrar directorio recursivamente
 *
 * Eliminamos un directorio recursivamente
 *
 * @author Eduardo Magrané
 * @version 1.0
 *
 * @param directorio Dirección del directorio a borrar
 *
 * @return TRUE/FALSE
 *
 */

function rmdir_recursivo($directorio) {

   if ($dir = opendir($directorio)) {

      while($file = readdir($dir)) {

         if ($file != "." && $file != ".." ) {

            if ( is_dir($directorio.'/'.$file) ) {

               rmdir_recursivo($directorio.'/'.$file);

            } else {

               if(unlink($directorio.'/'.$file)) {

                  $fichero=$directorio.$file;

               } else {

                  trigger_error(literal('Error').' '.literal('Eliminando contenido').': '.$directorio.'/'.$file, E_USER_ERROR);
                  }
               }
            }
         }
      closedir($dir);
     
      }

   if (rmdir($directorio) ) {

      return TRUE;

   } else {

      trigger_error(literal('Error').' '.literal('No se pudo eliminar').' '.literal('seccion').': '.$directorio, E_USER_ERROR);

      return FALSE;

      }

   }

/**
 * Minimizar archivos css
 */

function minimizar_css($buffer) {
   $buffer = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $buffer);
   $buffer = str_replace(array("\r\n", "\r", "\n", "\t"), '', $buffer);
   return $buffer;
   }    

/**
 * minutos2tiempo
 *
 * Pasamos minutos a formato tiempo.
 *
 * Ejemplo 14:20, 14 horas y 20 minutos
 *
 * @param $minutos Minutos
 *
 * @return Cadena de tiempo
 *
 */

function minutos2tiempo($minutos) {

   $horas = number_format($minutos/60,0);
   $min = number_format($minutos%60,0);
   $tiempo = $horas.':'.$min;

   return $tiempo;

   }


/**
 * Nos permite ordenar los campos por el peso especificado en los
 * campos.
 *
 * Cada array debe llevar una variable llamada peso con el valor correspondiente
 *
 * @param $a Variable a
 * @param $b Variable b
 */

function ordenar_por_peso($a, $b) {

   $aPeso = ( isset($a['peso']) ) ? $a['peso'] : 0 ;
   $bPeso = ( isset($b['peso']) ) ? $b['peso'] : 0 ;

   if ($aPeso == $bPeso) return 1;
   return ($aPeso < $bPeso) ? -1 : 1;
   }

/**
 * Devuelve el directorio temporal del proyecto.
 * en caso de no existir lo creamos.
 */

function getTemp() {
   if ( ! file_exists('tmp') ) mkdir('tmp');
   return 'tmp/';
   }

/**
 * Borrado de archivos viejos
 *
 * @param $horas Horas que han debido pasar desde la creación del archivo
 *               para ser borrado
 * @param $patron Patron para la busqueda de los archivos, este patron 
 *                será pasado a glob para obtener el listado de archivos.
 *                Ejemplo: '/tmp/*.log'
 */

function borrar_archivos_viejos($horas, $patron) {

   $fitxes = glob($patron);

   if ( ! empty($fitxes) ) {

      foreach ( $fitxes as $fitxer ) {

         $data_fitxer = filemtime($fitxer);

         $temps = ( time() - $data_fitxer ) / 60 ;

         if ( $temps > $horas ) {
            registrar(__FILE__,__LINE__,
               'Borramos archivos viejos ['.$fitxer.'] ['.date("Y-m-d H:i:s",$data_fitxer).']',
               'ADMIN');
            if ( is_dir($fitxer) ) {
               rmdir_recursivo($fitxer);
            } else {
               unlink($fitxer);
               }
            }
         }
      }

   }

if ( ! function_exists("sesion") ) {

/**
 * Con session() podemos recoger comodamente variables de sesión sin 
 * preocuparnos de que choquen entre proyectos.
 *
 * Una posible aplicación puede ser utilizar cookies para mantener las
 * sesiones de un día para otro.
 */

function sesion($elemento, $valor=FALSE) {

   global $gcm;

   if ( $valor ) {
      $_SESSION[$gcm->sufijo.$elemento] = $valor ;
      return;
      }

   if ( isset($_SESSION[$gcm->sufijo.$elemento]) ) 
      return $_SESSION[$gcm->sufijo.$elemento];

   return FALSE;

   }


}

/**
 * #caja {
 * 	border: 1px solid black;
 * 	background: #fff;
 *     <?php css3_write("border-radius", "5px"); ?>
 * 	width: 50%;
 *     <?php css3_write("box-shadow", "5px 5px 2px rgba(0, 0, 0, 0.5)"); ?>
 * }
 * 
 * Lo que generará el siguiente código:
 * 
 * #caja {
 * 	border: 1px solid black;
 * 	background: #fff;
 * 	-webkit-border-radius: 5px;
 * 	-moz-border-radius: 5px;
 * 	-o-border-radius: 5px;
 * 	-ms-border-radius: 5px;
 * 	border-radius: 5px; width: 50%;
 * 	width: 50%;
 * 	-webkit-box-shadow: 5px 5px 2px rgba(0, 0, 0, 0.5);
 * 	-moz-box-shadow: 5px 5px 2px rgba(0, 0, 0, 0.5);
 * 	-o-box-shadow: 5px 5px 2px rgba(0, 0, 0, 0.5);
 * 	-ms-box-shadow: 5px 5px 2px rgba(0, 0, 0, 0.5);
 * 	box-shadow: 5px 5px 2px rgba(0, 0, 0, 0.5);
 * }
 *
 * Extrido de: http://webintenta.com/generar-prefijos-css3-con-php.html
 */
 

function css3_write ($property, $value) {
	$css3 = "-webkit-".$property.": ".$value.";\n"
	." -moz-".$property.": ".$value.";\n"
	." -o-".$property.": ".$value.";\n"
	." -ms-".$property.": ".$value.";\n"
	." ".$property.": ".$value.";\n";
	echo $css3;
   } 

/**
 * Escapar cadena para añadir a html desde para un value
 * 
 * @param $cadena Cadena a escapar
 */

function esc_value($cadena) {

   return htmlspecialchars($cadena,ENT_QUOTES,'UTF-8');
   }

/**
 * Contabilizar saltos de linea dentro de una cadena de texto
 */


function contabilizar_saltos_linea($cadena) {

   //Definimos el ancho que queramos en una variable
   $ancho=55; 

   if (strtoupper(substr(PHP_OS,0,3)=='WIN')) { 
     $eol="\r\n"; 
   } elseif (strtoupper(substr(PHP_OS,0,3)=='MAC')) { 
     $eol="\r"; 
   } else { 
     $eol="\n"; 
      } 

   $cad=wordwrap($cadena, $ancho, $eol, 1); 
   return substr_count($cad,$eol)+1; 

   }

if ( ! function_exists("getTempFolder") ) {

/**
 * Devolver url de carpeta temporal
 *
 * En caso de tenerla la creamos en el directorio base
 *
 * @param $dir_base Directorio base por defecto $DIR_BASE
 */

function getTempFolder($dir_base=FALSE) {

   // Para proyectos gcm
   $dir_base = Router::$enlace_relativo;

   if ( ! $dir_base && ! isset($GLOBALS['DIR_BASE']) ) {
      registrar(__FILE__,__LINE__,"Es necesario un directorio base para definir el tempral",'ERROR');
      return FALSE;
      }

   $dir_base = ( $dir_base ) ? $dir_base : $GLOBALS['DIR_BASE'] ;

   $tmp = comprobar_barra($dir_base).'tmp/';

   if ( !file_exists($tmp) ) {
      if(!mkdir($tmp, 0700)) {
         registrar(__FILE__,__LINE__,"Fallo al crear carpeta temporal [$tmp]",'ERROR');
         return FALSE;
         }
      }

   return $tmp;
   }
}

?>
