/**
 * Visualizar ocultar contenido desde el título
 */

function initTituloOcultaContenido() {
   $('#titulo').click(function(e) {
     $('#contenido').toggle('slow');
     })
   };

addLoadEvent(function(){
   initTituloOcultaContenido();
   });


