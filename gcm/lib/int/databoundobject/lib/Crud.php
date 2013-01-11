<?php

/**
 * @file      Crud.php
 * @brief     Automatización de manipulación de base de datos
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 *   Created  09/02/11
 *  Revision  SVN $Id: $
 * Copyright  Copyright (c) 2011, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

require_once(dirname(__FILE__).'/DataBoundObject.php');

require_once(GCM_DIR.'lib/int/solicitud/lib/Solicitud.php');

// require_once(GCM_DIR.'lib/int/galeria/lib/Galeria.php');

/**
 * Extendemos DataBoundObject para automatizar los procesos de insertar,
 * modificar, y borrar registros de la base de datos.
 *
 * Convenciones para facilitar el trabajo y poder automatizar:
 *
 * - id: Necesario en cada tabla como identifiador unico
 * - nombre: Campo con el nombre
 * - <tabla>_id: Para relaciones con otras tablas, esto nos permite por ejemplo
 *   presentar automáticamente un select con las opciones de los registros de la
 *   tabla relacionada.
 * - fecha_creacion como timestamp.
 * - fecha_modificacion como datetime
 * - imagen_url Campo para url de imagen.
 *
 * Tener en cuenta que si estamos utilizando un módulo que requiere de otros módulos por
 * tener campos relacionados debemos hacer un require_once() con ellos.
 */

class Crud extends DataBoundObject {

   public $elementos_pagina = 10;         ///< Número de elementos por página en listado, por defecto los de paginarPDO

   public $css_formulario   = FALSE ;     ///< Añadimos css para Formulario o no.

   public $url_ajax;                      ///< Si se utiliza ajax es necesaria la url a enviar

   /**
    * Podemos definir un metodo personalizado para la visualización de los registros 
    * individuales, en caso de no tenerlo se presentara el formulario en versión
    * de solo lectura
    */

   protected function visualizando_registro() { return FALSE ; }

   /**
    * Opciones de presentación para el listado
    *
    * Array con:
    *
    * $opciones_array2table = array(
    * 'presentacion' => 'TinyTable' // Extensión de array2table,
    * 'args' => array ( 'dir_tinytable' => GCM_DIR.'lib/ext/TinyTable/',
    *                   'cargar_srcipt' => TRUE ),
    *                   );
    *
    * El nombre de la presentación es el nombre de la clase que extiende
    * a array2table.
    *
    * Los argumentos seran los requeridos por la extensión.
    *
    * @see Array2Table
    * @see TinyTable
    */

   public $opciones_array2table = FALSE;

   /**
    * Array con los atributos publicos que deamos pasar a paginador
    *
    * Para ver las posibilidades @see paginadorPDO
    */

   public $conf_paginador = FALSE;        

   protected $evento_guardar = FALSE;     ///< Metodo a lanzar al guardar registro
   protected $evento_modificar = FALSE;   ///< Metodo a lanzar al modificar registro
   protected $evento_borrar  = FALSE;     ///< Metodo a lanzar al borrar registro

   protected $restricciones;              ///< Restricciones para Solicitud
   protected $mensajes;                   ///< Mensajes de respuesta a fallos de restricciones

   /**
    * Desde los modelos podemos definir los tipos campos para Formulario.
    *
    * Ejemplo: protected $tipos_formulario = array( 'nIdLocalitzacio' => array('oculto_form' => 1));
    */

   protected $tipos_formulario;

   /**
    * Marca dentro del nombre del campo de BD que nos indica, la relacion con otra tabla
    *
    * Ejemplo: categoria_id, el nombre de la tabla sera categoria
    */

   protected $id_relacion = '_id';

   /** Plantilla para presentar formulario de registro */

   protected $plantilla;

   /** Archivo css para los estilos */

   protected $fichero_css;

   /**
    * SQL para generar listado, por defecto 'SELECT * FROM <tabla> ORDER BY id desc' 
    *
    * Nos permite tener un listado personalizado desde los modelos, para evitar un exceso
    * de campos no necesarios en los lidtados.
    *
    * uso:
    * <pre>
    * class Tareas extends Crud {
    * 
    *    function __construct(PDO $objPDO, $id=NULL) {
    * 
    *       $this->sql_listado = 'SELECT id, nombre as Nombre, fechaInicio as Inicio FROM tareas ORDER BY id desc';
    * 
    *       parent::__construct($objPDO, $id);
    * 
    *       }
    * 
    *    }
    * </pre>
    */

   public $sql_listado;

