<?php

/**
 * @file
 * @brief Acciones para galeria
 */

session_start();

require('GaleriaFactory.php');

$accion = ( isset($_GET['galeria_accion']) ) ? $_GET['galeria_accion'] : FALSE ;
$id = ( isset($_GET['galeria_id']) && ! empty($_GET['galeria_id']) ) ? $_GET['galeria_id'] : FALSE ;
$dir_gcm = ( isset($_GET['galeria_dir_gcm']) && ! empty($_GET['galeria_dir_gcm']) ) ? $_GET['galeria_dir_gcm'] : FALSE ;

$galeria = GaleriaFactory::galeria(FALSE, $id);

$galeria->dir_gcm = $dir_gcm;
$galeria->accion($accion);

exit();

