function guardar_metatags(formulario) {
  var titulo       = formulario.titulo.value;
  var description  = formulario.description.value;
  var keywords     = formulario.keywords.value;
  var metatags_url = formulario.metatags_url.value;

  if ( metatags_url ) {
    url = '?formato=ajax&m=metatags&a=insertar';
    url += '&metatags_url='+metatags_url;
  } else {
    alert('No hay url definida');
    return false;
  }

  if (titulo) {
    titulo = encodeURIComponent(titulo);
    url += '&titulo='+titulo;
  }
  if (description) {
    description = encodeURIComponent(description);
    url += '&description='+description;
  }
  if (keywords) {
    keywords    = encodeURIComponent(keywords);
    url += '&keywords='+keywords;
  }
  pedirDatos(url,'confirma_metatags');
  return false;
}
/**
 * Confirmación para las acciones
 */

function confirma_metatags()
   {
   if (pedido.readyState == 4 ) {
      if ( pedido.status == 200 ) {
         var datos = eval('['+pedido.responseText+']');
           var literal = datos[0]['elemento'];
           var valor = datos[0]['valor'];
           accion = typeof(datos[0]['accion']) != 'undefined' ? datos[0]['accion'] : 'modificado' ;
           console.log(accion);
           switch(accion) {
             case 'insertado':
               // alert('Literal insertado');
               break;
             
             case 'borrado':
               $('#lit_'+literal).text('');
               break;
             
             default:
               $('.literal_faltante_'+literal).each(function (index) {
                   $(this).removeClass();
                   $(this).text(valor);
            
               })
           }
           mostrar_avisos();
         }
      }
   }

