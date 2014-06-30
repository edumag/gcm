<?php

// PDO para descripciones

$galeria_pdo = new PDO('sqlite:'.dirname(__FILE__).'/test.db');

// Configuración de galería

$galeria_config = array(
    "dir_gal"              => GCM_DIR.'tmp/'.'galeriaTest/'
   ,"dir_base"             => "./"
   ,"amplada_presentacio"  => 250
   ,"amplaria_max"         => 600 
   ,"limit_imatges"        => 5 
   ,"descripcions_config"  => array(
      'tabla' => 'descripciones'
      ,'config' => FALSE
      )
   );

?>
