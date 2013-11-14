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
 *
 * @todo Crear formulario con filtros para listado
 * @ingroup crud
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
 * - tabla_id: Para relaciones con otras tablas, esto nos permite por ejemplo
 *   presentar automáticamente un select con las opciones de los registros de la
 *   tabla relacionada.
 * - fecha_creacion como timestamp.
 * - fecha_modificacion como timestamp
 * - imagen_url Campo para url de imagen.
 *
 * Tener en cuenta que si estamos utilizando un módulo que requiere de otros módulos por
 * tener campos relacionados debemos hacer un require_once() con ellos.
 *
 */

class Crud extends DataBoundObject {

   public $elementos_pagina = 10;         ///< Número de elementos por página en listado, por defecto los de paginarPDO

   public $css_formulario   = TRUE ;      ///< Añadimos css para Formulario o no.

   public $url_ajax;                      ///< Si se utiliza ajax es necesaria la url a enviar

   /**
    * Nos permite diferencirar entre presentaciones, en caso de no definirlo se utilizara el nombre de la tabla con un 
    * guión bajo para separarlo. Al llamar a PaginadorPDO se utilizara este sufijo y en los botones de acción.
    */

   public $sufijo;

   /**
    * Tipo de tabla soportados: 
    *
    * - normal, por defecto.
    * - combinatoria
    * - relacion_varios
    * - relacion_externa
    *
    * Podemos definir el tipo de tabla al instanciarla, para poder controlar su comportamiento
    * desde la tabla padre.
    *
    * normal
    * ------
    * 
    * Tablas normales.
    *
    * relacion_varios
    * ---------------
    * 
    * Son las tablas que contienes registros varios relacionados con la tabla padre ( 1,* ), 
    * un registro de la tabla padre puede estar relacionado con varios registros de esta.
    * 
    * relacion_externa
    * ----------------
    * 
    * Son las tablas que contienes registros varios relacionados con la tabla padre ( *,* ), 
    * la relación esta definida en la tabla combinatoria.
    * 
    * combinatoria
    * ------------
    *
    * Una tabla combinatoria nos permite guardar relaciones multiples (*,*) entre dos tablas,
    * las cararcteristicas de estas tablas es que tienen dos indices y cada uno apunta al indice
    * de otra tabla.
    *
    * Automaticamente intentaremos deducir las relaciones de sus indices con la referencia de su
    * nombre, por ejemplo un indice que se llama usuarios_id deduciremos que apunta a la tabla
    * usuarios. No obstante podrá especificarse manualmente su relación.
    *
    * En una tabla combinatoria será necesario definir sus indices, ejemplo:
    * @code
    * function DefineRelationMap($pdo) {
    *       $retorno['usuarios_id,roles_id'] = 'ID';
    *       return $retorno;
    *       }
    * @endcode
    *
    */

   public $tipo_tabla = 'normal';

   /**
    * Podemos definir una plantilla personalizada para editar los registros, tener en
    * cuenta qudebe basarse en la plantilla por defecto 'registro_editar.phtml'
    */

   public $plantilla_editar = FALSE;

   /**
    * Podemos definir una plantilla personalizada para editar los registros, tener en
    * cuenta qudebe basarse en la plantilla por defecto 'registros_relacionados_editar.phtml'
    */

   public $plantilla_relacion_varios = FALSE;

   /**
    * Podemos definir una plantilla personalizada para editar los registros, tener en
    * cuenta qudebe basarse en la plantilla por defecto 'registros_combinados_editar.phtml'
    */

   public $plantilla_relacion_externa = FALSE;

   /**
    * Tablas que contienen contenido relacionado con el registro actual, permitiendonos
    * relacionar varios registros de otra tabla con la actual.
    */

   public $relaciones_varios = FALSE;

   /**
    * Relaciones con tablas combinatorias
    *
    * Ejemplo: 
    *
    * <pre>
    * $this->combinar_tablas[]   =  'tv_autors.id=tv_rel_autors.autors_id,tv_id';
    * </pre>
    *
    * tv_autors.id:             Tabla con la información que nos interesa relacionar con el campo que lo indexa
    * tv_rel_autors.autors_id:  Tabla combinatoria con el campo que contiene el identificador del autor
    * tv_id:                    Campo dentro de la tabla combinatoria que contiene el identificador de la tabla padre. 
    *
    * Todas las tablas afectadas deben estar instanciadas desde los modelos como una extensión de Crud.
    *
    * @todo Mejorar selección de registros combinatorios
    */

   public $combinar_tablas = FALSE;

   /**
    * Permitir exportar los resultados del listado a csv
    */

   public $exportar_csv = FALSE;

   /**
    * Añadir caja con posibles filtros
    */

   public $formulario_filtros = FALSE;

   /** Archivos css para los estilos */

   public $ficheros_css;

   /** Librerías javascript a argar */

   public $librerias_js;

   /** Código javascript a añadir */

   public $codigo_js;

   /** Reglas para el código javascript a añadir */

   public $reglas_js;

   /**
    * SQL para generar listado, por defecto 'SELECT * FROM tabla ORDER BY id desc' 
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
    * Opciones de presentación para el listado con array2table o
    * una extensión de él.
    *
    * Ejemplo:
    * @code
    * $this->opciones_array2table = array(
    *    'presentacion' => 'Array2table',
    *    'op' => array (
    *       'ocultar_id'=>TRUE
    *       , 'eliminar'=>'eliminar'
    *       , 'fila_unica'=>'comentario'
    *       , 'enlaces'=> array(
    *          'url' => array(
    *             'campo_enlazado'=>'contenido'
    *             ,'titulo_columna'=>'Contenido'
    *             ,'base_url'=>Router::$base
    *             )
    *          )
    *       )
    *    );
    * @endcode
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
    * Array con los atributos publicos que deamos pasar a PaginarPDO
    *
    * Para ver las posibilidades @see paginarPDO
    */

   public $conf_paginador = FALSE;        

   /**
    * Definimos la url del formulario al que se debe volver en caso de errpres,
    * pordefecto es $_SERVER["REDIRECT_URL"] 
    */

   protected $url_formulario = FALSE;

   /**
    * Podemos definir un metodo personalizado para la visualización de los registros 
    * individuales, en caso de no tenerlo se presentara el formulario en versión
    * de solo lectura
    */

   protected function visualizando_registro() { return FALSE ; }

   protected $evento_guardar = FALSE;     ///< Metodo a lanzar al guardar registro, recibe identificador de registro como parametro
   protected $evento_modificar = FALSE;   ///< Metodo a lanzar al modificar registro, recibe identificador de registro como parametro
   protected $evento_borrar  = FALSE;     ///< Metodo a lanzar al borrar registro, recibe identificador de registro como parametro

   protected $restricciones;              ///< Restricciones para Solicitud
   protected $mensajes;                   ///< Mensajes de respuesta a fallos de restricciones

   /**
    * Desde los modelos podemos definir los tipos campos para Formulario.
    *
    * Ejemplo: protected $tipos_formulario = array( 'nIdLocalitzacio' => array('oculto_form' => 1));
    *
    * @see Formulario
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

   /**
    * Especificaciones de los campos de la tabla
    *
    * Esta información nos permite añadir automaticamente las condiciones de 
    * los campos del formulario, asi como conocer el formato en que se deben 
    * presentar.
    *
    * Definición de los tipos de campo y sus carateristicas.
    *
    * Ejemplo:
    * @code
    *
    * [usuario] => Array
    *     (
    *         [tipo] => char(50)
    *         [null] => YES
    *         [max] => 50
    *     )
    *
    * [pass_md5] => Array
    *     (
    *         [tipo] => char(32)
    *         [null] => YES
    *         [max] => 32
    *     )
    *
    * [nombre] => Array
    *     (
    *         [tipo] => char(50)
    *         [null] => YES
    *         [max] => 50
    *     )
    *
    * [apellidos] => Array
    *     (
    *         [tipo] => char(50)
    *         [null] => YES
    *         [max] => 50
    *     )
    *
    * [fecha_creacion] => Array
    *     (
    *         [tipo] => datetime
    *         [null] => NO
    *         [max] => 20
    *     )
    *
    * [fecha_modificacion] => Array
    *     (
    *         [tipo] => timestamp
    *         [null] => NO
    *         [default] => CURRENT_TIMESTAMP
    *         [max] => 20
    *     )
    *
    * [mail] => Array
    *     (
    *         [tipo] => char(60)
    *         [null] => YES
    *         [max] => 60
    *     )
    *
    * [telefono] => Array
    *     (
    *         [tipo] => char(15)
    *         [null] => YES
    *         [max] => 15
    *     ) 
    * @endcode
    */

