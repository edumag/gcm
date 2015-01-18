/* SVN $Id:$ */

/**
 * @category  Modulos
 * @package   Editar
 * @author    Eduardo Magrané <edu lesolivex com>
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

