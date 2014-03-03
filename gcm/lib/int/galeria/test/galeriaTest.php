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

require('../lib/GaleriaFactory.php');

foreach ( $_GET as $key => $val ) {

   $_SESSION[$key] = $val;

   }

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
</style>

<?php

/* Opciones */

$id           = ( isset($_GET['id']) )            ? $_GET['id']           : FALSE;
$accion       = ( isset($_GET['accion']) )        ? $_GET['accion']       : 'ver';
$modulo       = ( isset($_GET['modulo'] ) )       ? $_GET['modulo']       : FALSE;
$presentacion = ( isset($_GET['presentacion'] ) ) ? $_GET['presentacion'] : FALSE;
$debug        = ( isset($_GET['debug'] ) )        ? TRUE                  : FALSE;

?>
<form action="" method="GET">

Presentació: 
<select name="presentacion" >
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

<input type="submit" value="nova_galeria" />
<input type="submit" value=">" />

</form>
<?php

$config = array(
    "dir_tmp"              => '../../../../tmp/'
   ,"dir_gal"              => GCM_DIR.'tmp/'.'galeria_tmp/'
   ,"dir_base"             => "./"
   ,"dir_mod"              => "./".GCM_DIR.'lib/int/galeria/'
   ,"amplada_presentacio"  => 248
   ,"amplaria_max"         => 600 

   );

$galeria = GaleriaFactory::galeria($config, $id); 

// Posibles configuracións

// $galeria->descripcions = new DescripcionesGalerias('desc_galeria_noticies') ; // Descripcions

// Limit d'imatges

$galeria->limit_imatges = 5;
$galeria->id = 'test';


// Si se ha ejecutado submit hay que guardar la
// galería
if ( isset($_POST['guardar_galeria']) ) {

   $accion = 'ver';

   $galeria->guardar('test');

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

   // Aplicamos plantilla para piulades

   if ( $presentacion ) $galeria->plantilla_presentacio = $presentacion;

   // presentaGaleria

   $galeria->presentaGaleria();

} else {

   ?>
   <form action="" method="POST" >
   <?php $galeria->formulario();?>
   <div style="clear: both"></div>
   <input name="guardar_galeria" type="submit"/>
   </form>

   <?php

   }

?>



<?php 

if ( $debug == 1 ) {

   error_reporting( E_ALL );

   echo "<br />SESSION:<pre>" ; print_r($_SESSION) ; echo "</pre>"; // DEV  

   echo "<br />Directorio base del proyecto: <br /><b>".$galeria->dir_base."</b></p>";
   echo "<br />Directorio temporal en relacion a <br /><b>".$galeria->dir_tmp."</b></p>";
   echo "<br />Directorio principal del modulo: <br /><b>".$galeria->dir_mod."</b></p>";

   echo '<pre>';
   echo $galeria;
   echo '</pre>';

   }
?>
</body>
</html>
