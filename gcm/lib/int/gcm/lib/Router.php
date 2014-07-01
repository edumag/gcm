<?php

/**
 * @file      Router.php
 * @brief     Enrutar segun url
 * @ingroup   gcm_lib
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 *
 * Copyright  Copyright (c) 2010, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

/**
 * @class Router
 * @brief Clase destinada a determinar donde estamos, y que queremos hacer.
 * @ingroup gcm_lib
 * 
 * Podemos definir desde la url los eventos a lanzar, formatos, idioma, etc.., 
 * indicando al inicio de la url es decir justo antes de la sección especificada.
 *
 * Ejemplos:
 *
 * http://localhost/proyecto/ajax/test/Seccion/contenido.html
 * .
 * - formato: ajax
 * - evento: test
 * - seccion: Seccion
 * - Contenido: contenido.html
 *
 * Tambien podemos pasar argumentos tanto a modulos como a evento, el truco esta en que
 * si se indentifica un evento o módulo existente, se verifica contenido de las 
 * siguientes partes de la url, en caso de no existir como sección o contenido, se 
 * interpretara que son argumentos, guardandolos en router::args[].
 *
 * http://localhost/proyecto/contenidos/borrar/12/34/Seccion/contenido.html
 * .
 * - Módulo: contenidos
 * - acción: borrar
 * - argumentos: array('12','34')
 * - sección: Seccion
 * - Contenido: contenido.html
 *
 * @warning Los parametros pasados por GET o POST tienen preferencia ante los de la url
 * 
 * Para que todo funcione correctamente es necesario tener en el directorio principal
 * de cada proyecto un archivo .htaccess con el siguiente contenido y tener activado
 * RewriteRule en apache.
 *
 * @code
 * RewriteEngine on
 * RewriteOptions MaxRedirects=20
 * 
 * RewriteRule .*favicon.ico favicon.ico [L]
 * RewriteRule .*temas/(.*)$ temas/$1 [L]
 * RewriteRule .*DATOS/(.*)$ DATOS/$1 [L]
 * RewriteRule .*copiasSeguridad/(.*)$ copiasSeguridad/$1 [L]
 * 
 * # Si estamos llamando a un directorio existenete no ejecutamos reglas
 * RewriteCond %{REQUEST_FILENAME} !-d
 * # Si estamos llamando a un archivo no ejecutamos rewrite
 * RewriteCond %{REQUEST_FILENAME} !-f
 * 
 * RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
 * @endcode
 *
 */

class Router {

   private static $referencia = NULL;      ///< Referancia al objeto (singleton)

   /**
    * Listado de modulos que nos permite reconer segun la url si se esta pidiendo 
    * la acción de alguno de ellos.
    *
    * En caso de detectar que la url lleva un módulo como por ejemplo: 
    * proyecto/contenido/indexar/Seccion/contenido.html, de determina que la acción
    * demandada es la siguiente al nombre del módulo.
    */

   private static $modulos = NULL;

   /**
    * Array con argumentos pasados mediante url como ejemplo:
    *
    * proyecto/cache_http/borrar/todo
    *
    * Al pasar un módulo y una acción se mira que la siguiente
    * sección no exista si es así la añadimos a Router::$args
    *
    */
   
   public static $args;

   /**
    * Listado con los posibles eventos.
    */

   private static $eventos = NULL;

   /**
    * Formatos posibles a la hora de presentar la información
    *
    * Ejemplo: html, ajax, rss, ..., por defecto html
    */

   private static $formatos = NULL;

   /**
    * Listado de idiomas activados
    */

   public static $idiomas = NULL ;

