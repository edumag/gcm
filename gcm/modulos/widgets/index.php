<?php

/** Módulo para insertar codigo externo. 
 * Buscamos en el directorio del proyecto DATOS/modulos/widgets/<evento>/*
 * si encontramos alguno lo incluimos directamente por orden de aparición
 *
 * Si el evento es columna se generara panel con titulo del nombre de archivo como literal
 * aplicando el formato de panel ocultable con javascript.
 *
 * @todo Añadir administración que nos permita editar los archivos del proyecto <evento>*.php
 * @todo Utilizar archivos numerados para insertar 01_columna.php, 02_columna.php...
 *
 * @author Eduardo Mágrane
 */

if ( $e ) {

   /** Directorio donde buscar conntenido */

   $dir_widgets = 'DATOS/modulos/widgets/'.$e.'/';

   if ( is_dir($dir_widgets) === TRUE ) {
      
      $dir=dir($dir_widgets);

      while ($fich_w = $dir->read()) {
      
         if ( $fich_w[0] !== '.' ) {

            $fichero_widget=$dir_widgets.$fich_w;
            list($nombre_widget, $extension) = explode('.',$fich_w);
            $nombre_widget = quitarUtf8($nombre_widget);

            if ( $e == 'columna' ) {
?>
               <div class="panel">
                  <span class="tituloPanel">
                     <a href="javascript:visualizar('caja_<?=$nombre_widget;?>');">
                        <?=literal($nombre_widget)?>
                     </a>
                  </span>
                  <div id="caja_<?=$nombre_widget;?>" class="subpanel_visible">
                     <?php include($fichero_widget); ?>
                  </div>
               </div>
<?php
            } else {
               include($fichero_widget);
               }
            }

         }

      }

   }

?>
