<?php

/**
 * Javascript per la pujada d'imatges 
 *
 * @package Galeria
 * @author Eduardo Magrané
 */

?>

<script language="JavaScript">

   /** Estat de la resposta en ajax */
	var oHttpRequest = false;

   /** Ajax */

	function ognMakeAjaxRequest(strUrl,strPosting,strProcessResponse) {

		oHttpRequest = false;

		if (window.XMLHttpRequest) { // Mozilla, Safari,...
			oHttpRequest = new XMLHttpRequest();
			if (oHttpRequest.overrideMimeType) {
				oHttpRequest.overrideMimeType('text/xml');
			}
		} else if (window.ActiveXObject) { // IE
			try {
				oHttpRequest = new ActiveXObject("Msxml2.XMLHTTP");
			} catch (e) {
				try {
					oHttpRequest = new ActiveXObject("Microsoft.XMLHTTP");
				} catch (e) {}
			}
		}

		if (!oHttpRequest) {
			alert('Error!! \nEl navegador no permite conexiones asíncronas');
			return false;
		}
		
		
		oHttpRequest.onreadystatechange = strProcessResponse;
		oHttpRequest.open('GET', strUrl, true);
		oHttpRequest.send(strPosting);
		
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
    * @param id Identificador d'imatge 
    * @param idThumb Identificador de miniatura
    */

   function esborrarImatge(identificador_unic,id,idThumb) {

      var contador = document.getElementById('contador_'+identificador_unic);
      var galeria  = '<?php echo $this->id; ?>';
      var temporal = '<?php echo ($this->temporal) ? 'si' : 'no' ; ?>';

      ognMakeAjaxRequest('<?php echo $this->dir_base.$this->dir_mod;?>lib/acciones.php?galeria_dir_gcm=<?php echo $this->dir_gcm ?>&galeria_accion=esborra&id=<?php echo $this->id?>&g='+galeria+'&tmp='+temporal+'&idThumb='+idThumb+'&id='+id,null,esborrarMiniatura);
      
      var fimatge = document.getElementById('fimatge');
      fimatge.style.display	= 'block';

		// Actualitzem el comptador de thumbnails
		contador.value	= contador.value - 1;
      }

   /** Esborrar miniatura */

	function esborrarMiniatura() {

		if (oHttpRequest.readyState == 4) {
			if (oHttpRequest.status == 200) {
				if(isNaN(oHttpRequest.responseText)){
					alert(oHttpRequest.responseText);
				}
				else{
					hideMessageBoard();

               miniatura_id	= oHttpRequest.responseText;

               if ( isNaN(miniatura_id) ) {

                  showMessageBoard('No s\'ha trobat l\'imatge a esborrar'); // FALTA LITERAL

               } else {

                  oThumb = document.getElementById('miniatura_div'+miniatura_id);
                  oThumb.parentNode.removeChild(oThumb);

                  actualizar_missatges();  // caixa_input.php de plupload

                  <?php if ( $this->accio_esborra ) { ?>
                     var cmd = '<?php echo $this->accio_esborra; ?>('+miniatura_id+');';
                     eval(cmd);
                  <?php } ?>

                  }

				   }

         } else {

            alert('Error!!\La conexión con el servidor no se ha efectuado correctamente.\n'+oHttpRequest.status);

			}
		}
	}

   /**
    * Depurar variables o objectes
    */

   function depurar(obj) {
      var out = '';    
      for (var i in obj) {
         out += i + ": " + obj[i] + "\n";
         }
      var pre = document.createElement('pre');
      pre.innerHTML = out;
      document.body.appendChild(pre);
      }


   function actualizarGaleria() {

      var src = "<?php echo $this->dir_base.$this->dir_mod;?>lib/acciones.php?galeria_dir_gcm=<?php echo $this->dir_gcm ?>&galeria_accion=actualizar&id=<?php echo $this->id?>";

      ognMakeAjaxRequest(src,null,presentaGaleria);

      }

   function presentaGaleria() {

		if (oHttpRequest.readyState == 4) {
			if (oHttpRequest.status == 200) {

            hideMessageBoard();
   
            salida	= oHttpRequest.responseText;

            if ( salida == '' ) {

               alert('Error!! Sin salida.\n'+oHttpRequest.status);

            } else {

               var caixa_galeria = document.getElementById('caixa_galeria');

               // Recoger información del formulario para no perderla al actualizar la galería

               caixa_galeria.innerHTML += salida;

               }


         } else {

            alert('Error!!\La conexión con el servidor no se ha efectuado correctamente.\n'+oHttpRequest.status);

			}
		}
      }

</script>
