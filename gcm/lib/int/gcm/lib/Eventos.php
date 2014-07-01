<?php

/**
 * @file  Eventos.php
 * @brief Gestión de los eventos de gcm
 * @ingroup gcm_lib
 * 
 * @author    Eduardo Magrané 
 *
 * @internal
 * Copyright  Copyright (c) 2009, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

/** 
 * @class Eventos
 * @brief Módulo Eventos para conectar acciones con los módulos
 * @ingroup gcm_lib
 *
 * Utilizamos los archivos eventos_usuario.php y eventos_admin.php de
 * cada módulo para interconectar a los módulos.
 *
 * A parte de los módulos de la misma aplicación buscamos dentro de cada
 * proyecto la carpeta modulos para utilizarlos.
 *
 * Comportamiento de eventos:
 *
 * Podemos configurar diferentes comportamientos:
 * - unico:  Deja de ejecutarse acciones para el mismo evento.
 * - anular: Se anula cualquier acción de evento.
 *
 * Ejemplo de fichero de eventos de un módulo.
 *
 * @code
 * $eventos['cabecerad']['formulario_busqueda'][1]='';
 * $eventos['columna']['ultimas_entradas'][2]="num=7&seccion=".Router::$s."&formato=1";
 * $eventos['contenido_dinamico']['contenido_dinamico'][1]='';
 * $eventos['precarga']['presentar_busquedas'][3]='';
 *
 * // Comportamiento de eventos
 * $cEventos['evento']['unico'] = '<nombre del modulo>';
 * $cEventos['evento']['anular'] = '<nombre del modulo>';
 * @endcode
 *
 * Tambien se puede especificar comportamientos en tiempo de ejecución:
 *
 * @code
 * $gcm->event->anular('contenido','contenidos');
 * $gcm->event->unico('titulo','contenidos');
 * @endcode
 *
 * @see modulos/contenido/eventos_usuario.php
 */

class Eventos {

   /**
    * Array con los eventos de la aplicación
    */

   public $eventos = array();

   /** Ubicación de los módulos */

   public $ubicaciones = array();

   /** Comportamientos de los eventos
    *
    * Nos permite definir acciones para eventos desde los módulos
    *
    * Definidas hasta el momento:
    *
    * 'unico'
    *   Si un módulo define un evento como unico ej: 
    *   $cEvento['contenido_dinamico'] = 'unico'; proboca que
    *   el bucle que recorre las acciones del evento salte depues de ejecutar
    *   la propia acción.
    *
    */

   public $cEventos = array();           

   /**
    * Lista de módulos activos, que seran los basicos marcados por gcm
    * y los que esten activados por el módulo admin
    */

   public $modulos_activados;

   private $dir_modulos;               ///< Directorio de módulos
   private $dir_modulos_proyecto;      ///< Directorio de módulos propios del proyecto
   private $bAdmin;                    ///< Recogemos eventos de administración T/F

   /** Directorio donde se guardan los ficheros de configuración de eventos del proyecto */

   private $dir_eventos_proyecto = 'DATOS/eventos/';

   /** Cogemos eventos configurados en proyecto o los de los módulos por defecto */

   private $leer_eventos_proyecto = TRUE;

   /** 
    * Visualizar eventos en vez de lanzarlos, util para vista previa
    * de plantilla
    */

   private $visualizar = FALSE;

   /**
    * Instancias de módulos
    */

   public $instancias = array();

   /**
    * Lista blanca de acciones
    *
    * Todos los eventos que esten definidos en los archivos de los módulos 
    * 'eventos_usuarios.php' serán añadidos a la lista_blanca para evitar 
    * comprobar permisos sobre ellos.
    *
    * Esta lista es ampliable desde el método set_lista_blanca('módulo','acción'), o colsultable
    * desde get_lista_blanca('módulo','acción').
    *
    * Estructura del array:
    * @code
    * lista_blanca[módulo][0] = 'acción' 
    * lista_blanca[módulo][1] = 'acción' 
    * @endcode
    */

   private $lista_blanca = array();

   private $numero_eventos = 0 ;        //< Numero de eventos

   /** 
    * Llenamos el array eventos con la información de los archivos
    * de eventos de cada modulo
    *
    * @param $dir_modulos Directorio de módulos
    * @param $bAdmin      Leer eventos de adminstración TRUE/FALSE
    * @param $eProyecto   Leer eventos de proyecto TRUE/FALSE, si no solo leemos los eventos de los módulos por defecto
    * @param $visualizar  Mostramos acción a realizar sin realizarla 
    */

