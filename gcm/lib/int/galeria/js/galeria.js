<?php

/**
 * Javascript per la pujada d'imatges 
 *
 * @package Galeria
 * @author Eduardo Magrané
 */

// $dir_modulo = '../moduls/galeria/';

$separador = ( strpos($_SERVER['REQUEST_URI'],'?') ) ? '&' : '?';

?>

function galeriaAjax() {
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

var nSolicitud = 0;
var pedido;

/** galeriaSolicitud()
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

function galeriaSolicitud(url , accion , confirmacion){

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

/** Pujar imatge
 *
 * @param formulari Objecta formulari de la galeria
 * @param connector funció que rep avís d'imatge pujada
 */

function pujarImatge(formulari,limit_imatges,connector) {

   hideMessageBoard();
   var nIdThumb = formulari.nummin.value ;
   var total_miniaturas = formulari.total_miniaturas.value ;
   nIdThumb++;
   total_miniaturas++;

   oContainerDiv	= document.getElementById("galleryContainerDIV");
   
   // Eliminem el missatge de llista buida
   if (document.getElementById('noThumbsDIV')){
      document.getElementById('noThumbsDIV').innerHTML	= '';
   }
   
   // Creem un nou thumbnail
   oThumb				= document.createElement('div');
   oThumb.id			= 'thumbDIV'+ nIdThumb;
   oThumb.className	= 'galleryThumbnail';
   
   
   oImageDiv	= document.createElement("div");
   oImage	= document.createElement("img");
   oImage.id	= 'thumbIMG' + nIdThumb;
   oImage.src = '<?=$this->imatge_espera?>';
   <?php if ( $this->amplada_presentacio ) { ?>
   oImage.width = <?php echo $this->amplada_presentacio; ?>;
   <?php } ?>
   <?php if ( $this->altura_presentacio ) { ?>
   oImage.width = <?php echo $this->altura_presentacio; ?>;
   <?php } ?>
   oImage.border=1;
   oImage.style.borderColor='#<?=$this->color_bord?>';
   
   oImageDiv.appendChild(oImage);
   oThumb.appendChild(oImageDiv);
   
   oButtonDiv	= document.createElement('div');
   oButtonDiv.id	= 'buttonDIV'+nIdThumb;
   oButtonDiv.innerHTML = '<?=$this->contingut_enllac_borrar?>';
   oThumb.appendChild(oButtonDiv);
   
   oContainerDiv.appendChild(oThumb);
   
   // Avisem a la funció conectora
   if ( typeof(connector) != 'undefined') { 
      var cmd = connector + '('+nIdThumb+');';
      eval(cmd);
      }

   // Actualitzem el comptador de thumbnails
   formulari.nummin.value	= nIdThumb;
   formulari.total_miniaturas.value	= total_miniaturas;
   
   // Guardem el actual 'action' del formulari
   var actionActual = formulari.action ;

   // Enviem el fitxer al servidor
   //formulari.action	= '<?php echo $dir_modulo; ?>pujar_imatge.php';
   formulari.action	= '<?=$_SERVER['REQUEST_URI'].$separador?>accio_galeria=agafa_imatge';
   formulari.target	= 'frameImatge';
   formulari.enctype	= 'multipart/form-data';
   
   formulari.submit();	
   
   // Recuperem el submit del boto
   formulari.action	= actionActual;
   formulari.target	= '';
   formulari.enctype	= 'multipart/form-data';

   formulari.fimatge.value='';


   if ( total_miniaturas >= limit_imatges ) {
      formulari.fimatge.style.display = 'none';
      }

   }

/** Veure missatges */

function showMessageBoard(strMessage){

   oMessageBoardDiv	= document.getElementById('messageBoardDIV');
   
   oMessageDiv	= oMessageBoardDiv.getElementsByTagName('div').item(0);
   oMessageDiv.innerHTML	= strMessage;
   
   oMessageBoardDiv.style.display	= 'block';

   }

/** Amagar missatges */

function hideMessageBoard(){

   oMessageBoardDiv	= document.getElementById('messageBoardDIV');
   oMessageBoardDiv.style.display	= 'none';

   }

/** Esborrar imatge
 *
 * @param sufijo  Per diferencia entre galerias
 * @param idThumb Identificador de miniatura
 */

function esborrarImatge(sufijo,idThumb) {

   var oThumb = document.getElementById('thumbDIV'+idThumb);
   var nummin = document.getElementById(sufijo+'nummin');
   var total_miniaturas = document.getElementById(sufijo+'total_miniaturas');
   var num = total_miniaturas.value -1;
   var imatge_borrar = document.getElementById('thumbIMG'+idThumb).src;

   var input_imatge_borrar = document.createElement('input');
   input_imatge_borrar.name = 'imatges_borrar[]';
   input_imatge_borrar.value = imatge_borrar;
   input_imatge_borrar.type = 'text';
   input_imatge_borrar.style.display = 'none';
   oThumb.parentNode.appendChild(input_imatge_borrar);

   oThumb.parentNode.removeChild(oThumb);

   /* mostrem input de imatges */
   var input_imatge = document.getElementById(sufijo+'fimatge');
   input_imatge.style.display = 'block';

   // Actualitzem el comptador de thumbnails
   total_miniaturas.value	= num;

   }
