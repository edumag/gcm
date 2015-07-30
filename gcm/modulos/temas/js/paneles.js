
/**
 * Construimos botones para controlar la visibilidad o no
 * sobre los div con class panel que tengan un tituloPanel dentro
 * y una subcaja con la clase subpanel_oculto o subpanel_visible.
 *
 * los nombre de clases de los subpaneles hacen referencia a si de entrada
 * están ocultos o no.
 *
 * Si no se dan todas estas circustancias no hacemos nada.
 */

function paneles_boton() {

   var paneles = $(".panel_titulo a[class!='panel_iniciado']");
   paneles.addClass('panel_iniciado');

   paneles.click(function(){

      var on   = $(this).parent().parent().find('.subpanel_visible');
      var off  = $(this).parent().parent().find('.subpanel_oculto');
      var ajax = $(this).parent().find("a[name='ajax']");

      on.removeClass('subpanel_visible');
      on.addClass('subpanel_oculto');
      //on.hide(1000);
      on.slideUp(1000);
      //on.fadeOut(1000);

      off.removeClass('subpanel_oculto');
      off.addClass('subpanel_visible');
      //off.show(1000);
      //off.slideDown(1000);
      off.fadeIn(1000);
      if ( ajax ) { eval(ajax.attr('href')); }

      /* Si tenemos input le damos foco */
      var input = off.parents(".panel").find(":input:first");
      input.focus();

      return false;

      });
   }

/**
 * Construimos botones para controlar la visibilidad o no
 * sobre los div con class panel que tengan un tituloPanel dentro
 * y una subcaja con la clase subpanel_oculto o subpanel_visible.
 *
 * los nombre de clases de los subpaneles hacen referencia a si de entrada
 * están ocultos o no.
 *
 * Si no se dan todas estas circustancias no hacemos nada.
 */

function paneles() {

   var paneles = $(".panel_titulo a[class!='panel_iniciado']");
   paneles.addClass('panel_iniciado');

   paneles.click(function(){

      var on    = $(this).parent().parent().find('.subpanel_visible');
      var off   = $(this).parent().parent().find('.subpanel_oculto');
      var ajax  = $(this).parent().find("a[name='ajax']");
      var jajax = $(this).parent().find("a[name='jajax']");

      on.removeClass('subpanel_visible');
      on.addClass('subpanel_oculto');
      //on.hide(1000);
      on.slideUp(1000);
      //on.fadeOut(1000);

      off.removeClass('subpanel_oculto');
      off.addClass('subpanel_visible');
      //off.show(1000);
      //off.slideDown(1000);
      off.fadeIn(1000);

      // alert(off.get());
      if ( on.get() == '' ) {

         if ( jajax ) {
            off.load(jajax.attr('href'));
         } else if ( ajax ) { 
            eval(ajax.attr('href')); 
         }

         }
      /* Si tenemos input le damos foco */
      var input = off.parents(".panel").find(":input:first");
      input.focus();

      return false;

      });
   }
$(document).ready(function() { 
  $(".subpanel_oculto").hide(3000);
  paneles();
  });

