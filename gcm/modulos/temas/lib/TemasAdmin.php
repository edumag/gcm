<?php

/**
 * Gcm - Gestor de contenido mamedu
 *
 * @category Gcm
 * @package Modulos
 * @subpackage Temas
 * @author    Eduardo Magrané
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU/GPL
 * @version   SVN $Id: Temas.php 371 2010-10-07 10:23:45Z eduardo $ 
 */

/** 
 * @brief Sistema para la utilización de temas
 *
 * Con esta clase se puede tener un tema por defecto que recoja los archivos css y html
 * de cada módulo.
 *
 * Permitiendo añadir los ficheros del tema seleccionado que sobreescribiran a los del 
 * tema por defecto.
 *
 * Presentamos un formulario para que pueda modificarse un fichero en concreto que pasara
 * a formar parte del tema que estemos editando.
 *
 * Si el archivo es un css se editara en plano si es html con el editor web.
 *
 * Tener en cuenta que los archivos css admiten código php que nos facilita la modificación
 * de variables que podamos utilizar dentro de los css.
 *
 * Dentro de una carpeta de un tema determinado se espera que hayan las siguientes carpetas:
 *
 * - css:    Archivos css que suplantes a los del tema por defecto.
 * - html:   Archivos html.
 * - iconos: Iconos del tema.
 * - js:     Archivos javascript.
 *
 * Dentro del archivo html se encuentra la plantilla principal.html que es la plantilla con los
 * bloques correspondientes que seran interpretados por los eventos. Así si tenemos un bloque 
 * {columna} Gcm lanza el evento columna para que los módulos lo rellenen con contenido.
 *
 * @todo 
 *
 * - Previsualizar
 * - Borrar fichero de tema
 * - Configurar tema por defecto en administrador de temas
 * - Aplicar plantilla() en otros módulos
 * - Metodo icono que nos presente el icono correspondiente o su grafica alternativa si no
 *   lo encuentra.
 * - Separar logica de administración, o más bien hacer una librería independiente de gcm.
 *
 */

class TemasAdmin extends Temas {

   function __construct($tema_actual='') {

      parent::__construct($tema_actual);

      }

   /**
    * Comprobar que existen los directorios del tema en proyecto 
    *
    * sino es así los creamos
    *
    */

   function comprobar_directorios($dir) {

         $dirs = explode('/',$dir);

         $n = '';
         foreach ( $dirs as $d) {
            $n = $n.$d.'/';
            if ( !is_dir($n) ) {
               if ( !mkdir($n, 0700) ) {
                  trigger_error(literal('No se pudo crear directorio, compruebe permisos',3),E_USER_ERROR);
                  return FALSE;
                  }
               }
            }

      return TRUE;
      }

   /**
    * Formulario para subir archivo css
    *
    * Se puede subir un archivo css basado en proyecto.css modificado.
    *
    * Buscaremos las partes correspondientes a cada fichero y los añadiremos
    * en el tema actual.
    */

   function formulario_fichero_css() {

      global $gcm;

      include($this->ruta('temas','html','form_fichero_css.html'));

      }

   /**
    * Recogemos fichero proyecto.css modificado
    */

