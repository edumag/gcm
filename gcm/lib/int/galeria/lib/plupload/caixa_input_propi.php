<?php

/**
 * @file caixa_input.php Sustitució del metode caixa_input de Galeria
 *
 * @todo Hacer que funcione el resize en cliente del módulo teniendo en cuenta si es vertical
 *       o horizontal
 */


/** Directorio del módulo html */

$drh = $this->dir_base.$this->dir_mod;

?>
<style type="text/css">
   #container {border: 1px solid #efefef;background: #eee; margin-bottom:8px;}
   #missatge {border: 1px solid #fff;background: #efefef;padding: 5px;}
	#uploader {margin: 4px;font-family:Verdana, Geneva, sans-serif;font-size:13px;color:#333;background:url(<?php echo $drh?>moduls/plupload/bg.jpg);}
   #filelist {margin: 4px;font-family:Verdana, Geneva, sans-serif;font-size:13px;color:#333;background:url(<?php echo $drh?>moduls/plupload/bg.jpg);}
   .boto {margin: 6px;margin-botom: 116px;padding: 3px;background: white;border: 1px solid white;display: inline-block;}
</style>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
<script type="text/javascript" src="http://bp.yahooapis.com/2.4.21/browserplus-min.js"></script>

<script type="text/javascript" src="<?php echo $drh?>moduls/plupload/plupload/js/plupload.js"></script>
<script type="text/javascript" src="<?php echo $drh?>moduls/plupload/plupload/js/plupload.gears.js"></script>
<script type="text/javascript" src="<?php echo $drh?>moduls/plupload/plupload/js/plupload.flash.js"></script>
<script type="text/javascript" src="<?php echo $drh?>moduls/plupload/plupload/js/plupload.browserplus.js"></script>
<script type="text/javascript" src="<?php echo $drh?>moduls/plupload/plupload/js/plupload.html4.js"></script>
<script type="text/javascript" src="<?php echo $drh?>moduls/plupload/plupload/js/plupload.html5.js"></script>
<script type="text/javascript" src="<?php echo $drh?>moduls/plupload/plupload/js/i18n/es.js"></script> 

<div id="container">
   <div id="missatge">Afegir imatges</div> <!--FALTA LITARAL-->
       <div id="filelist">No se encontraron extensiones.</div>
       <br />
       <div align="right" id="fimatge" <?php if ( $this->count() >= $this->limit_imatges ) echo ' style="display:none; " '; else echo ' style="padding: 5px;"';?>>
       <a class="formulari2 ma" id="pickfiles" href="javascript:;">Seleccionar arxius</a> &nbsp; 
       <a class="formulari2 ma" id="uploadfiles" href="javascript:;">Pujar arxius</a>
       </div>
</div>
<script type="text/javascript">

function $(id) {

    return document.getElementById(id);

} 

// Convert divs to queue widgets when the DOM is ready

var uploader = new plupload.Uploader({

      browse_button : 'pickfiles',
		runtimes : 'html4,flash,html5,browserplus,gears',
      url : '<?php echo $drh?>moduls/plupload/pujar_imatge.php?nom=<?php echo $this->nom?>&tipo=<?php echo $this->tipo ?>',
		max_file_size : '1000mb',
      max_file_count: <?php echo ($this->limit_imatges - $this->count());?>, // user can add no more then 20 files at a time
		chunk_size : '1mb',
		unique_names : true,
		multiple_queues : true,
		rename : true,

		// Resize images on clientside if we can
      // resize : {width : <?php echo ( $this->amplaria_max ) ? $this->amplaria_max : 'null'?>, height : <?php echo ( $this->altura_max ) ? $this->altura_max : 'null'?>, quality : 90},
		
		// Rename files by clicking on their titles
		rename: true,
		
		// Sort files
		sortable: true,

		// Specify what files to browse for
		filters : [
			{title : "Image files", extensions : "jpg,gif,png,jpeg"},
			{title : "Zip files", extensions : "zip,avi"}
		],

		// Flash settings
		flash_swf_url : '<?php echo $drh?>moduls/plupload/plupload/js/plupload.flash.swf',

		// Silverlight settings
      silverlight_xap_url : '<?php echo $drh?>moduls/plupload/plupload/js/plupload.silverlight.xap',

	});

uploader.bind('Init', function(up, params) {
   $('filelist').innerHTML = "";
});
 
uploader.bind('FilesAdded', function(up, files) {
    for (var i in files) {
        $('filelist').innerHTML += '<div id="' + files[i].id + '">' + files[i].name + ' (' + plupload.formatSize(files[i].size) + ') <b></b></div>';
    }
});
 
uploader.bind('UploadProgress', function(up, file) {
    $(file.id).getElementsByTagName('b')[0].innerHTML = '<span>' + file.percent + "%</span>";
});

uploader.bind('FileUploaded', function(up, file) {

   actualizarGaleria();

   var limit_imatges = <?php echo $this->limit_imatges;?>;
    
   var contador = $("contador_<?php echo $this->identificador_unic?>").value ;
   contador++;
   // Actualitzem el comptador de thumbnails
   $("contador_<?php echo $this->identificador_unic?>").value	= contador;

   // Si tenim el tope de imatges permeses ocultem input
   if ( contador>=limit_imatges ) {
      var fimatge = document.getElementById('fimatge');
      fimatge.style.display	= 'none';
      //$('filelist').innerHTML = '';
      //alert('Maxim de imatges superat');
      // $('filelist').innerHTML = "<p><b>Afegir imatges</b></p>";
      $('missatge').innerHTML = "<b>Limit de imatges "+limit_imatges+"</b>";
      // uploader.init(); 
      }


   });
$('uploadfiles').onclick = function() {
    uploader.start();
    return false;
};
 
uploader.init(); 

</script>
