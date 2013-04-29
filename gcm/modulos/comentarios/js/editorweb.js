/** 
 * @file editorweb.js
 * @brief Incluimos tinyMCE al textarea del formulario de comentario
 * @ingroup modulo_comentarios
 */

addLoadEvent(function(){
   tinyMCE.init({
      mode : "textareas",
      editor_selector : "editor_simple",
      theme : "simple",
      languages : 'es',
      });

   });
