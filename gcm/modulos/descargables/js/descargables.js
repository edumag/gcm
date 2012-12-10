
function init_descargables() {

   $('.boton_codigo').click(function() {

         // desglosar programa de id
         var programa = this.id.substring(4);

         //var caja = $('#caja_'+programa);
         //caja.replaceWith('Cargando...');
         
         $('#caja_codigo').html('Cargando...');

         $.get('?m=descargables&a=presenta_contenido&p='+programa,function(data) {
            $('#caja_codigo').replaceWith(data);
            });
         return false;
         });

}


$(document).ready(function() { 
   init_descargables();
   });
