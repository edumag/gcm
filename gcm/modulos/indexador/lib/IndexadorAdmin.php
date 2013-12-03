<?php

/** 
 * @file IndexadorAdmin.php
 * @brief Administración para los indexador
 * @ingroup modulo_indexador
 */

require_once(dirname(__FILE__).'/Indexador.php');

/** 
 * @class IndexadorAdmin
 * @brief Métodos administrativos para indexador
 */

class IndexadorAdmin extends Indexador {

   function __construct() {

      parent::__construct();

      /* Comprobamos que las tablas sino tenemos que crearlas */

      if ( ! existe_tabla($this->pdo, $this->prefijo.'etiquetas') ) {

         $this->generar_tablas();

         }

      }

   /**
    * Presentar contenidos para reindexar
    *
    * Creamos lista de contenido a reindexar
    */

   function lista_contenido_reindexar() {

      // Indexamos
      $contenido = dir_array('File/'.Router::$ii, $this->descartar) ;

      echo "\n",'<div style="display: none;" class="error" id="errores_reindexando"></div>';

      echo "\n".'<div id="panel_indexado">Indexados: 0</div>';
      echo "\n".'<div id="listado_reindexar">';

      if ( !$this->gcm_indexar_paginas($contenido) ) {
         trigger_error(literal('Error').' '.literal('No se pudo reindexar'), E_USER_ERROR);
         }

      echo "\n".'</div> <!-- listado_reindexar -->';

      }

   /** 
    * reindexamos contenido
    *
    * Presentamos listado de contenido con boton para reindexar.
    *
    * Ejecutando cada reindexado individualmente con ajax para que el servidor 
    * no nos de problema con el tiempo de ejecución del script.
    *
    */

   function reindexar($e, $args=NULL) {

      global $gcm;

      $this->javascripts('listado_reindexado.js');

      $gcm->event->anular('contenido','indexador');
      $gcm->event->anular('titulo','indexador');
      $gcm->presentar_contenido_dinamico = FALSE;

      $gcm->titulo = literal('Reindexar contenido');

      echo "\n<br />";
      echo "\n<br />Directorios descartados de reindexado";
      echo "\n<ul>";
      foreach ($this->descartar as $descartado) echo "<li>".$descartado."</li>";
      echo "\n</ul>";
      echo "\nTamaño descripción: <b>".$this->maxLong."</b>";
      echo "\n<br />";
      echo "\n<br />",'<span class="boton"><a href="'.Router::$base.'?e=reindexado_completo" >Ejecutar reindexado completo</a></span>';
      echo '<br /><br />';

      $this->lista_contenido_reindexar($e,$args);

      }

   /**
    * Reindexado completo
    *
    * EL reindexado completo borra las tablas para volver a generarlas y ejecuta
    * reindexado de las paginas encontradas una a una con ajax para no tener 
    * problemas con el tiempo de ejecución del servidor
    */

   function reindexado_completo($e, $args=NULL) {

      global $gcm;

      $this->javascripts('listado_reindexado.js');
      $this->javascripts('reindexador_completo.js');

      $gcm->event->anular('contenido','indexador');
      $gcm->event->anular('titulo','indexador');
      $gcm->presentar_contenido_dinamico = FALSE;

      $gcm->titulo = literal('Reindexar contenido');

      // Indexamos
      $contenido = dir_array('File/'.Router::$ii, $this->descartar) ;

      // Borrar tablas
      borrar_tabla($this->pdo,$this->prefijo.'archivos');
      borrar_tabla($this->pdo,$this->prefijo.'etiquetas');
      borrar_tabla($this->pdo,$this->prefijo.'r_etiqueta_archivo');
      $this->generar_tablas();

      $this->lista_contenido_reindexar();

      }

   /** gcm_indexar_paginas
    *
    * Imprimir listado de contenidos para indexar
    *
    * @param contenido Es un array con el listado de los archivos a indexar
    *
    * @return TRUE/FALSE
    *
    */