   /**
    * Relación de los campos del listado con sus alias.
    *
    * Una sql compleja,  puede  ser  necesario añadir  estas  relaciones, al 
    * ordenar las fechas por el alias el orden nos lo hace como si fuera una 
    * cadena, así en este caso se hace necesario tener el nombre real del 
    * campo para que las ordene como fechas.
    *
    * Ejemplo de uso:
    *
    * @code
    * $this->sql_listado_relacion = array(literal('Data') => 'g.fecha_creacion' );
    * @endcode
    */

   protected $sql_listado_relacion = FALSE;

   /**
    * Parte FROM de la sql que genera el listado, por defecto $this->strTableName
    */

   protected $from_listado;

   protected $galeria = FALSE;            ///< Instancia de Galeria, para las imágenes, en caso de ser TRUE en modelo

   protected $tipos_campos;               ///< Especificaciones de los campos de la tabla

   protected $permisos = FALSE;           ///< Tenemos permisos de edición (T/F)  

   protected $accion = 'ver';             ///< Acción que se esta realizando

   /**
    * Directorio de imagenes utilizadas por @see Array2Table
    */

   protected $dir_img_array2table = FALSE;

   /** 
    * Constructor
    */

   function __construct(PDO $objPdo, $id = NULL) {

      parent::__construct($objPdo, $id);

      $this->restricciones_automaticas();
      $this->mensajes_automaticos();
      //$this->plantilla   = dirname(__FILE__).'/../html/form_registro.html';
      //$this->fichero_css = dirname(__FILE__).'/../css/crud.css';

      /** @todo Revisar tema de galeria da errores al intentar cargarla */

      // if ( $this->galeria ) {

      //    $conf_galeria = array(
      //         'tipo'         => 'tabla-archivo'
      //       , 'pdo'          => $this->objPDO
      //       , 'tabla'        => $this->strTableName
      //       , 'dir_imagenes' => $this->galeria
      //       , 'dir_tmp'      => 'tmp/'
      //       );

      //    $this->galeria = new Galeria($conf_galeria, $this->ID);

      //    $this->galeria->limit_imatges = 1;
      //    $this->galeria->amplaria_max = 640;
      //    $this->galeria->imatge_espera = GCM_DIR.'/lib/int/galeria/img/30.gif';
      //    $this->galeria->contingut_enllac_borrar = '[x]'; 
      //    $this->galeria->amplada_presentacio = 100;
      //    $this->galeria->sufijo = $this->strTableName;


      //    }

      }

   /**
    * devolver array con las restricciones
    */

   function restricciones() {
      return $this->restricciones;
      }

   /**
    * Devolver array con los mensajes 
    */

   function mensajes() {
      return $this->mensajes;
      }

   /**
    * Nombre de tabla
    */

   function DefineTableName() {
      return strtolower(get_class($this));
      }

   /**
    * Relación entre campos de la tabla y variables a utilizar en el dominio, a la vez
    * aprovechamos para ver los tipos de campo y definirlos junto con los 
    * tipos_formulario
    */

   function DefineRelationMap($objPDO) {

      $referencia_campos = array();

      $driver = $objPDO->getAttribute(constant("PDO::ATTR_DRIVER_NAME"));

      switch($driver) {

         case 'sqlite':

            $sql = sprintf("pragma table_info('%s')",$this->strTableName);

            $rst = $objPDO->query($sql) or die('Error al buscar información en la tabla '.$this->strTableName);

            while ( $row = $rst->fetch() ) {

               if ( $row['name'] == 'id' ) {

                  $referencia_campos['id'] = 'ID';

               } else {

                  $referencia_campos[$row['name']] = ucfirst($row['name']);

                  $this->definir_tipos_campo($row, $objPDO);
                  $this->definir_tipos_formulario($row, $objPDO);

                  }

            }
         
            break;

         case 'mysql':

            $sql = sprintf( "SHOW FIELDS FROM `%s`", $this->strTableName );

            $rst = $objPDO->query($sql) or die('Error al buscar información en la tabla '.$this->strTableName);

            while ( $row = $rst->fetch() ) {

               if ( $row['Field'] == 'id' ) {

                  $referencia_campos['id'] = 'ID';

               } else {

                  $referencia_campos[$row['Field']] = ucfirst($row['Field']);

                  $this->definir_tipos_campo($row, $objPDO);
                  $this->definir_tipos_formulario($row, $objPDO);

                  }
            }

            break;

       }

      return $referencia_campos;

      }

   /**
    * Definir tipos de campo especificado
    *
    * Las carateristicas de los campos definidas en el modelo no se chafan con las automaticas
    *
    * @param $row Array con los datos del campo de la base de datos
    */

