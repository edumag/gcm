<?php

/**
 * @file LiteralesAdmin.php
 * @brief Administración del módulo literales
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 *   Created  23/11/09
 *  Revision  SVN $Id: Literales.php 638 2012-08-01 16:39:14Z eduardo $
 * Copyright  Copyright (c) 2009, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

require_once(dirname(__FILE__).'/Literales.php');

/**
 * @class LiteralesAdmin
 * @brief Administración de literales
 */

class LiteralesAdmin extends Literales {

  /**
   * Listado de literales que faltan en la pagina actual.
   */
  protected $literales_faltantes;

  function __construct() {

    parent::__construct();

  }

  /**
   * Botón para insertar literal.
   *
   * @param $proyecto Literal de proyecto o de aplicación.
   *
   * @return HTML con botón.
   */
  function boton_insertar_literal($proyecto=TRUE) {
    $proyecto_js = ( $proyecto ) ? 1 : 0;
      ?>
     <a class="boton" style="cursor: pointer;" onclick="javascript:insertar_literal(<?php echo $proyecto_js ?>)" >
        <?php echo literal('Añadir',3);?>
     </a>
      <?php
  }

  /**
   * Botón para filtrar literales.
   *
   * @param $proyecto Literal de proyecto o de aplicación.
   *
   * @return HTML con botón.
   */
  function boton_filtrar($panel, $texto, $title=FALSE) {
    ?>
     <a class="boton"
       <?php if ( $title ) { ?>
       title="<?php echo htmlentities(literal($title,3),ENT_QUOTES, "UTF-8")?>" 
       <?php } ?>
       style="cursor: pointer;" 
       onclick="javascript:filtra(this,'<?php echo $panel?>');" >
       <?php echo literal($texto,3) ?>
     </a>
    <?php
  }

  /**
   * Construir lista de lierales faltantes en pagina actual.
   */
  function construir_lista_literales_faltantes() {
    if ( permiso('administrar','literales') && isset($_SESSION['literales_faltantes']) && !empty($_SESSION['literales_faltantes']) ) {
      $literales_proyecto = $this->recoger_literales();
      foreach ( $_SESSION['literales_faltantes'] as $literal ) {
        if ( $literales_proyecto[$literal] == '' ) $this->literales_faltantes[$literal] = '';
      }
      unset($_SESSION['literales_faltantes']);
    }
  }

  /**
   * Panel con los literales que faltan en la pagina actual.
   */

