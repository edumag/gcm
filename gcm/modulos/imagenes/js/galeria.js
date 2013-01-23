/** Modulo para visualizar imágenes
*
* Posibles valores enviados por GET
*
* - img: nombre de la imagen a mostrar primero
*
* - id: Se puede pasar el numero de imagen que se quiere visualizar primero en
* la url, ejemplo: id=3
*
* - alto: Altura de la caja de las imágenes
*
* - ancho: Anchura de la caja de las imágnenes
*
* El array debe llamarse lista y contener:
*
* - URL de la imagen: obligatorio.
* - Nombre: Si no se utiliza el del archivo.
* - url de la miniatura: Si se quiere miniaturas o sino la misma pero en pequeño
* - Descripción: texto a presentar al lado de la imagen
* - Datos exif: Un array con sus datos.
*
*   El unico dato obligatorio y necesario serán las urls de las imágenes sin los otro también
*   funciona.
*
* @todo Hacer que a las imágenes de los botones se les pueda pasar el url de alguna manera.
*
*/

// Inicializamos variables globales
/// Numero de imagen a visualizar
var id=1;
/// Conmutador de slideshow
var slideshow=0 ;
/// Intervalo entre imágenes en segundos
var intervalo=3;
/// Alto de la caja div que contiene la imagen
var alto=0;
/// Ancho de la caja div que contiene la imagen
var ancho=0;
/// slideshow
var automatic = 0;
var ancho_fijo=0;
var alto_fijo=0;
// ruta de las imágenes de los botones
var ruta_gcmImgs = '<?php echo Router::$base.GCM_DIR;?>modulos/imagenes/img/';

// leemos las variables de la url
cadVariables = location.search.substring(1,location.search.length);
arrVariables = cadVariables.split("&");
for (i=0; i<arrVariables.length; i++) {
   arrVariableActual = arrVariables[i].split("=");
   if (isNaN(parseFloat(arrVariableActual[1]))) {
      eval(arrVariableActual[0]+"='"+unescape(arrVariableActual[1])+"';");
   } else {
      eval(arrVariableActual[0]+"="+arrVariableActual[1]+";");
      }
   }

/**
 * Si recibimos el alto por GET o el ancho lo hacemos fijos
 */

if ( alto != 0 ) {
   var alto_fijo=1;
}
if ( ancho != 0 ) {
   var ancho_fijo=1;
}

/// lista de imágenes.
if ( typeof (lista) == "undefined" ) {
   alert('Se necesita un array con el listado de las imagenes');
}

var total = lista.length - 1;       // Total de imágenes
var i = id-1;                       // Numero imagen
var estado=0;                       // estado del slideshow

/**
 * Presentar la caja de miniaturas
 */

function presentar_caja_miniaturas() {

   document.write('<a id="e_ver_miniaturas" href="javascript:document.getElementById(\'gcmImgs_miniaturas\').style.display=\'\'; document.getElementById(\'e_ver_miniaturas\').style.display=\'none\' ; gcmImgs_miniaturas(); " >Miniaturas</a>');
   document.write('<div id="gcmImgs_miniaturas" style="display: none;">');
   document.write('</div>');
}
function gcmImgs_miniaturas() {

   var s = '<ul>';

   if ( total > 1 ) {
   for (var num_imagen = 0; num_imagen < total ; num_imagen++) {

      s=s+'<li>';
      s=s+'<img onclick="i=' + (num_imagen - 1)  + '; cambiar(); para();" src="' + lista[num_imagen][2] + '" />';
      s=s+'</li>';

      }
   }

   s=s+'</ul>';
   document.getElementById("gcmImgs_miniaturas").innerHTML=s;

}

function cambiar(lado) {

   // Si no tenemos alto lo sacamos de la primera imagen presentada
   if ( alto == 0 ) { var alto=document.images["ejemplo"].height;  }

   // vamos hacia adelante
   if ( lado == 0 ) {
      i-- ;
      if ( i < 0 ) i = total - 1 ;

   // vamos hacia atrás  
   } else {
      i++ ;
      if (i>=total) { i=0; }
   }


   if ( typeof (_IS) == "undefined" ) {
      _IMAGEN = new Image();
   } else {
      _IMAGEN = _IS;
   }
   _IMAGEN.src=lista[i][1] ;
   espera_carga();
   }

