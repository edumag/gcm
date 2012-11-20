/* SVN $Id:$ */

/**
 * @category  Modulos
 * @package   Editar
 * @author    Eduardo Magrané <eduardo mamedu com>
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.txt GNU/GPL
 * @version   0.2
 */

/** InsertarEmail
 *
 * Añadimos un email de forma oculta a los robots para tinyMCE
 */

function insertaEmail(formulario) {

   var em = formulario.email.value.split('@');
   tinyMCE.execCommand('mceInsertContent',true,'<a href="javascript:enviarEmail(\''+em[0]+'\',\''+em[1]+'\');">'+formulario.nombre.value+'</a>')
   return (false);
}

/** insertaReferencia()
 *
 * Para insertar referencias de documento que estamos editando, estas referencias posteriormente
 * son presentadas como Enlaces relacionados en una lista
 */

function insertaReferencia(formulario) {

   var contenido = '{Ref{'+formulario.enlace.value+'::'+formulario.nombre.value+'}}';
   tinyMCE.execCommand('mceInsertContent',false,contenido);
   return false;

   }

/*
* Presentamos los constantes para poder ser insertados en el formulario
* de tiny.
*/

function presentaConstantes(){

   if (pedido.readyState == 4 ) {
      if ( pedido.status == 200 ) {
         var container = document.getElementById('panelConstantes');
         var salida = '<br />' ;
         var datos = eval('['+pedido.responseText+']');
         if ( datos ) {
            for ( var elemento in datos ) {
               for ( var subelemento in datos[elemento] ) {
                  //salida += '<p class="subpanel" style="text-align:left">';
                  salida += '<p class="subpanel" >';
                  salida += '<a href="javascript:;" onmousedown="tinyMCE.execCommand(\'mceInsertContent\',false,\'{C{' + subelemento + '}}\');" ';
                  salida += 'title="' + datos[elemento][subelemento][0] + ' | ' + datos[elemento][subelemento][1] + '" >';
                  salida += subelemento + '</a>';
                  salida += '</p>';
               }
            }
            container.innerHTML = salida ;
         }

      }
   }

   }