   function definir_tipos_campo($row, $objPDO) {

      $driver = $objPDO->getAttribute(constant("PDO::ATTR_DRIVER_NAME"));

      if ( $driver == 'sqlite' ) {

         if ( ! isset($this->tipos_campos[$row['name']]['tipo']) )                               $this->tipos_campos[$row['name']]['tipo'] = $row['type'];
         if ( ! isset($this->tipos_campos[$row['name']]['null']) ) $this->tipos_campos[$row['name']]['null'] = ( $row['notnull'] == 0 ) ? 1 : 0;
         if ( !empty($row['Default']) && ! isset($this->tipos_campos[$row['name']]['default']) ) $this->tipos_campos[$row['name']]['default'] = $row['dflt_value'];
         if ( !empty($row['Extra']) && ! isset($this->tipos_campos[$row['name']]['extra']) )     $this->tipos_campos[$row['name']]['extra'] = $row['extra'];

         // Buscamos valor maximo para campo

         preg_match("/\((.*)\)/",$row['type'],$coincidencias);
         $max = FALSE;
         if ( isset($coincidencias[1]) ) {
            $max = $coincidencias[1];
            if ( strpos($max,',')  ) {
               $maxs = explode(',',$max);
               $max = $maxs[0] + $maxs[1] + 1;
               }
            $this->tipos_campos[$row['name']]['max'] = $max;
         } else {

            // timestamp
            if ( $row['type'] == 'timestamp' || $row['type'] == 'datetime' ) {
               $max=20;
               $this->tipos_campos[$row['name']]['max'] = $max;

            // date
            } elseif ( $row['type'] == 'date' ) {
               $max=10;
               $this->tipos_campos[$row['name']]['max'] = $max;

               }

            }

      } else {

         if ( ! isset($this->tipos_campos[$row['Field']]['tipo']) )                               $this->tipos_campos[$row['Field']]['tipo'] = $row['Type'];
         if ( ! isset($this->tipos_campos[$row['Field']]['null']) )                               $this->tipos_campos[$row['Field']]['null'] = $row['Null'];
         if ( !empty($row['Default']) && ! isset($this->tipos_campos[$row['Field']]['default']) ) $this->tipos_campos[$row['Field']]['default'] = $row['Default'];
         if ( !empty($row['Extra']) && ! isset($this->tipos_campos[$row['Field']]['extra']) )     $this->tipos_campos[$row['Field']]['extra'] = $row['Extra'];

         // Buscamos valor maximo para campo

         preg_match("/\((.*)\)/",$row['Type'],$coincidencias);
         $max = FALSE;
         if ( isset($coincidencias[1]) ) {
            $max = $coincidencias[1];
            if ( strpos($max,',')  ) {
               $maxs = explode(',',$max);
               $max = $maxs[0] + $maxs[1] + 1;
               }
            $this->tipos_campos[$row['Field']]['max'] = $max;
         } else {

            // timestamp
            if ( $row['Type'] == 'timestamp' || $row['Type'] == 'datetime' ) {
               $max=20;
               $this->tipos_campos[$row['Field']]['max'] = $max;

            // date
            } elseif ( $row['Type'] == 'date' ) {
               $max=10;
               $this->tipos_campos[$row['Field']]['max'] = $max;

               }

            }

         }

      }

   /**
    * Definir tipos de formulario para el campo especificado
    *
    * Si un campo ya tiene definido un valor desde el modelo no se chafa
    *
    * @param $row Array con los datos del campo en la base de datos
    */

