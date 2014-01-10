<?php

/**
 * @file      GcmConfig.php
 * @brief     Manipulación de archivos de configuración
 * @ingroup   GcmConfig
 * @author    Eduardo Magrané 
 *
 * @internal
 * Copyright  Copyright (c) 2010, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

/**
 * @class GcmConfig
 *
 * @brief     Lectura y edición de archivos de configuración
 *
 * Esta clase nos permite leer archivos de configuración nativos de php y
 * a la vez la edición de su contenido con formularios php.
 *
 * Inspirado en @see http://www.jourmoly.com.ar/introduccion-a-mvc-con-php-segunda-parte/
 * 
 * Las descripciones de las variables se guardan en archivos a parte uno por cada idioma 
 * que utilizamos, Podemos especificar el idioma por defecto de la aplicación y el idioma
 * actual.
 *
 * En caso de no tener todas las descripciones del idioma actual las cogeremos del idioma 
 * por defecto.
 *
 * Uso:
 *
 * Formato para el archivo que contiene las variables de configuración TGC.php:
 * El nombre de la variable debe coincidir con el nombre del archivo, el resto será
 * ignorado y por lo tanto perdido al volver a escribir el archivo.
 *
 * @code
 * <?php
 * $TGC[v1]="variable1";
 * $TGC[v2]="variable2";
 * ?>
 * @endcode
 *
 * Formato para archivo de descripciones TGC_es.php:
 *
 * @code
 * <?php
 * $TGC_DESC[v1]="descripcion_variable1";
 * $TGC_DESC[v2]="descripcion_variable2"; 
 * ?>
 * @endcode
 *
 * Ejemplo de uso:
 *
 * @code
 * $config = new GcmConfig('config/TGC.php');
 * $variable1 = $config->get('v1');
 * $config->set('v1','NUEVO VALOR PARA v1');
 * $config->setDescripcion('v1','NUEVO VALOR PARA DESCRIPCION v1');
 * $config->del('v4');
 * @endcode
 *
 * La clase recibe la ruta del archivo que contiene las variables, a partir de la ruta
 * se deduce el nombre de la variable, que sera el del archivo sin '.php'.
 *
 * Los ficheros de idiomas contendran su especificación en el nombre, ejemplo: TGC_es.php
 *
 * @ingroup GcmConfig
 */

class GcmConfig {

   protected $variables = array();              ///< Variables del archivo de configuración
   protected $descripciones;                    ///< Descripciones de las variables
   protected $descripcionesxdefecto;            ///< Descripciones de las variables en el idioma por defecto
   protected $archivo;                          ///< Ruta de archivo que contiene el array con las variables
   protected $archivoxdefecto;                  ///< Ruta de archivo que contiene el array con las variables por defecto

   /** 
    * Ruta de directorio que contiene los archivos de descripciones.
    *
    * Por defecto no hace falta definirlo se recogen las descripciones del mismo directorio 
    * donde se encuentra el archivo de configuración, ejemplo: config_es.php 
    *
    * Pero se permite definir otro diferente.
    */

   protected $directorio_descripciones;

   protected $nombre_array;                     ///< Nombre del array que contiene las variables;

   private $variables_modificadas = FALSE;      ///< Para saber si hubo modificaciones para guardar
   private $descripciones_modificadas = FALSE;  ///< Para saber si hubo modificaciones para guardar

   /**
    * Array para saber si ya se recogieron las descripciones de los diferentes idiomas
    */

   private $descripciones_recogidas = FALSE;

   public $idioma = 'es';                       ///< Idioma de las descripciones por defecto
   public $idiomaxdefecto = 'es';               ///< Idioma por defecto de la aplicación
 
   public $ordenar = FALSE;                     ///< Ordenar variables al escribir archivo (T/F)


