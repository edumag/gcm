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
<!--
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
-->
<script type="text/javascript" src="<?php echo $drh?>lib/plupload/plupload/js/plupload.js"></script>
<script type="text/javascript" src="<?php echo $drh?>lib/plupload/plupload/js/plupload.html4.js"></script>
<script type="text/javascript" src="<?php echo $drh?>lib/plupload/plupload/js/plupload.html5.js"></script>
<script type="text/javascript" src="<?php echo $drh?>lib/plupload/plupload/js/i18n/es.js"></script> 

<div style="clear:both"></div>
<div id="container">
   <div id="missatge"><?php echo literal('Afefir imatge');?></div>
       <div id="filelist">No se encontraron extensiones.</div>
       <br />
       <div align="right" id="fimatge" <?php if ( $this->count() >= $this->limit_imatges ) echo ' style="display:none; " '; else echo ' style="padding: 5px;"';?>>
       <a class="formulari2 ma" id="pickfiles" href="#">Seleccionar arxius</a> &nbsp; 
       <a class="formulari2 ma" id="uploadfiles" href="#">Pujar arxius</a>
       </div>
</div>

<div style="clear:both"></div>

<script type="text/javascript">

/**
* @returns A string which specifies which is the current
* browser in which we are running.
*
* Currently-supported browser detection and codes:
* * 'opera' -- Opera
* * 'msie' -- Internet Explorer
* * 'safari' -- Safari
* * 'firefox' -- FireFox
* * 'mozilla' -- Mozilla
*
* If we are unable to property identify the browser, we
* return an empty string.
*
* @type String
 */

function nom_navegador() {

   var browserName = "";

   var ua = navigator.userAgent.toLowerCase();
   if ( ua.indexOf( "opera" ) != -1 ) {
      browserName = "opera";
   } else if ( ua.indexOf( "msie" ) != -1 ) {
      browserName = "msie";
   } else if ( ua.indexOf( "safari" ) != -1 ) {
      browserName = "safari";
   } else if ( ua.indexOf( "mozilla" ) != -1 ) {
      if ( ua.indexOf( "firefox" ) != -1 ) {
         browserName = "firefox";
      } else {
         browserName = "mozilla";
      }
   } else {
      browserName = "desconegut";
      }

   return browserName;

   }

function getEl(id) {
   return document.getElementById(id);
   } 

var navegador = nom_navegador();
var runtimesList;

if ( navegador == 'msie' ){   // Es internet esplorer
  runtimesList='html5,html4';
  // runtimesList='flash,html5,html4';
}else{
  runtimesList='html5,html4';
   }

var limit_galeria = <?php echo $this->limit_imatges;?>;
var maxim_imatges = <?php echo ($this->limit_imatges - $this->count());?>;
var seleccio_multiples_imatges = true;
var contador = getEl("contador_<?php echo $this->identificador_unic?>") ;

if ( limit_galeria == 1 ) seleccio_multiples_imatges = false;


// Convert divs to queue widgets when the DOM is ready

var uploader = new plupload.Uploader({

  browse_button : 'pickfiles',
  container : 'container',
  runtimes : runtimesList,
  url : '<?php echo $drh?>lib/acciones.php?galeria_dir_gcm=<?php echo $this->dir_gcm ?>&galeria_id=<?php echo $this->id?>&galeria_accion=subir',
  max_file_size : '1000mb',
  max_file_count: maxim_imatges,
  chunk_size : '1mb',
  unique_names : true,
  multiple_queues : false,
  multi_selection:seleccio_multiples_imatges,
  rename : true,

  // Resize images on clientside if we can
  resize : {width : <?php echo ( $this->amplaria_max ) ? $this->amplaria_max : 'null'?>, height : <?php echo ( $this->altura_max ) ? $this->altura_max : 'null'?>, quality : 90},

  // Rename files by clicking on their titles
  rename: true,

  // Sort files
  sortable: true,

  // Specify what files to browse for
  filters : [
    {title : "Image files", extensions : "jpg,gif,png,jpeg"},
    {title : "Zip files", extensions : "zip"}
    ],

// Flash settings
flash_swf_url : '<?php echo $drh?>lib/plupload/plupload/js/plupload.flash.swf',

// Silverlight settings
silverlight_xap_url : '<?php echo $drh?>lib/plupload/plupload/js/plupload.silverlight.xap'

});

