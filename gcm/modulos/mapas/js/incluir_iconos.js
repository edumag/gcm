var textarea_seleccionado = false;

$(function() {
   $('textarea.GC_icono').focus(function() {
      alert('Selecciona icono o especifica nombre');
      textarea_seleccionado = $(this);
      });
   $('.seleccionar_icono img').click(function() {
      var icono = $(this).attr('alt');
      if ( textarea_seleccionado ) textarea_seleccionado.val(icono);
      });
 });

