<?php

/**
 * @file
 * @brief Acciones para galeria
 */

session_start();

require('GaleriaFactory.php');

$accion = ( isset($_GET['galeria_accion']) ) ? $_GET['galeria_accion'] : FALSE ;
$id = ( isset($_GET['galeria_id']) && ! empty($_GET['galeria_id']) ) ? $_GET['galeria_id'] : FALSE ;

$galeria = GaleriaFactory::galeria(FALSE, $id);

$galeria->accion($accion);

exit();