   function definir_tipos_formulario($row, $objPDO) {

      $driver = $objPDO->getAttribute(constant("PDO::ATTR_DRIVER_NAME"));

      if ( $driver == 'sqlite' ) {

         if ( isset($this->tipos_formulario[$row['name']]['tipo']) ) return ;

         if ( isset($this->tipos_campos[$row['name']]['max']) ) {
            if ( $this->tipos_campos[$row['name']]['max'] > 150 ) {
               $this->tipos_formulario[$row['name']]['tipo'] = 'textarea';
               $this->tipos_formulario[$row['name']]['cols'] = '30';
               $this->tipos_formulario[$row['name']]['rows'] = '3';
            } else {
               $this->tipos_formulario[$row['name']]['tipo'] = 'text';
               $this->tipos_formulario[$row['name']]['maxlength'] = $this->tipos_campos[$row['name']]['max'];
               $this->tipos_formulario[$row['name']]['size'] = ($this->tipos_campos[$row['name']]['max'] > 30) ? 30 : $this->tipos_campos[$row['name']]['max'];
               }
         } else {
            $this->tipos_formulario[$row['name']]['tipo'] = 'text';
            }

         /* Realciones entre tablas */

         if ( strpos($row['name'],$this->id_relacion ) !== FALSE ) {

            $this->tipos_formulario[$row['name']]['tipo'] = 'relacion';
            $this->tipos_formulario[$row['name']]['tabla'] = str_replace($this->id_relacion,'',$row['name']);

            }

         /* Para campos mail */

         if ( $row['name'] == 'mail'  ) {
            $this->tipos_formulario[$row['name']]['tipo'] = 'mail';
            }

         /* Para campos imagen_url */

         if ( $row['name'] == 'imagen_url'  ) {
            $this->tipos_formulario[$row['name']]['tipo'] = 'imagen_url';
            }

         // Campos 'text' seran 'textarea'
         
         if ( $row['type'] == 'text' ) {

            $this->tipos_formulario[$row['name']]['tipo'] = 'textarea';
            $this->tipos_formulario[$row['name']]['cols'] = '30';
            $this->tipos_formulario[$row['name']]['rows'] = '3';

            }

         // Campos 'date' seran 'fecha'
         
         if ( $row['type'] == 'date' ) {

            $this->tipos_formulario[$row['name']]['tipo'] = 'fecha';

            }

         // Campos 'timestamp' seran 'fecha_hora'
         
         if ( $row['type'] == 'timestamp' || $row['type'] == 'datetime' ) {

            $this->tipos_formulario[$row['name']]['tipo'] = 'fecha_hora';

            }

      } else {

         if ( isset($this->tipos_formulario[$row['Field']]['tipo']) ) return ;

         if ( isset($this->tipos_campos[$row['Field']]['max']) ) {
            if ( $this->tipos_campos[$row['Field']]['max'] > 150 ) {
               $this->tipos_formulario[$row['Field']]['tipo'] = 'textarea';
               $this->tipos_formulario[$row['Field']]['cols'] = '30';
               $this->tipos_formulario[$row['Field']]['rows'] = '3';
            } else {
               $this->tipos_formulario[$row['Field']]['tipo'] = 'text';
               $this->tipos_formulario[$row['Field']]['maxlength'] = $this->tipos_campos[$row['Field']]['max'];
               $this->tipos_formulario[$row['Field']]['size'] = ($this->tipos_campos[$row['Field']]['max'] > 30) ? 30 : $this->tipos_campos[$row['Field']]['max'];
               }
         } else {
            $this->tipos_formulario[$row['Field']]['tipo'] = 'text';
            }

         /* Realciones entre tablas */

         if ( strpos($row['Field'],$this->id_relacion ) !== FALSE ) {

            $this->tipos_formulario[$row['Field']]['tipo'] = 'relacion';
            $this->tipos_formulario[$row['Field']]['tabla'] = str_replace($this->id_relacion,'',$row['Field']);

            }

         /* Para campos mail */

         if ( $row['Field'] == 'mail'  ) {
            $this->tipos_formulario[$row['Field']]['tipo'] = 'mail';
            }

         /* Para campos imagen_url */

         if ( $row['Field'] == 'imagen_url'  ) {
            $this->tipos_formulario[$row['Field']]['tipo'] = 'imagen_url';
            }

         // Campos 'text' seran 'textarea'
         
         if ( $row['Type'] == 'text' ) {

            $this->tipos_formulario[$row['Field']]['tipo'] = 'textarea';
            $this->tipos_formulario[$row['Field']]['cols'] = '30';
            $this->tipos_formulario[$row['Field']]['rows'] = '3';

            }

         // Campos 'date' seran 'fecha'
         
         if ( $row['Type'] == 'date' ) {

            $this->tipos_formulario[$row['Field']]['tipo'] = 'fecha';

            }

         // Campos 'timestamp' seran 'fecha_hora'
         
         if ( $row['Type'] == 'timestamp' || $row['Type'] == 'datetime' ) {

            $this->tipos_formulario[$row['Field']]['tipo'] = 'fecha_hora';

            }

         }
      }

   /**
    * Restricciones automaticas, añadimos longitud maxima según información 
    * de la base de datos, en caso de no permitir null se añade minima 
    * longitud 1. Si ya tenemos una restricción añadida no la generamos así 
    * permitimos añadir restricciones personalizadas sin que las automaticas 
    * las chafen.
    */

