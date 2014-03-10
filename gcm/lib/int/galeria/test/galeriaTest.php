<?php

/**
 * @file galeriaTest.php
 *
 * @brief Test para Galeria 
 * 
 * $galeria->dir_base debe estar correctamente definido para poder presentar correctamente las imágenes
 *
 */

session_start();

function literal($l,$t=0) { echo $l; }

function registrar($file, $line,$mensaje, $tipo='DEBUG') {

   $_SESSION['msg'][] = $mensaje;
   return;

   ?>
   <div class="<?php echo strtolower($tipo); ?>"><?php echo $mensaje; ?></div>
   <?php

   }

define('GCM_DIR','../../../../');

foreach ( $_REQUEST as $key => $val ) {

   $_SESSION[$key] = $val;

   }

/* Opciones */

$id           = ( isset($_POST['id']) )               ? $_POST['id']              : FALSE;
$id_select    = ( isset($_REQUEST['id_select']) )     ? $_REQUEST['id_select']    : FALSE;
$accion       = ( isset($_REQUEST['accion']) )        ? $_REQUEST['accion']       : 'ver';
$modulo       = ( isset($_REQUEST['modulo'] ) )       ? $_REQUEST['modulo']       : FALSE;
$presentacion = ( isset($_REQUEST['presentacion'] ) ) ? $_REQUEST['presentacion'] : FALSE;
$debug        = ( isset($_REQUEST['debug'] ) )        ? TRUE                      : FALSE;

// Si hemos seleccionado una galeria cambiamos id por el de la selección

if ( isset($id_select) && ! empty($id_select) ) $id = $id_select;

if ( $debug == 1 ) {
   // define("GCM_DEBUG",TRUE);
   error_reporting( E_ALL );
   }

require('../lib/GaleriaFactory.php');

include('config.php');

?>
<!DOCTYPE HTML>
<meta charset="utf-8">
<html>
<head>
<script type="text/javascript" src="http://code.jquery.com/jquery-latest.js"></script>

<title>Pruebas para galeriaFile</title>
</head>
<body>

<style>
body{ font-family:Verdana, Geneva, sans-serif;}
h2 {
   color: white;
   padding: 10px;
   margin: 10px;
   border: 1px #fff solid;
   background: #333;
   }
.presentaImatgeEditar, .presentaImatgeEditar div {
   display: inline;
   }
.error { background: red; color: white; }
</style>

<?php

?>
<form action="" method="POST">

Nom de la galeria: <input type="text" name="id" value="<?php echo $id ?>"/>

Seleccionar galeria: 
<select name="id_select" 
onchange="this.form.id.value = '';return false;"
>
<option value="">Galerías</option>
<?php foreach (glob(GCM_DIR.'tmp/'.'galeriaTest/'.'*') as $gal) { $galerias = basename($gal); ?>
<option <?php if ( $gal == $id ) echo 'selected' ; ?> value="<?php echo $galerias?>"><?php echo $galerias?></option>
<?php } ?>
</select>

Presentació: 
<select name="presentacion">
<option value="">sense</option>
<?php foreach (glob('../presentacions/*') as $pre) { $presenta = basename($pre,'.phtml'); ?>
<option <?php if ( $presentacion == $presenta ) echo 'selected' ; ?> value="<?php echo $presenta?>"><?php echo $presenta?></option>
<?php } ?>
</select>

<!--
Moduls: 
<?php $modulos_posibles = array('plupload');?>
<select name="modulo" >
<option value="">sense</option>
<?php foreach ($modulos_posibles as $mod) { ?>
<option <?php if ( $modulo == $mod ) echo 'selected' ; ?> value="<?php echo $mod?>"><?php echo $mod?></option>
<?php } ?>
</select>
-->

<input <?php if ( $accion == 'editar' ) echo 'checked' ; ?> type="radio" name="accion" value="editar"/> editar
<input <?php if ( $accion == 'ver' ) echo 'checked' ; ?> type="radio" name="accion" value="ver"/> veure

