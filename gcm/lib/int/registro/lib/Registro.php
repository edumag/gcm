<?php

/**
 * @file Registro.php
 * @brief Sistem de registros
 * @ingroup registro
 *
 * Nos permite guardar los registros que se van generando
 *
 * @package Registro
 * @author    Eduardo Magrané <eduardo mamedu com>
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU/GPL
 * @version   SVN $Id: Registro.php 661 2012-11-02 11:26:56Z eduardo $ 
 */

require_once(GCM_DIR.'lib/int/gcm/lib/helpers.php');

/** Modulo para el registro de los mensajes de la aplizació
 *
 * Inicamos el módulo para tener una instancea del mismo desde el inicio
 * y así tenerlo accesible desde el principio, generamos la conexíon con la
 * base de datos, y en caso de no existir la cremos.
 *
 * Declaramos privado al constructor para asegurarnos de que se utliza getInstance()
 * y utilizamos siempre la misma instancia.
 *
 * Tenemos tres opciones para la conexión con la base de datos.
 *
 * - Pasar un objeto PDO al constructor.
 * - Pasar un directorio, esto hara que Registros genere archivos sqlite 1 por día
 * - Pasar un archivo, supondremos es una base de datos sqlite
 *
 * Como guardar todos los registros hace muy lenta la aplicación, guardaremos solo los registros
 * importantes como ADMIN y ERROR, guardando el resto en array para poder ser depurados o enviados 
 * por email al enviar un error.
 *
 * Uso:
 * @code
 * $reg = Registro::getInstance();
 * $reg->registra(__FILE__,__LINE__,'Mensaje a registrar','ADMIN');         // Regsitro tipo ADMIN
 * $reg->ver_registros('sesion=21221212 AND tipo=ADMIN'); // Devolver registros de la session x que sean del tipo ADMIN 
 * @endcode
 *
 * @author Eduardo Magrané
 *
 * @todo Terminar formulario para ver registros con filtros predefinidos
 * @ingroup registro
 */

class Registro {

   public $sufijo   = '';                                                 ///< Sufijo para tabla en Base de datos
   public $tipos_admitidos;                                               ///< Tipos de registros dentro del $nivel
   public $conexion = NULL;                                               ///< Conexión con base de datos

   /**
    * Si recibimos algun registro de tipo ERROR pasamos $errores a TRUE
    */

   private $errores = FALSE;

   /**
    * Array con los registros de la pagina actual
    */

   public $registros;

   /**
    * Niveles de registro
    *
    * DEBUG   Solo sirven para depurar y saber lo que hace la aplicación
    * NORMAL  Sin uso explicito
    * ADMIN   Mensajes para el administrador
    * AVISO   Mensajes para el usuario
    * ERROR   Mensajes de error 
    */
   
   public  $tipos_registros = array('DEBUG', 'NORMAL', 'ADMIN', 'AVISO', 'ERROR');

   public  $sesion = NULL;                                                           ///< Marca de sesión para los registros
   public  $limite_mensajes = 500;                                                   ///< Limite para los mensajes de registro
   public  $limite_descripcion = 1300;                                               ///< Limite para la descripción
   public  $base_datos;                                                              ///< Base de datos por defecto

   private $motor_bd;             ///< Motor que se utiliza en PDO (mysql o sqlite)

   /** 
    * Iniciamos módulo, creando instancia de PDO segun parametro para Registro
    *
    * @param $base_datos Forma de conectar a base de datos
    * @param $sufijo     Sufijo si se desea diferenciar entre tablas de distintos proyectos
    */