   public static $url;                     ///< Url que nos llega
   public static $dir;                     ///< Base de url relativa al directorio
   public static $dd;                      ///< Directorio con idioma por defecto
   public static $d;                       ///< Directorio con idioma actual
   public static $s;                       ///< Sección
   public static $c;                       ///< Contenido
   public static $f;                       ///< Url del contenido que se pide
   public static $m;                       ///< Modulo que se desea utilizar
   public static $a;                       ///< Accion que se desea realizar
   public static $e;                       ///< Evento que se desea realizar
   public static $i;                       ///< Idioma actual
   public static $ii;                      ///< Idioma por defecto
   public static $base;                    ///< $enlace_relativo + $dir 
   public static $base_absoluta;           ///< http://dominio/ + $dir 
   public static $estamos;                 ///< Nombre de la sección actual
   public static $editar;                  ///< Editamos o vemos
   public static $mime_type;               ///< Tipo de archivo
   public static $formato;                 ///< Formato de presentación, ejemplo html, ajax, rss, etc...
   public static $esBorrador = FALSE;      ///< Saber si el contenido es un borrador
   public static $sin_contenido = FALSE;   ///< En caso de no encontrar contenido definimos variable a TRUE
   public static $sin_traduccion = FALSE;  ///< En caso de pedirse un texto en idioma no predeterminado y no tenerlo.
   public static $forma_comentarios = '<!-- %s -->';  ///< Formato para los comentarios.

   /**
    * prefijo para el enlace relativo, para tener en cuenta si acaba la url con barra o sin ella
    *
    */

   public static $enlace_relativo;

   /** Construir lista de idiomas activados */

   function set_idiomas() {

      global $gcm;

      self::$idiomas = $gcm->config('idiomas','Idiomas activados');

      }

   /** Construir lista de formatos */

   function set_formatos() {
      self::$formatos = array('html', 'ajax', 'rst');
      }

   /** Construir lista de eventos */

   function set_eventos() {

      global $gcm;

      if ( isset($gcm->event->eventos) ) {

         self::$eventos = array_keys($gcm->event->eventos);

         }

      }

   /**
    * Construir listado de módulos
    *
    * Hay dos posibles directorios de módulos el de gcm y el del proyecto
    * recorremos los dos para crear la lista
    *
    * @see modulos
    */

   function set_modulos() {

      global $gcm;

      if ( isset($gcm->event->modulos_activados) ) {
         foreach ( $gcm->event->modulos_activados as $modulo ) {
            if ( ! self::$modulos || ! in_array($modulo,self::$modulos) ) {
               self::$modulos[] = $modulo;
               }
            }
         }
      return;

      if ( isset($gcm->event->eventos) ) {

         foreach ( $gcm->event->eventos as $evento ) {
            foreach ( $evento as $modulo => $accion ) {
               if ( ! self::$modulos || ! in_array($modulo,self::$modulos) ) {
                  self::$modulos[] = $modulo;
                  }
               }
            }

         }

      return;

      // $modulos = glob(GCM_DIR.'modulos/*');
      // $modulos2 = glob('modulos/*');

      // if ( ! empty($modulos2)  ) $modulos = array_merge($modulos, $modulos2);

      // foreach ($modulos as $modulo) {
      //    self::$modulos[] = basename($modulo);
      //    }

      }

   /**
    * Comprobar que no coincidan palabras reservadas de diferentes secciones
    */

   function comprobar_reservadas() {

      $msg = FALSE;
      $msg_comun = "\nLas coincidencias en las palabras reservadas que utiliza Router pueden afectar 
         al comportamiento de Router a la hora de seleccionar la acción que se desea realizar, para 
         más información vease la documentación de ".GUtil::enlace_documentacion('Router');

      $reservadas = array('modulos', 'eventos', 'idiomas', 'formatos');

      foreach ( $reservadas as $reservada ) {

         foreach ( self::$$reservada as $elemento ) {
            if ( $reservada != 'eventos' && in_array($elemento, self::$eventos)  ) {
               $msg .= "$reservada: $elemento coincide con eventos\n";
               }
            if ( $reservada != 'modulos' &&  in_array($elemento, self::$modulos)  ) {
               $msg .= "$reservada: $elemento coincide con modulos\n";
               }
            if ( $reservada != 'idiomas' &&  in_array($elemento, self::$idiomas)  ) {
               $msg .= "$reservada: $elemento coincide con idiomas\n";
               }
            if ( $reservada != 'formatos' &&  in_array($elemento, self::$formatos)  ) {
               $msg .= "$reservada: $elemento coincide con formatos\n";
               }
            }
         }

      if ( $msg ) registrar(__FILE__,__LINE__,$msg.$msg_comun,'ADMIN');


      }

