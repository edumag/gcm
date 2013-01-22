/** gcm.js
 *
 * Funciones javascript imprescindibles para el funcionamiento de gcm
 *
 */

/** Ajax
 *
 * Funciones javascript para la administración de la aplización
 * 
 * @author Eduardo Magrané
 */

function newAjax() {
   var xmlhttp = false;
   try {
      xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
   } catch (e1) {
      try {
         xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
      } catch (e2) {
         xmlhttp = false;
      }
   }
   if (!xmlhttp && (typeof XMLHttpRequest != 'undefined' || window.XMLHttpRequest)) {
      xmlhttp = new XMLHttpRequest();
   }
   return xmlhttp ;
}

/**
* pedirDatos
*/

var nSolicitud = 0;
var pedido;

/** pedirDatos()
 *
 * Solicitar una respuesta
 *
 * @param url url al servidor
 * @param accion funcion allamar al recibir los resultados
 * @param confirmacion Mensaje que se presenta para confirmar la acción
 *        si no hay no se pide.
 *
 * @autor Eduardo Magrané
 * @version 0.2
 *
 */

function pedirDatos(url , accion , confirmacion){

   var self = this;
   nSolicitud++ ;
   var indicador=false;
   var temporizador=false;

   if ( confirmacion ) {
      if ( ! confirm(confirmacion) ) {
         return FALSE;
      }
   }
   self.pedido = newAjax();
   if ( ! self.pedido ) {
      alert('ERROR::No se pudo crear una conexión con ajax');
      return FALSE;
   }
   self.pedido.open('GET',url, true);
   //pedido.open('GET',url, false);
   self.pedido.onreadystatechange=eval(accion) ;
   self.pedido.send(null);
}

/**
 * Con esta función podemos conmutar la visualización de una capa
 */

function visualizar( id ) {
	if (document.getElementById){
		var elemento = document.getElementById(id);
		if (elemento.style.display == "none") {
			//elemento.style.display = "";
			$(elemento).fadeIn(500);
		} else {
			//elemento.style.display = "none";
			$(elemento).fadeOut(100);
		}
	}
}

/**
* Crear una ventana con javascript con el contenido de un div existente
* centrarlo en la ventana y añadir un boton de cierre
*
* @param titulo Titulo a mostrar en la ventana
* @param contenido Contenido de la ventana
* @oaram identificador Id de la ventana
*
* @todo Hacer la ventana arrastrable y evitar que se repira una ventana con el mismo identifiador
*/

function ventana(titulo,contenido, identificador) {

   // centramos ventana
   var topp = 40 ;
   var leftt = 30 ;
   win = document.createElement('div');
   //document.getElementById('contenido').appendChild(win);
   if ( document.getElementById('editando') ) {
      document.getElementById('editando').appendChild(win);
   } else {
      document.getElementById('contenido').appendChild(win);
   }

   win.id = 'ventana_'+identificador;
   win.className+=' ventana_flotante';
   contenido = '<div class="titulo_ventana_flotante" style="text-align:right"><span style="float:left">'+titulo+'</span><span style="position:relative;left: 0px; " ><a href="javascript:this.cerrar(\''+win.id+'\')">X</a></span></div><div style="padding: 15px">' + contenido + '</div>';
   win.innerHTML = contenido;

   this.cerrar = function(id){
     cr = document.getElementById(id);
     cr.parentNode.removeChild(cr);

   }

}


/**
 * Enviar email oculto
 */

function enviarEmail(nom,dominio){

   window.open('mailto:'+nom+'@'+dominio,'_self');

}

/**
 * Comprobar si una variable es array o no
 *
 * @return true/false
 */

function is_array(obj) {

   if (obj.constructor.toString().indexOf("Array") == -1)
      return false;
   else
      return true;
}

/** Funciones que se inician al cargar la pagina
 *
 * @todo Anulamos galeria de imagenes hasta resolver los problemas
 */

addLoadEvent(function(){
   // linksImgView();
   paneles();
   // $(this).pngFix();
   });

