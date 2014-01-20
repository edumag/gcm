/**
*
* Transformammos los links con identificador `menu_ajax` en enlaces hacia ajax
*
* @author Eduardo Magrané
* @version 1.0
*
*/

function initMenu(contexto) {

   $('.m_off', contexto ).click(function() {

      var lista = $(this).parent();
      var url = $(this).attr("href");  // alert(url);
      lista.html("Cargando...");

      if ( url ) {
         $.get('?formato=ajax&a=menu_ajax_off&m=menu&url='+url,function(data){
            lista.replaceWith(data);
            initMenu("#barraNavegacion");
           });
         }
         return false;
      });

   $('.m_on', contexto).click(function() {

      var lista = $(this);
      var caja = $(this).parent();
      var url = lista.attr("href"); // alert(url);
      // var base = '<?php echo dirname($_SERVER['REDIRECT_URL']);?>';
      caja.css('background','green');

      if ( url ) {
         caja.html("");
         //$.get('?a=menu_ajax_off&m=menu&url='+url,function(data){
         $.get(url+'?formato=ajax&a=menu_ajax_on&m=menu&url='+url,function(data){
         // $.get(url+'?formato=ajax&a=barra_navegacion&m=menu&base='+base,function(data){
            // lista.parent().next().remove();
            //caja.next().remove();
            caja.replaceWith(data);
            initMenu("#barraNavegacion");
           });
         }
         return false;
      });

   $('.m_off', contexto ).removeClass('m_off');
   $('.m_on', contexto ).removeClass('m_on');
}

$(document).ready(function() { 
      if ( !(jQuery.browser.msie && jQuery.browser.version < 7)) { // take away IE6
         initMenu("#barraNavegacion");
         }
      });