   function gcm_indexar_paginas($contenido) {

      global $gcm;

      $funcion_js_indexar = 'indexar';

      foreach ( $contenido as $item => $elemento ) {

         if ( is_array($elemento) ) {                             // si elemento es un array es un directorio

            $nombre = basename($item);
            if (  $nombre[0] != '.' ) {                         // No es un directorio oculto
               $this->gcm_indexar_paginas($elemento);
               }

         } elseif ( !esImagen($elemento) && substr_count($elemento,'.html') > 0 ) { // comprobamos que sea un archivo html

            $nombre = basename($elemento);
            if ( $nombre[0] != '.' ) { // No es un fichero oculto

               $camino = GUtil::camino($elemento);

               include($gcm->event->instancias['temas']->ruta('indexador','html','elemento_lista_indexar.html'));
               }

            }
         }
      return TRUE;

      }


   /** indexado() 
    *
    * indexamos Router::$f
    *
    * Esta función nos sirve para hacer un indexado por ajax
    * o no.
    */

   function indexado() {

      global $gcm;

      if ( $this->indexar_archivo_pdo('indexado',Router::$f) !== FALSE ) {
         $salida = "Contenido reindexado";
      } else {
         $salida  = '<p class="error">';
         $salida .= 'Error al indexar contenido';
         $salida .= '</p>';
         }

      if ( Router::$formato == 'ajax'  ) {
         echo $salida;
      } else {
         registrar(__FILE__,__LINE__,$salida,'AVISO');
         }

      }

   /** Indexar contenido
    *
    * Indexamos contenido especificado
    */

   function indexar_contenido($e,$args) {

      global $gcm;

      if ( $this->indexar_archivo_pdo('indexar_contenido',Router::$f) === FALSE ) {
         $gcm->registra(__FILE__,__LINE__,'Error al indexar ['.Router::$f.']','ERROR');
         }

      }

   /**
    * Cambio de nombre de una sección
    *
    * @param $e Evento recibido
    * @param $args Argumentos extras si los hay
    */

   function cambio_ruta_seccion($e, $args=NULL) {

      global $gcm;

      $literal = $_POST['titulo_seccion'];
      $nombre_seccion = GUtil::textoplano($literal);
      $seccion_nueva = ( isset($gcm->seleccionado[0]) ) ? $gcm->seleccionado[0] : Router::$dd.Router::$s; 
      $seccion_nueva = comprobar_barra($seccion_nueva).$nombre_seccion;

      if ( empty($seccion_nueva) ) {
         registrar(__FILE__,__LINE__,'Sin sección para seleccionada para renombrar','ERROR');
         return FALSE;
         }

      // Limpiar nombre de sección
      $ruta_destino=$nombre_seccion;
      $ruta_origen = Router::$s;

      $sql = "SELECT id, url FROM ".$this->prefijo."archivos WHERE url like '%".$ruta_origen."%'";
      $result = $this->pdo->query($sql);
      $num_afectados = $result->fetchColumn();

      registrar(__FILE__,__LINE__,'Modificar url [ '.$ruta_origen.' ] por [ '.$ruta_destino.' ], afectados: [ '.$num_afectados.' ]');

      if ( $num_afectados > 0 ) {

         if ( $sqlResult = $this->pdo->prepare($sql) ) {

            $sqlResult->execute();

            while($fila = $sqlResult->fetch(PDO::FETCH_OBJ)) {

               $nueva_ruta = str_replace($ruta_origen,$ruta_destino.'/',$fila->url);
               $subsql="UPDATE ".$this->prefijo."archivos SET url=? WHERE id=?";
               $subresult = $this->pdo->prepare($subsql);
               if ( $subresult->execute(array($nueva_ruta,$fila->id)) ) {
                  registrar(__FILE__,__LINE__,'Modificada url de sección [ '.$nueva_ruta.' ]');
               } else {
                  registrar(__FILE__,__LINE__,'Sin resultados para ['.$nueva_ruta.']','ERROR');
                  }

               }

            } 

         }

      }

   /**
    * @f
    * @brief borrar_archivo_pdo
    *
    * Eliminar entrada de un archivo en la base de datos
    *
    * @author Eduardo Magrané
    * @version 1.0
    *
    * @param url Url del archivo a eliminar
    *
    * @return TRUE/FALSE
    *
    */