   function __construct($dir_modulos, $bAdmin=FALSE, $eProyecto=TRUE, $visualizar=FALSE) {

      global $gcm;

      $this->modulos_activados = array_merge($gcm->modulos_basicos,$gcm->config('admin','Módulos activados'));

      registrar(__FILE__,__LINE__,'Listado de módulos activados',FALSE,depurar($this->modulos_activados,'Modulos activados'));

      $this->visualizar = $visualizar;
      $this->eventos = array();
      $this->dir_modulos = $dir_modulos;         // GCM_DIR.'modulos/'
      $this->dir_modulos_proyecto = 'modulos/';
      $this->bAdmin = $bAdmin;
      $this->leer_eventos_proyecto = $eProyecto;

      /* Comprobar directorio de eventos en proyecto */

      if ( ! file_exists($this->dir_eventos_proyecto)  ) {
         if ( ! mkdir ($this->dir_eventos_proyecto) ) {
            return FALSE;
            }
         }

      /* Si queremos eventos de administración */

      if ( $this->bAdmin ) {
         $this->leer_eventos('admin');
         if ( file_exists($this->dir_modulos_proyecto) ) $this->leer_eventos('admin',$this->dir_modulos_proyecto);
         }

      $this->leer_eventos('usuario');
      if ( file_exists($this->dir_modulos_proyecto) ) $this->leer_eventos('usuario',$this->dir_modulos_proyecto);

      registrar(__FILE__,__LINE__,'Variables en eventos',FALSE,depurar(get_object_vars($this)));

      // echo "<pre>Eventos: " ; print_r($this->eventos) ; echo "</pre>"; // DEV  
      // echo "<pre>cEventos: " ; print_r($this->cEventos) ; echo "</pre>"; // DEV  
      // echo "<pre>ubicaiones: " ; print_r($this->ubicaciones) ; echo "</pre>"; // DEV  
      }

   /**
    * Comprobar si una acción está en la lista_blanca
    *
    * @param $modulo Módulo
    * @param $accion Acción
    * @return TRUE/FALSE
    */

   public function get_lista_blanca($modulo, $accion) {
      if ( isset($this->lista_blanca[$modulo]) && in_array($accion, $this->lista_blanca[$modulo]) ) return TRUE;
      return FALSE;
      }

   /**
    * Añadir una acción a la lista blanca
    *
    * Nos permite añadir métodos que no estan definidos en los eventos de usuario
    * pero que se utilizan y no requieres permisos.
    *
    * Un ejemplo, en el archivo modulos/menu/eventos_usuario.php añadimos el método
    * 'menu_ajax' a la lista, sino lo hicieramos nos daria error la aplicación por
    * falta de permisos al selecccionar una sección en el menú.
    * @code
    * $this->set_lista_blanca('menu','menu_ajax');
    * @endcode
    *
    * @param $modulo Módulo
    * @param $accion Acción
    *
    * @ingroup permisos
    */

   public function set_lista_blanca($modulo,$accion) {
      if ( ! $this->get_lista_blanca($modulo, $accion) ) $this->lista_blanca[$modulo][] = $accion;
      // registrar(__FILE__,__LINE__,'<pre>'.depurar($this->lista_blanca).'</pre>','AVISO');
      }

   /**
    * Buscamos dentro de modulos los archivos directorio módulo/eventos_usuario.php
    * que tienen la información del módulo.
    *
    * Construimos: $this->eventos, $this->cEventos, $this->ubicaciones
    *
    * En caso haber un array de $acciones las sumamos a las ya definidas en Autentificacion
    *
    * @see Autentificacion
    *
    * @param $nivel usuario o admin
    * @param $directorio Directorio donde se encuantran los módulos
    */