   /**
    * Constructor
    *
    * @param $archivo con las variables
    * @param $archivoxdefecto Variables por defecto, necesario si deseamos que 
    *        tener en cuenta variables por defecto que no esten en los archivos
    *        definitivos.
    */
   function __construct($archivo, $archivoxdefecto = FALSE) {

      $this->archivo = $archivo;
      $this->nombre_array = str_replace('.php','',basename($this->archivo));
      $this->directorio_descripciones = dirname($this->archivo);
      $this->archivoxdefecto = $archivoxdefecto;

      $this->leer_variables();

      }

   /**
    * Recogemos las variables para obtener sus valores.
    * 
    * Si tenemos archivo de variables por defecto, primero recogemos
    * estas para que en caso de que el archivo final no tenga alguna
    * de ellas las recoga igualmente.
    */

   function leer_variables() {

      $variablesxdefecto = FALSE;

      // Variables por defecto
      if ( $this->archivoxdefecto ) {
         include ($this->archivoxdefecto);
         $variablesxdefecto = ${$this->nombre_array};
         ${$this->nombre_array} = array();
         }
         
      if ( !file_exists($this->archivo) ) {
         registrar(__FILE__,__LINE__,"Archivo de configuración [".$this->archivo."] no existe, lo creamos",'ADMIN');
         if ( ! file_exists(dirname($this->archivo)) ) mkdir_recursivo(dirname($this->archivo));
      } else {
         include ($this->archivo);
         }
         
      /* Los valores del array del archivo son introducidos en $this->variables */

      if ( ! isset(${$this->nombre_array}) ) {
         registrar(__FILE__,__LINE__,"Archivo de configuración [".$this->archivo."] sin contenido",'ADMIN');
         return;
      } else {
         $this->variables = ${$this->nombre_array};
         }

      if ( ! $variablesxdefecto ) return ;

      // Combinar valores por defecto con los actuales.

      foreach ( $variablesxdefecto as $key => $val ) {
         if ( ! array_key_exists($key, $this->variables) ) {
            $this->variables[$key] = $val;
            // Variables que no se encuentran en destino, solo en default
            // echo "<br>$key : $val";
            }
         }

      }

   /**
    * Para evitar guardar los nombres de los ficheros con acentos y otras cosas que pueda
    * producir que no funcione bien el sistema de ficheros
    *
    * @param $text Texto a modificar
    *
    * @return Texto modificado
    *
    */

   function quitarUtf8($text) {
      $text = str_replace("\\","",$text);
      $text = stripslashes($text);
      $text = trim($text);
      $text = ereg_replace("á|à|â|À|Á|Â", "a", $text);
      $text = ereg_replace("é|è|ê|È|É|Ê", "e", $text);
      $text = ereg_replace("í|ì|î|Ì|Í|Î", "i", $text);
      $text = ereg_replace("ó|ò|ô|Ò|Ó|Ô", "o", $text);
      $text = ereg_replace("ú|ù|û|ü|Ù|Ú|Û|Ü", "u", $text);
      //$text = str_replace(" ", "_", $text);
      //$text = str_replace("'", "\'", $text);
         
      return($text);	

   }

   /** Presentamos variables para debug */

   function debug() {

      $salida = "\nVariables: ";
      $salida .= print_r($this->variables,TRUE);
      $salida .= "\nDescripciones: ";
      $salida .= print_r($this->descripciones,TRUE);

      return $salida;

      }

   /**
    * Definir directorio donde se guardan y recogen las descripciones
    *
    * @param $directorio Directorio descripciones
    */

   function directorio_descripciones($directorio) {

      $this->directorio_descripciones = $directorio;

      }

   /**
    * Recuperamos descripciones en caso de ser necesario
    *
    * @param $idioma Idioma de las descripciones
    */