uploader.bind('Init', function(up, params) {
   // getEl('filelist').innerHTML = "<div>Current runtime: " + params.runtime + "</div>";
   // alert("Current runtime: " + params.runtime);
   // getEl('filelist').innerHTML = "<div>Nombre d'imatges: "+contador.value+"/"+limit_galeria+"</div>";

   if ( navegador == 'msie' ){   // Es internet esplorer
      getEl('filelist').innerHTML = "<div><?php echo literal('NOSE');?></div>";
   } else {
      getEl('filelist').innerHTML = "<div></div>";
      }
   });
 
uploader.bind('FilesAdded', function(up, files) {

   // for (var i in files) {
   //     getEl('filelist').innerHTML += '<div id="' + files[i].id + '">' + files[i].name + ' (' + plupload.formatSize(files[i].size) + ') <b></b></div>';
   //    }

   if(uploader.files.length <= 0){
      getEl('filelist').innerHTML = "";
      }

   // No funciona be
   // if(up.files.length > maxim_imatges || uploader.files.length > maxim_imatges)
   // {
   //    alert('Nomes es poden ' + maxim_imatges + ' imatges!');

   //    return false;
   // }

   var limit_pasat = false;
   var conta = 0;
   var sor = '';
   for (var i in files) {
      conta++;
      if ( conta > maxim_imatges ) {
         limit_pasat = true;
         removeme(i);
         alert('Nomes es poden ' + maxim_imatges + ' imatges!');
         return;
      } else {
         // var tamany = plupload.formatSize(files[i].size);
         // '<span class="size">[ ' + tamany + ' ]</span>' +
         sor = sor + '<div class="item" id="' + files[i].id + '">' + 
            '<span class="name">' + files[i].name + '</span>' +
            '<span onclick="removeme(\''+files[i].id+'\');" id="remove-'+files[i].id+
            '" class="remove">[x]</span>' +
            '<span><b class="percent"></b></span></div>';
         }
      }

   // sor = sor + '';
   getEl('filelist').innerHTML = sor;
   // if ( navegador == 'msie' ){   // Es internet esplorer
   //    getEl('filelist').innerTEXT = sor;
   // } else {
   //    getEl('filelist').innerHTML = sor;
   //    }

   up.refresh(); // Reposition Flash/Silverlight

   // Subimos las imágenes automáticamente
   setTimeout(function () { up.start(); }, 100);
   });

uploader.bind('UploadProgress', function(up, file) {
   if ( getEl(file.id) ) {
      if ( navegador !== 'msie' ){   // Es internet esplorer
         getEl(file.id).getElementsByClassName('percent')[0].innerHTML = file.percent + "%";
         return false;
         }
   } else {
      return false;
      }
   });

uploader.bind('FileUploaded', function(up, file) {

   if ( maxim_imatges < 1 ) {
      return;
      }

   actualizarGaleria();

   contador.value++;
   maxim_imatges--;

   // Comprobar que no pasemos el limite
   if ( maxim_imatges < 1 ) {
      getEl('filelist').innerHTML ="";
      }

   actualizar_missatges();

   });

getEl('uploadfiles').onclick = function() {
    uploader.start();
    return false;
};

/**
 * esborrar imatge de la llista si pasem del limit
 */

function removeme(id){
    // if(inprogress) return false;
    // if(uploader.files.length == 1) getEl('filelist').innerHTML += '<div id="missatge"></div>';

    var element = document.getElementById(id);
    if ( element ) element.parentNode.removeChild(element);

    var toremove = '';

    for(var i in uploader.files){
        if(uploader.files[i].id === id){
            toremove = i;
        }
    }
    uploader.files.splice(toremove, 1);
}

function actualizar_missatges() {

   // Si el contador pasa del limite lo dejamos en él
   if ( contador.value > limit_galeria ) contador.value = limit_galeria;

   maxim_imatges = limit_galeria - contador.value;

   getEl('missatge').innerHTML = "<div>Afegir imatges: "+contador.value+"/"+limit_galeria+"</div>";

   if ( maxim_imatges > 0 ) {
      getEl('missatge').innerHTML += "<div>Encara pots afegir " + maxim_imatges + " imatge/s</div>";
   } else {
      getEl('missatge').innerHTML += "<div>Has arribat al màxim d'imatges</div>";

      var fimatge = document.getElementById('fimatge');
      fimatge.style.display	= 'none';

      }

   }

uploader.init(); 

</script>
