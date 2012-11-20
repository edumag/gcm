<?php

/** imagenes.js.php 
*
* javascript para la administración de imagenes
*
* @author Eduardo Magrané
* @version 1.0
*/

// vim:cal SetSyn("javascript") 

header('Content-Type: text/javascript');

?>

/*
* Visualizar un imagen
*
* Abrimos una ventana emergente con la imagen
*
* @param img url de la imagen
* @param ancho ancho de la imagen
* @param alto alto de la imagen
*/

function verImagen(img, ancho, alto) {
   open(img,img,'toolbar=no,scrollbars=no,width='+ancho+'px,height='+alto+'px');
}

/**
* Presentar imagenes de la sección que estamos editando
* en el administrador de imagenes
*/

function editarImagenesAdmin(){

  if (pedido.readyState == 4 ) {
   if ( pedido.status == 200 ) {
     // Imagenes del diectoio las tenemos en listaImg.js
        var container = document.getElementById('thumbnails');
        var salida = '' ;
        //var datos = eval(pedido.responseText);
        var datos = eval('('+pedido.responseText+')');
         if ( datos ) {
           var conta=0;
           //for ( var x=0; x<datos.length ; x++ ) {
           for ( i in datos.imgs ) {
               var x = conta;
               conta++;
               // presentar las imagenes que se encuentran en el arrray
               salida = salida + '<p class="thumb" ><tt>';
               salida = salida + '<a rel="thumbnail" onclick=\'open("' + datos.imgs[i][1] + '","'+datos.imgs[i][0]+'","toolbar=no,scrollbars=no,width='+datos.imgs[i][3]+'px,height='+datos.imgs[i][4]+'px")\' >';
               salida = salida + '<img width="100px" id="thumb_'+x+'" src="'+datos.imgs[i][2]+'" alt="imagen" /></a>';
               salida = salida + '<br /><span class="idim"><b>'+datos.imgs[i][0]+'</b><br /> ['+datos.imgs[i][3]+'x'+datos.imgs[i][4]+'] '+datos.imgs[i][5];
               salida += '  [<a href="javascript:;" onmousedown="pedirDatos(\'?m=imagenes&a=borrarImg&img='+datos.imgs[i][1]+'\',\'borrarImg\',\'Borrar imágen\')" >X</a>] ';
               if ( datos.imgs[i][6]  ) {
                  salida += '[<a href="javascript:;" onmousedown="visualizar(\'exif_' + x + '\');visualizar(\'thumb_' + x + '\')" >exif</a>]';
                  salida += '</span><br />';
                  salida += '<span id="exif_'+x+'" class="isize" style="display: none" ><p>';
                  for ( var elemento in datos.imgs[i][6] ) {
                     salida += elemento+'<br />';
                     for ( var subElemento in datos.imgs[i][6][elemento]) {
                        salida += '<b>'+subElemento+': </b>'+datos.imgs[i][6][elemento][subElemento]+'<br />';
                     }
                  }
               salida += '</span></tt></p>';
            } else {
               salida += '</span></tt></p>';
            }
           }
           container.innerHTML = salida ;

           // Si tenemos una ventana abierta la cerramos
           var v = document.getElementById('ventana_subeImagen');
           if ( v ) v.parentNode.removeChild(v);
         } else {
            container.innerHTML = "<p class='aviso' style='width: 100px'>Sin Imágenes</p>";
         }
      } else {
         alert('Fallo');
      }
   }
}

/**
* Presentar imagenes de la sección que estamos editando
*/