   function recoger_descripciones($idioma = NULL) {

      $idioma = ( $idioma ) ? $idioma : $this->idioma ;

      if ( isset($this->descripciones_recogidas[$idioma]) && $this->descripciones_recogidas[$idioma] == TRUE ) {
         return;
         }
      
      $this->descripciones_recogidas[$idioma] = TRUE;

      $archivo = $this->directorio_descripciones.'/'.$this->nombre_array.'_'.$idioma.'.php';

      if ( file_exists($archivo) ) {
         include ($archivo);
      } else {
         return;
         }

      /* Los valores del array del archivo son introducidos en $this->variables */

      $this->descripciones[$idioma] = ${$this->nombre_array.'_DESC'};

      }

   /** Devolver array con las variables */

   function variables() { return $this->variables; }

   /** Devolver array con las variables en formato JSON */

   function variablesJSON() { 

      $c = 0;
      foreach ($this->variables as $key => $valor ) {

         $respuesta->variables[$c]['id']    = $key;
         $respuesta->variables[$c]['valor'] = $valor;
         $c++;

         }

      echo json_encode($respuesta);
      }

   /** Devolver array con las descripciones */

   function descripciones($idioma = NULL) {

      $idioma = ( $idioma ) ? $idioma : $this->idioma ;

      return $this->descripciones[$idioma];

      }

   /** Validar descripciones */

   function validarDescripcion($descripcion) {

      if ( ! $descripcion ) return FALSE ;
      if ( $descripcion == '' ) return FALSE;

      /* Si tenemos marca de descripción correspondiente
       * a idioma por defecto no validamos
       */

      $marca = $this->idiomaxdefecto.'::';

      if ( strpos($marca,$descripcion) ) return FALSE;

      return TRUE;

      }

   /** 
    * Con set vamos guardando nuestras variables. 
    *
    * @param $variable Variable a añadir o modificar
    * @param $valor Valor
    */

   function set($variable, $valor) {

      if ( !isset($variable) || empty($variable) ) {
         throw new Exception('Sin variable no podemos añadir elementos');
         return FALSE;
         }

      $this->variables_modificadas = TRUE;

      if ( is_array($valor)) {   // si es un array 
         $variable = html_entity_decode($variable,ENT_NOQUOTES,'UTF-8');
         $conta=0;
         foreach ( $valor as $key => $val ) {
            if ( is_array($val) ) {   // si es un grupo
               foreach ( $val as $k => $v ) {
                  $v = html_entity_decode($v,ENT_NOQUOTES,'UTF-8');
                  $this->variables[$variable][$conta][$k] = $v;
                  }
            } else {
               $val = html_entity_decode($val,ENT_NOQUOTES,'UTF-8');
               $this->variables[$variable][] = $val;
               }
            $conta++;
            }
      } else {
         $variable = html_entity_decode($variable,ENT_NOQUOTES,'UTF-8');
         $valor    = html_entity_decode($valor,ENT_NOQUOTES,'UTF-8');
         $this->variables[$variable] = $valor;
         }

      }

   /**
    * Guardar valor para descripción
    *
    * Si la descripción viene con marca de idioma ejemplo: es::descripción
    * no la incluimos, esto quiere decir que a falta de la descripción del
    * idioma actual se ha utilizado la del idioma por defecto pero no hay que
    * guardarla ya que perderiamos la referencia de que falta descripción en 
    * ese idioma
    *
    * @param $variable Variable a la que pertenece la descripción
    * @param $descripcion Descripción
    * @param $idioma Idioma en que se desea presentar las descripciones
    * @param $clave En caso de ser un grupo pasamos el array como variable y la clave
    */

