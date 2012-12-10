/** Lanzar funciones al cargarse la página
 * Función que nos permite lanzar más de una función al
 * cargar la página.
 *
 * Forma de uso::
 *
 *    addLoadEvent(function(){
 *       pedirDatos('?m=imagenes&a=ajaxImg&s=<?=$d ?>','editarImagenesAdmin')
 *       });
 *
 * @ref http://www.danilat.com/weblog/2007/11/20/la-forma-correcta-de-usar-windowonload/
 *
 */

function addLoadEvent(func){

   var oldonload = window.onload;

   if (typeof window.onload != 'function') {

      window.onload = func;

   } else {

      window.onload = function(){
         if (oldonload) {
            oldonload();
            }
         func();
         }
      }
   }
