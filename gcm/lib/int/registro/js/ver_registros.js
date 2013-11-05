/** 
 * javascript para el módulo ver_registros
 *
 */

/**
 * Visualizar los registros
 */

function visualizar_registros(formulario,sesion) {

   var filtro = formulario.filtro.value;
   var container = $('#caja_registros');

   container.html('<br /><b>Recuperando registros...</b>');

   $.get('?formato=ajax&filtro='+filtro,function(data){
      container.html(data);
      });
   return false;

   }

/** 
 * Procesa la llegada de datos de l servidor
 */

function presenta_registros() {

   var container = document.getElementById('caja_registro_'+sesion);

   if (pedido.readyState == 4 ) {

      container.innerHTML = 'Buscando registros...';

      if ( pedido.status == 200 ) {
         var salida = pedido.responseText;
         container.innerHTML = salida ;
         }

      }
   return false;
   }