   function setDescripcion($variable, $descripcion, $idioma = NULL, $clave=FALSE) {

      if ($variable) { $variable = trim($variable);}

      if ( $clave ) {    // Es un grupo

         $descripcion = html_entity_decode($descripcion,ENT_NOQUOTES,'UTF-8');
         $descripcion = trim($descripcion);
         $clave = html_entity_decode($clave,ENT_NOQUOTES,'UTF-8');
         $this->descripciones[$idioma][$variable][$clave] = $descripcion;
         if ( ! $this->validarDescripcion($descripcion) ) {
            registrar(__FILE__,__LINE__,'Descripción no pasa validación','ERROR');
            return FALSE ;
            }
         $idioma = ( $idioma ) ? $idioma : $this->idioma ;
         // $this->recoger_descripciones($idioma);
         $this->descripciones_modificadas[$idioma] = TRUE;

         /* Si no hay descripción en el idioma por defecto añadimos la misma */

         if ( $idioma != $this->idiomaxdefecto ) {

            $this->recoger_descripciones($this->idiomaxdefecto);

            $descripcion = $this->descripciones[$this->idiomaxdefecto][$variable][$clave];

            if ( ! $this->validarDescripcion($descripcion) ) {

               $this->descripciones_modificadas[$this->idiomaxdefecto] = TRUE;

               $this->setDescripcion($variable,$descripcion, $this->idiomaxdefecto, $clave);

               }

            }
      } else {

         $descripcion = trim($descripcion);
         $descripcion = html_entity_decode($descripcion,ENT_NOQUOTES,'UTF-8');
         $variable = html_entity_decode($variable,ENT_NOQUOTES,'UTF-8');
         if ( ! $this->validarDescripcion($descripcion) ) return FALSE ;
         $idioma = ( $idioma ) ? $idioma : $this->idioma ;
         $this->recoger_descripciones($idioma);
         $this->descripciones_modificadas[$idioma] = TRUE;
         $this->descripciones[$idioma][$variable] = $descripcion;

         /* Si no hay descripción en el idioma por defecto añadimos la misma */

         if ( $idioma != $this->idiomaxdefecto ) {

            $this->recoger_descripciones($this->idiomaxdefecto);

            $descripcion = $this->descripciones[$this->idiomaxdefecto][$variable];

            if ( ! $this->validarDescripcion($descripcion) ) {

               $this->descripciones_modificadas[$this->idiomaxdefecto] = TRUE;

               $this->setDescripcion($variable,$descripcion, $this->idiomaxdefecto, $clave);

               }

            }

         }

      }

   /** Con get('nombre_de_la_variable') recuperamos un valor */

   function get($variable) {

      if(isset($this->variables[$variable])) {
         return $this->variables[$variable];
      } else {
         return FALSE;
         }

      }

   /** 
    * Con getDescripcion('nombre_de_la_variable') recuperamos la descripción
    *
    * En caso de no tener descripción en el idioma actual devolvemos la descripción 
    * del idioma por defecto.
    *
    * @param $variable Variable
    * @param $idioma Idioma a mostrar
    *
    */