   function fichero_css($e,$args=NULL) {

      global $gcm;

      $this->anularEvento('contenido','temas');
      $this->anularEvento('titulo','temas');

      $gcm->titulo = literal('Procesar fichero proyecto.css modificado',3);

      $fichero = $_FILES['fichero']['name'];
      $fichero_tmp = $_FILES['fichero']['tmp_name'];
       
      // Comprueba que se ha indicado un fichero en el formulario
      if ($fichero == "") {
         registrar(__FILE__,__LINE__
            ,__CLASS__.'->'.__FUNCTION__.'('.$e.','.depurar($args).') No se ha especificado fichero'
            ,'ERROR');
         return FALSE;
         }

      $vlineas = file($fichero_tmp);

      $ficheros    = array();
      $fichero     = FALSE;
      $colores     = array();
      $count_color = 0;

      foreach ($vlineas as $linea) {

         // Si la linea contiene 'fichero:'
         if ( stripos($linea,'fichero:')  ) {
            list($literal,$fichero,$nada) = explode(':',$linea);
            $ficheros[$fichero] = '';
            $count_color=0;
         // Si la linea contiene 'acaba:'
         } elseif ( stripos($linea,'acaba:') ) {
            $fichero = FALSE;
         // Si tenemos $fichero
         } elseif ( $fichero ) {
            // Buscamos colores
            // Si tenemos color, background, border 
            if ( preg_match("/border|background|color/",$linea) ) {
               foreach ( explode(':',$linea) as $bloque ) {
                  foreach ( explode(' ',$bloque) as $palabra ) {
                     $palabra = str_replace(';','',$palabra);
                     $palabra = trim($palabra);
                     // separamos palabras buscamos # con seis caracteres más o tres
                     if (preg_match ('/^#[a-f0-9]{6}$/', $palabra) || preg_match ('/^#[a-f0-9]{3}$/', $palabra) ) {
                        if ( !in_array($palabra,$colores) ) {
                           $clave = basename($fichero,'.css').'-'.sprintf("%02d",$count_color);
                           $count_color++;
                        } else {
                           $clave = array_search($palabra, $colores); 
                           }
                        $colores[$clave]=$palabra;
                        $mod = '<?=$this->color("'.$clave.'")?>';
                        $linea = str_replace($palabra,$mod,$linea);
                     } elseif ( preg_match("/rgba\((.*)\){1}/",$palabra,$resultado) ) {
                        $limpia = "rgba(".str_replace(')','',$resultado[1]).")";
                        if ( !in_array($limpia,$colores) ) {
                           $clave = basename($fichero,'.css').'-'.sprintf("%02d",$count_color);
                           $count_color++;
                        } else {
                           $clave = array_search($limpia, $colores); 
                           }
                        $colores[$clave]=$limpia;
                        $mod = '<?=$this->color("'.$clave.'")?>';
                        $linea = str_replace($limpia,$mod,$linea);
                        }
                     }
                  }
               } elseif ( preg_match("/rgba\((.*)\){1}/",$linea,$resultado) ) {
                  $limpia = "rgba(".str_replace(')','',$resultado[1]).")";
                  if ( !in_array($limpia,$colores) ) {
                     $clave = basename($fichero,'.css').'-'.sprintf("%02d",$count_color);
                     $count_color++;
                  } else {
                     $clave = array_search($limpia, $colores); 
                     }
                  $colores[$clave]=$limpia;
                  $mod = '<?=$this->color("'.$clave.'")?>';
                  $linea = str_replace($limpia,$mod,$linea);
               }
            // Buscamos iconos o imagenes de módulos
            if ( preg_match("/url\(.*\)/",$linea, $coincidencias, PREG_OFFSET_CAPTURE) ) {

               $url = str_replace('url(','',$coincidencias[0][0]);
               $url = str_replace(')','',$url);
               $url = str_replace('\'','',$url);
               $url_split = explode('/',$url);
               $nombre = end($url_split);

               if ( empty($nombre) ) {

                  registrar(__FILE__,__LINE__,'Url de imagen sin contenido en '.$fichero."\nLinea: ".$linea,'AVISO');

               } elseif ( in_array('File',$url_split) || in_array('http',$url_split) ) {

                  /* Si la imagen es de contenido o es un enlace externo no hacemos nada */
                  registrar(__FILE__,__LINE__,'Imagen de contenido no hacemos nada en '.$fichero."\nLinea: ".$linea,'AVISO');

               } else {


                  if ( count($url_split) < 3 ) {
                     registrar(__FILE__,__LINE__,"Fichero: ".$fichero." Imagen fuera de sistema: ".depurar($url_split),'AVISO');
                  } else {
                     $tipo = $url_split[count($url_split)-2];
                     $modulo = $url_split[count($url_split)-3];
                     $nueva_url = Router::$dir.$gcm->event->instancias['temas']->ruta($modulo,$tipo,$nombre);
                     if ( ! $nueva_url ) {
                        registrar(__FILE__,__LINE__,"Fichero: ".$fichero." Imagen fuera de sistema: ".depurar($url_split),'AVISO');
                     } else {
                        $remplazo = 'url(\'<?=Router::$dir.$this->ruta("'.$modulo.'","'.$tipo.'","'.$nombre.'")?>\')';
                        $imagenes[] = $nueva_url;
                        $linea = str_replace($coincidencias[0][0],$remplazo,$linea);
                        }
                     }

                  }
               }
            $linea = trim($linea,"\n\r");
            if ( $linea != '' ) $ficheros[$fichero] .= "\n".$linea;
            }

         }

      if ( empty($ficheros) ) {
         registrar(__FILE__,__LINE__,
            __CLASS__.'->'.__FUNCTION__.'('.$e.','.depurar($args).') No se pudo especificar contenido'
            ,'ERROR');
         echo 'El archivo css que se suba debe ser una replica de proyecto.css modificado, sin perder las marcas especificas, que nos indican a que archivo pertenece el contenido';
         return FALSE;
         }


      if ( isset( $colores ) ) echo '<br />Numero de colores  procesados: <b>'.count($colores).'</b>';
      if ( isset( $ficheros ) ) echo '<br />Numero de ficheros procesados: <b>'.count($ficheros).'</b>';
      if ( isset( $imagenes ) ) echo '<br />Numero de imágenes procesados: <b>'.count($imagenes).'</b>';

      echo '<br /><h2>Ficheros procesados</h2>';
      foreach ( $ficheros as $key => $valor ) {
         echo '<br />- ',$key;
         $this->comprobar_directorios($this->dir_tema_actual.'modulos/'.dirname($key));
         file_put_contents($this->dir_tema_actual.'modulos/'.$key,$valor,LOCK_EX);
         }

      $fichero_colores_tema = $this->dir_tema_actual.'modulos/temas/css/colores.php';

      $salida = "<?php\n\n/* Fichero procesado por TemasAdmin.php */";
      foreach ( $colores as $key => $valor ) {
         $salida .= "\n".'$colores[\''.$key.'\'] = \''.$valor.'\';';
         }
      $salida .= "\n?>";
      file_put_contents($fichero_colores_tema,$salida,LOCK_EX);

      if ( isset($imagenes) && count($imagenes) > 0 ) {
         echo '<br /><h2>Imágenes procesadas</h2>';
         foreach ($imagenes as $imagen) {
            echo '<br />'.$imagen.'<br /><img src="'.$imagen.'" />';
            }
         }
      }