   protected $tipos_campos;

   protected $permisos = FALSE;           ///< Tenemos permisos de edición (T/F)  

   protected $accion = 'ver';             ///< Acción que se esta realizando

   /**
    * Directorio de imagenes utilizadas por @see Array2Table
    */

   protected $dir_img_array2table = FALSE;

   /**
    * sql que utilizamos para administrar, puede ser requerida para exportar,
    * nos da la sql del listado con la condición establecida pero sin el orden
    * ya que el orden viene por otro lado.
    */

   protected $sql;

   /** 
    * Constructor
    */

   function __construct(PDO $objPdo, $id = NULL, $tipo_tabla = 'normal') {

      global $gcm;

      $this->tipo_tabla = $tipo_tabla;

      parent::__construct($objPdo, $id);
 
      $this->restricciones_automaticas();
      $this->mensajes_automaticos();

      $this->sufijo = $this->strTableName.'_';

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
    *
    * Si definimos los campos desde el modelo tener en cuenta que:
    *
    * Si es una tabla combinatoria con dos indices el primer indice debe corresponder al 
    * que relaciona la tabla con el contenido y el segundo el que relaciona la tabla padre.
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
    * @see $tipos_campos
    *
    * @param $row Array con los datos del campo de la base de datos
    */

   private function definir_tipos_campo($row, $objPDO) {

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
    * @param $row    Array con los datos del campo en la base de datos
    * @param $objPDO Instancia de PDO
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

         /* Para campos pass_md5 */

         if ( $row['name'] == 'pass_md5'  ) {
            $this->tipos_formulario[$row['name']]['tipo'] = 'pass_md5';
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
         
         if ( ! isset($this->tipos_formulario[$row['name']]['tipo'])  && $row['type'] == 'text' ) {

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

      } else { // Para mysql

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

         /* Para campos pass_md5 */

         if ( $row['Field'] == 'pass_md5'  ) {
            $this->tipos_formulario[$row['Field']]['tipo'] = 'pass_md5';
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

         if ( $campo == 'pass_md5' ) $this->restricciones[$campo][RT_PASSWORD] = 1;

         if ( $campo == 'fecha_creacion' || $campo == 'fecha_modificacion'  ) continue;

         if ( isset($this->tipos_formulario[$campo]['oculto_form']) && $this->tipos_formulario[$campo]['oculto_form'] == 1 ) continue;

         // Buscamos longitud maxima
         if ( ! isset($this->restricciones[$campo][RT_LONG_MAX]) && isset($this->tipos_campos[$campo]['max'])  ) {
            $this->restricciones[$campo][RT_LONG_MAX] = $this->tipos_campos[$campo]['max'];
            }

         // Si un campo no permite null es requerido a no ser que sea una tabla relacionada
         // en ese caso no es obligatorio

         if ( isset($this->tipos_campos[$campo]['null']) 
            && $this->tipos_campos[$campo]['null'] == 'NO' 
            && !isset($this->tipos_campos[$campo]['Default']) 
            && $this->tipo_tabla == 'normal' ) {

               $this->restricciones[$campo][RT_REQUERIDO] = 1;
               $this->mensajes[$campo][RT_REQUERIDO] = literal('Campo obligatorio',3);

            }

         }

      }

   /**
    * Mensajes automáticos, en caso de tener el mensaje definido ya no lo chafamos
    *
    * Construimos las restricciones de javascript segun contenido de restricciones y mensajes
    *
    * Este metodo se basa en validate.js que se puede encontrar en:
    * http://rickharrison.github.com/validate.js/
    *
    * restricciones:
    *
    * campo[tipo restriccion][valor]
    *
    * @todo Los casos que estan comentados hay que buscar la manera de implementarlos
    */

   function mensajes_automaticos() {

      /** 
       * En caso de relaciones varios o externas añadimos corchetes a los nombres de los campos 
       * para que funcionen las validaciones.
       */

      $prefijo_names_js = ( $this->tipo_tabla == 'relacion_externa' || $this->tipo_tabla == 'relacion_varios' ) ? $this->sufijo : '' ;
      $sufijo_names_js = ( $this->tipo_tabla == 'relacion_externa' || $this->tipo_tabla == 'relacion_varios' ) ? '[]' : '' ;

      if ( isset($this->restricciones) ) {

         foreach ( $this->restricciones as $campo => $restriccion ) {

            foreach ( $restriccion as $tipo => $valor) {

               switch ($tipo) {

                  case RT_MAIL:
                     $this->mensajes[$campo][$tipo] = literal('El correo no parece valido',3);
                     $this->reglas_js .= "{
                        name: '$prefijo_names_js$campo$sufijo_names_js',
                        rules: 'valid_email'
                        },";
                     break;

                  case RT_LONG_MIN:
                     $this->mensajes[$campo][$tipo] = literal('Longitud minima',3);
                     $this->reglas_js .= "{
                        name: '$prefijo_names_js$campo$sufijo_names_js',
                        rules: 'min_length[$valor]'
                        },";
                     break;

                  case RT_LONG_MAX:
                     $this->mensajes[$campo][$tipo] = literal('Longitud máxima',3)
                        .' '.$this->tipos_campos[$campo]['max'];

                     $this->reglas_js .= "{
                        name: '$prefijo_names_js$campo$sufijo_names_js',
                        rules: 'max_length[$valor]'
                        },";
                     break;

                  case RT_CARACTERES_PERMITIDOS:
                     $this->mensajes[$campo][$tipo] = literal('Caracteres no permitidos',3);
                     break;

                  case RT_CARACTERES_NO_PERMITIDOS:
                     $this->mensajes[$campo][$tipo] = literal('Caracteres no permitidos',3);
                     break;

                  case RT_MENOR_QUE:
                     $this->mensajes[$campo][$tipo] = literal('Demasiado pequeño',3);
                     $this->reglas_js .= "{
                        name: '$prefijo_names_js$campo$sufijo_names_js',
                        rules: 'greater_than[$valor]'
                        },";
                     break;

                  case RT_MAYOR_QUE:
                     $this->mensajes[$campo][$tipo] = literal('Demasiado grande',3);
                     $this->reglas_js .= "{
                        name: '$prefijo_names_js$campo$sufijo_names_js',
                        rules: 'less_than[$valor]'
                        },";
                     break;

                  case RT_IGUAL_QUE:
                     $this->mensajes[$campo][$tipo] = literal('No coincide',3);
                     $this->reglas_js .= "{
                        name: '$prefijo_names_js$campo$sufijo_names_js',
                        rules: 'matches[$valor]'
                        },";
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

                  case RT_PASSWORD:
                     $this->mensajes[$campo][$tipo] = literal('Contraseña no pasa verificación',3);
                     break;

                  case RT_NO_ES_NUMERO:
                     $this->mensajes[$campo][$tipo] = literal('Debe ser un numero',3);
                     $this->reglas_js .= "{
                        name: '$prefijo_names_js$campo$sufijo_names_js',
                        rules: 'numeric[$valor]'
                        },";
                     break;

                  case RT_REQUERIDO:
                     $this->mensajes[$campo][$tipo] = literal('Campo requerido',3);
                     $this->reglas_js .= "{
                        name: '$prefijo_names_js$campo$sufijo_names_js',
                        rules: 'required'
                        },";
                     break;
                  }
               }

            }
         }

      // Mensajes para javascript

      if ( isset($this->mensajes) ) {
         foreach ( $this->mensajes as $campo => $mensajes ) {

            foreach ( $mensajes as $restriccion => $mensaje ) {

               switch ($restriccion) {

               case RT_MAIL:
                  $this->codigo_js.= "\nvalidator.setMessage('valid_email', '%s $mensaje');";
                  break;

               case RT_LONG_MIN:
                  $this->codigo_js.= "\nvalidator.setMessage('min_length', '%s $mensaje');";
                  break;

               case RT_LONG_MAX:
                  $this->codigo_js.= "\nvalidator.setMessage('max_length', '%s $mensaje');";
                  break;

               // case RT_CARACTERES_PERMITIDOS:
               //    $this->codigo_js.= "\nvalidator.setMessage('min_length', '%s $mensaje');";
               //    break;

               // case RT_CARACTERES_NO_PERMITIDOS:
               //    $this->codigo_js.= "\nvalidator.setMessage('min_length', '%s $mensaje');";
               //    break;

               case RT_MENOR_QUE:
                  $this->codigo_js.= "\nvalidator.setMessage('greater_than', '%s $mensaje');";
                  break;

               case RT_MAYOR_QUE:
                  $this->codigo_js.= "\nvalidator.setMessage('less_than', '%s $mensaje');";
                  break;

               case RT_IGUAL_QUE:
                  $this->codigo_js.= "\nvalidator.setMessage('matches', '%s $mensaje');";
                  break;

               // case RT_NO_IGUAL:
               //    $this->codigo_js.= "\nvalidator.setMessage('min_length', '%s $mensaje');";
               //    break;

               // case RT_PASA_EXPRESION_REGULAR:
               //    $this->codigo_js.= "\nvalidator.setMessage('min_length', '%s $mensaje');";
               //    break;

               // case RT_NO_PASA_EXPRESION_REGULAR:
               //    $this->codigo_js.= "\nvalidator.setMessage('min_length', '%s $mensaje');";
               //    break;

               case RT_NO_ES_NUMERO:
                  $this->codigo_js.= "\nvalidator.setMessage('numeric', '%s $mensaje');";
                  break;

               case RT_REQUERIDO:
                  $this->codigo_js.= "\nvalidator.setMessage('required', '%s $mensaje');";
                  break;

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


      if (isset($this->ID) && !empty($this->ID) ) {

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

         if ( $campo != array_search('ID',$this->arRelationMap) ) $this->tipos_formulario[$campo]['valor'] = $this->valores($campo, $displayHash);

         // Si es una tabla combinatoria miramos de añadir los campos que hacen de indice, ya
         // que probablemente no haya nada más que presentar.
         if ( count($this->campos_indices) > 1 ) {
            foreach ( $this->campos_indices as $indice ) {
               $this->tipos_formulario[$indice]['tipo'] = "relacion";
               $this->tipos_formulario[$indice]['valor'] = $this->valores[$indice];
               }
            }
         if ( isset($this->tipos_formulario[$campo]['tabla'])  ) {
            $modelo_relacionado = ucfirst($this->tipos_formulario[$campo]['tabla']);
            $id_relacionado = $this->GetAccessor($campo);
            $relacion = new $modelo_relacionado($this->objPDO,$id_relacionado);
            $this->tipos_formulario[$campo]['opciones'] = $relacion->listado_para_select();
            }

         }

      $form = new Formulario($this->tipos_formulario, $displayHash);

      // Si tenemos plantilla para visualizar desde el modelo se la pasamos a Formulario
      if ( isset($this->plantilla_visualizar) ) {
         $form->plantilla_visualizar = $this->plantilla_visualizar;
         }

      $form->genera_formulario(TRUE, $this->accion, $this);

      $this->visualizar_registros_relacionados();

      if ( $this->galeria ) $this->galeria->inicia();

      }
   
   /**
    * Generar formulario de filtros
    *
    * @param $displayHash Array con los valores
    * @param $nombre_campo_relacional Nombre del campo que contiene la relación con la tabla
    *        padre
    * @param $modelo_padre Enviamos el modelo padre para que pueda relacionarse con él y 
    *        enviarle petición de carga de scripts y conocer el identificador del registro.
    * @param $contador Contador, nos servira para las tablas relaciones_varias diferenciar entre registros.
    */

   function generar_formulario_filtro($displayHash=NULL, $nombre_campo_relacional = FALSE, $modelo_padre = FALSE, $contador = FALSE) {

      if ( isset($_REQUEST['formato']) && $_REQUEST['formato'] == 'ajax' ) return ;

      require_once(dirname(__FILE__).'/Formulario_filtro.php');

      /* Rellenamos datos para Formulario con sus valores si los tiene */

      foreach ( $this->arRelationMap as $campo => $R ) {

         // Si tenemos identificador lo añadimos oculto
         if ( $this->ID ) {
            $this->tipos_formulario[$this->sufijo.'id']['valor'] = $this->ID;
            $this->tipos_formulario[$this->sufijo.'id']['oculto_form'] = 1;
            }

         // Si la tenemos nombre_campo_relacional lo ocultamos.
         if ( $campo == $nombre_campo_relacional ) {
            if ( isset($modelo_padre->ID) && !empty($modelo_padre->ID) ) {
               $this->tipos_formulario[$campo]['valor'] = $modelo_padre->ID;
               $this->tipos_formulario[$campo]['oculto_form'] = 1;
            } else {
               $this->tipos_formulario[$campo]['ignorar'] = 1;
               }
            }

         // Si es una tabla combinatoria miramos de añadir los campos que hacen de indice, ya
         // que probablemente no haya nada más que presentar.
         if ( count($this->campos_indices) > 1 ) {
            foreach ( $this->campos_indices as $indice ) {
               $this->tipos_formulario[$indice]['tipo'] = "relacion";
               $this->tipos_formulario[$indice]['valor'] = $this->valores[$indice];
               }
            }

         if ( $campo != array_search('ID',$this->arRelationMap) ) 
            $this->tipos_formulario[$campo]['valor'] = $this->valores($campo, $displayHash);

         // Si es un campo con el indice de una tabla relacionada, buscamos los posibles 
         // valores desde su modelo.
         if ( isset($this->tipos_formulario[$campo]['tabla'])  ) {
            $modelo_relacionado = ucfirst($this->tipos_formulario[$campo]['tabla']);
            $id_relacionado = $this->GetAccessor($campo);
            $relacion = new $modelo_relacionado($this->objPDO,$id_relacionado);
            $this->tipos_formulario[$campo]['opciones'] = $relacion->listado_para_select();
            }

         }

      $form = new Formulario_filtro($this->tipos_formulario, $displayHash);

      // Si es una tabla de tipo 'relacion_varios' cargamos plantilla registros_relacionados_editar.phtml
      // y en caso de tener una definida en el modelo la cogemos.

      // if ( $this->tipo_tabla == 'relacion_varios' ) {

      //    if ( $this->plantilla_relacion_varios ) {
      //       $form->plantilla = $this->plantilla_relacion_varios;
      //    } else {
      //       $form->plantilla = dirname(__FILE__).'/../html/registros_relacionados_editar.phtml';
      //       }

      // // Si es una tabla de tipo relacion_externa

      // } elseif ( $this->tipo_tabla == 'relacion_externa' ) {

      //    if ( $this->plantilla_relacion_externa ) {
      //       $form->plantilla = $this->plantilla_relacion_externa;
      //    } else {
      //       $form->plantilla = dirname(__FILE__).'/../html/registros_combinados_editar.phtml';
      //       }

      // } else {

      //    // Si tenemos plantilla desde el modelo se la pasamos a Formulario
      //    if ( $this->plantilla_editar ) $form->plantilla = $this->plantilla_editar;

      //    }

      // Si es una tabla normal añadimos cabecera de formulario, es caso contrario es una tabla relacionada
      // no hay que hacerlo

      ?>
      <form id="crud_filtro" name="crud_filtro" action="" method="post">
      <fieldset>
      <legend><?php echo literal('Filtros')?></legend>
      <input type="hidden" name="url_formulario_filtro" value="<?php echo ( isset($_SERVER['HTTP_REFERER']) ) ? $_SERVER['HTTP_REFERER'] : $_SERVER['REQUEST_URI'] ?>" />

      <?php $form->genera_formulario(FALSE, $this->accion, $this); ?>

      <br />
      <p class="botonera_crud"><input type="submit" name="<?php echo $this->sufijo; ?>filtro" value="Filtrar"></p>
      </fieldset>
      </form>
      <?php


      }


   /**
    * Generar formulario
    *
    * @param $displayHash Array con los valores
    * @param $nombre_campo_relacional Nombre del campo que contiene la relación con la tabla
    *        padre
    * @param $modelo_padre Enviamos el modelo padre para que pueda relacionarse con él y 
    *        enviarle petición de carga de scripts y conocer el identificador del registro.
    * @param $contador Contador, nos servira para las tablas relaciones_varias diferenciar entre registros.
    */

   function generar_formulario($displayHash=NULL, $nombre_campo_relacional = FALSE, $modelo_padre = FALSE, $contador = FALSE) {

      require_once(dirname(__FILE__).'/Formulario.php');

      /* Rellenamos datos para Formulario con sus valores si los tiene */

      foreach ( $this->arRelationMap as $campo => $R ) {

         // Si tenemos identificador lo añadimos oculto
         if ( $this->ID ) {
            $this->tipos_formulario[$this->sufijo.'id']['valor'] = $this->ID;
            $this->tipos_formulario[$this->sufijo.'id']['oculto_form'] = 1;
            }

         // Si la tenemos nombre_campo_relacional lo ocultamos.
         if ( $campo == $nombre_campo_relacional ) {
            if ( isset($modelo_padre->ID) && !empty($modelo_padre->ID) ) {
               $this->tipos_formulario[$campo]['valor'] = $modelo_padre->ID;
               $this->tipos_formulario[$campo]['oculto_form'] = 1;
            } else {
               $this->tipos_formulario[$campo]['ignorar'] = 1;
               }
            }

         // Si es una tabla combinatoria miramos de añadir los campos que hacen de indice, ya
         // que probablemente no haya nada más que presentar.
         if ( count($this->campos_indices) > 1 ) {
            foreach ( $this->campos_indices as $indice ) {
               $this->tipos_formulario[$indice]['tipo'] = "relacion";
               $this->tipos_formulario[$indice]['valor'] = $this->valores[$indice];
               }
            }

         if ( $campo != array_search('ID',$this->arRelationMap) ) 
            $this->tipos_formulario[$campo]['valor'] = $this->valores($campo, $displayHash);

         // Si es un campo con el indice de una tabla relacionada, buscamos los posibles 
         // valores desde su modelo.
         if ( isset($this->tipos_formulario[$campo]['tabla'])  ) {
            $modelo_relacionado = ucfirst($this->tipos_formulario[$campo]['tabla']);
            $id_relacionado = $this->GetAccessor($campo);
            $relacion = new $modelo_relacionado($this->objPDO,$id_relacionado);
            $this->tipos_formulario[$campo]['opciones'] = $relacion->listado_para_select();
            }

         }

      $form = new Formulario($this->tipos_formulario, $displayHash);

      // Si es una tabla de tipo 'relacion_varios' cargamos plantilla registros_relacionados_editar.phtml
      // y en caso de tener una definida en el modelo la cogemos.

      if ( $this->tipo_tabla == 'relacion_varios' ) {

         if ( $this->plantilla_relacion_varios ) {
            $form->plantilla = $this->plantilla_relacion_varios;
         } else {
            $form->plantilla = dirname(__FILE__).'/../html/registros_relacionados_editar.phtml';
            }

      // Si es una tabla de tipo relacion_externa

      } elseif ( $this->tipo_tabla == 'relacion_externa' ) {

         if ( $this->plantilla_relacion_externa ) {
            $form->plantilla = $this->plantilla_relacion_externa;
         } else {
            $form->plantilla = dirname(__FILE__).'/../html/registros_combinados_editar.phtml';
            }

      } else {

         // Si tenemos plantilla desde el modelo se la pasamos a Formulario
         if ( $this->plantilla_editar ) $form->plantilla = $this->plantilla_editar;

         }

      // Si es una tabla normal añadimos cabecera de formulario, es caso contrario es una tabla relacionada
      // no hay que hacerlo

      if ( $this->tipo_tabla == 'normal' ) {
         ?>
         <form id="crud" name="crud" action="<?php if ( isset($_SERVER['PHP_SELF']) ) echo $_SERVER['PHP_SELF'];?>" method="post">
         <input type="hidden" name="url_formulario" value="<?php echo ( isset($_SERVER['HTTP_REFERER']) ) ? $_SERVER['HTTP_REFERER'] : $_SERVER['REQUEST_URI'] ?>" />
         <?php
         }

      if ( $this->tipo_tabla == 'normal' ) {
         $form->genera_formulario(FALSE, $this->accion, $this);
      } else {
         $form->genera_formulario(FALSE, $this->accion, $modelo_padre, $this->strTableName, $contador);
         }

      $this->formulario_registros_relacionados($displayHash);

      $this->formulario_registros_combinados($displayHash);

      if ( $this->galeria ) $this->galeria->inicia();

      if ( $this->tipo_tabla == 'normal' ) {
         ?>
         <p class="botonera_crud"><input type="submit" name="<?php echo $this->sufijo; ?>guardar" value="Guardar"></p>
         </form>
         <?php

         // Cargamos css, librerías javascript y código javascript
         // al final del formulario.

         if ( $this->css_formulario ) {
            ?>
            <style>
            <?php require(dirname(__FILE__).'/../css/formulario.css'); ?>
            </style>
            <?php
            }


         if ( $this->ficheros_css ) {
            foreach ( $this->ficheros_css as $fichero_css ) {
               ?>
               <link type="text/css" href="<?php echo $fichero_css;?>" rel="stylesheet" />
               <?php
               }
            }

         if ( $this->librerias_js ) {
            foreach ( $this->librerias_js as $libreria_js ) {
               ?>
               <script type="text/javascript" src="<?php echo $libreria_js;?>"></script>
               <?php
               }
            }

         if ( $this->reglas_js ) {

            $reglas_js = trim($this->reglas_js,',');
            $this->codigo_js = file_get_contents(dirname(__FILE__).'/../js/validate.js')."
              var validator = new FormValidator('crud', [$reglas_js], 
                 function(errors, events) {
                    if (errors.length > 0) {
                       salida = errors.join(".'"\n"'.");
                       alert(salida);
                       }
                    }
                  );
               ".$this->codigo_js;
            }

         if ( $this->codigo_js ) {
            ?>
            <script>
            addLoadEvent(function(){
               <?php echo $this->codigo_js;?>
            });
            </script>
            <?php
            }

         }

      }

   /**
    * Administrar 
    *
    * @param $condicion         Condicion para el listado
    * @param $order             Orden para presentar el listado
    * @param $dir_img           Directorio de las imagenes de iconos
    * @param $permisos          Especificar si tenemos permisos para modificar T/F
    * @param $accion_directa    Acción por defecto.
    */

   function administrar($condicion = FALSE, $order = FALSE, $dir_img = '', $permisos = FALSE, $accion_directa = FALSE) {

      global $gcm;

      $this->permisos = $permisos;

      $condicion = $this->anyadir_filtros($condicion);

      $displayHash = array();

      if ( isset($_REQUEST[$this->sufijo.'id']) ) $this->ID = $_REQUEST[$this->sufijo.'id'];

      // El identificador puede que venga por sesión.
      if ( ! $this->ID && isset($_SESSION['VALORES'][$this->sufijo.'id']) ) 
         $this->ID = isset($_SESSION['VALORES'][$this->sufijo.'id']);

      // Verificar registro
      if ( $this->ID ) {
         if ( ! $this->blIsLoaded ) {
            if ( ! $this->Load() ) {
               registrar(__FILE__,__LINE__,literal('No esiste registro').( ( isset($this->ID) && ! empty($this->ID) ) ? ' ['.$this->ID.']' : '' ),'ERROR');
               return FALSE;
               }
            }
      }

      // Determinar acción actual

      if ( isset($_POST[$this->sufijo.'guardar']) ) {
         if ( isset($_SESSION['RESPUESTA_ERRONEA']) || isset($_POST[$this->sufijo.'formulario'])) {
            $this->accion = 'con_errores';
         } else {
            $this->accion = 'guardando';
            }
      } elseif ( isset($_REQUEST[$this->sufijo.'insertar']) && $this->permisos ) {
         $this->accion = 'insertar';
      } elseif ( isset($_REQUEST[$this->sufijo.'accio_galeria']) && $_REQUEST[$this->sufijo.'accio_galeria'] == 'agafa_imatge' ) {
         $this->accion = 'agafa_imatge';
      } elseif ( isset($_REQUEST[$this->sufijo.'accion']) && $_REQUEST[$this->sufijo.'accion'] == 'ver') {
         $this->accion = 'ver';
      } elseif ( isset($_REQUEST[$this->sufijo.'accion']) && $_REQUEST[$this->sufijo.'accion'] == 'eliminar') {
         $this->accion = 'eliminar';
      } elseif ( isset($_REQUEST[$this->sufijo.'accion']) && $_REQUEST[$this->sufijo.'accion'] == 'editar') {
         $this->accion = 'editar';
      } elseif ( $accion_directa ) {
         $this->accion = $accion_directa;
      } elseif ( isset($_REQUEST[$this->sufijo.'csv']) ) {
         $this->exportar($condicion, $order, 'csv');
         return;
      } else {
         if ( isset($_REQUEST[$this->sufijo.'id']) ) {
            $this->accion = 'ver';
         } else {
            $this->accion = 'inicio';
            }
         }

      // Permisos para la acción
      // Lanzar eventos si los hay para las acciones

      /** Acciones */

      if ( $this->accion == 'agafa_imatge' ) $this->galeria->inicia();

      if ( $this->accion == 'guardando' ) {

         $solicitud = new Solicitud();
         $solicitud->SetRedirectOnConstraintFailure(true);
         // Si tenemos definida la url de vuelta al formulario la especificamos
         // a solicitud.
         if ( isset($_POST['url_formulario']) ) {
            $solicitud->SetConstraintFailureRedirectTargetURL($_POST['url_formulario']);
         } elseif ( $this->url_formulario ) {
            $solicitud->SetConstraintFailureRedirectTargetURL($this->url_formulario);
         } else {
            $solicitud->SetConstraintFailureRedirectTargetURL($_SERVER['REDIRECT_URL']);
            }
         $_SESSION['VALORES'] = $solicitud->GetParameters();

         if ( isset($this->restricciones) && ! empty($this->restricciones) ) {
            $conta=0;
            foreach ( $this->restricciones() as $campo => $restriccion ) {
               foreach ( $restriccion as $tipo => $valor ) {
                  $restricciones[$conta] = new Restricciones($tipo, $valor);
                  $solicitud->AddConstraint($campo, ENTRADAS_POST, $restricciones[$conta]);
                  $conta++;
                  }
               }
            }

         // Si tenemos registros relacionados de otras tablas tenemos que comprobar
         // sus restricciones teniendo en cuenta que llegan como arrays del formularios

         if ( $this->relaciones_varios ) {
            foreach ( $this->relaciones_varios as $relacion_varios ) {
               list($nombre_tabla,$nombre_campo_relacional) = explode('.',$relacion_varios); 
               $nombre_clase = ucwords($nombre_tabla);
               $condicion_relacion = "$nombre_campo_relacional = $this->ID";
               $rel = new $nombre_clase($this->objPDO, NULL, 'relacion_varios');

               if ( isset($rel->restricciones) && ! empty($rel->restricciones) ) {
                  $conta=0;
                  foreach ( $rel->restricciones() as $campo => $restriccion ) {
                     foreach ( $restriccion as $tipo => $valor ) {
                        $restricciones[$conta] = new Restricciones($tipo, $valor);
                        // En caso de estar insertando un registro nuevo hay que evitar las restricciones sobre
                        // el campo relacionado ya que al no tener un identificativo todavia nos dara error por 
                        // estar vacio.
                        if ( ! isset($this->ID) && !empty($this->ID) && $campo == $nombre_campo_relacional ) continue;
                        $solicitud->AddConstraint($rel->sufijo.$campo, ENTRADAS_POST, $restricciones[$conta]);
                        $conta++;
                        }
                     }
                  }

               }
            }

         // Si tenemos registros combinados de otras tablas tenemos que comprobar
         // sus restricciones teniendo en cuenta que llegan como arrays del formularios

         if ( $this->combinar_tablas ) {

            foreach ( $this->combinar_tablas as $combinados ) {

               list(
                  $tabla_contenido   
                  ,$campo_contenido   
                  ,$tabla_combinatoria
                  ,$campo_combinatoria
                  ,$campo_relacion    
                  ) = explode(',',$combinados); 

               $clase_contenido    = ucwords($tabla_contenido);
               $clase_combinatoria = ucwords($tabla_combinatoria);
               $condicion_combinatoria = "$campo_relacion = $this->ID";
               $rel = new $clase_combinatoria($this->objPDO, NULL, 'combinatoria');
               $ids_relacionados = $rel->find($condicion_combinatoria, array($campo_combinatoria));

               if ( $ids_relacionados ) {

                  foreach ( $ids_relacionados as $id_relacionado ) {

                     $id = $id_relacionado[$campo_combinatoria];
                     $condicion_contenido = "$campo_contenido = ".$id.' ';

                     $rel_contenido = new $clase_contenido($this->objPDO, NULL, 'relacion_externa');

                     if ( isset($rel_contenido->restricciones) && ! empty($rel_contenido->restricciones) ) {
                        $conta=0;
                        foreach ( $rel_contenido->restricciones() as $campo => $restriccion ) {
                           foreach ( $restriccion as $tipo => $valor ) {
                              $restricciones[$conta] = new Restricciones($tipo, $valor);
                              // En caso de estar insertando un registro nuevo hay que evitar las restricciones sobre
                              // el campo relacionado ya que al no tener un identificativo todavia nos dara error por 
                              // estar vacio.
                              if ( ! isset($this->ID) && !empty($this->ID) && $campo == $nombre_campo_relacional ) continue;
                              $solicitud->AddConstraint($rel->sufijo.$campo, ENTRADAS_POST, $restricciones[$conta]);
                              $conta++;
                              }
                           }
                        }
                     }
                  }
               }
            }

         $solicitud->TestConstraints();

         // Si hemos llegado aquí hemos pasado las pruebas

         /* Comprobamos datos que nos llegan */

            $resultado = $_POST;

            if ( $resultado ) {

               $this->recoger_valores_formulario($this, $resultado);

               $this->accion = ( isset($this->ID) && ! empty($this->ID) ) ? 'modificando' : 'insertando';

               if ( $this->save() ) {

                  /* Si utilizamos galería hay que guardar imagenes */

                  $this->ID = ( isset($this->ID) && ! empty($this->ID) ) ? $this->ID : $this->ultimo_identificador();

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

                  // Si tenemos registros relacionados de otras tablas hay que guardarlos tambien
                  // Primero borramos los que ya existan relacionados al registro padre
                  // Despues añadimos teniendo en cuenta que vendran en forma de arrays y con el
                  // nombre de la tabla como prefijo.

                  if ( $this->relaciones_varios ) {
                     foreach ( $this->relaciones_varios as $relacion_varios ) {
                        list($nombre_tabla,$nombre_campo_relacional) = explode('.',$relacion_varios); 
                        $nombre_clase = ucwords($nombre_tabla);
                        $condicion_relacion = "$nombre_campo_relacional = $this->ID";
                        $rel = new $nombre_clase($this->objPDO, NULL, 'relacion_varios');

                        // Recorremos identificadores de los registros relacionados existentes
                        $ids_relacionados = $rel->find($condicion_relacion, array('id'));
                        if ( $ids_relacionados ) {
                           foreach ( $ids_relacionados as $id_relacionado ) {
                              //echo "<br>id: ".$id_relacionado['id'];
                              $rel = new $nombre_clase($this->objPDO, $id_relacionado['id']);
                              $rel->MarkForDeletion();
                              }
                           }

                        // Guardar registros relacionado

                        $numero_registros_formulario = 20; // Ponemos un limite pero al darse cuenta que no hay datos parara
                        for ( $index = 0 ; $index < $numero_registros_formulario ; $index++ ) {

                           // Comprobar que no este marcado para eliminar
                           $nombre_campo_eliminar = $rel->strTableName.'_eliminar-'.$index;
                           if ( isset($resultado[$nombre_campo_eliminar]) ) continue; 

                           $nueva_relacion = new $nombre_clase($this->objPDO, NULL, 'relacion_varios');
                           if ( ! $this->recoger_valores_formulario($nueva_relacion, $resultado, $index) ) break ;

                           // Si estamos insertando faltara añadir el identificador del registro padre a los valores
                           // de los registros relacionados.
                           if ( $this->accion == 'insertando' ) {
                              $nueva_relacion->SetAccessor(ucwords($nombre_campo_relacional),$this->ID);
                              } 
                           $nueva_relacion->save();
                        
                           }
                        }
                     }

                  // Si tenemos registros combinados de otras tablas hay que guardarlos tambien
                  // Primero borramos los que ya existan relacionados al registro padre
                  // Despues añadimos teniendo en cuenta que vendran en forma de arrays y con el
                  // nombre de la tabla como prefijo.

                  if ( $this->combinar_tablas ) {

                     foreach ( $this->combinar_tablas as $combinatoria ) {

                        list(
                           $tabla_contenido   
                           ,$campo_contenido   
                           ,$tabla_combinatoria
                           ,$campo_combinatoria
                           ,$campo_relacion    
                           ) = explode(',',$combinatoria); 

                        $clase_contenido    = ucwords($tabla_contenido);
                        $clase_combinatoria = ucwords($tabla_combinatoria);
                        $condicion_combinatoria = "$campo_relacion = $this->ID";
                        $rel = new $clase_combinatoria($this->objPDO, NULL, 'combinatoria');
                        $ids_relacionados = $rel->find($condicion_combinatoria, array($campo_combinatoria,$campo_relacion));

                        if ( $ids_relacionados ) {

                           foreach ( $ids_relacionados as $id_relacionado ) {

                              $id = $id_relacionado[$campo_combinatoria];
                              $rel_combinatoria = new $clase_combinatoria($this->objPDO, "$id,".$this->ID, 'combinatoria');
                              $rel_combinatoria->MarkForDeletion();
                              unset($rel_combinatoria);
                              }

                           }

                        // Vamos añadiendo los id de la tabla con el contenido que añadimos para poder
                        // finalmente añadirlos a la tabla combinatoria

                        $identificadores_contenido_insertados = FALSE;

                        // Guardamos registros nuevos en tabla contenido si los hay y añadirlos
                        // a la lista de ids relacionados para añadirlos en la tabla combinatoria

                        $numero_registros_formulario = 20; // Ponemos un limite pero al darse cuenta que no hay datos parara
                        for ( $index = 0 ; $index < $numero_registros_formulario ; $index++ ) {

                           // Comprobar que no este marcado para eliminar
                           $nombre_campo_eliminar = $tabla_contenido.'_eliminar-'.$index;
                           if ( isset($resultado[$nombre_campo_eliminar]) ) continue; 

                           // Si tiene su propio identificador no hay que guardarlo
                           $nombre_campo_identificativo = $tabla_contenido.'_'.$tabla_contenido.'_id';
                           if ( isset($resultado[$nombre_campo_identificativo][$index]) ) { 
                              $ID = $resultado[$nombre_campo_identificativo][$index]; 
                              $identificadores_contenido_insertados[] = $ID;
                           } else {
                              $ID = NULL;

                              $nueva_relacion = new $clase_contenido($this->objPDO, $ID, 'relacion_externa');
                              if ( ! $this->recoger_valores_formulario($nueva_relacion, $resultado, $index) ) break ;

                              $nueva_relacion->save();
                              $identificadores_contenido_insertados[] = $nueva_relacion->ID;
                              unset($nueva_relacion);

                              } 

                           }

                        // Guardar registros en la tabla combinatoria

                        if ( $identificadores_contenido_insertados ) {

                           foreach ( $identificadores_contenido_insertados as $id_contenidos ) {
                              $ID = "$id_contenidos,".$this->ID;
                              $nueva_relacion = new $clase_combinatoria($this->objPDO, NULL, 'combinatoria');
                              $nueva_relacion->SetAccessor('ID', $ID);
                              $nueva_relacion->save();
                              unset($nueva_relacion);
                              }
                           }
                        }
                     }

                  $mens = ( $this->accion == 'modificando' ) ? literal('Registro modificado') : literal('Registro incluido');
                  registrar(__FILE__,__LINE__,literal($mens,3),'AVISO');
                  unset($_SESSION['VALORES']);
               } else {
                  registrar(__FILE__,__LINE__,literal('Error al añadir o modificar registro'),'ERROR');
                  }

            }

         $_SESSION['dh'] = $displayHash;
         $_SESSION['mens'] = $gcm->reg->sesion;
         $redireccion = $_SERVER['REDIRECT_URL'];
         header("Location: ".$redireccion);
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

         if ( ! $this->ID ) {
            registrar(__FILE__,__LINE__,literal('No existe registro'),'ERROR');
            return FALSE;
            }

         $this->visualizar_registro();
         $this->botones_acciones($this->accion);

      } elseif ( $this->accion == 'eliminar' ) {

         if ( ! $this->ID ) {
            registrar(__FILE__,__LINE__,literal('No existe registro'),'ERROR');
            return FALSE;
            }

         /* Si tenemos evento_guardar lo lanzamos */
         if ( $this->evento_borrar ) {
            $metodo = $this->evento_borrar;
            $this->$metodo($this->ID);
            }

         $this->MarkForDeletion();
         $this->__destruct();

         if ( $this->relaciones_varios ) {

            foreach ( $this->relaciones_varios as $relacion_varios ) {
               list($nombre_tabla,$nombre_campo_relacional) = explode('.',$relacion_varios); 
               $nombre_clase = ucwords($nombre_tabla);
               $condicion_relacion = "$nombre_campo_relacional = $this->ID";
               $rel = new $nombre_clase($this->objPDO, NULL, 'relacion_varios');

               // Recorremos identificadores de los registros relacionados existentes
               $ids_relacionados = $rel->find($condicion_relacion, array('id'));
               if ( $ids_relacionados ) {
                  foreach ( $ids_relacionados as $id_relacionado ) {
                     //echo "<br>id: ".$id_relacionado['id'];
                     $rel = new $nombre_clase($this->objPDO, $id_relacionado['id']);
                     $rel->MarkForDeletion();
                     }
                  }
              }
            }



         registrar(__FILE__,__LINE__,literal("Registro eliminado"),'AVISO');
         header("Location: ".$_SERVER['PHP_SELF']);
         exit(0);

      } elseif ( $this->accion == 'editar' ) {
         
         $this->generar_formulario();
         $this->botones_acciones($this->accion);

      } elseif ( $this->accion == 'insertar' ) {

         $this->generar_formulario(NULL);
         $this->botones_acciones($this->accion);

      } else {

         if ( $this->formulario_filtros ) $this->generar_formulario_filtro();
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
            echo '<a class="formulari2 boton" href="?'.$this->sufijo.'insertar=1">'.literal('Insertar',3).' </a>&nbsp;';
            }

         // Boton editar
         if ( $this->accion != "editar" && $this->ID ) {
            echo ' <a class="formulari2 boton" href="?'.$this->sufijo.'accion=editar&'.$this->sufijo.'id='.$this->ID.'">'.literal('Editar',3).' </a>&nbsp;';
            }

         // Boton borrar
         if (  $this->ID ) {
            echo ' <a onclick="return confirm(\''.literal('Confirmar borrado').'\');" id="a_eliminar_'.$this->sufijo.'" class="formulari2 boton" href="?'.$this->sufijo.'accion=eliminar&'.$this->sufijo.'id='.$this->ID.'">'.literal('Borrar',3).' </a>&nbsp;';
            }

         // Boton Cancelar
         if ( $this->accion == "editar" || $this->accion == 'insertar' ) {
            echo ' <a class="formulari2 boton" href="'.$_SERVER['PHP_SELF'].'">'.literal('Cancelar',3).' </a> ';
            }

         // Boton exportar csv
         if ( $this->exportar_csv && $accion == "inicio" ) {
            $filtro = $this->filtro2get(array('csv' => 1,'formato'=>'ajax'));
            echo ' <a class="formulari2 boton" href="'.construir_get($filtro).'">'.literal('Exportar a CSV').' </a> ';
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
      if ( $condicion ) $sql = $this->anyadir_condicion_sql($condicion,$sql);

      $order = ( $order ) ? ' ORDER BY '.$order : ' ORDER BY '.array_search('ID',$this->arRelationMap).' desc';

      require_once(GCM_DIR.'lib/int/GcmPDO/lib/paginarPDO.php');

      $this->elementos = ( $this->elementos_pagina ) ? $this->elementos_pagina : NULL;

      $pd = new PaginarPDO($this->objPDO, $sql, $this->sufijo, $this->elementos_pagina, $order, $this->sql_listado_relacion, $this->conf_paginador);

      // Configuración de paginador
      if ( $this->conf_paginador ) {
         foreach($this->conf_paginador as $atr => $valor ) {
            $pd->$atr = $valor;
         }
      }

      if ( $pd->validar() ) {

         if ( $this->permisos ) {
            $opciones = array(
               'url'           => '?'.$this->sufijo.'id='
               ,'identifiador' => 'id'
               ,'modificar'    => 'editar'  
               // ,'eliminar'     => 'eliminar'
               //,'ver'          => 'ver'
               ,'accion'       => $this->sufijo.'accion'
               ,'ocultar_id'   => 1
               ,'dir_img'      => ( $this->dir_img_array2table ) ? $this->dir_img_array2table : NULL
               );
         } else {
            $opciones = array(
               'url'           => '?'.$this->sufijo.'id='
               ,'identifiador' => 'id'
               //,'ver'          => 'ver'
               ,'accion'       => $this->sufijo.'accion'
               ,'ocultar_id'   => 1
               ,'dir_img'      => ( $this->dir_img_array2table ) ? $this->dir_img_array2table : NULL
               );
         }

         // Opciones personalizadas para presentación de datos
         if ( isset($this->opciones_array2table['op']) ) 
            $opciones = array_merge($opciones, $this->opciones_array2table['op']);

         $presentacion = ( isset($this->opciones_array2table['presentacion']) ) ?
            $this->opciones_array2table['presentacion'] :
            'Array2table' ;

         $pd->generar_pagina($this->url_ajax, $opciones, $presentacion);

      } else {
         registrar(__FILE__,__LINE__,literal("Tabla sin contenido"),'AVISO');
         }

      }

   /**
    * Visualizar los registros relacionados de otras tablas
    */

   function visualizar_registros_relacionados() {

      // Si tenemos un tipo de campo 'relaciones_varios' presentamos su propio form
      if ( $this->relaciones_varios ) {
         foreach ( $this->relaciones_varios as $relacion_varios ) {
            list($nombre_tabla,$nombre_campo_relacional) = explode('.',$relacion_varios); 
            $nombre_clase = ucwords($nombre_tabla);
            $condicion_relacion = "$nombre_campo_relacional = $this->ID";
            $rel = new $nombre_clase($this->objPDO, NULL, 'relacion_varios');
            $rel->listado($condicion_relacion);
            }
         }

      }
      
   /**
    * Formulario para los registros relacionados de otras tablas
    */

   function formulario_registros_relacionados($displayHash = FALSE) {

      // Si tenemos un tipo de campo 'relaciones_varios' presentamos su propio form
      if ( $this->relaciones_varios ) {

         foreach ( $this->relaciones_varios as $relacion_varios ) {

            list($nombre_tabla,$nombre_campo_relacional) = explode('.',$relacion_varios); 
            $nombre_clase = ucwords($nombre_tabla);
            $condicion_relacion = "$nombre_campo_relacional = $this->ID";
            $rel = new $nombre_clase($this->objPDO, NULL, 'relacion_varios');

            ?>
            <fieldset id="forms_<?php echo $nombre_tabla ?>" class="formularios_registros_varios">
            <legend  accesskey="r"><?php echo literal($nombre_clase) ?></legend>

            <?php
            // Recorremos identificadores de los registros relacionados existentes
            $ids_relacionados = $rel->find($condicion_relacion, array('id'));
            $conta = 0;
            if ( $ids_relacionados ) {

               foreach ( $ids_relacionados as $id_relacionado ) {

                  $rel->ID = $id_relacionado['id'];
                  $rel->tipos_formulario = FALSE;
                  $rel->load();
                  $rel->generar_formulario($displayHash, $nombre_campo_relacional, $this, $conta);
                  $conta++;

                  }
               }

            // Añadimos una más para poder añadir registros nuevos
            $rel = new $nombre_clase($this->objPDO, NULL, 'relacion_varios');
            $rel->generar_formulario(FALSE, $nombre_campo_relacional, $this, $conta);
            $this->codigo_js .= $rel->codigo_js;
            $this->reglas_js .= $rel->reglas_js;

            ?>
            </fieldset>
            <?php

            // Añadimos javascript para poder insertar registros nuevos
            $this->codigo_js .= "
               $nombre_tabla = new Administrar_registros_varios('$nombre_tabla',$conta); 
               $nombre_tabla.Literal_insertar = '".literal('Insertar nuevo '.$nombre_tabla)."';
               $nombre_tabla.inicia();
               ";

            }
         }
      }
      
   /**
    * Recoger campos de formulario.
    * 
    * @param $modelo Modelo basado en Crud
    * @param $resultado Array con los resultados, por lo general $_POST pero 
    *        podemos jugar con ello.
    * @param $numero_registro Numero de registro, para los casos en que los valores esten
    *        en un subarray de resultados.
    */

   function recoger_valores_formulario($modelo, $resultado, $numero_registro = FALSE) {

      // Retornamos T/F para conocer si hay valores o no

      $hay_valores = FALSE;

      foreach ( $modelo->arRelationMap as $campo => $rCampo ) {

         $valor = ( $numero_registro !== FALSE ) ? $resultado[$modelo->sufijo.$campo][$numero_registro] : $resultado[$campo];

         if ( ! empty($valor) && $valor ) $hay_valores = TRUE;

         if ( isset($modelo->ID) && ! empty($modelo->ID) && $campo == 'fecha_creacion'  ) {
            $modelo->SetAccessor($rCampo, date("Y-m-d H:i:s"));
            continue;
            }

         if ( $campo == 'fecha_modificacion'  ) {
            $modelo->SetAccessor($rCampo, date("Y-m-d H:i:s"));
            continue;
            }

         // pass_md5: password debe ser igual a la verificación y lo convertimos 
         // con md5()
         if ( $campo == 'pass_md5' && !empty($valor) ) {
            $modelo->SetAccessor($rCampo, md5($valor));
            continue;
            }

         // Si viene la contraseña vacia la eliminamos de los datos a modificar sino
         // la modifica sin ser lo que queremos.
         if ( $campo == 'pass_md5' && empty($valor) ) {
            $modelo->DelAccessor($rCampo);
            continue;
            }

         // Añadimos campo generico
         if ( $campo != array_search('ID',$modelo->arRelationMap)  ) {
            if (get_magic_quotes_gpc() == 1) {
               $modelo->SetAccessor($rCampo, stripslashes($valor));
            } else {
               $modelo->SetAccessor($rCampo, $valor);
               }
            }

         }

      return $hay_valores;

      }

   /**
    * Devolvemos array con los registros relacionados 
    *
    * @param $relacion Cadena que contiene la tabla relacionada y el campo (tabla.campo)
    * @param $campos   Array con los campos que se quieres recuperar
    * @param $orden    Orden para la SQL
    */

   function get_registros_relacion_varios($relacion, $campos=NULL, $orden=NULL) {

      list($nombre_tabla,$nombre_campo_relacional) = explode('.',$relacion); 
      $nombre_clase = ucwords($nombre_tabla);
      $condicion_relacion = "$nombre_campo_relacional = $this->ID";
      $rel = new $nombre_clase($this->objPDO, NULL, 'relacion_varios');
      $relacionados = $rel->find($condicion_relacion,$campos, $orden);
      return $relacionados;

      }

   /**
    * Devolvemos array con los registros relacionados de una tabla externa 
    *
    * @param $relacion Cadena que contiene la tabla relacionada y el campo (tabla.campo)
    * @param $campos   Array con los campos que se quieres recuperar
    * @param $orden    Orden para la SQL
    */

   function get_registros_relacion_combinada($relacion, $campos=NULL, $orden=NULL) {

      $tabla_contenido    = FALSE;  //< Tabla que contiene el contenido
      $campo_contenido    = FALSE;  //< Campo del contenido que contiene id
      $tabla_combinatoria = FALSE;  //< Tabla que contiene las relaciones
      $campo_combinatoria = FALSE;  //< Campo de la combinatoria que contiene id del contenido
      $campo_relacion     = FALSE;  //< Campo de la combinatoria que contiene id padre

      list(
         $tabla_contenido   
         ,$campo_contenido   
         ,$tabla_combinatoria
         ,$campo_combinatoria
         ,$campo_relacion    
         ) = explode(',',$relacion); 

      $clase_contenido    = ucwords($tabla_contenido);
      $clase_combinatoria = ucwords($tabla_combinatoria);

      $condicion_combinatoria = "$campo_relacion = $this->ID";

      $rel = new $clase_combinatoria($this->objPDO, NULL, 'combinatoria');

      $ids_relacionados = $rel->find($condicion_combinatoria, array($campo_combinatoria));

      if ( ! $ids_relacionados ) return FALSE;

      $ids_relacionados_sql = FALSE;
      foreach ( $ids_relacionados as $id_relacion ) {
         $ids_relacionados_sql .= $id_relacion[$campo_combinatoria].',';
         }

      $ids_relacionados_sql = rtrim($ids_relacionados_sql,',');

      $contenido = new $clase_contenido($this->objPDO);
      $condicion_contenido = "$campo_contenido IN (".$ids_relacionados_sql.")";

      return $contenido->find($condicion_contenido);
      }

   /**
    * Formulario para los registros combinados
    */

   function formulario_registros_combinados($displayHash = FALSE) {

      // Si tenemos un tipo de campo 'relaciones_varios' presentamos su propio form
      if ( $this->combinar_tablas ) {

         foreach ( $this->combinar_tablas as $combinados ) {

            $tabla_contenido    = FALSE;  //< Tabla que contiene el contenido
            $campo_contenido    = FALSE;  //< Campo del contenido que contiene id
            $tabla_combinatoria = FALSE;  //< Tabla que contiene las relaciones
            $campo_combinatoria = FALSE;  //< Campo de la combinatoria que contiene id del contenido
            $campo_relacion     = FALSE;  //< Campo de la combinatoria que contiene id padre

            list(
               $tabla_contenido   
               ,$campo_contenido   
               ,$tabla_combinatoria
               ,$campo_combinatoria
               ,$campo_relacion    
               ) = explode(',',$combinados); 

            // echo "<br>tabla_contenido    $tabla_contenido    ";
            // echo "<br>campo_contenido    $campo_contenido    ";
            // echo "<br>tabla_combinatoria $tabla_combinatoria ";
            // echo "<br>campo_combinatoria $campo_combinatoria ";
            // echo "<br>campo_relacion     $campo_relacion     ";

            $clase_contenido    = ucwords($tabla_contenido);
            $clase_combinatoria = ucwords($tabla_combinatoria);

            $condicion_combinatoria = "$campo_relacion = $this->ID";

            $rel = new $clase_combinatoria($this->objPDO, NULL, 'combinatoria');

            ?>
            <fieldset id="forms_<?php echo $tabla_contenido ?>" class="formularios_registros_varios">
            <legend  accesskey="r"><?php echo literal($clase_contenido) ?></legend>

            <?php
            // Recorremos identificadores de los registros relacionados existentes
            $ids_relacionados = $rel->find($condicion_combinatoria, array($campo_combinatoria));

            $conta = 0;
            if ( $ids_relacionados ) {

               foreach ( $ids_relacionados as $id_relacionado ) {

                  // Creamos instancias del modelo que contiene el contenido

                  $id = $id_relacionado[$campo_combinatoria];
                  $condicion_contenido = "$campo_contenido = ".$id.' ';

                  $rel_contenido = new $clase_contenido($this->objPDO, NULL, 'relacion_externa');
                  $rel_contenido->ID = $id;
                  $rel_contenido->tipos_formulario = FALSE;
                  $rel_contenido->load();
                  $rel_contenido->generar_formulario($displayHash, $campo_contenido, $this, $conta);
                  $conta++;

                  }
               }

            // Añadimos una más para poder añadir registros nuevos
            $rel_contenido = new $clase_contenido($this->objPDO, NULL, 'relacion_externa');
            $rel_contenido->generar_formulario(FALSE, $campo_contenido, $this, $conta);
            $this->codigo_js .= $rel->codigo_js;
            $this->reglas_js .= $rel->reglas_js;

            // Añadimos javascript para poder insertar registros nuevos
            $this->codigo_js .= "
               $tabla_contenido = new Administrar_registros_varios('$tabla_contenido',$conta); 
               $tabla_contenido.Literal_insertar = '".literal('Insertar nuevo '.$tabla_contenido)."';
               $tabla_contenido.inicia_combinatorio();
               ";

            // Añadimos opción de asignar registro existente
            $posibles = $rel_contenido->find();
            ?>
            <fieldset>
               <legend  accesskey="c"><?php echo literal('Seleccionar existente') ?></legend>
               <div class="posibles_registros" id="posibles_registros_<?php echo $tabla_contenido;?>">
                  <ul>
                     <?php if ( $posibles ) { ?>
                     <?php foreach ( $posibles as $posible ) { ?>
                     <?php $id_posible = $posible[$campo_contenido]; ?>
                     <li id="li_posible_<?php echo $tabla_contenido;?>-<?php echo $id_posible ?>">
                        <?php $conta=0; $salida = ''; foreach($posible as $campo => $valor ) { $conta++ ; ?>
                        <?php if ( $campo != $campo_contenido ) $salida .= literal($valor); ?>
                        <?php if ( $conta > 1 ) { break; } // Solo añadimos el siguiente campo al identificador ?> 
                        <?php } ?>
                        <a href="javascript:<?php echo $tabla_contenido; ?>.insertar_registro(<?php echo $id_posible; ?>,'<?php echo str_replace("'","\'",$salida) ?>')">
                           <?php echo $salida ?>
                        </a>
                     </li>
                     <?php } ?>
                     <?php } ?>
                  </ul>
               </div>

               </fieldset>
            </fieldset>
            <?php

            }

         }

      }

   /**
    * Añadir condición a la sql
    * 
    * @param $condicion Condición
    * @param $sql SQL a modificar por defecto $this->sql_listado 
    */

   function anyadir_condicion_sql($condicion, $sql=FALSE) {

      $sql = ( $sql ) ? $sql : $this->sql_listado ;

      if ( stripos($sql,'GROUP ') !== FALSE ) {
         $sql = str_ireplace('GROUP ',' WHERE '.$condicion.' GROUP ', $sql);
      } else {
         $sql .= ' WHERE '.$condicion ;
         }

      return $sql;

      }

   /**
    * Exportación, de momento solo a csv
    *
    * @param $condicion Condicion para el listado
    * @param $order     Orden para presentar el listado, por defecto id desc
    * @param $formato   Por defecto csv y de momento el único
    */

   function exportar($condicion=FALSE, $orden=FALSE,$formato='csv') {

      $sql = ( isset($this->sql_listado) ) ? $this->sql_listado : 'SELECT * FROM '.$this->strTableName;

      // La condición debe añadirse antes de GROUP en caso de lo haya.
      if ( $condicion ) $sql = $this->anyadir_condicion_sql($condicion,$sql);

      require_once GCM_DIR.'lib/int/GcmPDO/lib/GcmPDO.php';
      $export = new GcmPDO($this->objPDO, $sql);
      $export->to_csv(); 
      exit();
      
      }

   /**
    * Añadir filtros a la condición que nos vienen por GET p POST
    */

   function anyadir_filtros($condicion) {

      if ( ! isset($_REQUEST['filtro_campo']) ) return $condicion;

      $condicion = ( $condicion ) ? $condicion : '1' ;

      for ( $i=0; $i < count($_REQUEST['filtro_campo']); $i++ ) {
         $campo = $_REQUEST['filtro_campo'][$i];
         $cond  = $_REQUEST['filtro_condicion'][$i];
         $texto = $_REQUEST['filtro_texto'][$i];

         switch ($cond) {
            case 'igual':
               $condicion .= " AND ($campo = '$texto')";
               break;
            
            case 'contiene':
               $condicion .= " AND ($campo LIKE '%$texto%')";
               break;
            }
         }

      return $condicion;

      }

   /**
    * Convertimos filtro actual a GET para poder ser pasado desde enlaces
    *
    * @param $variables Podemos sumar las variables que deseemos, 
    *         formato: array('csv' => 1,'formato'=>'ajax')
    */

   function filtro2get($variables=FALSE) {

      $filtro = FALSE;
      if ( isset($_POST) ) {
         foreach ( $_POST as $k => $post ) {
            if ( is_array($post) && stripos($k,'filtro_') !== FALSE ) {    // Si cadena contiene '.html'
               $cf=0;
               foreach ( $post as $v ) {
                  $filtro[$k.'[]'] = $post[0];
                  $cf++;
                  }
               }
            }
         }

      if ( $filtro && $variables ) return array_merge($filtro,$variables);
      if ( $filtro ) return $filtro;
      if ( $variables ) return $variables;

      }
   }

?>