   function leer_eventos($nivel, $directorio=NULL) {

      global $gcm;

      registrar(__FILE__,__LINE__,__FUNCTION__.'('.$nivel.','.$directorio.')');

      $nombre_cache = ( $directorio ) ? 'eventos_'.$nivel.'_'.str_replace('/','-',rtrim($directorio,'/')) : 'eventos_'.$nivel ;

      /**
       * Solo comprobamos modulos activados en caso de ser módulos de gcm
       * si son del mismo proyecto, la activación es inerente.
       */

      $comprobar_activados = FALSE;

      $n = 0;

      if ( ! $directorio  ) {
         $directorio = $this->dir_modulos;
         $comprobar_activados = TRUE;
         }

      if ( ! file_exists($directorio) ) {
         registrar(__FILE__,__LINE__,'No tenemos directorio de modulos en '.$directorio, 'ERROR');
         return FALSE;
         }

      $directorio_modulos = dir($directorio);

      while ($modulo = $directorio_modulos->read()) {

         if ( $comprobar_activados && ! in_array($modulo,$this->modulos_activados) ) continue;

         $fichero_modulo = $directorio.$modulo.'/eventos_'.$nivel.'.php';
         $fichero_evento = $this->dir_eventos_proyecto.$modulo.'/eventos_'.$nivel.'.php';

         if ( ! is_file($fichero_modulo) ) continue;
         if ( ! is_file($fichero_evento) ) {
            if ( ! is_dir($this->dir_eventos_proyecto.$modulo)  ) {
               if ( ! mkdir($this->dir_eventos_proyecto.$modulo)  ) {
                  registrar(__FILE__,__LINE__,'No se pudo crear directorio de módulo en la configuración del proyecto ['.$modulo.']','ERROR');
                  return FALSE;
                  }
               }
            if ( ! copy($fichero_modulo, $fichero_evento)  ) {
                  registrar(__FILE__,__LINE__,'No se pudo copiar archivo de evento ['.$fichero_evento.']','ERROR');
               return FALSE;
               }
            }

         if ( $this->leer_eventos_proyecto ) {
            include($fichero_modulo);
            if ( isset($eventos) && is_array($eventos) ) $this->recoger_eventos($modulo,$directorio,$eventos,$nivel);
            unset($eventos);
            include($fichero_evento);
            if ( isset($eventos) && is_array($eventos) ) $this->recoger_eventos($modulo,$directorio,$eventos,$nivel);
            unset($eventos);
         } else {
registrar(__FILE__,__LINE__,"fichero_modulo: $fichero_modulo",'AVISO');
            include($fichero_modulo);
            if ( isset($eventos) && is_array($eventos) ) $this->recoger_eventos($modulo,$directorio,$eventos,$nivel);
            unset($eventos);
            }

         $n++;

         }

         registrar(__FILE__,__LINE__,'Numero de eventos en '.$directorio.': '.$this->numero_eventos);

      }

   /**
    * Recoger eventos de módulo a eventos
    */

   function recoger_eventos($modulo,$directorio,$eventos,$nivel = FALSE) {

      if ( isset($eventos) && is_array($eventos) ) {
         foreach ( $eventos as $e => $accion ) {
            foreach ( $accion as $a => $valor ) {
               foreach ( $valor as $prioridad => $argumentos ) {
                  // Si son eventos de usuario añadimos acciones a lista_blanca
                  if ( $nivel == 'usuario' ) $this->lista_blanca[$modulo][] = $a;
                  // Si ya tenemos el evento lo borramos para que predominen los 
                  // últimos que son los fijados por el proyecto
                  if ( isset($this->eventos[$e][$modulo][$a]) ) unset($this->eventos[$e][$modulo][$a]) ;
                  $this->eventos[$e][$modulo][$a][$prioridad] = $argumentos; 
                  $this->ubicaciones[$modulo] = $directorio.$modulo; 
                  $this->numero_eventos++;
                  if ( isset($cEventos[$e]) ) {
                     $this->cEventos[$e][$cEventos[$e]] = $modulo;
                     }
                  }
                  unset($a);
                  if ( isset($cEventos)  ) unset($cEventos[$e]);
               }
            }
         }

         // echo 'eventos: '.$modulo.'<pre>' ; print_r($this->eventos) ; echo '</pre>'; // exit() ; // DEV  
      }

   /** Verificar la existencia de un evento
    *
    * @param $e Evento
    *
    * @return TRUE/FALSE
    */

   function verificar_evento($e) {

      if ( empty($this->eventos[$e]) ) {
         return FALSE;
      } else {
         return TRUE;
         }

      }

   /** lanzarEvento 
    *
    * Recogemos la información del arreglo eventos para ordenarla por orden
    * de prioridad y lanzar los métodos especificados.
    *
    * @param e Evento que se lanza
    * @param args Argumentos para el evento, estos argumentos son añadidos en tiempo de ejecución del evento y son sumados
    *             a los predefinidos al momento de configurar el evento. @see recoger_parametros
    *
    * @return TRUE/FALSE
    *
    * @author Eduardo Magrané
    * @version 1.0
    *
    */