   /**
    * Formulario para temas
    */

   function formulario_ficheros() {

      global $gcm;

      $tipos = array('css','html','js');

      include($this->ruta('temas','html','form_ficheros_tema.html'));

      }

   /**
    * Editamos archivo escogido, si no se encuentra en el tema que estamos modificando
    * lo copiamos del tema por defecto.
    *
    * @author Eduardo Magrané
    * @version 1.0
    *
    * @param $e Evento
    * @param $args Argumentos
    *
    */

   function editar_fichero($e, $args='') {

      global $gcm;

      $this->anularEvento('contenido','temas');
      $this->anularEvento('titulo','temas');

      if ( isset($_POST['editar_xdfecto']) && !empty($_POST['editar_xdfecto']) ) {
         $archivo = $_POST['editar_xdfecto'];
      } elseif ( isset($_POST['editar_tema']) && !empty($_POST['editar_tema'])  ) {
         $archivo = $_POST['editar_tema'];
      } else {
         registrar(__FILE__,__LINE__
            ,__CLASS__.'->'.__FUNCTION__.'('.$e.','.depurar($args).') No se especifico fichero para editar'
            ,'ERROR');
         return;
         }

      $gcm->titulo = 'Editando <b>'.$archivo.'</b>';

      $url_archivo = $this->dir_tema_actual.'modulos/'.$archivo;

      // Buscamos informacion de archivo

      /* Si es necesario creamos directorios padre */

      $dir = dirname($url_archivo);

      if ( ! $this->comprobar_directorios($dir) ) return FALSE; 

      /* Si no existe fichero copiamos el del modulo */

      if ( !is_file($url_archivo) ) {
         /* comprobamos si es de un módulo de gcm o de la misma aplicación */
         if ( is_file($this->dir_modulos.'/'.$archivo)  ) {
            copy($this->dir_modulos.'/'.$archivo,$url_archivo);
         } else {
            if ( is_file('modulos/'.$archivo)  ) {
               copy('modulos/'.$archivo,$url_archivo);
            } else {
               registrar(__FILE__,__LINE__,__CLASS__.'->'.__FUNCTION__.'('.$e.','.depurar($args).') Fichero no encontrado','ERROR');
               }
            }
         }

      registrar(__FILE__,__LINE__,"EditorFicheros::Editar $url_archivo");

      /* Si no tenemos definido el tipo de archivo lo buscamos */

      if ( empty($tipo_archivo) ) { $tipo_archivo = GUtil::tipo_de_archivo($url_archivo); }

      echo '<p>mimetype: '.$tipo_archivo.'</p>';

      /* Ediamos según tipo de archivo */

      if ( $tipo_archivo == 'text/html' ) {

         if ( ! $fich= fopen($url_archivo,"r") ) {
            registrar(__FILE__,__LINE__,"No se puede abrir archivo [".$url_archivo."]",'ERROR');
            return FALSE;
            }

         $contingut_inicial = fread($fich, filesize($url_archivo)); 
         fclose($fich);

         if (!$contingut_inicial){  
            registrar(__FILE__,__LINE__,"EditorFicheros::No se puede leer el archivo [".$url_archivo."]",'ERROR') ;  
            return FALSE;

         } else {

            $_SESSION['edit']='si'; // Para que podamos editar bien con tiny
            $nom_textarea = 'contenido_interno';
            include($this->ruta('temas','html','form_editar.html'));

            }

      } elseif ( $tipo_archivo == 'text/css' || $tipo_archivo == 'application/x-javascript') {

         if ( ! $fich= fopen($url_archivo,"r") ) {
            registrar(__FILE__,__LINE__,"No se puede abrir archivo [".$url_archivo."]",'ERROR');
            return FALSE;
            }

         $contingut_inicial = fread($fich, filesize($url_archivo)); 
         fclose($fich);

         if (!$contingut_inicial){  
            registrar(__FILE__,__LINE__,"EditorFicheros::No se puede leer el archivo [".$url_archivo."]",'ERROR') ;  
            return FALSE;
         } else {

            $_SESSION['edit']='si'; // Para que podamos editar bien con tiny
            $nom_textarea = 'contenido_interno';
            include($this->ruta('temas','html','form_editar.html'));

            }

      } else {
         
         registrar(__FILE__,__LINE__,'Tipo de archivo desconocido','AVISO');
         return FALSE;

         }

      }