   function getDescripcion($variable, $idioma = NULL, $grupo=FALSE) {

      $idioma = ( $idioma ) ? $idioma : $this->idioma ;

      /* Si no tenemos las descripciones hay que recuperarlas */

      /* Primero las descripciones del idioma por defecto */

      if ( !$this->descripciones_recogidas[$this->idiomaxdefecto] ) { $this->recoger_descripciones($this->idiomaxdefecto); }

      /* Idioma actual */

      if ( !isset($this->descripciones_recogidas[$idioma]) ) { $this->recoger_descripciones($idioma); }

      // Si es un grupo
      if ( $grupo && isset($this->descripciones[$idioma][$grupo][$variable])) {
         return $this->descripciones[$idioma][$grupo][$variable];
         }

      // Nos estan pidiendo la descripción del grupo
      if(isset($this->descripciones[$idioma][$variable]) 
         && is_array($this->descripciones[$idioma][$variable])
         && isset($this->descripciones[$idioma][$variable]['grupo']) ) {
            return $this->descripciones[$idioma][$variable]['grupo'];
         }

      if(isset($this->descripciones[$idioma][$variable])) {
         return $this->descripciones[$idioma][$variable];
         }

      /* Si no es el idioma por defecto y no tenemos descripción
       * buscamos la descripción del idioma por defecto con una marca
       * para ser conscientes de que la descripción no es la del idioma 
       * actual
       */

      // Nos estan pidiendo la descripción del grupo
      if(isset($this->descripciones[$this->idiomaxdefecto][$variable]) && is_array($this->descripciones[$this->idiomaxdefecto][$variable]) ) {
         // Comprobar si existe
         if ( isset($this->descripciones[$this->idiomaxdefecto][$variable]['grupo']) ) {
            return $this->idiomaxdefecto.'::'.$this->descripciones[$this->idiomaxdefecto][$variable]['grupo'];
            registrar(__FILE__,__LINE__,literal('No hay descripción del grupo ['.$variable.'] en el idioa actual'),'ADMIN');
         } else {
            return FALSE;
            registrar(__FILE__,__LINE__,literal('No hay descripción del grupo ['.$variable.']'),'ADMIN');
            }
         }

      // Puede que sea un grupo comprobar si existe en él.
      if ( $grupo && isset($this->descripciones[$this->idiomaxdefecto][$grupo][$variable])) {
         return $this->idiomaxdefecto.'::'.$this->descripciones[$this->idiomaxdefecto][$grupo][$variable];
         }

      if(isset($this->descripciones[$this->idiomaxdefecto][$variable])) {
         return $this->idiomaxdefecto.'::'.$this->descripciones[$this->idiomaxdefecto][$variable];
         }

      return FALSE;

      }

   /** 
    * Borrar variable
    *
    * @param $variable Variable a borrar
    */

   function del($variable) {
   
      $this->variables_modificadas = TRUE;

      unset($this->variables[$variable]);

      /* Borramos descripciones en todos los idiomas */

      /* Buscar ficheros de idiomas */

      $fidiomas = glob(dirname($this->archivo).'/'.$this->nombre_array.'_*.php');

      /* Recorrer cada idioma para eliminarlo */

      foreach( $fidiomas as $f ) {
         $idioma = str_replace(dirname($this->archivo).'/'.$this->nombre_array.'_','',$f);
         $idioma = str_replace('.php','',$idioma);
         $this->delDescripcion($variable, $idioma);
         }

      return TRUE;

      }

   /** 
    * Borrar descripción
    *
    * @param $variable Variable a borrar su descripción
    */

   function delDescripcion($variable, $idioma = NULL) {
   
      $idioma = ( $idioma ) ? $idioma : $this->idioma ;

      $this->recoger_descripciones($idioma);

      $this->descripciones_modificadas[$idioma] = TRUE;

      unset($this->descripciones[$idioma][$variable]);
      return TRUE;

      }

   /** Guardar cambios de variables en archivo */

   function guardar_variables() {
      
      if ( ! $this->variables_modificadas ) return;

      return $this->escribirArchivos($this->archivo, $this->variables, $this->nombre_array);

      }

   /** Guardar cambios de descripciones en archivo */

   function guardar_descipciones($idioma = NULL) {

      $idioma = ( $idioma ) ? $idioma : $this->idioma ;

      /* Miramos si han habido cambios antes de escribir archivo sino nos crearia un
       * archivo sin contenido
       */

      if ( $this->descripciones_modificadas[$idioma] != TRUE ) {
         return TRUE;
         }

      $archivo_descripciones = $this->directorio_descripciones.'/'.$this->nombre_array.'_'.$idioma.'.php';

      $this->escribirArchivos($archivo_descripciones, $this->descripciones[$idioma], $this->nombre_array.'_DESC', TRUE);

      return TRUE;

      }

   /** 
    * Pasar contenido a archivos php 
    * 
    * @param $archivo Archivo destino
    * @param $datos   Array con los datos 
    * @param $nombre del array
    * @param $descripciones Son descripciones TRUE/FALSE
    */