   public function lanzarEvento($e, $args=''){

      global $gcm;

      registrar(__FILE__,__LINE__,'lanzarEvento('.$e.','.$args.')');

      $evento = $e;

      if ( $this->visualizar ) echo '<div class="visualizar_evento" ><p style="font-size: 12px; color: red;">'.$e.'</p>';

      $parametros_eventos = $args;

      /* Comprobar que no se haya anulado el evento */

      if (  isset($this->cEventos[$e]['anular']) )   {
         registrar(__FILE__,__LINE__,'Eventos->lanzarEvento('.$e.','.$args.') Evento anulado por ['.$this->cEventos[$e]['anular'].'] nos volvemos');
         return;
         }

      /* Comprobar si hay subevento (pre<evento>) y lanzar subevento si lo hay */

      if ( $this->verificar_evento('pre'.$evento) ) { $this->lanzarEvento('pre'.$evento); }

      try {

         if ( !$this->verificar_evento($evento) ) {

            registrar(__FILE__,__LINE__,'Sin eventos para ['.$evento.']');

         } else {
            
            /* Eventos ordenados por prioridad */

            $orden = $this->prioridad($e);

            registrar(__FILE__,__LINE__,'Acciones para evento ['.$evento.']',FALSE,depurar($orden));

            /* Lanzar eventos */

            foreach ( $orden as $accion => $prioridad ) {

               /* Comprobar que no se haya anulado el evento */

               if (  isset($this->cEventos[$e]['anular']) )   {
                  registrar(__FILE__,__LINE__,'Eventos->lanzarEvento('.$e.','.$args.') Evento anulado nos volvemos');
                  return;
                  }

               list($m,$a,$parametros_metodo) = explode('|',$accion);

               if ( !empty( $parametros_metodo) && !empty($parametros_eventos) ) {
                  $parametros = $parametros_eventos.'&'.$parametros_metodo;
               } elseif ( empty($parametros_eventos) ) {
                  $parametros = $parametros_metodo;
               } elseif ( empty($parametros_metodo) ) {
                  $parametros = $parametros_eventos;
                  }

               /* Comprovamos comportamiento de eventos */

               if ( isset($this->cEventos[$e]['unico']) && $this->cEventos[$e]['unico'] != $m )   {          // unico

                  /* El evento tiene comportamiento de único y no es para este modulo */

                  registrar(__FILE__,__LINE__,
                     'Comportamiento [ unico ] para evento ['.$e.'] por parte de [ '.$this->cEventos[$e]['unico'].' ]');

               } else {
                  
                  // Los eventos para cache no los comentamos en debug para que se muestren antes de la definición
                  // de doctype y despiste al navegador

                  if ( GCM_DEBUG && Router::$formato == 'html' && $m !== 'cache_http') printf("\n".Router::$forma_comentarios."\n",'Eventos: '.$m.'->'.$a.'('.$e.','.$parametros.')');
                  if ( $this->visualizar ) {
                     if ( $m == "temas"  ) $this->lanzar_accion_modulo($m,$a,$e,$parametros);
                     echo "\n".'<p style="font-size: 12px"><a href="?m=admin&a=editar_conexion&md='.$m.'">',$m,'->',$a,'('.$e.','.$parametros.')</a></p>'."\n";
                  } else {
                     $this->lanzar_accion_modulo($m,$a,$e,$parametros);
                     }

                  if ( GCM_DEBUG && Router::$formato == 'html' && $m !== 'cache_http') printf("\n".Router::$forma_comentarios."\n",' / Eventos: '.$m.'->'.$a.'('.$e.','.$parametros.')');
                  }

               }
            }

         } catch (Exception $ex) {

            registrar($ex->getFile(),$ex->getLine(),'Excepción en eventos [ '.$e.' ] : '.$ex->getMessage(). "\n",'ADMIN');

            }

         /** Comprobar si hay eventos para post<evento> */

         if ( $this->verificar_evento('post'.$evento) ) { $this->lanzarEvento('post'.$evento); }

         if ( $this->visualizar ) echo '</div>';
         return TRUE;
      }

   /**
    * Ordenar evento por prioridad
    *
    * @param $e Evento a inspeccionar
    *
    * @return Array en formato [modulos|acción|argumentos] = orden
    */

