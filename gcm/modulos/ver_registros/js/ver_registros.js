/** 
 * javascript para el módulo ver_registros
 *
 */

/**
 * Visualizar los registros
 */

function visualizar_registros(formulario,sesion) {

   var filtro = formulario.filtro.value;
   var container = $('#caja_registro_'+sesion);

   container.html('<br /><b>Recuperando registros...</b>');

   $.get('<?=Router::$base?>?formato=ajax&m=ver_registros&a=registros_ajax&filtro='+filtro,function(data){
      container.html(data);
      });
   // pedirDatos('?m=ver_registros&a=registros_ajax&filtro='+filtro,'presenta_registros()');
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


/**
 * Datatable
 */

$(document).ready(function() {
   $('#table').dataTable();
   } );
