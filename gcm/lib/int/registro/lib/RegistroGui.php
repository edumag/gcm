<?php

/**
 * @file RegistroGui.php
 * @brief Presentación de registros
 */

require_once(GCM_DIR.'lib/int/gcm/lib/helpers.php');
require_once(dirname(__FILE__).'/Registro.php');

/** Modulo para la visualización de los registros de la aplizació
 *
 * Uso:
 * <pre>
 * $reg = Registro::getInstance();
 * $reg->registra(__FILE__,__LINE__,'Mensaje a registrar','ADMIN');         // Regsitro tipo ADMIN
 * $reg->ver_registros('sesion=21221212 AND tipo=ADMIN'); // Devolver registros de la session x que sean del tipo ADMIN 
 * </pre>
 *
 */

class RegistroGui extends Registro {

   static $cargado_css;  //< Saber si hemos cargado los css
   static $cargado_js ;  //< Saber si hemos cargado los js 

   /** 
    * Constructor 
    * 
    * @param $base_datos Base de datos puede ser una instancia de PDO o un 
    *           archivo sqlite
    * @param $sufijo Sufijo a utilizar al crear o consultar a base de datos
    */

   function __construct($base_datos=NULL, $sufijo='') {

      parent::__construct($base_datos,$sufijo);

      }

   /**
    * Presentamos tabla con los registros nos sirve para presentar
    * el array con los registros de la sesión
    * 
    *
    * @param $filtro Filtro en caso de tenerlo
    * @param $array  Array con los registros
    */

   function tabla_registros($filtro=NULL, $array=NULL) {

      if ( $array  ) {

         $registros = $array;

      } else {
         
         $registros = FALSE;

      }

      if ( !is_array($registros) || count($registros) < 1 ) {
         echo "<div class='aviso'>Sin registros</div>";
         return FALSE;
         }

      $conta = 0;
      reset($registros);
      while ( current($registros) ) {
         $registro = current($registros);
         list($id,$sesion,$fecha,$tipo,$fichero,$linea,$mensaje,$descripcion) = $registro;
         $resultado[$conta]['id'] = $id;
         $resultado[$conta]['sesion'] = $sesion;
         $resultado[$conta]['fecha'] = $fecha;
         $resultado[$conta]['tipo'] = $tipo;
         $resultado[$conta]['fichero'] = $fichero;
         $resultado[$conta]['linea'] = $linea;
         $resultado[$conta]['mensaje'] = $mensaje;
         $resultado[$conta]['descripcion'] = $descripcion;
         $conta++;
         next($registros);
         }

      require_once(GCM_DIR.'lib/int/array2table/lib/Array2table.php');

      //$opciones = array ('ocultar_id' => TRUE, 'fila_unica' => 'mensaje','table_id' => 'table');

      $opciones = array ('ocultar_id' => TRUE
                        ,'table_id' => 'table'
                        , 'fila_unica' => 'descripcion'
                        );


      $array2table = new Array2table();
      $array2table->generar_tabla($resultado, $opciones);

      return;

      }

   function filtro($filtro=NULL) {
   
      $reg = &$GLOBALS['registre_db'] ;

      $filtro = ( isset($_POST['filtro']) ) ? $_POST['filtro'] : FALSE ;

      ?>
      <div class="tabla_registros">
      <fieldset>
      <legend><?=literal('Registros',3)?></legend>
      <div class="ayuda">
      Podemos filtrar por id, sesion, fecha, tipo ('ERROR','AVISO','ADMIN','DEBUG'), fichero, mensaje
      <br />
      Ejemplos:
      <ul>
         <li>sesion>100 AND tipo='ERROR' ORDER BY id desc</li>
         <li>mensaje LIKE "%ERROR%"</li>
      </ul> 
      </div>
      <form name='form_ver_registros' action='' method='post' onSubmit='javascript: visualizar_registros(this); return false;'>
      <fieldset>
      <legend><?php echo literal('Filtro',3);?></legend>
         <input type="text" style='width:98%;' name='filtro' value="<?php echo $filtro; ?>">
      </fieldset>
      <input type='hidden' name='formato' value='ajax' />
      <br />
      <br />
      <input type='submit' />
      </form>
      </fieldset>

      <?php


      if ( isset($_POST['filtro']) ) { 
         $this->admin('ajax',$filtro); 
         }

      echo '</div>';

      if ( ! self::$cargado_js ) {
         ?>
         <script>
         <?php include(dirname(__FILE__).'/../js/registro.js'); ?>
         </script>
         <?php
         self::$cargado_js = TRUE;
         }

      if ( ! self::$cargado_css ) {
         ?>
         <style>
         <?php include(dirname(__FILE__).'/../css/registro.css'); ?>
         </style>
         <?php
         self::$cargado_css = TRUE;
         }

      }

   function admin($e, $filtro=FALSE) {

      require_once(dirname(__FILE__).'/../modelos/registros_crud.php');

      // if ( !isset($_GET['formato']) || ( isset($_GET['formato']) && $_GET['formato'] !== 'ajax' ) ) $this->filtro($filtro);

      $GLOBALS['sufijo_para_modelo'] = $this->sufijo;
      $registros = new Registros_crud($this->conexion);
      $registros->sufijo = '';
      $registros->url_ajax = '&formato=ajax';
      $registros->elementos_pagina = 60;
      $registros->exportar_csv = TRUE;
      $registros->formulario_filtros = TRUE;
      $registros->opciones_array2table = array(
          'op' => array (
             'ocultar_id'=>FALSE
             ,'fila_unica' => 'descripcion'
             ,'table_id' => 'table'
             // , 'enlaces'=> array(
             //    'cat_id' => array(
             //       'campo_enlazado'=>'Categoria'
             //       ,'titulo_columna'=>'Categoria'
             //       ,'base_url'=>'?cat='
             //       )
             //   )
             )
          );

      if ( ! isset($_GET['csv']) ) echo '<div id="caja_registros">';
      $registros->administrar($filtro, 'id desc', FALSE, TRUE);
      if ( ! isset($_GET['csv']) ) echo '</div>';

      }


   }

?>