   /** Pasar formato de colores de rgb a html */

   function rgb2html($rgb) {

      if ( strpos($rgb,'rgb') === FALSE  ) return $rgb ;

      $rgb = str_replace('rgb(','',$rgb);
      $rgb = str_replace(')','',$rgb);

      list ($r,$g,$b) = explode(',',$rgb);

      $r = intval($r); $g = intval($g);
      $b = intval($b);

      $r = dechex($r<0?0:($r>255?255:$r));
      $g = dechex($g<0?0:($g>255?255:$g));
      $b = dechex($b<0?0:($b>255?255:$b));

      $color = (strlen($r) < 2?'0':'').$r;
      $color .= (strlen($g) < 2?'0':'').$g;
      $color .= (strlen($b) < 2?'0':'').$b;
      return '#'.$color;
      }

   /**
    * Panel de colores que nos presente la lista para al editar archivos css
    * veamos que colores tenemos dispuestod
    */

   function panel_colores() {

      /* Recogemos información de los colores actuales del tema */

      $arch_colores_tema_xdefecto = $this->dir_modulos.'/temas/css/colores.php';
      $arch_colores_tema_actual = $this->dir_tema_actual.'/modulos/temas/css/colores.php';

      $arch_colores = ( is_file($arch_colores_tema_actual) ) ? $arch_colores_tema_actual : $arch_colores_tema_xdefecto ;

      include ($arch_colores);

      ksort($colores);
      reset($colores);

      $salida = '';

      foreach ($colores as $key => $color) {
         $salida .= '<div style="background: '.$color.'">';
         $salida .= '&lt;?=$colores[\''.$key.'\']?&gt;';
         $salida .= '</div>';
         }

      $panel = array();
      $panel['titulo'] = literal('Colores',3);
      $panel['oculto'] = TRUE;
      $panel['href'] = 'javascript:visualizar(\'lista_colores\');';
      $panel['subpanel'] ='lista_colores';
      $panel['contenido'] =$salida;

      self::panel($panel);
      
      }

   /** 
    * Formulario para la modificación de los colores comodamente
    *
    * @todo Hacer copia de seguridad antes de cambiar el archivo de colores
    *
    */

