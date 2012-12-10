function modificarComentario(id) {
   if (pedido.readyState == 4 ) {
      if ( pedido.status == 200 ) {
         var datos = pedido.responseText;
         if ( datos ) {
            if ( datos == 'FALSE' ) {
               alert('No se pudo borrar comentario');
               return false;
            } else {
               $('#comentario_'+datos).replaceWith(datos);
            }
         } else {
            alert('No se pudo eliminar comentario\nERROR::' + datos);
            }
         }
      }
      return true;
   }

function confirmarBorradoComentario() {
   if (pedido.readyState == 4 ) {
      if ( pedido.status == 200 ) {
         var datos = pedido.responseText;
         if ( datos ) {
            if ( datos == 'FALSE' ) {
               alert('Error en servidor: No se pudo borrar comentario');
               return false;
            } else {
               visualizar('comentario_'+datos);
            }
         } else {
            alert('No se pudo recuperar información del servidor');
            }
         }
      }
      return true;
   }

