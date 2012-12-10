/**
 * Mecanismo con ajax para ver todo el contenido de la entrada sin 
 * salir de página
 */

function initListadoContenido(contexto) {

   var boton_html = "<a title='<?=literal('Ampliar contenido')?>' ";
   boton_html    += "class='boton_ajax simb_abajo simb_neutro' ";
   boton_html    += "style='font-size: small; display: none ;' href=''>&nbsp;</a>";

   var titulos = $('.titulo_articulo', contexto);

   titulos.prepend(boton_html);

   /* Añadimos title a titulo */
   titulos.attr('title','Ver en contexto');

   /* Mostramos botón si estamos sobre elemento */
   titulos.mouseover(function(e) {

         $(this).children('.boton_ajax').css('display','inline');
         });

   /* Ocultamos botón si no estamos sobre elemento */
   titulos.mouseout(function(e) {

         $(this).children('.boton_ajax').css('display','none');
         });

   $('.boton_ajax', contexto).click(function(e) {

      var caja = $(this).parent().parent("div");
      var titulo = $(this).next('a');
      titulo.attr('title','Ver en contexto');
      // caja.css('background','red');
      // titulo.css('background','yellow'); 
      // return false;
      var contenido = caja.children(".contenido_articulo");
      var url = titulo.attr("href");

      /* Miramos la clase para saber el estado */

      var estado = caja.attr("class");

      if ( estado == 'elemento_lista_off'  ) {

         caja.removeClass("elemento_lista_off");
         caja.addClass("elemento_lista_on");

         contenido.html("Buscando contenido...");
         $(this).removeClass('simb_abajo');
         $(this).addClass('simb_arriba');

         if ( url ) {
            $.get('?e=contenido&formato=ajax&url='+url,function(data){
               contenido.html(data);
               });
            }

         return false;
         }
 
      if ( estado == 'elemento_lista_on'  ) {

         caja.removeClass("elemento_lista_on");
         caja.addClass("elemento_lista_hide");
         $(this).addClass('simb_abajo');
         $(this).removeClass('simb_arriba');

         contenido.hide("slow");
         return false;
         }

      if ( estado == 'elemento_lista_hide'  ) {

         caja.removeClass("elemento_lista_hide");
         caja.addClass("elemento_lista_on");
         $(this).removeClass('simb_abajo');
         $(this).addClass('simb_arriba');

         contenido.show("slow");

         return false;
         }

      });

   }

$(document).ready(function() { 
      if ( !(jQuery.browser.msie && jQuery.browser.version < 7)) { // take away IE6
         initListadoContenido("#listado_de_contenido");
         }
      });
