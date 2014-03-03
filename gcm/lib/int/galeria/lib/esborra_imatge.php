<?php

/**
 * @file esborra_imatge.php
 * @brief Esborrar imatges de Galeria
 */

registrar(__FILE__,__LINE__,"Galeria desde esborra_imatge: ".depurar($this));

/* Agafem galeria de la sessio */

$this->esborrarImatge($_GET['id'],$_GET['idThumb']);

?>