   function __construct($base_datos=NULL, $sufijo='') {

      $this->sufijo = $sufijo;

      /* Por defecto registramos solo los tipos de avisos */

      $this->nivel('AVISO');

      if ( $base_datos instanceof PDO ) {

         $this->conexion = $base_datos;

      } elseif ( is_dir($base_datos) ) {

         $this->base_datos = $base_datos.'/'.date("d-m-Y").'_registros.bd';
         $this->conexion = new PDO('sqlite:'.$this->base_datos);

      } else {
         
         $this->base_datos = $base_datos;
         $this->conexion = new PDO('sqlite:'.$this->base_datos);
         }

      // comprobamos la existencia de la tabla en base de datos

      $this->existe_tabla($this->sufijo.'registros');

      /** Buscamos la ultima sesión para incrementarla */

      $ultima_sesion = $this->ultima_sesion();
      $this->sesion = $ultima_sesion + 1;

      $this->motor_bd = $this->conexion->getAttribute(constant("PDO::ATTR_DRIVER_NAME"));
      $this->registra(__FILE__,__LINE__,"Sesion: ".$this->sesion."\n".'Se inicia registro a las '.strftime("%d de %b del %Y a las %r").' en '.$this->base_datos,'NORMAL');

      }

   /**
    * Existe tabla
    */

   function existe_tabla($tabla) {

      try {

         $sql = 'SELECT COUNT(*) FROM '.$tabla;

         if ( ! $sth = $this->conexion->prepare($sql) ) {

            $this->crear_tabla($tabla);

         } else {

            if (!$sth->execute()) {

               $this->crear_tabla($tabla);

               }

            }
      } catch (Exception $ex) {

         $this->crear_tabla($tabla);
         // echo '<div class="error">';
         // echo 'Error en la conexión con base de datos';
         // echo '<br />'.$ex->getMessage();
         // echo '</div>';
         // return FALSE;

         }

      return TRUE;

      }

   /**
    * crear_tabla
    *
    * En caso de que no exista aun la tabla de la base de datos hay que crearla.
    *
    * Diferencias entre mysql y sqlite:
    *
    * - Autoincrement en sqlite es automatico al ser el campo "primery key"
    *
    */

   function crear_tabla($tabla) {

      if ( $this->conexion->getAttribute(constant("PDO::ATTR_DRIVER_NAME")) == 'sqlite' ) {
         $autoincrement = '';
      } else {
         $autoincrement = 'AUTO_INCREMENT';
         }

      $SQL = "CREATE TABLE $tabla (
            id INTEGER PRIMARY KEY $autoincrement,
            sesion INTEGER,
            fecha INT(14),
            tipo VARCHAR(10),
            fichero VARCHAR(100),
            linea INTEGER,
            mensaje VARCHAR(".$this->limite_mensajes."),
            descripcion VARCHAR(".$this->limite_descripcion.")
            )";