   function restricciones_automaticas() {

      foreach ( $this->arRelationMap as $campo => $referencia) {

         if ( $campo == 'mail' ) $this->restricciones[$campo][RT_MAIL] = 1;

         if ( $campo == 'fecha_creacion' || $campo == 'fecha_modificacion'  ) continue;

         if ( isset($this->tipos_formulario[$campo]['oculto_form']) && $this->tipos_formulario[$campo]['oculto_form'] == 1 ) continue;

         // Buscamos longitud maxima
         if ( ! isset($this->restricciones[$campo][RT_LONG_MAX]) && isset($this->tipos_campos[$campo]['max'])  ) {
            $this->restricciones[$campo][RT_LONG_MAX] = $this->tipos_campos[$campo]['max'];
            }

         // Si un campo no permite null es requerido
         if ( ! isset($this->restricciones[$campo][RT_REQUERIDO]) && 
            isset($this->tipos_campos[$campo]['null']) && 
            $this->tipos_campos[$campo]['null'] == 'NO' && 
            !isset($this->tipos_campos[$campo]['Default']) ) {

               $this->restricciones[$campo][RT_REQUERIDO] = 1;
               $this->mensajes[$campo][RT_REQUERIDO] = literal('Campo obligatorio',3);

            }

         }

      }

   /**
    * Mensajes automáticos, en caso de tener el mensaje definido ya no lo chafamos
    */

   function mensajes_automaticos() {

      if ( isset($this->restricciones) ) {

         foreach ( $this->restricciones as $campo => $restriccion ) {

            foreach ( $restriccion as $tipo => $valor) {

               if ( ! isset($this->mensajes[$campo][$tipo]) ) {

                  switch ($tipo) {

                     case RT_MAIL:
                        $this->mensajes[$campo][$tipo] = literal('El correo no parece valido',3);
                        break;

                     case RT_LONG_MIN:
                        $this->mensajes[$campo][$tipo] = literal('Longitud minima',3);
                        break;

                     case RT_LONG_MAX:
                        $this->mensajes[$campo][$tipo] = literal('Longitud máxima',3)
                           .' '.$this->tipos_campos[$campo]['max'];
                        break;

                     case RT_CARACTERES_PERMITIDOS:
                        $this->mensajes[$campo][$tipo] = literal('Caracteres no permitidos',3);
                        break;

                     case RT_CARACTERES_NO_PERMITIDOS:
                        $this->mensajes[$campo][$tipo] = literal('Caracteres no permitidos',3);
                        break;

                     case RT_MENOR_QUE:
                        $this->mensajes[$campo][$tipo] = literal('Demasiado pequeño',3);
                        break;

                     case RT_MAYOR_QUE:
                        $this->mensajes[$campo][$tipo] = literal('Demasiado grande',3);
                        break;

                     case RT_IGUAL_QUE:
                        $this->mensajes[$campo][$tipo] = literal('No coincide',3);
                        break;

                     case RT_NO_IGUAL:
                        $this->mensajes[$campo][$tipo] = literal('No coincide',3);
                        break;

                     case RT_PASA_EXPRESION_REGULAR:
                        $this->mensajes[$campo][$tipo] = literal('Contenido no permitido',3);
                        break;

                     case RT_NO_PASA_EXPRESION_REGULAR:
                        $this->mensajes[$campo][$tipo] = literal('Contenido no permitido',3);
                        break;

                     }

                  }
               }

            }
         }

      }

   /**
    * Devolver valor dentro del array pasado, en caso de no haberlo
    * pasar el valor en base de datos, o en caso de no tener FALSE
    */

   function valores($campo,$displayHash=NULL) {

      if ( isset($displayHash['VALORES'][$campo])  ) {
         return $displayHash['VALORES'][$campo];
         }


      if (isset($this->ID)) {

         $strRetVal = $this->GetAccessor(ucfirst($campo));
         //$strRetVal = $this->GetAccessor($campo);
         if ( $strRetVal ) return $strRetVal;
         }

      // En caso de un valor definido expresamente en el modelo
      if ( isset($this->tipos_formulario[$campo]['valor'] ) ) 
         return $this->tipos_formulario[$campo]['valor'];

      return FALSE;

      }

   
   /**
    * Visualizar registro
    *
    * @return TRUE/FALSE
    */
   
   function visualizar_registro() {
   
      // Si tenemos definida una acción para visualizar el registro la lanzamos y nos vamos

      $salida = $this->visualizando_registro() ;

      if ( $salida ) { 
         echo $salida;
         return;
         }

      require_once(dirname(__FILE__).'/Formulario.php');

      $displayHash = ( isset($displayHash) ) ? $displayHash : NULL;

      /* Rellenamos datos para Formulario */

      foreach ( $this->arRelationMap as $campo => $R ) {

         if ( $campo != 'id' ) $this->tipos_formulario[$campo]['valor'] = $this->valores($campo, $displayHash);

         if ( isset($this->tipos_formulario[$campo]['tabla'])  ) {
            $modelo_relacionado = ucfirst($this->tipos_formulario[$campo]['tabla']);
            $id_relacionado = $this->GetAccessor($campo);
            $relacion = new $modelo_relacionado($this->objPDO,$id_relacionado);
            $this->tipos_formulario[$campo]['opciones'] = $relacion->listado_para_select();
            }

         }

      $form = new Formulario($this->tipos_formulario, $displayHash, $this->restricciones, $this->mensajes);

      $form->css = $this->css_formulario;

      // Si tenemos plantilla para visualizar desde el modelo se la pasamos a Formulario
      if ( isset($this->plantilla_visualizar) ) {
         $form->plantilla_visualizar = $this->plantilla_visualizar;
         }

      $form->genera_formulario(TRUE, $this->accion);

      if ( $this->galeria ) $this->galeria->inicia();

      }
   