   private function prioridad($e) {

      $arreglo = array();              ///< Array a devolver con las acciones ordenador por prioridad
      $eventos = $this->eventos[$e];   ///< Contenido de las acciones del evento
      $args = NULL;                    ///< Argumentos del evento

      foreach ( $eventos as $ee => $aa ) {
         $n_m = $ee;
         foreach( $aa as $n_a => $valor ) {

            foreach ( $valor as $prioridad => $argumentos ) {

               if ( $prioridad > 0 ) {
                  $arreglo[$n_m.'|'.$n_a.'|'.$argumentos] = $prioridad;
                  }

               }
            }
         unset($n_a, $mm);
         unset($n_e);
         }
      
      asort($arreglo);
      reset($arreglo);
      return $arreglo;
   }

   /**
    * Devolver instancia de un módulo, nos permite interactuar entre
    * componentes sin reacrgar la aplicación
    *
    * @param $m Modulo a instanciar
    */

   function instancia_modulo($m) {

      global $gcm;

      // Comprobar que esta activo

      if ( ! in_array($m,$this->modulos_activados) ) return;

      /**
       * url del fichero que contiene la clase, ejemplo: temas/lib/Temas.php
       */

      $fm = NULL ;

      /** Nombre de la clase */

      $M = ucfirst($m);

      /** Nombre de la clase administrativa */

      $logeados = ( $gcm->au->logeado() ) ? TRUE : FALSE ;

      if ( isset($this->ubicaciones[$m])  ) {
         if ( $logeados && is_file($this->ubicaciones[$m].'/lib/'.$M.'Admin.php') !== FALSE  ) {
            $MA = $M.'Admin';
            $fm = $this->ubicaciones[$m].'/lib/'.$MA.'.php';
         } else {
            $fm = $this->ubicaciones[$m].'/lib/'.$M.'.php';
            $MA = FALSE;
            }
      } elseif ( $logeados && file_exists($this->dir_modulos_proyecto.$m.'/lib/'.$M.'Admin.php') ) {
         $MA = $M.'Admin';
         $fm = $this->dir_modulos_proyecto.$m.'/lib/'.$MA.'.php';
      } elseif ( file_exists($this->dir_modulos_proyecto.$m.'/lib/'.$M.'.php') ) {
         $fm = $this->dir_modulos_proyecto.$m.'/lib/'.$M.'.php';
         $MA = FALSE;
      } elseif ( $logeados && file_exists($this->dir_modulos.$m.'/lib/'.$M.'Admin.php') ) {
         $MA = $M.'Admin';
         $fm = $this->dir_modulos.$m.'/lib/'.$MA.'.php';
      } elseif ( file_exists($this->dir_modulos.$m.'/lib/'.$M.'.php') ) {
         $MA = FALSE;
         $fm = $this->dir_modulos.$m.'/lib/'.$M.'.php';
      } else {
         trigger_error('Error al cargar módulo, Fichero de módulo no encontrado ['.$m.']', E_USER_ERROR);
         registrar(__FILE__,__LINE__,'Error al cargar módulo, Fichero de módulo no encontrado ['.$m.']','ERROR');
         return FALSE;
         }

      if ( !isset($this->instancias[$m])  ) {

         registrar(__FILE__,__LINE__,'Instancia de módulo: ['.$m.']');
         require_once($fm);

         if ( $MA ) {
            $this->instancias[$m] = new $MA(); 
         } else {
            $this->instancias[$m] = new $M(); 
            }

         } 

      return $this->instancias[$m];

      }

   /**
    * Lanzar acción de módulo
    *
    * Buscamos módulo segun ubicacinoes encontradas desde los archivos de 
    * eventos_usuario y eventos_admin, pero como se puede dar el caso de 
    * que no exista tal ubicación registrada por no tener eventos prefijados, 
    * hacemos una comprobación antes, en caso de negación buscamos en módulos 
    * de proyectos y por último lugar en módulos de gcm.
    *
    * En caso de estar logeados se buscara si tenemos un archivo con nombre 
    * modulos/lib/ModuloAdmin.php, si es así lo  utilizamos, con ello 
    * conseguimos poder separar las clases segun usuario o administración 
    * reduciendo la carga de  la aplicación.
    *
    * @param $m Nombre del directorio del módulo
    * @param $a Método de módulo
    * @param $e Evento que lo llama
    * @param $args Argumentos
    */