      if ( ! $sqlResult = $this->conexion->query($SQL) ) {

         $err = $this->conexion->errorInfo();
         throw new Exception("Error al crear tabla de ".$tabla."\n".$err[2]);
         return FALSE;

         }

      }

   /** Saber si hemos recibido algun error */

   function errores() { return $this->errores; }

   /** Instance única */

   public static function getInstance() {

      static $objetoRegistro;

      if ( !isset($objetoRegistro) ) {
         $objetoRegistro = new Registro();
         }

      return $objetoRegistro;

      }

   /**
    * Saber si un tipo de registro esta dentro
    * del nivel especificado.
    *
    * @param $tipo Tipo de nivel
    */

   function tipoAregistro($tipo) {

      if ( in_array($tipo,$this->tipos_admitidos) ) { 
         return TRUE;
      } else {
         return FALSE;
         }

      }

   /** 
    * Definimos nivel de registro, Solo se añaden los registros de nivel
    * especificado o superior.
    *
    * @param $nivel Por defecto DEBUG que es el minimo y abarca a todos
    */

   function nivel($nivel='AVISO') {

      if ( ! $this->validar_tipo($nivel) ) {
			throw new Exception('Nivel '.$nivel.' inexistente');
         return FALSE;
         }

      $marca = NULL;
      $this->tipos_admitidos = array();

      foreach( $this->tipos_registros as $tipo ) {

         if ( $tipo ==  $nivel || $marca ) {
            $this->tipos_admitidos[] = $tipo;
            $marca = TRUE;
            }

         }

      $this->registra(__FILE__,__LINE__,'Nivel de registros a '.$nivel. "\nTipos adminitidos: ".depurar($this->tipos_admitidos));

      return TRUE;

      }

   /** Devolver numero de la última sesión */

   function ultima_sesion() {

      $sql = 'SELECT sesion FROM '.$this->sufijo.'registros order by sesion desc limit 1';
      $result = $this->conexion->query($sql,PDO::FETCH_NUM);

      if (!$result) {
         return 0;
      } else {
         foreach ( $result as $ultimo ) {
            return $ultimo[0];
            }
         }
      }

   /** Validar tipo
    *
    * Comprobar que el tipo de registro especificado esta dentro de los tipos de
    * registros aceptados.
    *
    * @param string $tipo Tipo de registro
    *
    */

   function validar_tipo($tipo) {

      if ( ! in_array($tipo, $this->tipos_registros) ) {
			throw new Exception('Validacion de tipo negativa ['.$tipo.']');
			return FALSE;
      } else {
         return TRUE;
         }

      }

   /**
    * Registrar un mensaje si esta dentro de los tipos admitidos por el nivel de
    * registro especificado.
    *
    * @param m Mensaje
    * @param tipo Tipo de mensaje, @see registros
    * @param archivo Archivo Si no especificamos archivo sera el archivo por defecto
    *
    * @return TRUE/FALSE
    *
    * @author Eduardo Magrané
    * @version 1.0
    */

   function registra($fichero,$linea, $m, $tipo = 'DEBUG', $descripcion = NULL) {

      if ( ! $tipo ) $tipo = 'DEBUG';

      $tiempo = time();

      if ( !$m || $m == '' ) {
			throw new Exception('Se requiere de contenido a registrar');
			return false;
         }

      if ( ! $this->validar_tipo( $tipo ) ) {
			throw new Exception('Tipo_incorrecto');
			return false;
         }

      $salida = '';

      /* Si el mensaje es un array con diferentes mensajes los agrupamos
       * para hacer la insercción al archivo más rapido
       */

      if ( is_array($m) )  {
         $salida = '';
         foreach ( $m as $key => $mensaje ) {
            if ( is_array($mensaje) ) {
               foreach ($mensaje as $key2 => $valor2)  {
                  if ( is_array($valor2) ) {
                     $salida .= $key2." => ".var_export($valor2,true)."\n";
                  } else {
                     $salida .= $key2." => ".$valor2."\n";
                  }
               }
            } else {
               $salida .= $key." => ".$mensaje."\n";
            }
         }
      } else {
         $salida = $m;
         }

      /* Si la salida es mayor al limite estipulado para `mensaje` lo dividimos 
       * Añadiendo una marca '@->' para poder volverlos a juntar al presentarlos
       */

      while ( strlen($salida) > $this->limite_mensajes ) {

         $salidaArray[] = substr($salida,0,$this->limite_mensajes).'@->';
         $salida = substr($salida,$this->limite_mensajes);

      }

      $salidaArray[] = $salida;

      foreach ( $salidaArray as $salida ) {
         $nr = ( count($this->registros) > 0 ) ? count($this->registros) : 0 ;
         $this->registros[$nr] = array($nr,$this->sesion,$tiempo,$tipo,$fichero,$linea,$salida,$descripcion);
         }

      /* Si no es del tipo admitido solo lo guardamos en array no en base de datos */

      if ( ! $this->tipoAregistro($tipo) ) return;

      // Añadimos mensaje a sessión para ser presentado al usuario cuendo se requiera.

      $_SESSION[$this->sufijo.'registros'][$tipo][] = array($fichero,$linea,$salida);

      // Añadimos a base de datos

      foreach ( $salidaArray as $salida ) {

         if (  $tipo == 'ERROR' ) {

            $this->errores = TRUE;

            if ( GCM_DEBUG  ) {

               echo "<div class='error'>";
               echo "<pre>$salida</b></pre>";
               echo '<a href="vim://'.$fichero.'@'.$linea.'">';
               echo "<p>$fichero : $linea</p>";
               echo "</a>";
               echo "</div>";
               }

            }

         $SQL = "INSERT INTO ".$this->sufijo."registros ( sesion, fecha, tipo, mensaje, fichero, linea, descripcion ) VALUES ";
         $SQL .= "(?,?,?,?,?,?,?)";
         $comando = $this->conexion->prepare($SQL);
         if ( !$comando ) {
            $err = $this->conexion->errorInfo();
            $men_error = $err[2];
            throw new Exception('Error al preparar sql'.$men_error);
            }

         if ( ! $comando->execute(array($this->sesion,$tiempo,$tipo,$salida, $fichero, $linea, $descripcion) ) ) {
            $err = $this->conexion->errorInfo();
            $men_error = $err[2];
            throw new Exception('Error al insertar registro: '.$men_error);
            }

         }
      return $tiempo.'::'.$tipo.'::'.$salida;

      }

   /** Devolver los registros
    *
    * @param string $tipo Tipo de registro a devolver, sino todos
    * @param string $condicion parte de la sql detras del WHERE
    */

   function ver_registros($tipos = NULL, $condicion = NULL ) {

      $fecha_mysql  = "FROM_UNIXTIME( fecha )";
      $fecha_sqlite = "datetime(fecha, 'unixepoch', 'localtime')";

      if ( $this->conexion->getAttribute(constant("PDO::ATTR_DRIVER_NAME")) == 'sqlite' ) {
         $fecha = $fecha_sqlite;
      } else {
         $fecha = $fecha_mysql;
         }

      $sql0 = 'SELECT id, sesion, '.$fecha.', tipo, fichero, linea, mensaje, descripcion ';
      $sql1 = 'FROM '.$this->sufijo.'registros WHERE 1 ';

      /* Comprobar tipo */

      if ( is_array($tipos) ) {

         $sql1 .= ' AND (';

         foreach ( $tipos as $tipo ) {
            if ( $tipo == $tipos[0] ) {
               $sql1 .= ' tipo = "'.$tipo.'"';
            } else {
               $sql1 .= ' OR tipo = "'.$tipo.'"';
               }
            }
         $sql1 .= ')';
         
      } else {
         
         if ( $tipos ) {
            $sql1 .= ' AND tipo="'.$tipos.'"';
            }

         }

      /* Si no hay condición filtramos por sesión actual */

      if ( $condicion ) {
         $sql1 .= ' AND '.stripslashes($condicion) ;
      } else {
         // No limitamos asesión actual
         //$sql1 .= ' AND sesion='.$this->sesion;
      }

      $consulta = $sql0.$sql1;
      $result = $this->conexion->prepare($consulta);

      if ( ! $result  ) { return FALSE; }

      $result->execute();
      $retorno = $result->fetchAll(PDO::FETCH_NUM);

      /* Comprobar numero de filas devueltas */

      if (!$result) {

         $err = $this->conexion->errorInfo();
         $men_error = $err[2];
         throw new Exception('Error ejecutando query: '.$men_error. ' sql: '.$consulta);

      } elseif ( count($retorno) > 0 ) {

         /// @todo Verificar que realmente devuelve algo o no
         $this->registra(__FILE__,__LINE__,'Registro::Con resultado::'.$sql1);
         return $retorno;

      } else {
   
         $this->registra(__FILE__,__LINE__,'Registro::Sin resultados::'.$sql0.$sql1);
         return FALSE;

         }

      }

   /**
    * Devolver registros en sesión
    */

   function registros_sesion() {

      if ( ! isset($_SESSION[$this->sufijo.'registros']) || empty($_SESSION[$this->sufijo.'registros'])  ) { return FALSE; }

      $registros = $_SESSION[$this->sufijo.'registros']; 
      unset($_SESSION[$this->sufijo.'registros']);
      return $registros;

      }

   /**
    * Borrado de registros para no acumular 
    *
    * @param $dias Días pasados para borrar registros 
    */

   function borrado_registros_antiguos($dias) {

      if ( $this->motor_bd == 'sqlite' ) {

         $horas = ( $dias * 24 );
         borrar_archivos_viejos($horas, dirname($this->base_datos).'/*' );

      } else {

         $fecha_unix_borrado = time() - ( $dias * 24 * 60 * 60 );

         $condicion = "fecha < ".$fecha_unix_borrado;

         $sql = "DELETE FROM ".$this->sufijo.'registros WHERE '.$condicion;
         $result = $this->conexion->prepare($sql);
         $result->execute();

         }

      }

   }
?>
