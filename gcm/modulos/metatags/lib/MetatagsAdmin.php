<?php

/**
 * @file      MetatagsAdmin.php
 * @brief     Administración de metatags.
 *
 * Añadir metatags a la página web
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 *   Created  26/02/10
 *  Revision  SVN $Id: Metatags.php 651 2012-10-17 09:19:07Z eduardo $
 * Copyright  Copyright (c) 2010, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

include "Metatags.php";

/** 
 * Administrar Metatags.
 *
 * @category Gcm
 * @package   Metatags
 * @author Eduardo Magrané
 * @version 0.1
 *
 */

class MetatagsAdmin extends Metatags {

  /** Constructor */

  function __construct() {

    parent::__construct();

    $this->Titulo      = $this->config('name');

  }

  function panel($e, $args=FALSE) {

    global $gcm;

    $this->javascripts('metatags.js');

    ob_start(); 
?>
    <div class="ayuda">

    Ten en cuenta que los metatags son literales que al presentarse se convertirán en el idioma adecuado, por lo tanto añádelos en el idioma por defecto.
    <br />
    <br />
    Puedes dejar un campo vacío para dejar que sea gcm el que lo defina.
    <br />
    <br />
    Por ejemplo en los títulos de las paginas por defecto gcm añade el literal del contenido donde estamos y la fecha de la última actualización del mismo, si añades un título aquí los cambios que pueda hacer otro módulo sobre ellos no afectaran.
    </div>
    <div id="panel_metatags">
      <form name="formmeta" action="#" onsubmit="guardar_metatags(this); return false;"; method="post" accept-charset="utf-8">
        <fieldset id="ftitulo">
          <legend><?php echo literal('Titulo',3) ?></legend>
        <input type="text" id="titulo" name="titulo" value="<?php echo $this->metatags['titulo'] ?>">
        </fieldset>
        <fieldset id="fdescription">
          <legend><?php echo literal('Descripción',3) ?></legend>
          <textarea name="description"><?php echo $this->metatags['description'] ?></textarea>
        </fieldset>
        <fieldset id="fkeywords">
          <legend>keywords</legend>
          <p>Palabras clave separadas por coma</p>
          <input type="text" name="keywords" value="<?php echo $this->metatags['keywords'] ?>">
        </fieldset>
        <input type="hidden" name="metatags_url" value="<?php echo Router::$s.Router::$c; ?>">
        <br />
        <p><input type="submit" value="<?php echo literal('Enviar',3) ?>"></p>
      </form>
    </div>
<?php

    $salida = ob_get_contents() ; ob_end_clean();

    $panel = array();
    $panel['titulo']     = literal('Metatags',3);
    $panel['oculto']     = TRUE;
    $panel['subpanel']   ='panel_metatags';
    // $panel['jajax']      = "?formato=ajax&m=literales&a=lista&columna=1"; 
    $panel['contenido']  = $salida; 

    Temas::panel($panel);
  }

  /**
   * Insertar.
   */

  function insertar($e, $args) {

    global $gcm;

    $valores = FALSE;

    $metatags_url = ( isset($_GET['metatags_url']) && ! empty($_GET['metatags_url']) ) ? $_GET['metatags_url'] : FALSE;
    $titulo = ( isset($_GET['titulo']) && ! empty($_GET['titulo']) ) ? $_GET['titulo'] : FALSE;
    $description = ( isset($_GET['description']) && ! empty($_GET['description']) ) ? $_GET['description'] : FALSE;
    $keywords = ( isset($_GET['keywords']) && ! empty($_GET['keywords']) ) ? $_GET['keywords'] : FALSE;

    if ( ! $metatags_url ) {
      registrar(__FILE__,__LINE__,"Sin url no se puede añadir metatags",'ERROR');
      return FALSE;
    }

    if ( $titulo ) $valores['titulo'] = $titulo;
    if ( $description ) $valores['description'] = $description;
    if ( $keywords ) $valores['keywords'] = $keywords;

    if ( !$valores ) {
      registrar(__FILE__,__LINE__,"Sin valores no se puede añadir metatags",'ERROR');
      return FALSE;
    }

    $this->config('url:'.$metatags_url,array($valores));

    registrar(__FILE__,__LINE__,"Metatags de pagina añadidos",'AVISO');

    print json_encode(
      array(
        'accion' => 'insertado',
        'elemento' => GUtil::textoplano($metatags_url),
        'valor' => $titulo
      )
    );
  }

}

?>
