/** imagenes.js.php 
*
* javascript para la administración de imagenes
*
* @author Eduardo Magrané
* @version 1.0
*/

/**
* Presentar imágenes de la sección que estamos editando
* en la columna, para poder ser insertadas comodamente
* en el editor.
*/

function galeria_columna(){

   if (pedido.readyState == 4 ) {
      if ( pedido.status == 200 ) {
         // Imagenes del diectoio las tenemos en listaImg.js
         var container = document.getElementById('thumbnails_columna');
         var datos = pedido.responseText;
         if ( datos ) {

            container.innerHTML = datos ;

         } else {
            container.innerHTML = "<p class='aviso' style='width: 100px'>Sin Imágenes</p>";
         }
         } else {
            alert('Fallo');
         }
      }
   }

/**
* Presentar imágenes de la sección que estamos editando
* en el administrador de imágenes
*/

function galeria(){

   if (pedido.readyState == 4 ) {
      if ( pedido.status == 200 ) {
         // Imagenes del diectoio las tenemos en listaImg.js
         var container = document.getElementById('contenido');
         var datos = pedido.responseText;
         if ( datos ) {

            container.innerHTML = datos ;

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

   return false;

   }


/**
* borrar imagen desde columna
*/

function borrar_imagen_columna(img){

   if (pedido.readyState == 4 ) {
      if ( pedido.status == 200 ) {
         var estado = eval(pedido.responseText);
         if ( estado[0] == 0 ) { // la imagen se borro bien
            // actualizar caja de imagenes
            pedirDatos('?formato=ajax&m=imagenes&a=galeria_columna&s='+estado[1],'galeria_columna');

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

/**
* borrar imagen
*/

function borrar_imagen(img){

   if (pedido.readyState == 4 ) {
      if ( pedido.status == 200 ) {
         var estado = eval(pedido.responseText);
         if ( estado[0] == 0 ) { // la imagen se borro bien
            // actualizar caja de imagenes
            pedirDatos('?formato=ajax&m=imagenes&a=galeria&s='+estado[1],'galeria');

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

/**
 * Transformamos los enlaces de las miniaturas de imágenes para que se muestre la
 * ampliación en la misma pagina utilizando colorbox.
 *
 * Podemos ver una lista de opciones de colorbox en http://www.jacklmoore.com/colorbox
 */

function img2thickbox() {

   // Es mejor hacerlo manual sino coge todas las imágenes que se presentan
   // en la pagina.
   // 
   //    var links = document.links;
   //    var num   = 0;
   // 
   //    for (var x=0 ; x<links.length ; x++ ) {
   // 
   //       var tipo = links[x].href.substring(links[x].href.length-4,links[x].href.length);
   //       if ( tipo == '.gif' || tipo == '.jpg' || tipo == 'jpeg' || tipo == '.png' || tipo == 'tiff' || tipo == '.JPG' || tipo == '.GIF'  ) {
   //          var ancla = links[x];
   //          //ancla.setAttribute('onclick','verImgEnGaleria(\''+links[x].href+'\'); return false;');
   //          // ancla.setAttribute('rel','galeria_imagenes');
   //          ancla.setAttribute('class',"galeria_imagenes");
   //          num++;
   //       }
   //    }

   $(document).ready(function(){
      //Examples of how to assign the ColorBox event to elements
      $(".galeria_imagenes").colorbox({
         rel:'galeria_imagenes'
         , slideshow:true
         , slideshowSpeed: 5000
      });
   });

}

/**
 * Procesamos la subida de imágenes
 *
 * @param id_input Identificador del input de las imágenes
 * @param barra_progreso Identificador dei id de progreso
 * @param metodo_retorno Método del módulo a llamar al acabar el proceso
 * @param seccion Sección en la que estamos
 */

function subida_imagenes_jquery(id_input, barra_progreso, metodo_retorno, seccion) {

       $('#'+id_input).fileupload({
           dataType: 'json',
           done: function (e, data) {
               $.each(data.result.files, function xborrar_(index, file) {
                   //$('<p/>').text(file.name).appendTo(document.body);
                   $('<p/>').html('<img src="'+file.thumbnail_url+'" />').appendTo('#mensajes');
               });

            //pedirDatos('?m=imagenes&a=ajaxImg&s='+estado[1],'galeria');
            pedirDatos('?formato=ajax&m=imagenes&a='+metodo_retorno+'&s='+seccion,metodo_retorno);
           },

          progressall: function (e, data) {
              var progress = parseInt(data.loaded / data.total * 100, 10);
              $('#'+barra_progreso+' .bar').css(
                  'width',
                  progress + '%'
              );
          },
          add: function (e, data) {
               // data.context = $('<p/>').text('Uploading...').appendTo(document.body);
               // $('<p/>').text('Subiendo...').appendTo('#mensajes');
               data.submit();
           },
       });

       return false;

   }
