/**
 * @file array2table.js
 * @brief Javascript para Array2table
 * @ingroup array2table
 */

/**
 * Mostrar ocultar fila_unica
 */

function mostrar_fila_unica(t) {

  var altura_caja = 200;
  var altura_mini = 16;

  var altura = $('#'+t).height();

  if ( altura > altura_caja ) {
    $('#'+t).height(altura_mini + 'px');
  } else {
    $('#'+t).height('auto');
    console.log(t);
    }

  }