   function borrar_archivo_pdo($e, $args) {

      global $gcm; 

      $parametros = recoger_parametros($args);

      if ( isset($parametros['url']) ) {               // Si tenemos url
         $urls = array($parametros['url']) ;
         $gcm->registra(__FILE__,__LINE__,'Recibimos un archivo para borrar desde url ['.$url.']');
      } elseif ( isset($parametros['ruta_origen']) ) { // Si tenemos una ruta origen por cambio de nombre
         $urls = array($parametros['ruta_origen']) ;
         $gcm->registra(__FILE__,__LINE__,'Recibimos un archivo para borrar desde ruta_origen['.$url.']');
      } elseif ( isset($gcm->memoria['origen']) ) { // Si tenemos una ruta origen por cambio de nombre
         $urls = array($gcm->memoria['origen']) ;
         $gcm->registra(__FILE__,__LINE__,'Recibimos un archivo para borrar desde gcm->memoria[origen] '.$urls[0]);
      } elseif ( isset($_POST['seleccionado']) ) {          // Nos llegan los archivos borrados por POST['seccion']
         $gcm->registra(__FILE__,__LINE__,'Recibimos lista rachivos a borrar desde POST[seleccionado]: ');
         foreach ( $_POST['seleccionado'] as $val ) {
            $urls[]=stripslashes($val);
            }
      } else {
         $gcm->registra(__FILE__,__LINE__,'Se necesita url de contenido para borrar','ERROR');
         }

      foreach ( $urls as $url ) {

         $url = GUtil::desglosar_url($url);

         $gcm->registra(__FILE__,__LINE__,'Eliminamos archivo de la bd: '.$url);

         $SQL =  "DELETE FROM ".$this->prefijo."archivos WHERE url=?";

         if ( $sqlResult = $this->pdo->prepare($SQL) ) {
            if ( $sqlResult->execute(array($url)) ) {
               $gcm->registra(__FILE__,__LINE__,'Eliminado de la base de datos [ '.$url.' ]');
            } else {
               $gcm->registra(__FILE__,__LINE__,'ERROR: Borrando archivo de la base de datos: ['.$url.']. '.$SQL,'ERROR');
            }
         } else {

            $gcm->registra(__FILE__,__LINE__,'No se pudo borrar de la base de datos ['.$url.']'."\nsql: ".$SQL,'ADMIN');

            }
         }

      return TRUE;
      }

   /**
    * gcm_borrar_tabla_pdo
    *
    * Eliminar una tabla de la base de datos
    *
    * @author Eduardo Magrané
    * @version 1.0
    *
    * @param tabla nombre de la tabla a eliminar
    *
    * @return TRUE/FALSE
    *
    */

   function gcm_borrar_tabla_pdo($tabla) {

      global $gcm; 

      $gcm->registra(__FILE__,__LINE__,'Eliminamos tabla de la bd: '.$tabla);

      $SQL =  "DROP TABLE $tabla";

      if ( $sqlResult = $this->pdo->prepare($SQL) ) {
         $sqlResult->execute();
         return TRUE;
      } else {

         if ( $this->pdo_error) {
            $gcm->registra(__FILE__,__LINE__,"No se pudo borrar archivo de la base de datos",'ERROR');
            $gcm->registra(__FILE__,__LINE__,$this->pdo_error);
            $gcm->registra(__FILE__,__LINE__,"SQL::".$SQL);
            }

         return FALSE;

         }

      }

   /** indexar_archivo_pdo
    *
    * Añadimos información sobre un archivo
    *
    * Los parametros son recogidos por recoger_parametros()
    *
    * Si tenemos inicio_descripción en parametros, recogemos el contenido del archivo a partir 
    * de encontrar la parte de texto recibida màs su logintud
    *
    * @author Eduardo Magrané
    * @version 1.0
    *
    * @param $e Evento
    * @param $args Parametros
    *
    * @return TRUE/FALSE
    *
    */