function para() {
	clearInterval(automatic);
   document.getElementById('gcmImgs_boton_parar').style.display='none';
   document.getElementById('gcmImgs_botonera_intervalo').style.display='none';
   document.getElementById('gcmImgs_boton_slideshow').style.display='';
   estado = 0;
   return true;
 }

/**
 * Si estamos haciendo slideshow pero no da tiempo para momentaneamente
 */

function pausar_slideshow() {
   if ( estado == 1 ) {
      clearInterval(automatic);
   }
}
function espera_carga() {

   if (_IMAGEN.complete) {

      gcmImgs_presenta_mensaje('');
      document.getElementById("gcmImgs_titulo").innerHTML=i + 1 + '/' + total + " - " + lista[i][0];
      document.images['ejemplo'].src=lista[i][1] ;

      // Vamos cargando la siguiente imagen para que se presente más rapido
      if ( i >= total ) {
         _IS = new Image();
         _IS.src=lista[i+1][1];
      }
      // Hay que restablecer slideshow si se pauso por no llegar a tiempo.
      if ( estado == 1 ) {
         automatic=setInterval('cambiar(1)', intervalo*1000);
      }

   } else {
      // @todo Si no se ha cargado la imagen hay que para el contador del slideshow sino
      // no da tiempo.
      pausar_slideshow();
      var siguiente = i + 1;
      if ( siguiente > total ) {
         var siguiente = 0 ;
      }
      gcmImgs_presenta_mensaje("Cargando imagen: " + siguiente + " - " + lista[i][0]) ;
      setTimeout("espera_carga()", 1000);
         }
      }

/** Presentar caja de titulo */

function presenta_gcmImgs_titulo() {

   // Titulo
   document.write('<div id="gcmImgs_titulo" >');
   document.write(i+1  + '/' + total + ' - ' + lista[i][0] + '</div>');

}

function gcmImgs_calcular_tamanyo() {

   if ( alto_fijo != 1 ) {
      var alto_anterior = alto;
      // Si no tenemos el alto definido añadimos el de la imagen
      if ( alto == 0 ) {
         alto = document.images["ejemplo"].height;
      }
      // Si el alto de la imagen actual es mayor adaptamos la caja de imágenes
      // para que no se vea cortada
      if ( alto < document.images["ejemplo"].height ) {
         alto = document.images["ejemplo"].height;
         }
      if ( alto > alto_anterior ) {
         document.getElementById("gcmImgs").style.height=alto + 10 + 'px';
         }

      }

   if ( ancho_fijo != 1 ) {
      var ancho_anterior = ancho;

      // Si no tenemos el ancho definido añadimos el de la imagen
      if ( ancho == 0 ) {
         ancho = document.images["ejemplo"].width;
      }

      if ( ancho < document.images["ejemplo"].width ) {
         ancho = document.images["ejemplo"].width;
         }

      if ( ancho > ancho_anterior ) {
         document.getElementById("gcmImgs").style.width=ancho + 10 + 'px';
         }
      }

   }

function caja_imagen() {

   document.write('<div id="gcmImgs" >');
   document.write('<img onload="gcmImgs_calcular_tamanyo()" src="'+ lista[i][1] +'"' );
   document.write('id="ejemplo" name="ejemplo" alt="'+ lista[i][0] +'" />');
   document.write('</div>');

   }

function gcmImgs_botonera() {

   document.write('<div id="gcmImgs_botonera" >');
   document.write('<a title="Anterior" style="cursor: pointer" onclick="para(); cambiar(0); return true;">');
   document.write('<img src="' + ruta_gcmImgs + 'go-previous.gif" alt=" [<] " />');
   document.write('</a>');
   document.write('<a  id="gcmImgs_boton_slideshow" title="Slideshow" style="cursor:pointer;"');
   document.write('    onclick="hacer_slideshow()" >');
   document.write('<img src="' + ruta_gcmImgs + 'slideshow.gif" alt=" [>>]  " />');
   document.write('</a>');
   document.write('<a id="gcmImgs_boton_parar" title="Parar" style="cursor:pointer; display:none;" onclick="para()" >');
   document.write('<img src="' + ruta_gcmImgs + 'stop.gif" alt=" [0] " />');
   document.write('</a>');
   document.write('<a title="Seguent" style="cursor:pointer" onclick="para(); cambiar(1); return true;">');
   document.write('<img src="' + ruta_gcmImgs + 'go-next.gif" alt=" [>] " />');
   document.write('</a>');
   document.write('</div>');

}