   /**
    * Generar formulario
    */

   function generar_formulario($displayHash=NULL) {

      require_once(dirname(__FILE__).'/Formulario.php');

      /* Rellenamos datos para Formulario */

      foreach ( $this->arRelationMap as $campo => $R ) {

         // Si tenemos identificador lo añadimos oculto
         if ( $this->ID ) {
            $this->tipos_formulario[$this->DefineTableName().'_id']['valor'] = $this->ID;
            $this->tipos_formulario[$this->DefineTableName().'_id']['oculto_form'] = 1;
            }

         if ( $campo != 'id' ) $this->tipos_formulario[$campo]['valor'] = $this->valores($campo, $displayHash);

         if ( isset($this->tipos_formulario[$campo]['tabla'])  ) {
            $modelo_relacionado = ucfirst($this->tipos_formulario[$campo]['tabla']);
            $id_relacionado = $this->GetAccessor($campo);
            $relacion = new $modelo_relacionado($this->objPDO,$id_relacionado);
            $this->tipos_formulario[$campo]['opciones'] = $relacion->listado_para_select();
            }

         }

      $form = new Formulario($this->tipos_formulario, $displayHash, $this->restricciones, $this->mensajes);

      if ( $this->css_formulario ) $form->css = $this->css_formulario;

      // Si tenemos plantilla desde el modelo se la pasamos a Formulario
      if ( isset($this->plantilla_editar) ) $form->plantilla = $this->plantilla_editar;

      ?>
         <form name="crud" action="<?=$_SERVER['REDIRECT_URL']?>" method="post">
      <?php

      $form->genera_formulario(FALSE, $this->accion);

      if ( $this->galeria ) $this->galeria->inicia();

      ?>
         <p class="botonera_crud"><input type="submit" name="<?php echo $this->DefineTableName(); ?>_guardar" value="Guardar"></p>
      </form>
      <?php
      }

   /**
    * Administrar 
    *
    * @param $condicion  Condicion para el listado
    * @param $order      Orden para presentar el listado
    * @param $dir_img    Directorio de las imagenes de iconos
    */