   private function escribirArchivos($archivo, $datos, $nombre_array, $descripciones=FALSE) {

      /* Ordenamos variables */

      if ( $this->ordenar ) ksort($datos);

      reset($datos);

      try {

         // $ubicacion = dirname($_SERVER['SCRIPT_FILENAME']).'/';

         if ( ! $file = @fopen($archivo, "w",TRUE) ) {
            $error = error_get_last();
            $msg = "No se pudo abrir archivo $archivo para incluir en [$nombre_array] ";
            $msg .= "\nDirectori actual: ".getcwd();
            if ( $error ) $msg .= "\nError: ".$error['message'];
            throw new Exception($msg);
         } else {

            fputs($file, "<?php\n");
            fputs($file, "// Archivo generado automáticamente por ".__CLASS__." ".date("D M j G:i:s T Y")."\n");

            while (list($clave, $val)=each($datos)){

               if ( is_array($val)) {   // si es un array 

                  $conta=0;
                  while (list($claveArray, $valorArray)=each($val)) {

                     if ( is_array($valorArray) ) {  // Es un grupo

                        $conta2=0;
                        while (list($claveArray2, $valorArray2)=each($valorArray)) {

                           $valorArray2 = str_replace("\\","",$valorArray2);
                           $valorArray2 = stripcslashes($valorArray2);

                           if (fputs($file, '$'."$nombre_array"."['".str_replace("'","\'",$clave)."'][".$conta."]['".$claveArray2."']='".str_replace("'","\'",$valorArray2)."';\n") === FALSE ) {
                              throw new Exception("No se puede escribir en ".$archivo);
                              return FALSE;
                              }

                           $conta2++;
                           }

                     } else {

                        $valorArray = str_replace("\\","",$valorArray);
                        $valorArray = stripcslashes($valorArray);

                        // si son descripciones funciona diferente
                        if ( $descripciones ) {
                           if (fputs($file, '$'."$nombre_array"."['".str_replace("'","\'",$clave)."']['".$claveArray."']='".str_replace("'","\'",$valorArray)."';\n") === FALSE ) {
                              throw new Exception("No se puede escribir en ".$archivo);
                              return FALSE;
                              }
                        } else {
                           if (fputs($file, '$'."$nombre_array"."['".str_replace("'","\'",$clave)."'][".$conta."]='".str_replace("'","\'",$valorArray)."';\n") === FALSE ) {
                              throw new Exception("No se puede escribir en ".$archivo);
                              return FALSE;
                              }
                           }

                        }

                     $conta++;
                     }

               } else {                   // No es un array

                  $val = str_replace("\\","",$val);
                  $val = stripcslashes($val);

                  if (fwrite($file, '$'."$nombre_array"."['".str_replace("'","\'",$clave)."']='".str_replace("'","\'",$val)."';\n") === FALSE ) {
                     throw new Exception("No se puede escribir en ".$archivo);
                     return FALSE;
                     }

                  }
               }

            fputs($file, '?>');
            fclose($file);

            $this->variables_modificadas = FALSE;
         
            }

         } catch (Exception $ex) {

            // throw new Exception("Error escribiendo archivo ".$archivo."\n".$ex->getMessage());
            registrar($ex->getFile(), $ex->getLine(),"Error escribiendo archivo ".$archivo."\n".$ex->getMessage(),'ERROR');
            return FALSE;

            }

      }

   /** Antes de terminar guardar cambios si los han habido */

   function __destruct() {

      if ( $this->variables_modificadas ) { 

         try {

            $this->guardar_variables();

         } catch (Exception $ex) {

            echo 'ERROR en destructor';
            return FALSE;
            }

         }
      
      /* Recorremos array de descripciones_modificadas para saber cuales hay que actualizar */

      if ( !empty($this->descripciones_modificadas) ) {

         foreach ( $this->descripciones_modificadas as $i => $dm ) {
            if ( $dm ) { $this->guardar_descipciones($i); }
            }

         }

      }

   }


?>