<input <?php if ( $debug ) echo 'checked' ; ?> type="checkbox" name="debug" value="debug" /> debug

<input type="submit" value=">" />

<?php

$galeria = GaleriaFactory::inicia(dirname(__FILE__).'/config.php', $id); 

// Si se ha ejecutado submit hay que guardar la
// galería
if ( isset($_REQUEST['guardar_galeria']) ) {

   if ( ! $id ) {

      ?>
      <div class="error">
      Es Necesario un nombre de galería para poder guardar
      </div>
      <?php

   } else {

      $accion = 'ver';
      $galeria->guardar($id);

      }
   }

if ( $accion == 'ver' ) {
   ?>
   <h2>Presentar galeria</h2>
   <?php
   } else {
   ?>
   <h2>Editar galería</h2>
   <?php
   }
?>

<p>
Número d'imatges: <b><?php echo $galeria->count()?></b>/<?php echo $galeria->limit_imatges?>

Mides: <?php echo $galeria->amplaria_max.'x'.$galeria->altura_max
                    .' '.$galeria->amplada_presentacio.'x'.$galeria->altura_presentacio;?>
</p>
<hr />

<br>

<?php

if ( $accion == 'ver' ) {

   if ( $presentacion ) $galeria->plantilla_presentacio = $presentacion;

   // si no tenemos galeria seleccionada presentamos todas.

   // Juntamos el código javascript de cada galería
   $javascript = '';

   // Posible código con libreria de presentación en javascript
   $librerias = FALSE ;

   if ( $id ) {

      // presentaGaleria

      $galeria->presentaGaleria();
      $javascript .= $galeria->carga_js;

      if ( $galeria->carga_js_general() ) $librerias = $galeria->carga_js_general();

      if ( $galeria->carga_php_general() ) include(GCM_DIR.$galeria->carga_php_general());




   } else {

      $javascript = "";

      foreach (glob($galeria_config['dir_gal'].'*') as $gal) { $galerias = basename($gal); 
         echo "<h2>$galerias</h2>";
         $galeria = GaleriaFactory::inicia(dirname(__FILE__).'/config.php', $galerias); 
         if ( $presentacion ) $galeria->plantilla_presentacio = $presentacion;
         $galeria->presentaGaleria();
         ?>
         <div style="clear:both"></div>
         <?php
         $javascript .= $galeria->carga_js;
         if ( $galeria->carga_js_general() ) $librerias = $galeria->carga_js_general();

         if ( $galeria->carga_php_general() ) include(GCM_DIR.$galeria->carga_php_general());

         }

      }

      ?>
      <script type="text/javascript" charset="utf-8">

         $(document).ready(function () {

         // Cargamos librería de presentación

         <?php 
         if ( $librerias ) {
            foreach ( $librerias as $lib ) {
               if ( file_exists(GCM_DIR.$lib) ) {
                  echo "\n";
                  echo file_get_contents(GCM_DIR.$lib);
                  echo "\n";
               } else {
                  ?>
               // ERROR no se pudo obtener librería

               console.log('Error cargando librería: <?php echo GCM_DIR.$lib?>');

                  <?php
               }
            }
         }
          ?>
         <?php echo $javascript; ?>   

      });
      </script>

      <?php

} else {

   ?>
   <?php $galeria->formulario();?>
   <div style="clear: both"></div>
   <input name="guardar_galeria" type="submit"/>
   </form>

   <?php

   }

?>



<?php 

if ( $debug == 1 ) {

   ?>
   <div style="clear:both"></div>
   <h2>POST</h2>
   <pre>
      <?php print_r($_POST) ;?>
   </pre>
   <div style="clear:both"></div>
   <h2>SESSION</h2>
   <pre>
      <?php print_r($_SESSION) ;?>
   </pre>

   <h2>Galeria</h2>
   <pre>
      <?php echo $galeria;?>
   </pre>
   <?php


   }
?>
</body>
</html>