   function lanzar_accion_modulo($m, $a, $e=NULL, $args=NULL) {

      global $gcm;

      if ( ! in_array($m,$this->modulos_activados) ) {
         registrar(__FILE__,__LINE__,'Modulo '.$m.' desactivado','ADMIN');
         return;
         }

      // Comprobar permisos
      if ( ! $this->get_lista_blanca($m,$a) ) {

         if ( ! permiso($a, $m) ) {
            //registrar(__FILE__,__LINE__,$m.'->'.$a.'() sin permisos','AVISO');
            registrar(__FILE__,__LINE__,literal('Autorización denegada'),'AVISO',$m.'->'.$a.'() sin permisos');
            return FALSE;
            }

         }

      if ( $this->instancia_modulo($m) ) {

         if ( method_exists($this->instancias[$m], $a ) ) {

            registrar(__FILE__,__LINE__,$m.'->'.$a.'('.$e.','.$args.');');
            /* Si estamos en debug calculamos tiempo de ejecución de la acción */
            if ( GCM_DEBUG ) $tiempo_inicio = microtime(TRUE);
            if ( $this->instancias[$m]->$a($e,$args) === FALSE )  {
               registrar(__FILE__,__LINE__,'Eventos->lanzar_accion_modulo('.$m.','.$a.','.$e.','.$args.') Error al lanzar evento');
               // Anulamos evento por error en uno de los módulos
               if ( stripos($e,'pre') !== FALSE ) {
                  $evento_original = str_replace('pre','',$e);
                  $this->anular('pre'.$evento_original, $m);
                  $this->anular($evento_original, $m);
                  $this->anular('post'.$evento_original, $m);
               } elseif ( stripos($e,'post') !== FALSE ) {
                  $evento_original = str_replace('post','',$e);
                  $this->anular($evento_original, $m);
                  $this->anular('post'.$evento_original, $m);
               } else {
                  $this->anular($e, $m);
                  $this->anular('post'.$e, $m);
                  }

               }
            if ( GCM_DEBUG ) $tiempo_total = round(microtime(TRUE)-$tiempo_inicio,4);
            if ( GCM_DEBUG ) registrar(__FILE__,__LINE__,'Tiempo de ejecución de '.$m.'->'.$a.'('.$e.','.depurar($args).'): ('.$tiempo_total.')'); 


         } else {
            
            if ( isset($a) && !empty($a) ) {
               trigger_error('Eventos->lanzar_accion_modulo('.$m.','.$a.','.$e.','.$args.') Acción no reconocida');
            } else {
               trigger_error('Eventos->lanzar_accion_modulo('.$m.','.$a.','.$e.','.$args.') Sin acción definida', E_USER_ERROR);
               }

            }

      } else {
         trigger_error('Eventos->lanzar_accion_modulo('.$m.','.$a.','.$e.','.$args.') Módulo no reconocido', E_USER_ERROR);
         }

      }

   /**
    * Anular evento, eliminamos conexiones de evento
    *
    * @param $evento Evento que se desea anular
    * @param $modulo Módulo que pide la anulación.
    */

   function anular($evento, $modulo) {
      registrar(__FILE__,__LINE__,'Eventos->anular('.$evento.','.$modulo.')');
      $this->cEventos[$evento]['anular'] = $modulo;
      }

   /**
    * Comprobar si un evento esta anula o no
    *
    * @param $e
    */

   function anulado($e) {

      return ( isset($this->cEventos[$e]['anular']) ) ? TRUE : FALSE ;

      }

   /**
    * Evento unico, eliminamos conexiones de evento que
    * no sean las del módulo que se especifica
    *
    * @param $evento Evento afectado
    * @param $modulo Módulo
    */

   function unico($evento, $modulo) {
      registrar(__FILE__,__LINE__,'Eventos->unico('.$evento.','.$modulo.')');
      $this->cEventos[$evento]['unico'] = $modulo;
      }

   /**
    * Añadir una acción a un evento en tiempo de ejecución
    *
    * @param $evento Evento
    * @param $modulo Módulo
    * @param $metodo Método del módulo
    * @param $prioridad Orden de prioridad
    * @param $args Parametros
    */

   function accion2evento($evento,$modulo,$metodo,$prioridad,$args='') {

      registrar(__FILE__,__LINE__,"Se añade acción [".$metodo."] a evento [".$evento."] desde [".$modulo."]");
      
      $this->eventos[$evento][$modulo][$metodo][$prioridad] = $args;

      }

   }

?>