   /** 
    * Solo permitimos una instancia de Router
    */

   static function getInstance() {

      if (  !self::$referencia instanceof self ) {

         $clase = __CLASS__;
         self::$referencia = new $clase;

         }

      return self::$referencia;

      }

   /** 
    * Inicia
    *
    * Desglosamos la url, en caso de tener tambien parametros en REQUEST
    * prevaleceran a los de la misma url
    *
    */

   function inicia($url=NULL) {

      global $gcm;

      $proyecto = $gcm->config('admin','Proyecto');

      $this->set_modulos();
      $this->set_eventos();
      $this->set_formatos();
      $this->set_idiomas();

      // Desglosamos url
      if ( ! isset($url) ) $url = stripslashes((isset($_REQUEST['url'])) ? $_REQUEST['url'] : '' );
      $retorno = self::desglosarUrl($url);

      /* Definir url */
      self::$url = $retorno['url'];
      self::$s = $retorno['s'];
      self::$c = $retorno['c'];
      self::$a = $retorno['a'];
      self::$e = $retorno['e'];
      self::$i = $retorno['i'];
      self::$ii = $retorno['ii'];
      self::$d = $retorno['d'];
      self::$dd = $retorno['dd'];
      self::$m = $retorno['m'];
      self::$args = $retorno['args'];
      self::$formato = $retorno['formato'];
      self::$enlace_relativo = $retorno['enlace_relativo'];
      self::$mime_type = $retorno['mime_type'];
      self::$esBorrador = $retorno['esBorrador'];
      self::$forma_comentarios = $retorno['forma_comentarios'];

      if ( isset($_REQUEST['formato']) ) { self::$formato = $_REQUEST['formato']; }

      if ( isset($_REQUEST['a']) ) { self::$a = $_REQUEST['a']; }
      if ( isset($_REQUEST['e']) ) { self::$e = $_REQUEST['e']; }
      if ( isset($_REQUEST['m']) ) { self::$m = $_REQUEST['m']; }
      if ( isset($_REQUEST['args']) ) { self::$args = $_REQUEST['args']; }

      /*
       * Definir idioma del usuario
       * 
       * Si no tenemos definido idioma en la url cogemos el de sesión y si
       * tampoco tenemos comprobamos idiomas definidos en el navegador y si
       * no tiene ninguno que tengamos activado el por defecto.
       */

      $proyecto = $gcm->config('admin','Proyecto');

      if ( ! self::$i ) {

         if ( isset($_SESSION[$proyecto.'-idioma']) ) {
            self::$i = $_SESSION[$proyecto.'-idioma'];
         } else {
            // comprobar idiomas en navegador
            $sitelang = getenv("HTTP_ACCEPT_LANGUAGE");
            $sitelang = $sitelang[0].$sitelang[1];

            if ( in_array($sitelang,self::$idiomas) ) {    // Si es un idioma activado lo definimos

               $_SESSION[$proyecto."-idioma"] = $sitelang;
               self::$i = $sitelang;

            } else {                                                // sino es un idioma activado cogemos el por defecto
               
               $_SESSION[$proyecto."-idioma"] = self::$ii;
               self::$i = self::$ii ;

               }

            } 
      } else {
         $_SESSION[$proyecto."-idioma"] = self::$i;
         }


      /* Definir directorios de contenido */
      self::$d = "File/".self::$i.'/';

      /*
       * A REQUEST_URI Hay que quitarle las variables que vienen por GET
       * sino se confunde e interpreta que hay mas secciones, como cuando viene por
       * GET ?mod=admin/menuAdmin
       */

      // $uri_p=ereg_replace('\?(.*)$', '', $_SERVER['REQUEST_URI']);

      $uri_p=preg_replace('/\?(.*)$/', '', $_SERVER['REQUEST_URI']);
      $uri = explode("/",$uri_p);
      $scriptName = explode("/",$_SERVER['SCRIPT_NAME'] );
      $sec = array_diff($uri, $scriptName);
      $diferencia = count($uri)-count($scriptName);

      /// Para crear urls relativas al directorio
      self::$dir = '';
      for ($x=0;$x<$diferencia;$x++) {
         self::$dir .= "../";
         }

      self::$base = self::$enlace_relativo.self::$dir;

      // Si es una dirección no relativo hay que quitar
      // hasta la carpeta de proyecto
      $aCarpeta_base=explode('/',$_SERVER['SCRIPT_FILENAME']);
      $carpeta_base = $aCarpeta_base[count($aCarpeta_base)-2];
      // Buscar la primera aparición de la carpeta_base en la url
      $aUrl = explode('/',self::$url);
      $clave = array_search($carpeta_base, $aUrl); 
      if ( $clave > 0 ) {
         self::$url=implode('/',array_slice($aUrl,$clave+1));
         }

      // Si es una dirección con ../../... hay que quitarlos
      //$url = substr($url,strpos($url,'File/')); 
      self::$url = str_replace('../','',self::$url); 

      /**
       * Si viene definido $s o $c desde POST o GET Tiene prioridad
       */

      self::$s = ( isset($_REQUEST['s']) ) ? stripslashes($_REQUEST['s']) : self::$s;
      self::$c = ( isset($_REQUEST['c']) ) ? stripslashes($_REQUEST['c']) : self::$c;

      if ( self::$s == '/' ) self::$s = '';

      /* 
       * Si el fichero final es una imagen la presentamos o un archivo javascript
       * Estos tipos de archivos hay que buscarlos en la carpeta del idioma predeterminado.
       */

      if ( esImagen( self::$url) ) {

         $archivo = self::$dd.self::$url;
         if ( !is_file($archivo) ) {
            // Si no se encuentra la imagen el directorio de la sección actual
            // intentamos encontrarla creando una dirección absoluta.
            registrar(__FILE__,__LINE__,'Router::No encontrado:......'.$archivo);
            // Quitar la parte de la seccion
            $archivo_array = explode('File',$archivo);
            //$archivo = 'http://'.$_SERVER['SERVER_NAME'].dirname($_SERVER['PHP_SELF']).'/File'.$archivo_array[count($archivo_array)-1];
            $archivo = 'File'.$archivo_array[count($archivo_array)-1];
            if ( ! file_exists($archivo) ) {
               registrar(__FILE__,__LINE__,'Router::Tampoco encuentro:..'.$archivo);
               // Suprimimos posible seccion de la url del archivo
               $archivo = str_replace(self::$s,'',$archivo);
               if ( ! file_exists($archivo) ) {
                  registrar(__FILE__,__LINE__,'Router::inicia::Tampoco encuentro:..'.$archivo.
                             "\n".' Salimos');
                  exit();
                  }
               }
            }
         $archivo_contenido = file_get_contents($archivo);
         ob_clean();
         header('Content-type: '. self::$mime_type);
         header ("Expires: ".gmdate("D, d M Y H:i:s",time()+86400).' GMT'); // 86400 segundos, 24 horas 24x60x60
         echo $archivo_contenido;
         exit();
         }

      /*
       * Si recibimos la url hacia una sección sin barra al final la redirigimos
       * para añadirle la barra y que no den problemas los enlaces absolutos
       */

      if ( self::$s !== "" && self::$c == "" && self::$url{strlen(self::$url)-1} !== '/' ) {
         header( "HTTP/1.1 301 Moved Permanently" );
         header("Location: ".self::$base.self::$s.self::$c);
         }

      /* Definir fichero final */

      if (!self::$s && ( !self::$c || self::$c == 'index.html' || self::$c == 'index.php') ) {
         registrar(__FILE__,__LINE__,'Router::inicia::No hay sección ni contenido');
         self::$f=self::$d."index.html";
         self::$c = "index.html";
         self::$estamos="inicio";

      } elseif (!self::$s && self::$c){
         self::$f=self::$d.self::$c;
         registrar(__FILE__,__LINE__,"Router::inicia::No hay seccion");

      } elseif (self::$c && !self::$s) {
         self::$f=self::$d.self::$c;
         registrar(__FILE__,__LINE__,"Router::inicia::No hay seccion, pero si contenido");

      } elseif (self::$s && !self::$c) {
         registrar(__FILE__,__LINE__,"Router::inicia::Hay seccion, pero no hay contenido");
         self::$f=self::$d.self::$s."index.html";
         self::$c = "index.html";
      } elseif (self::$c && self::$s) {
         registrar(__FILE__,__LINE__,"Router::inicia::Hay seccion, y hay contenido");
         self::$f=self::$d.self::$s.self::$c;
         }

      /* 
       * Si hay una seccion estamos es el nombre del directorio para que la clase menu
       * diferencie con los botones
       */

      if (!empty(self::$s)) {
         self::$estamos = basename(self::$d.self::$s);
         }

      // if ( $gcm->au->logeado() ) {
          $this->comprobar_reservadas();
      //    }

      // Crear $base_absoluta
      $base_absoluta = rtrim('http://'.$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']),'/');
      self::$base_absoluta = $base_absoluta.'/'.self::$s;

      self::$base_absoluta = str_replace('/./','/',self::$base_absoluta);

      // Si hay un idioma definido en GET se redefine idioma actual
      if (isset($_GET["idioma"])) { 
         $_SESSION[$proyecto."-idioma"] = $_GET["idioma"]; 
         // Enrutamos para evitar la inconcruencia de tener el idioma en la url y en la
         // variable GET
         registrar(__FILE__,__LINE__,"Recargamos pagina para tener idioma definido",'AVISO');

         header("Location: ".self::$base.$_GET['idioma'].'/'.self::$s.self::$c);
         exit();
         return $_GET['idioma'];
         }

      registrar(__FILE__,__LINE__,'Variables en Router',FALSE,depurar(get_class_vars(__CLASS__)));

      }

   /** 
    * desglosarUrl nos devuelve una matriz con la información
    * relevante de una url que pasemos como argumento
    *
    * @param url Url del archivo que deseamos inspeccionar
    *
    * @return Array Arreglo con la información
    */

   static function desglosarUrl($url) {

      global $gcm;

      /** Instancia de contenidos para poder verificarlos */

      $contenidos = $gcm->event->instancia_modulo('contenidos');

      $s               = NULL;            
      $c               = NULL;            
      $dd              = NULL;            
      $d               = NULL;            
      $ii              = NULL;            
      $i               = NULL;            
      $a               = NULL;            
      $m               = NULL;            
      $args            = NULL;
      $e               = NULL;            
      $enlace_relativo = NULL;
      $mime_type       = NULL;      
      $formato         = NULL;      

      $retorno = array();
      $url = str_replace('//','/',$url);

      /** Definir idioma por defecto */

      $ii = $gcm->config('idiomas','Idioma por defecto');

      $proyecto = $gcm->config('admin','Proyecto');

      /* Si tenemos Sección antes de File/ la quitamos */

      if ( strpos($url,'/File/') !== FALSE ) {
         $url = substr($url,strpos($url,'/File/'));
         }

      /* Si tenemos File/ lo quitamos */

      $url = preg_replace('/^File\//','',$url);

      /* Comprobar palabras reservadas en url */

      $continuar = TRUE;

      while ( $continuar ) {

         $continuar = FALSE;

         /* Comprobar modulos */

         $secciones = explode('/',$url);

         if ( end($secciones) != 'proyecto.js'  && end($secciones) != 'proyectos.css' ) {

            if ( in_array($secciones[0], self::$modulos)  ) {
               $m = $secciones[0];
               array_shift($secciones);
               $a = ( isset($secciones[0]) ) ? $secciones[0] : FALSE;
               array_shift($secciones);

               /* Si la siguiente sección no existe es un argumento */
               while ( isset($secciones[0]) && ! $contenidos->verificar_seccion($secciones[0]) && $secciones[0] != 'proyectos.css' && $secciones[0] != 'proyecto.js' ) {
                  $args[] = $secciones[0];
                  array_shift($secciones);
                  }

               $url = implode('/',$secciones);
               $continuar = TRUE;
               }

            }

         /* Comprobar eventos en url */

         $secciones = explode('/',$url);

         if ( in_array($secciones[0], self::$eventos)  ) {
            $e = $secciones[0];
            array_shift($secciones);

            /* Si la siguiente sección no existe es un argumento */
            while ( isset($secciones[0]) && ! $contenidos->verificar_seccion($secciones[0]) && $secciones[0] != 'proyectos.css' && $secciones[0] != 'proyecto.js' ) {
               $args[] = $secciones[0];
               array_shift($secciones);
               }

            $url = implode('/',$secciones);
            $continuar = TRUE;
            }

         /* Comprobar formatos en url */

         $secciones = explode('/',$url);

         if ( in_array($secciones[0], self::$formatos)  ) {
            $formato = $secciones[0];
            array_shift($secciones);
            $url = implode('/',$secciones);
            $continuar = TRUE;
            }

         // Si no tenemos formato por defecto html
         if ( !$formato  ) { $formato = 'html'; }

         /* Comprobar idiomas en url */

         $secciones = explode('/',$url);

         if ( in_array($secciones[0], self::$idiomas)  ) {
            $i = $secciones[0];
            array_shift($secciones);
            $url = implode('/',$secciones);
            $continuar = TRUE;
            }

         }

      $dd = 'File/'.$ii.'/';

      /* Definir mime_type */

      // La url puede venir con los subdirectorios de la dirección http, debe limpiarse sino nos dara error
      // al ir a buscar información , ya que los subdirectorios son respectivos de la url pero desde php nos vamos 
      // del directorio del proyecto.
      $url_sin_subdirectorios = str_replace('../','',$url);
      $mime_type = GUtil::tipo_de_archivo($dd.$url_sin_subdirectorios);

      if ( substr_count($url,".btml") != 0) {
         $esBorrador = TRUE ;
      } else {
         $esBorrador = FALSE ;
         }

      /* definir formato de comentarios */

      switch($mime_type) {

         case 'text/css':
            $forma_comentarios = '/* %s */';
            break;
         case 'application/x-javascript':
            $forma_comentarios = '/* %s */';
            break;
         default:
            $forma_comentarios = self::$forma_comentarios;
            break;
         }

      /* Si es un archivo separamos seccion de contenido */

      if ( empty($mime_type) || $mime_type != 'text/directory' ) {

         $url_array = explode("/",$url);
         $ultimo = $url_array[count($url_array)-1];
         unset($url_array[count($url_array)-1]);
         $s = implode('/',$url_array);
         $c = $ultimo;

      } else {

         if ( $url == 'index.html' ) {
            $s = '';
            $c = 'index.html';
         } else {
            $s = $url;
            $c= '';
            }

         }

      /* Limpiamos sección */

      if ( !empty($s) ) $s = ( $s{strlen($s)-1} == '/' ) ? $s : $s.'/' ;
      if ( $s == '/' ) $s = '';
      $s = str_replace('../','',$s);

      /* Prefijo para enlaces */

      if ( $mime_type == 'text/directory' && comprobar_barra($url) != $url ) {
         $secciones = explode('/',$s);
         $ultimo = $secciones[count($secciones)-2];
         $enlace_relativo = ( empty($ultimo) ) ? '' : './';
      } else {
         $enlace_relativo = './';
         }


      $i = ( $i ) ? $i : FALSE ;

      $d = ( $i ) ? 'File/'.$i.'/' : FALSE ;

      /* Limpiar url de carpeta inicial File/<idioma> */

      $url=str_replace($dd,'',$url);
      $url=str_replace($d,'',$url);

      $retorno['url']               = $url;
      $retorno['ii']                = $ii;
      $retorno['i']                 = $i;
      $retorno['s']                 = $s;
      $retorno['c']                 = $c;
      $retorno['dd']                = $dd;
      $retorno['d']                 = $d ;
      $retorno['a']                 = $a;
      $retorno['m']                 = $m;
      $retorno['args']              = $args;
      $retorno['e']                 = $e;
      $retorno['enlace_relativo']   = $enlace_relativo;
      $retorno['mime_type']         = $mime_type;
      $retorno['formato']           = $formato;
      $retorno['esBorrador']        = $esBorrador;
      $retorno['forma_comentarios'] = $forma_comentarios;

      return $retorno;

      }


   }

?>