   function formulario_colores() {

      $incluir_colores = TRUE;

      $this->librerias_js('farbtastic.js');
      $this->javascripts('iniciar_selector_color.js');

      /* Recogemos información de los colores actuales del tema */

      $arch_colores_tema_xdefecto = $this->dir_modulos.'/temas/css/colores.php';
      $arch_colores_tema_actual = $this->dir_tema_actual.'/modulos/temas/css/colores.php';

      $arch_colores = ( is_file($arch_colores_tema_actual) ) ? $arch_colores_tema_actual : $arch_colores_tema_xdefecto ;

      /* Comprobar directorios afectados sino creamos */

      if ( !$this->comprobar_directorios($this->dir_tema_actual.'/modulos/temas/css/') ) {
         return FALSE;
         }

      // Procesar css de proyecto para obtener la lista de todos los colores
      ob_start();
      $this->contruir_lista_colores();
      $this->tema->colores=$this->colores;
      echo "\n/* fichero:".$this->tema->ficheros['css']['temas/css/body.css'].": */\n";
      include($this->tema->ficheros['css']['temas/css/body.css']);
      echo "\n/* acaba:".$this->tema->ficheros['css']['temas/css/body.css'].": */\n";

      foreach ( $this->tema->ficheros['css'] as $llave => $fichero) {

         if ( $llave != 'temas/css/body.css' ) {
            echo "\n/* fichero:".$llave.": */\n";
            include($fichero);
            echo "\n/* acaba:".$llave.": */\n";
            }

         }
      $proceso_css = ob_get_clean();
      if ( GCM_DEBUG ) echo "\n<h3>Resultado de procesar archivos css</h3>\n<pre>\n$proceso_css\n</pre>";

      /* Si se añade un nuevo color */

      if ( isset($_REQUEST['accion']) && $_REQUEST['accion'] == 'anyadir_color' ) {

         include ($arch_colores);

         $incluir_colores = FALSE;

         $nuevo_color = $_POST['color'];

         if ( array_key_exists( $nuevo_color, $colores ) ) {
            registrar(__FILE__,__LINE__,literal('El color ya existe',3),'ERROR');

         } elseif ( !isset($nuevo_color) || empty($nuevo_color) ) {
            registrar(__FILE__,__LINE__,literal('El color no es válido',3),'ERROR');

         } else {
            $this->tema->colores[$nuevo_color] = '#ffffff';
            ksort($this->tema->colores);
            reset($this->tema->colores);
            registrar(__FILE__,__LINE__,literal('Añade un valor al nuevo color para que tenga efecto el cambio',3),'AVISO');
            }

         }

      /* Si tenemos colores nuevos */

      if ( isset($_REQUEST['accion']) && $_REQUEST['accion'] == 'guardar_colores' ) {

         $this->guardar_colores();

      }

      // if ( $incluir_colores ) include ($arch_colores);

      ksort($this->tema->colores);

      include($this->ruta('temas','html','form_colores.html'));

      }

   /**
    * @brief Presentar formulario de temas
    *
    * @param $e    Evento que lo reclama
    * @param $args Parametros 
    */

   function formulario_temas() {

      $temas = glob($this->dir_temas.'*');

      if ( !$temas  ) {
         echo '<p class="aviso">'.literal('No hay temas para seleccionar',3).'</p>';
      } else {
         foreach ( $temas as $tema ) {
            list($dir,$tema_proyecto) = explode('/',$tema);
             $temas_proyecto[] = $tema_proyecto;
         }
      }

      include($this->ruta('temas','html','form_temas.html'));

      }

   /**
    * Nos llega tema seleccionado
    */

   function seleccionar_tema($e,$args) {

      registrar(__FILE__,__LINE__,'Nuevo tema seleccionado','AVISO');
      $this->tema_actual = $_POST['tema_actual'];
      $this->config('tema_actual',$this->tema_actual);
      $this->dir_tema_actual = $this->dir_temas.$this->tema_actual;
      $this->administrar();
      }
      
   /**
    * Crear nuevo tema
    */

   function nuevo_tema($e,$args) {

      $nuevo_tema = ( isset($_POST['nuevo_tema']) ) ? $_POST['nuevo_tema'] : NULL ;

      if ( $nuevo_tema ) {

         $this->config('tema_actual',$nuevo_tema);
         $this->tema_actual = $nuevo_tema;
         $this->dir_tema_actual = $this->dir_temas.$nuevo_tema;
         $this->administrar();

         }
      }

   /**
    * Administración de temas
    *
    * - Formulario de temas:
    *   Si tenemos tema configurado presentamos formulario para poder seleccionar otro tema.
    *   Acciones de formulario: Tema nuevo, Guardar como, Borrar
    *
    * - Editar archivos de tema:
    *   Separamos la lista en css, html y js
    *
    * - Colores de tema:
    *   Panel para los colores.
    *
    * - Iconos:
    *   Panel de los iconos. ¿Hacer paquetes de iconos individual de temas?
    *   Separar por directorios 16/ 24/ 48/
    */