  function paneladmin($e, $args=FALSE) {

    if ( permiso('administrar','literales') && isset($_SESSION['literales_faltantes']) && !empty($_SESSION['literales_faltantes']) ) {
      $this->construir_lista_literales_faltantes();
    }

    if ( $this->literales_faltantes ) {

      $this->javascripts('literales.js');

      ob_start();
      ?>
      <h3>Literales que faltan por traducir en la pagina actual</h3>
      <a class="boton" href="#" onclick="javascript:literales_faltantes();">Mostrar literales en la pagina</a>
      <?php
      $this->lista('literales_pagina',TRUE,TRUE);
      $salida = ob_get_contents() ; 
      ob_clean();

      $this->panel_admin('literales_pagina',count($this->literales_faltantes), literal('Faltan literales en la pagina actual'), $salida);

  }


}

/**
 * Listado de literales para modificar
 *
 * @param $panel
 *   Nombre del panel que contendrá los literales.
 * @param $proyecto
 *   Literales de proyecto o de aplicación. TRUE = proyecto.
 * @param $literales_pagina_actual
 *   Mostrar solo los de la pagina actual.
 */

function lista($panel='lit_columna', $proyecto = TRUE, $literales_pagina_actual=FALSE) {

  global $gcm;

  $boton_insertar = FALSE;
  $filtro_pendientes = FALSE;

  if ( isset($_GET['columna']) ) {
    $boton_insertar = TRUE;
    $filtro_pendientes = TRUE;
    $panel = "lit_columna";
    $proyecto = TRUE ;
  }

  if ( $panel == 'panel_admin' ) {
    $boton_insertar = TRUE;
    $filtro_pendientes = TRUE;
  }

  $proyecto_js = ( $proyecto ) ? '1' : '0' ;

  if ( $literales_pagina_actual ) {
    $literales = $this->literales_faltantes;
  } else {
    $literales = $this->recoger_literales($proyecto);
  }

  ?>
  <div id="<?php echo $panel?>">
  <ul id="litadmin">
  <?php

  if ( $boton_insertar ) {
  ?>
    <a class="boton" title="<?php echo htmlentities(literal('Añadir nuevo literal',3),ENT_QUOTES, "UTF-8");?>" style="cursor: pointer;" onclick="javascript:insertar_literal(1)">
      <?php echo literal('Añadir',3); ?>
    </a>
  <?php
  }

  if ( $filtro_pendientes ) {
    $this->boton_filtrar('subpanel','Filtro','Fitra literales con contenido');
  ?>
  <br />
  <?php
  }

  if ( $literales ) {

    foreach ( $literales as $key => $valor ) {

      $clase = ( empty($valor) ) ? 'subpanelNegativo' : "subpanel" ;

      $valor = ($valor) ? $valor : $key ;

      if ( ! isset($_GET['columna']) ) {
      
       ?>
       <li id="lit_<?php echo $key ?>" class="<?php echo $clase?>">
          <a class="literal_faltante_<?php echo GUtil::textoplano($key) ?>  " title="Modificar <?php echo GUtil::textoplano($key) ?>" 
             href="javascript:;" onclick="modificar_literal('<?php echo $key?>','<?php echo str_replace("'","\'",$valor)?>',<?php echo $proyecto_js ?>)" >
             <?php echo $valor ?>
          </a>
          <div style="visibility: hidden;">
             <a title="Eliminar" 
                href="javascript:;" onclick="eliminar_literal('<?php echo $key ?>',<?php echo $proyecto_js ?>)" >
                [X]
             </a>
          </div>
       </li>
      <?php

      } else {

        ?>
       <li id="lit_<?php echo $key ?>" class="<?php echo $clase?>">
         <a href="javascript:;" 
            class="literal_faltante_<?php echo GUtil::textoplano($key) ?>  
            onclick="tinyMCE.execCommand('mceInsertContent',false,'{L{<?php echo $key?>}}'); return false"
            title="<?php echo htmlentities($key.' ('.$valor.')',ENT_QUOTES, "UTF-8") ?>">
            <?php echo $valor ?>
         </a>
         <a style="font-size: smaller;" title="Eliminar" 
            href="javascript:;" onclick="eliminar_literal('<?php echo $key ?>',1)" >
            [X]
         </a>
         <a style="font-size: smaller;" title="Modificar" 
            href="javascript:;" onclick="modificar_literal('<?php echo $key.'\',\''.$valor?>')" >
            [M]
         </a>
       </li>
        <?php

      }
  }


  }

  ?>
         </ul>
      </div>
  <?php

}

/**
 * Recoger literales 
 *
 * @param $proyecto Especificar si queremos los de proyecto o de la aplicación.
 *
 * @return Array con los literales
 */