   function administrar($condicion = FALSE, $order = FALSE, $dir_img = '', $permisos = FALSE) {

      global $gcm;

      $this->permisos = $permisos;

      $displayHash = array();

      if ( isset($_REQUEST[$this->DefineTableName().'_id']) ) $this->ID = $_REQUEST[$this->DefineTableName().'_id'];

      // Determinar acción actual

      if ( isset($_POST[$this->DefineTableName().'_guardar']) ) {
         $this->accion = 'guardando';
      } elseif ( isset($_REQUEST[$this->DefineTableName().'_insertar']) && $this->permisos ) {
         $this->accion = 'insertar';
      } elseif ( isset($_REQUEST[$this->DefineTableName().'_accio_galeria']) && $_REQUEST[$this->DefineTableName().'_accio_galeria'] == 'agafa_imatge' ) {
         $this->accion = 'agafa_imatge';
      } elseif ( isset($_SESSION['RESPUESTA_ERRONEA']) || isset($_POST[$this->DefineTableName().'_formulario'])) {
         $this->accion = 'con_errores';
      } elseif ( isset($_REQUEST[$this->DefineTableName().'_accion']) && $_REQUEST[$this->DefineTableName().'_accion'] == 'ver') {
         $this->accion = 'ver';
      } elseif ( isset($_REQUEST[$this->DefineTableName().'_accion']) && $_REQUEST[$this->DefineTableName().'_accion'] == 'eliminar') {
         $this->accion = 'eliminar';
      } elseif ( isset($_REQUEST[$this->DefineTableName().'_accion']) && $_REQUEST[$this->DefineTableName().'_accion'] == 'editar') {
         $this->accion = 'editar';
      } else {
         if ( isset($_REQUEST[$this->DefineTableName().'_id']) ) {
            $this->accion = 'ver';
         } else {
            $this->accion = 'inicio';
            }
         }

      // Permisos para la acción
      // Lanzar eventos si los hay para las acciones

      /** Acciones */

      // echo "Acción: ".$this->accion; // DEV

      if ( $this->accion == 'agafa_imatge' ) $this->galeria->inicia();

      if ( $this->accion == 'guardando' ) {

         $solicitud = new Solicitud();
         $solicitud->SetRedirectOnConstraintFailure(true);
         $_SESSION['VALORES'] = $solicitud->GetParameters();

         $conta=0;
         foreach ( $this->restricciones() as $campo => $restriccion ) {
            foreach ( $restriccion as $tipo => $valor ) {
               $restricciones[$conta] = new Restricciones($tipo, $valor);
               $solicitud->AddConstraint($campo, ENTRADAS_POST, $restricciones[$conta]);
               $conta++;
               }
            }

         $solicitud->TestConstraints();

         // Si hemos llegado aquí hemos pasado las pruebas

         /* Comprobamos datos que nos llegan */

            $resultado = $_POST;

            if ( $resultado ) {

               foreach ( $this->arRelationMap as $campo => $rCampo ) {

                  if ( isset($this->ID) && $campo == 'fecha_creacion'  ) {
                     $this->SetAccessor($rCampo, date("Y-m-d H:i:s"));
                     continue;
                     }

                  if ( $campo == 'fecha_modificacion'  ) {
                     $this->SetAccessor($rCampo, date("Y-m-d H:i:s"));
                     continue;
                     }

                  if ( $campo != 'id'  ) {
                     $this->SetAccessor($rCampo, $resultado[$campo]);
                     }

                  }

               if ( $this->save() ) {

                  /* Si utilizamos galería hay que guardar imagenes */

                  $this->accion = ( isset($this->ID) ) ? 'modificando' : 'insertando';

                  $this->ID = ( isset($this->ID) ) ? $this->ID : $this->ultimo_identificador();

                  /* Si tenemos eventos los lanzamos */

                  if ( $this->evento_guardar && $this->accion == 'insertando' ) {
                     $metodo = $this->evento_guardar;
                     $this->$metodo($this->ID);
                     }
                  if ( $this->evento_modificar && $this->accion == 'modificando' ) {
                     $metodo = $this->evento_modificar;
                     $this->$metodo($this->ID);
                     }

                  if ( $this->galeria ) $this->galeria->guardar($this->ID);

                  $mens = ( $this->accion == 'modificando' ) ? 'Registro modificado' : 'Registro incluido';
                  registrar(__FILE__,__LINE__,literal($mens,3),'AVISO');
               } else {
                  registrar(__FILE__,__LINE__,'ERROR al añadir o modificar registro','ERROR');
                  }

            }

         $_SESSION['dh'] = $displayHash;
         $_SESSION['mens'] = $gcm->reg->sesion;
         header("Location: ".$_SERVER['PHP_SELF']);
         exit(0);

      /* LLega con errores de formulario o se esta insertando */

      } elseif ( $this->accion == 'con_errores' ) {

         $solicitud = new Solicitud();
         $problemas = ($solicitud->IsRedirectFollowingConstraintFailure());
         $displayHash['HADPROBLEMS'] = $problemas;

         if ( $problemas  ) {

            $objFailingRequest = $solicitud->GetOriginalRequestObjectFollowingConstraintFailure();
            $arConstraintFailures = $objFailingRequest->GetConstraintFailures();

            $displayHash["PROBLEMS"] = Array();

            $mensajes_error = $this->mensajes();

            for ($i=0; $i<=sizeof($arConstraintFailures)-1; $i++) {

               $objThisConstraintFailure = &$arConstraintFailures[$i];
               $objThisFailingConstraintObject = $objThisConstraintFailure->GetFailedConstraintObject();
               $intTypeOfFailure = $objThisFailingConstraintObject->GetConstraintType();
               $nombre_parametro = $arConstraintFailures[$i]->GetParameterName();

               if ( isset($mensajes_error[$nombre_parametro][$intTypeOfFailure])  ) {
                  $displayHash["PROBLEMS"][$nombre_parametro] = $mensajes_error[$nombre_parametro][$intTypeOfFailure];
               } else {
                  $displayHash["PROBLEMS"][$nombre_parametro] = "Error sin definir";
                  }

               }

            if ( isset($_SESSION['VALORES']) ) {
               $displayHash['VALORES'] = $_SESSION['VALORES'];
               unset($_SESSION['VALORES']);
               }

            }

         $this->generar_formulario($displayHash);
         $this->botones_acciones($this->accion);

      } elseif ( $this->accion == 'ver' ) {

         $this->visualizar_registro();
         $this->botones_acciones($this->accion);

      } elseif ( $this->accion == 'eliminar' ) {

         /* Si tenemos evento_guardar lo lanzamos */
         if ( $this->evento_borrar ) {
            $metodo = $this->evento_borrar;
            $this->$metodo($this->ID);
            }

         $this->MarkForDeletion();
         $this->__destruct();

         registrar(__FILE__,__LINE__,"Registro eliminado",'AVISO');
         header("Location: ".$_SERVER['PHP_SELF']);
         exit(0);

      } elseif ( $this->accion == 'editar' || $this->accion == 'insertar' ) {

         $this->generar_formulario();
         $this->botones_acciones($this->accion);

      } else {

         $this->listado($condicion, $order);
         $this->botones_acciones($this->accion);

         }

      }