function gcmImgs_slideshow() {
   document.write('<div id="gcmImgs_botonera_intervalo" style="display:none;" >');
   document.write('<a onclick="intervalo=intervalo-1; para(); hacer_slideshow(); ');
   document.write('document.getElementById(\'intervalo\').value=intervalo" >');
   document.write('<</a>');
   document.write('<input id="intervalo" ');
   document.write('type=text value="'+intervalo+'" size="2" axlength="5"');
   document.write('onchange="intervalo=document.getElementById(\'intervalo\').value; para();');
   document.write('hacer_slideshow()" />');
   document.write('<a onclick="intervalo=intervalo+1; para(); hacer_slideshow(); ');
   document.write('document.getElementById(\'intervalo\').value=intervalo" >');
   document.write('></a>');
   document.write('</div>');
   }

/**
 * Enviamos mensaje a la caja de mensajes
 */

function gcmImgs_presenta_mensaje(mensaje) {

   document.getElementById('gcmImgs_titulo').innerHTML=mensaje;

}

function hacer_slideshow() {

   if ( intervalo <= 0 ) {
      intervalo = 5;
      para();
      return true;
   }

   if (estado==0) {
      estado=1;
      document.getElementById('gcmImgs_botonera_intervalo').style.display='';
      document.getElementById('gcmImgs_boton_parar').style.display='';
      document.getElementById('gcmImgs_boton_slideshow').style.display='none';
      automatic=setInterval('cambiar(1)', intervalo*1000);
      return true;
   } else {
      document.getElementById("gcmImgs_titulo").innerHTML='Slideshow ya esta en marcha';
      return false;
      }
   }

/**
 * Modificamos tamaño de contenido para que ocupe toda la pantalla
 */

function gcmImgs_inicializa() {

   // document.getElementById('contenido').style.width='96%'; ¿Si no tenemos contenido?

   /**
    * Si tenemos ancho o alto fijado redimensionamos caja de imágenes
    */

   if ( alto_fijo == 1 ) {
      document.getElementById("gcmImgs").style.height=alto + 10 + 'px';
      }
   if ( ancho_fijo == 1 ) {
      document.getElementById("gcmImgs").style.width=ancho + 10 + 'px';
      }


   /**
    * Si recibimos en GET slideshow=1 comenzamos son él
    * dándole tiempo a cargar la pagina
    */

   if ( slideshow == 1 ) {
      hacer_slideshow();
   }

   /**
    * Si tenemos img=<imagen a mostrar> tenemos que mirar que numero es
    * y mostrarla la primera.
    */

   if ( typeof (img) !== "undefined" ) {

      // extraemos el nombre de la imagen que llega por GET
      var imagen_inicio=img.split('/');
      imagen_inicio = imagen_inicio[imagen_inicio.length - 1];
      for (var index = 0; index < total; index++) {

         // Extraemos el nombre de la imagen del array para poderlo comparar
         var img_array = lista[index][0];
         if ( img_array.indexOf('/') >= 0 ) {
            var img_array = img_array.split('/');
            img_array = img_array[img_array.length -1];
         }

         if ( img_array == imagen_inicio ) {
            i = index;
            break;
            }
         }
      }
   }

/**
 * Presentar contenido
 *
 * Añadimos el contenido en la pagina por orden por defecto, para hacerlo más comodo
 * pero puede cambiarse el orden en que se presentan las diferentes secciones para un mayor control
 * sobre la presentación.
 */

function presentar_contenido() {

   gcmImgs_botonera();
   gcmImgs_slideshow();
   presentar_caja_miniaturas();
   presenta_gcmImgs_titulo();
   caja_imagen();

}

addLoadEvent(function(){
   setTimeout("gcmImgs_inicializa()", 1000);
   });

