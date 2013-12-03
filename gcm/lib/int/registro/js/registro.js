/** 
 * @file registro.js
 * @brief javascript para el módulo ver_registros
 * @ingroup registro
 */

/**
 * Visualizar los registros
 */

function visualizar_registros(formulario) {

   var filtro = formulario.filtro.value;
   var container = $('#caja_registros');

   container.html('<br /><b>Recuperando registros...</b>');

   $.get('?formato=ajax&filtro='+filtro,function(data){
      container.html(data);
      });
   return false;

   }