   /**
    * Botones para acciones
    */

   function botones_acciones($accion) {

      // Si no tenemos permisos de edición volvemos
      if ( ! $this->permisos ) return ;

      // Si estamos en formato ajax no se presentan botones
      if ( !isset($_REQUEST['formato']) || $_REQUEST['formato'] != 'ajax' ) {

         echo '<div class="botones_acciones">';

         // Boton insertar
         if ( $this->accion == "ver" || $this->accion == "inicio" ) {
            echo '<a class="formulari2" href="?'.$this->DefineTableName().'_insertar=1">'.literal('Insertar',3).' </a>&nbsp;';
            }

         // Boton editar
         if ( $this->accion != "editar" && $this->ID ) {
            echo ' <a class="formulari2" href="?'.$this->DefineTableName().'_accion=editar&'.$this->DefineTableName().'_id='.$this->ID.'">'.literal('Editar',3).' </a>&nbsp;';
            }

         // Boton borrar
         if (  $this->ID ) {
            echo ' <a class="formulari2" href="?'.$this->DefineTableName().'_accion=eliminar&'.$this->DefineTableName().'_id='.$this->ID.'">'.literal('Borrar',3).' </a>&nbsp;';
            }

         // Boton Cancelar
         if ( $this->accion == "editar" || $this->accion == 'insertar' ) {
            echo ' <a class="formulari2" href="'.$_SERVER['PHP_SELF'].'">'.literal('Cancelar',3).' </a> ';
            }

         echo '</div>';

         }
      }

   /**
    * Listado
    * @param $condicion Condicion para el listado
    * @param $order     Orden para presentar el listado, por defecto id desc
    */

   function listado($condicion = FALSE, $order = FALSE) {

      $sql = ( isset($this->sql_listado) ) ? $this->sql_listado : 'SELECT * FROM '.$this->strTableName;
      
      // La condición debe añadirse antes de GROUP en caso de lo haya.

      if ( $condicion ) {

         if ( stripos($sql,'GROUP') !== FALSE ) {
            $sql = str_ireplace('GROUP',' WHERE '.$condicion.' GROUP', $sql);
         } else {
            $sql .= ' WHERE '.$condicion ;
            }

         }

      $order = ( $order ) ? ' ORDER BY '.$order : ' ORDER BY id desc';

      require_once(GCM_DIR.'lib/int/GcmPDO/lib/paginarPDO.php');

      $this->elementos = ( $this->elementos_pagina ) ? $this->elementos_pagina : NULL;

      $pd = new PaginarPDO($this->objPDO, $sql, $this->strTableName.'_', $this->elementos_pagina, $order, $this->sql_listado_relacion);

      if ( $this->conf_paginador ) {
         foreach($this->conf_paginador as $atr => $valor ) {
            $pd->$atr = $valor;
         }
      }

      if ( $pd->validar() ) {

         if ( $this->permisos ) {
            $opciones = array(
               'url'           => '?'.$this->DefineTableName().'_id='
               ,'identifiador' => 'id'
               ,'modificar'    => 'editar'  
               ,'eliminar'     => 'eliminar'
               //,'ver'          => 'ver'
               ,'accion'       => $this->DefineTableName().'_accion'
               ,'ocultar_id'   => 1
               ,'dir_img'      => ( $this->dir_img_array2table ) ? $this->dir_img_array2table : NULL
               );
         } else {
            $opciones = array(
               'url'           => '?'.$this->DefineTableName().'_id='
               ,'identifiador' => 'id'
               //,'ver'          => 'ver'
               ,'accion'       => $this->DefineTableName().'_accion'
               ,'ocultar_id'   => 1
               ,'dir_img'      => ( $this->dir_img_array2table ) ? $this->dir_img_array2table : NULL
               );
         }

         $pd->generar_pagina($this->url_ajax, $opciones, $this->opciones_array2table);

      } else {
         registrar(__FILE__,__LINE__,"Tabla sin contenido",'AVISO');
         }

      }

   }

?>
