/** 
 * @file comentarios.js
 * @brief Scripts para comentarios
 * @ingroup modulo_comentarios
 */

/**
 * Respuesta a la modificación de un comentario
 */

function modificarComentario(id) {
   if (pedido.readyState == 4 ) {
      if ( pedido.status == 200 ) {
         var datos = pedido.responseText;
         if ( datos ) {
            if ( datos == 'FALSE' ) {
               alert('No se pudo modificar comentario');
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

/**
 * Confirmación de borrado de un comentario
 */

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

/**
 * Confirmación activación de un comentario
 */

function confirmarActivacionComentario() {
   if (pedido.readyState == 4 ) {
      if ( pedido.status == 200 ) {
         var datos = pedido.responseText;
         if ( datos ) {
            if ( datos == 'FALSE' ) {
               alert('Error en servidor: No se pudo borrar comentario');
               return false;
            } else {
               alert('Comentario activado');
            }
         } else {
            alert('No se pudo recuperar información del servidor');
            }
         }
      }
      return true;
   }