function editarImagenes(){

  // Imagenes del diectoio las tenemos en listaImg.js
  if (pedido.readyState == 4 ) {
   if ( pedido.status == 200 ) {
     var container = document.getElementById('imgEdit');
     // var container = document.getElementById('cajaImg');
     var salida = '' ;
      var datos = eval('('+pedido.responseText+')');
      if ( datos ) {

         // Recogemos otras secciones
         for ( i in datos.sec ) {
            if ( datos.sec[i][0] == 'actual' ) { // sección actual
               salida = salida + '<br /><a href="javascript:;" onmousedown="visualizar(\'navegador\');">' + datos.sec[i][1] + '</a>';
               salida = salida + '<div id="navegador" >';
            } else {
               salida = salida + '<br /><a href="javascript:;" onmousedown="pedirDatos(\'?m=imagenes&a=ajaxImg&s='+ datos.sec[i][0] +'\',\'editarImagenes\');" >' + datos.sec[i][1] + '</a>';
               }
            }
         salida = salida + '</div>';

         conta = 0;
         // Recogemos imagenes
         salida = salida + '<div id="cajaImg">';
         for ( i in datos.imgs ) {
            conta ++;
            // presentar las imagenes que se encuentran en el arrray
            // Parece que tiny analiza el contenido y no inserta el class de <a>
            salida = salida + '<img src="'+datos.imgs[i][1]+'" />';
            salida = salida + '<br />';
            salida = salida + '<a title="<?=literal('miniatura')?>" class="boton" onclick="javascript:;" onmousedown="tinyMCE.execCommand(\'mceInsertContent\',\'false\',\'<a href=\\\''+datos.imgs[i][1]+'\\\' class=\\\'botonImg\\\' ><img src=\\\''+datos.imgs[i][2]+'\\\' /></a>\')" ><-</a>';
            salida = salida + '<a title="<?=literal('Tamaño original')?>" class="boton" href="javascript:;" onmousedown="tinyMCE.execCommand(\'mceInsertContent\',\'false\',\'<img src=\\\''+datos.imgs[i][1]+'\\\' />\')" ><-(</a>';
            salida = salida + '<a title="<?=literal('Eliminar')?>" class="boton" href="javascript:;" onmousedown="pedirDatos(\'?m=imagenes&a=borrarImg&img='+datos.imgs[i][1]+'\',\'borrarImg\',\'Borrar imágen\')" >X</a>';
            salida = salida + '<br />';
           }
         salida = salida + '</div>';

         // Si no tenemos imagenes presentamos mensaje
         if ( conta == 0 ) {
            salida  = salida + "<p class='aviso' style='width: 100px'>Sin Imágenes</p>";
         }

         container.innerHTML = salida ;

        // Si tenemos una ventana abierta la cerramos
        var v = document.getElementById('ventana_subeImagen');
        if ( v ) v.parentNode.removeChild(v);

      } else {
         container.innerHTML = "<p class='aviso' style='width: 100px'>Sin Imágenes</p>";
      }
   }
  }
}

/**
* borrar imagen
*/

function borrarImg(img){
  if (pedido.readyState == 4 ) {
   if ( pedido.status == 200 ) {
      var estado = eval(pedido.responseText);
      if ( estado[0] == 0 ) { // la imagen se borro bien
         // actualizar caja de imagenes
         if ( document.getElementById('imgEdit') ) {
            pedirDatos('?m=imagenes&a=ajaxImg&s='+estado[1],'editarImagenes');
         }
         if ( document.getElementById('thumbnails') ) {
            pedirDatos('?m=imagenes&a=ajaxImg&s='+estado[1],'editarImagenesAdmin');
         }

      } else {
         var res = "Error al borrar imágen";
         for(x=0;x<estado.length;x++) {
            res += '\n'+estado[x];
         }
         //res += estado[0];
         alert(res);
      }
   }
  }

}

function verImg(img){
window.open('?m=imagenes&a=presentar_galeria&img='+img,'','toolbar=no,scrollbars=yes')
}
   /**
   *
   * Transformammos los links hacia imagenes directos a un link a la misma pagina con
   * el argumento img=con el link, asi podemos enseñar las imagenes de forma más completa
   *
   * @author Eduardo Magrané
   * @version 1.0
   *
   */

function linksImgView() {
   var links = document.links;
   for (var x=0 ; x<links.length ; x++ ) {
      var tipo = links[x].href.substring(links[x].href.length-4,links[x].href.length);
      if ( tipo == '.gif' || tipo == '.jpg' || tipo == 'jpeg' || tipo == '.png' || tipo == 'tiff' || tipo == '.JPG' || tipo == '.GIF'  ) {
         var ancla = links[x];
         ancla.setAttribute('onmousedown','verImg(\''+links[x].href+'\')');
         //alert(ancla);
         //ancla.onclick=verImg(links[x].href);
      }
   }
}

