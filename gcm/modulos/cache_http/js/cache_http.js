/**
 * @file cache_http.js
 * @brief Recibimos respuesta sobre el borrado de cache en javascript
 *
 * Presentamos ventana informativa con la respuesta
 *
 * @ingroup Cache_http
 */

/** Respuesta para borrar cache en javascript */

function respuesta_borrar_cache(respuesta) {

   var titulo = '<?=literal("Borrado de cache")?>';

   if (pedido.readyState == 4 ) {
      if ( pedido.status == 200 ) {
         var datos = pedido.responseText;
         if ( datos == '' ) { datos = 'Cache borrada'; }
         ventana(titulo,datos,'id');
         }
      }
   }