   function indexar_archivo_pdo($e, $args) {

      global $gcm;

      $parametros = recoger_parametros($args);

      if ( isset($parametros['url']) ) {                 // Si tenemos a entrar
         $file = $parametros['url'] ;
         registrar(__FILE__,__LINE__,'Recibimos un archivo para indexar desde args[url] '.$file);
      } elseif ( ! is_array($args) && is_file($args) ) {                      // Si args es la ruta de fichero
         $file = $args ;
         registrar(__FILE__,__LINE__,'Recibimos un archivo para indexar desde args='.$file);
      } elseif ( isset($gcm->memoria['destino']) ) {     // Si se guardo destino en memoria de gcm al mover documento
         $file = $gcm->memoria['destino'] ;
         registrar(__FILE__,__LINE__,'Recibimos un archivo para indexar desde gcm->memoria[destino] '.$file);
      } elseif ( Router::$f ) {                          // Documento actual
         $file = Router::$f ;
         registrar(__FILE__,__LINE__,'Indexamos contenido actual: '.$file);
      } else {
         $gcm->registra(__FILE__,__LINE__,'Se necesita url de contenido para indexar::'.$args,'ERROR');
         return FALSE;
         }

      $file = str_replace('//','/',$file);

      /* Si no es un archivo html nos vamos */

      if ( ! isset($parametros['sin_comprobar_extension']) && substr($file, -5) !== '.html' ) {
         registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$e.','.depurar($args).
            'No tiene extension html, salimos');
         return FALSE;
         }

      /* Literal para el nombre de archivo */

      if ( isset($parametros['literal'])  ) {
         $nombre = $parametros['literal'];
      } else {
         $nombre = str_replace('.html','',basename($file) );
         // Si el nombre del archivo es index, estamos ante el contenido de la sección
         // debemos añadir el nombre de la sección como nombre del archivo.
         if ( $nombre == 'index' ) $nombre = basename( dirname($file) );
         $nombre = literal($nombre);
         }

      $descripcion='';                                                                             ///< Descripción

      // Eliminamos File/es de la url
      $url=str_replace('File/'.$gcm->config('idiomas','Idioma por defecto').'/','',$file);

      $fichero = 'File/'.Router::$ii.'/'.stripslashes(html_entity_decode($url));
      // Si no existe el fichero nos volvemos