function recoger_literales($proyecto=TRUE) {

  global $gcm;

  $literales_default = FALSE;

  if ( ! $proyecto ) {
    $file = GCM_DIR."DATOS/idiomas/GCM_LG_".Router::$i.".php";
  } else {
    $file = $gcm->config('idiomas','Directorio idiomas')."LG_".Router::$i.".php";
  }

  $arr = GcmConfigFactory::GetGcmConfig($file);

  $literales = $arr->variables();

  if ( Router::$i != Router::$ii ) {

    if ( !$proyecto ) {
      $file_default = GCM_DIR."DATOS/idiomas/GCM_LG_".Router::$ii.".php";
    } else {
      $file_default = $gcm->config('idiomas','Directorio idiomas')."LG_".Router::$ii.".php";
    }

    if ( !file_exists($file_default) ) {
      trigger_error('Archivo de idiomas ['.$file_default.'] no existe', E_USER_ERROR);
      return FALSE;
    }
    $arr_default = GcmConfigFactory::GetGcmConfig($file_default);
    $literales_default = $arr_default->variables();

  }

  if ( $literales_default ) {
    foreach ( $literales_default as $key => $lit ) {
      if ( ! isset($literales[$key]) ) $literales[$key] = '';
    }
  }

  return $literales;

}

 /**
  * Modificar literal
  *
  * $_GET Parametros recogidos mediante GET
  *   - elemento: clave del array a modificar
  *   - valor:    Valor a añadir
  *   - file:     Archivo con array, de formato especifico
  *               En caso de no haberlo cogemos el del idioma actual
  *
  * @see GcmConfig
  */

  function modificarLiteral() {

    global $gcm;

    if ( isset($_GET['proyecto']) && $_GET['proyecto'] == 0 ) {
      $file = GCM_DIR."DATOS/idiomas/GCM_LG_".Router::$i.".php";
    } else {
      $file = $gcm->config('idiomas','Directorio idiomas')."LG_".Router::$i.".php";
    }

    $arr = GcmConfigFactory::GetGcmConfig($file);

    $arr->set($_GET['elemento'],trim($_GET['valor']));

    $arr->guardar_variables();

    registrar(__FILE__,__LINE__,"Literal (".$_GET['elemento'].") modifcado con [".$_GET['valor']."]",'AVISO');
    
    print json_encode(
      array(
        'accion' => 'modificado',
        'elemento' => GUtil::textoplano($_GET['elemento']),
        'valor' => $_GET['valor']
      )
    );
    if ( Router::$formato == 'ajax' ) exit();
  }

  /** 
   * Presentar panel de literales 
   *
   * @param $e Evento
   * @param $args Array de argumentos
   *
   */

  function panel_literales($e,$args='') {

    $this->javascripts('literales.js');

    ob_start(); 
    echo '<div id="panelLiterales">';
    // $this->devolverLiterales(); 
    echo '</div>';
    $salida = ob_get_contents() ; ob_end_clean();

    $panel = array();
    $panel['titulo']     = literal('Literales',3).'['.Router::$i.']';
    $panel['oculto']     = TRUE;
    $panel['subpanel']   ='panelLiterales';
    $panel['jajax']      = "?formato=ajax&m=literales&a=lista&columna=1"; 
    $panel['contenido']  = $salida; 

    Temas::panel($panel);

  }

  /**
   * Administración de literales
   *
   * @param  $e Evento
   * @param  $args Argumentos
   * @return TRUE/FALSE
   *
   */
 
 function administrar($e,$args=NULL) {

    global $gcm;

    $this->javascripts('literales.js');

    // Añadimos contenido a título
    $gcm->titulo = 'Literales de '.literal(Router::$i);
    // Anulamos eventos que son llamados para generar el título
    $gcm->event->anular('titulo','literales');  
    $gcm->event->anular('contenido','literales');  

    echo '<h3>'.literal('Literales de proyecto').'('.literal(Router::$i).')</h3>';
    $this->lista('panel_admin');
    echo '<h3>'.literal('Literales de aplicación').'</h3>';
    $this->lista('panel_admin_gcm',FALSE);

    return; // DEV
 
    }


 /**
  * Eliminar literal
  *
  * Eliminamos literal especifico
  */

 function eliminar_literal() {

    global $gcm, $LG, $GCM_LG;

    $elemento = $_GET['elemento'];
    $proyecto = ( isset($_GET['proyecto']) ) ? $proyecto : 1 ;
    
    if ( $proyecto == 1 ) {
       $dir   = GCM_DIR."DATOS/idiomas/";
       $array = "GCM_LG_";
       $literal_de = 'aplicación';
    } else {
       $dir   = $gcm->config('idiomas','Directorio idiomas');
       $array = "LG_";
       $literal_de = 'proyecto';
       }

    foreach ( Router::$idiomas as $idioma ) {
       $file=$dir.$array.$idioma.".php";
       $arr = GcmConfigFactory::GetGcmConfig($file);
       $arr->del($elemento);
       unset($arr);
       }

    registrar(__FILE__,__LINE__,"Literal de $literal_de [$elemento] eliminado",'AVISO');
    
    print json_encode(
      array(
        'accion' => 'borrado',
        'elemento' => GUtil::textoplano($elemento),
        'valor' => ''
      )
    );
    exit();
    }
}


?>