   function administrar() {

      global $gcm;

      $this->anularEvento('contenido','temas');
      $this->anularEvento('titulo','temas');

      $gcm->titulo = literal('Administración de temas',3);

      if ( !$gcm->au->logeado() ) {
         registrar(__FILE__,__LINE__,
            __CLASS__.'->'.__FUNCTION__.'('.$e.','.depurar($args).') '.literal('Sin permisos')
            ,'ERROR');
         return FALSE;
         }

      /* Tenemos tema configurado */

      if ( !$this->tema_actual  ) {
         $this->formulario_temas();
         return;
         }

      /* Comprobar que tenemos directorio de tema actual en proyecto
       * Si no es así lo creamos.
       *
       */

      $this->comprobar_directorios($this->dir_tema_actual);

      if ( $gcm->au->logeado() ) {

         // Acciones posibles

         if ( isset($_POST['contenido_interno']) && isset($_POST['documento']) && isset($_POST['guardar']) ) { // Recibimos nuevo contenido

            $archivo = $_POST['documento'];

            registrar(__FILE__,__LINE__,"guardar fichero: [".$archivo."]");

            /* Remplazamos url para que apunte al directorio del tema y no al de gcm */

            $archivo = str_replace($this->dir_modulos,$this->dir_temas.$this->tema_actual.'/modulos/',$archivo);

            registrar(__FILE__,__LINE__,"guardar fichero como: [".$archivo."]");

            /* Crear directorios en el tema si hace falta */

            $camino = explode('/',$archivo);
            $dir = '';

            for ( $n=0; $n < count($camino)-1; $n++) {
               $dir .= $camino[$n].'/';
               if ( !empty($dir) ) {
                  if ( !is_dir($dir) ) {
                     if ( ! mkdir($dir) ) {
                        registrar(__FILE__,__LINE__,'No pude crear directorio de tema ['.$dir.']','ERROR');
                        }
                     }
                  }
               }

            if ( !file_put_contents($archivo,stripslashes($_POST['contenido_interno'])) ) {
               registrar(__FILE__,__LINE__,'Error al escribir archivo ['.$archivo.']','ERROR');
            } else {
               registrar(__FILE__,__LINE__,'Archivo de tema ['.$archivo.'] modificado correctamente','AVISO');
               }

            $this->formulario_ficheros();

         } else {

            $this->formulario_temas();
            $this->formulario_ficheros();
            $this->formulario_fichero_css();
            $this->formulario_colores();
            
            }

         }

      }

   /**
    * Guardar los colores en el archivo del tema
    */

   function guardar_colores() {

      $arch_colores_tema_actual = $this->dir_tema_actual.'/modulos/temas/css/colores.php';

      try {

         if ( ! ( $file = @fopen($arch_colores_tema_actual, "w") ) ) {

            throw new Exception('No se pudo abrir archivo para editar');

            }

      $nombre_array = 'colores';
      if ( isset($_POST['colores']) ) $this->tema->colores = $_REQUEST['colores'];
      ksort($this->tema->colores);
      reset($this->tema->colores);


      fputs($file, "<?php\n");
      fputs($file, "// Archivo generado automaticamente por ".__FILE__."\n");

      while (list($clave, $val)=each($this->tema->colores)){

         if ( is_array($val)) {   // si es un array 

            while (list($claveArray, $valorArray)=each($val)) {

               $valorArray = str_replace("\\","",$valorArray);
               $valorArray = stripcslashes($valorArray);

               if (fputs($file, '$'."$nombre_array"."['".str_replace("'","\'",$clave)."'][]='".str_replace("'","\'",$valorArray)."';\n") === FALSE ) {
                  throw new Exception("No se puede escribir en ".$archivo);
                  return FALSE;
                  }
               }

         } else {                   // No es un array

            $val = str_replace("\\","",$val);
            $val = stripcslashes($val);

            if (fputs($file, '$'."$nombre_array"."['".str_replace("'","\'",$clave)."']='".str_replace("'","\'",$val)."';\n") === FALSE ) {
               throw new Exception("No se puede escribir en ".$archivo);
               return FALSE;
               }

            }
         }

         fputs($file, "?>");
         fclose($file);

         registrar(__FILE__,__LINE__,'Colores guardados','AVISO');
   
      } catch (Exception $ex) {
         registrar($ex->getFile(),$ex->getLine(),$ex->getMessage(),'ERROR');
         }

      $arch_colores = $arch_colores_tema_actual;
      }


   }
?>
