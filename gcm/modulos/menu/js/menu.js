/**
*
* Transformammos los links con identificador `menu_ajax` en enlaces hacia ajax
*
* @author Eduardo Magrané
* @version 1.0
*
*/

function initMenu(contexto) {

   var icono_on  = '<?php echo $gcm->event->instancias['temas']->icono('+')?>';
   var icono_off = '<?php echo $gcm->event->instancias['temas']->icono('-')?>';

   // Cerramos los menús off
   $('.m_on', contexto).parent().find("ul").hide();
   $('.m_actual', contexto).parent().find("ul").show();

   $('.m_on, .m_off', contexto).click(function() {

      var clase = $(this).attr('class');
      console.log(clase);

      if ( clase == 'm_on' ) {
         $(this).removeClass('m_on');
         $(this).addClass('m_off');
         var img = $(this).find('img');
         img.attr('src',icono_off);
         img.attr('alt','-');
         console.log($(this).find('img'));
         var sublista = $(this).parent().find("ul");
         console.log(sublista);
         sublista.toggle();
      } else {
         $(this).removeClass('m_off');
         $(this).addClass('m_on');
         var img = $(this).find('img');
         img.attr('src',icono_on);
         img.attr('alt','+');
         console.log($(this).find('img'));
         var sublista = $(this).parent().find("ul");
         console.log(sublista);
         sublista.toggle();
      }
      return false;
      });

   function toggle(el) {
   }

   // $('.m_off', contexto ).removeClass('m_off');
   // $('.m_on', contexto ).removeClass('m_on');
}

$(document).ready(function() { 
      if ( !(jQuery.browser.msie && jQuery.browser.version < 7)) { // take away IE6
         initMenu("#barraNavegacion");
         }
      });