      if ( ! @file_exists($fichero) ) {
         registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$e.','.depurar($args).
            'No existe ['.$fichero.'] archivo, salimos');
         return ;
         }

      $fecha_creacion_at = date('Y-m-d H:i:s', filemtime($fichero));
      $fecha_actualizacion_in = $fecha_creacion_at;
      $tags = array();

      $gcm->registra(__FILE__,__LINE__,"Indexamos $file $fecha_creacion_at $fecha_actualizacion_in ");

      // Si tenemos inicio_descripción en parametros, recogemos el contenido del archivo a partir 
      // de encontrar la parte de texto recibida

      $archivo_contenido = $fichero;

      // Si tenemos en parametros 'archivo_descripcion' lo utlizamos para
      // obtener la descripción de su contenido, sino sera el mismo archivo
      // a indexar

      if ( ( isset($parametros['archivo_descripcion']) ) ) {
         $archivo_contenido = $parametros['archivo_descripcion'];
         }

      if ( ( isset($parametros['inicio_descripcion']) ) ) {

         $contenido = file_get_contents($archivo_contenido);
         $posicion = strpos($contenido,$parametros['inicio_descripcion']);
         if ( $posicion === FALSE ) {
            registrar(__FILE__,__LINE__,'No se encontro ['.$parametros['inicio_descripcion'].'] en ['.$archivo_contenido.']','ADMIN');
         } else {
            $posicion = $posicion + strlen($parametros['inicio_descripcion']);
            }

         $contenido = trim(substr($contenido,$posicion));
         $contenido = strip_tags($contenido);

      } elseif ( isset($parametros['descripcion'])) {

         // La descripción nos llega directamente.

         $contenido = strip_tags($parametros['descripcion']);

      } else {

         $contenido = strip_tags(file_get_contents($archivo_contenido)); ///< Contenido del archivo
         }

      while ( $pos1 = strpos($contenido, "{Tags{") ) {
         $pos2 = strpos($contenido, "}}", $pos1);
         if ( $pos1 > 0 && $pos2 < $pos1 ) {
            $gcm->registra(__FILE__,__LINE__,'Error de formato en etiquetas ['.$url.']','ERROR');
            $contenido = str_replace('{Tags{','',$contenido);
         }
         $remplazar = substr($contenido, $pos1, $pos2 - $pos1 + 2);
         $lit = str_replace('{Tags{','',$remplazar);
         $lit = str_replace('}}','',$lit);
         $contenido = str_replace($remplazar,literal($lit),$contenido);
         $tags = array_merge($tags,explode(',',$lit));
         }

      // Si la posición de las etiquetas es menor a maxLong cortamos hasta la etiqueta.
      if ( $pos1 && $pos1 < $this->maxLong ) { $this->maxLong = $pos1; }

      // Hacemos lo mismo con las referencias.
      $posRef = strpos($contenido, "{Ref{");
      if ( $posRef && $posRef < $this->maxLong ) { $this->maxLong = $posRef; }

      $descripcion=trim(substr($contenido,0,$this->maxLong))."...";                                      ///< Descripción para la BD

      // Comprobar si existe ya el archivo en la base de datos en tal caso lo modificamos
      $SQL = "SELECT id from ".$this->prefijo."archivos WHERE url=?";
      if ( $sqlResult = $this->pdo->prepare($SQL) ) {
         $sqlResult->execute(array($url));
         $res = $sqlResult->fetch();
         $archivo_id = $res[0];
      } else {
         $archivo_id = FALSE ;
         }

      if ( $archivo_id ) {
         $SQL = "UPDATE ".$this->prefijo."archivos SET nombre=?, descripcion=?, fecha_actualizacion_in=?";
         $SQL .= " WHERE id=? ";
         $gcm->registra(__FILE__,__LINE__,'Actualizamos: '.$url);
         $comando = $this->pdo->prepare($SQL);
         if ( ! $comando->execute(array($nombre,$descripcion, $fecha_actualizacion_in, $archivo_id) ) ) {
            $err = $this->pdo->errorInfo();
            $err_msg = $err[2];
            $gcm->registra(__FILE__,__LINE__,'ERROR al ejecutar '.$SQL,'ERROR');
            $gcm->registra(__FILE__,__LINE__,$err_msg,'ERROR');
            return FALSE;
            }
      } else {
         // Insertamos datos en base de datos
         $SQL = "INSERT INTO ".$this->prefijo."archivos ( nombre, descripcion, url, fecha_creacion_at, fecha_actualizacion_in ) VALUES ";
         $SQL .= "(?,?,?,?,?)";
         $gcm->registra(__FILE__,__LINE__,'Indexador::Insertamos: '.$url);

         try {

            $comando = $this->pdo->prepare($SQL);
            $comando->execute(array($nombre,$descripcion,$url, $fecha_creacion_at, $fecha_actualizacion_in) );

         } catch (Exception $ex) {

            $mens = $ex->getMessage()."\nsql: ".$SQL.
               "\n$nombre,$descripcion,$url, $fecha_creacion_at, $fecha_actualizacion_in";
            registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$e.','.depurar($args).') '."\n".$mens,'ERROR');
            return FALSE;

            }  

         }

      if ( count($tags) > 0 ) {

         if ( ! $archivo_id ) {
            $archivo_id = $this->pdo->lastInsertId();                                      // Ultimo archivo añadido
            }

         foreach ( $tags as $t ) {

            // recuperar id de la etiqueta si la hay sino la creamos
            $SQL="SELECT id FROM ".$this->prefijo."etiquetas WHERE nombre='".trim($t)."'";
            if ( $sqlResult = $this->pdo->query($SQL) ) {
               $res = $sqlResult->fetch();
               $etiqueta_id = $res[0];
            } else {
               $etiqueta_id = FALSE ;
               }

            if ( ! $etiqueta_id ) {                                                     // No hay etiqueta
               $SQL="INSERT INTO ".$this->prefijo."etiquetas (nombre) VALUES ('".trim($t)."')";
               if ( ! $this->pdo->query($SQL) ) {      // Insertamos nueva etiqueta
                  $err = $this->pdo->errorInfo();
                  $err_msg = $err[2];
                  $gcm->registra(__FILE__,__LINE__,'ERROR al ejecutar '.$SQL,'ERROR');
                  $gcm->registra(__FILE__,__LINE__,$err_msg,'ERROR');

                  }
               $etiqueta_id = $this->pdo->lastInsertId();                                   // Ultima etiqueta añadida
               }

            $SQL = "INSERT INTO ".$this->prefijo."r_etiqueta_archivo VALUES (".$etiqueta_id.",".$archivo_id.")";      // Insertamos relacion

            try {

               $this->pdo->query($SQL);

            } catch (Exception $ex) {

               $gcm->registra(__FILE__,__LINE__,'ERROR al ejecutar '.$SQL);
               $gcm->registra(__FILE__,__LINE__,$ex->getMessage());

               }

            }
         }

      if ( ( $e == 'indexar' )  ) {
         $gcm->registra(__FILE__,__LINE__,'Archivo indexado','AVISO');
      } else {
         $gcm->registra(__FILE__,__LINE__,literal('Archivo indexado'));
         }
      return TRUE;

      }

   /**
    * Generar tablas
    */

   function generar_tablas() {

      global $gcm;

      registrar(__FILE__,__LINE__,'Creación de base de datos para indexación');

      $driver = $this->pdo->getAttribute(constant("PDO::ATTR_DRIVER_NAME"));

      switch($driver) {

         case 'sqlite':

            $SQL = "CREATE TABLE ".$this->prefijo."etiquetas (
                  id INTEGER PRIMARY KEY,
                  nombre CHAR(20) UNIQUE,
                  descripcion VARCHAR(80)
                  )";
            $sqlResult = $this->pdo->query($SQL);

            $SQL="CREATE TABLE ".$this->prefijo."archivos (
               id INTEGER PRIMARY KEY,
               nombre CHAR(150) ,
               descripcion VARCHAR(".$this->maxLong."),
               url CHAR(200) UNIQUE,
               fecha_creacion_at TIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
               fecha_actualizacion_in TIME
               )";
            $sqlResult = $this->pdo->query($SQL);

            $SQL="CREATE TABLE ".$this->prefijo."r_etiqueta_archivo (
               etiquetas_id INTEGER,
               archivos_id INTEGER,
               PRIMARY KEY (etiquetas_id, archivos_id)
               )";
            $sqlResult = $this->pdo->query($SQL);
            break;

         case 'mysql':

            $SQL = "CREATE TABLE ".$this->prefijo."etiquetas (
                  id MEDIUMINT NOT NULL AUTO_INCREMENT,
                  nombre CHAR(20) UNIQUE,
                  descripcion VARCHAR(80),
                  PRIMARY KEY (id)
                  )";
            $sqlResult = $this->pdo->query($SQL);

            $SQL="CREATE TABLE ".$this->prefijo."archivos (
               id MEDIUMINT NOT NULL AUTO_INCREMENT,
               nombre CHAR(150) ,
               descripcion VARCHAR(".$this->maxLong."),
               url CHAR(200) UNIQUE,
               fecha_creacion_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
               fecha_actualizacion_in TIMESTAMP,
               PRIMARY KEY (id)
               )";
            $sqlResult = $this->pdo->query($SQL);

            $SQL="CREATE TABLE ".$this->prefijo."r_etiqueta_archivo (
               etiquetas_id MEDIUMINT NOT NULL,
               archivos_id MEDIUMINT NOT NULL,
               PRIMARY KEY (etiquetas_id, archivos_id)
               )";
            $sqlResult = $this->pdo->query($SQL);
            break;

         default:
            registrar(__FILE__,__LINE__,'El driver ['.$driver.'] para la base de datos no está soportado','ERROR');
            break;

         }

      registrar(__FILE__,__LINE__,'Tablas creadas para '.$driver.' y preparadas para indexación','ADMIN');

      }

   /**
    * Comprobar funcionamiento
    */

   function test() {

      $sql = 'SELECT COUNT(*) FROM '.$this->prefijo.'etiquetas';

      $this->ejecuta_test('Tablas creadas', $this->pdo->prepare($sql));

      }




   }

?>
