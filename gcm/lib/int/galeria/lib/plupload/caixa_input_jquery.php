<?php

/**
 * @file caixa_input.php Sustitució del metode caixa_input de Galeria
 */


/** Directorio del módulo html */

$drh = $this->dir_base.$this->dir_mod;

?>

<style type="text/css">
	#uploader {
      width: 500px;
      margin: 40px;
		font-family:Verdana, Geneva, sans-serif;
		font-size:13px;
		color:#333;
		background:url(<?php echo $drh?>moduls/plupload/bg.jpg);
	}
</style>
<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.9/themes/base/jquery-ui.css" type="text/css" />
<link rel="stylesheet" href="<?php echo $drh?>moduls/plupload/plupload/js/jquery.ui.plupload/css/jquery.ui.plupload.css" type="text/css" />

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.9/jquery-ui.min.js"></script>
<script type="text/javascript" src="http://bp.yahooapis.com/2.4.21/browserplus-min.js"></script>

<script type="text/javascript" src="<?php echo $drh?>moduls/plupload/plupload/js/plupload.js"></script>
<script type="text/javascript" src="<?php echo $drh?>moduls/plupload/plupload/js/plupload.gears.js"></script>
<script type="text/javascript" src="<?php echo $drh?>moduls/plupload/plupload/js/plupload.silverlight.js"></script>
<script type="text/javascript" src="<?php echo $drh?>moduls/plupload/plupload/js/plupload.flash.js"></script>
<script type="text/javascript" src="<?php echo $drh?>moduls/plupload/plupload/js/plupload.browserplus.js"></script>
<script type="text/javascript" src="<?php echo $drh?>moduls/plupload/plupload/js/plupload.html4.js"></script>
<script type="text/javascript" src="<?php echo $drh?>moduls/plupload/plupload/js/plupload.html5.js"></script>
<script type="text/javascript" src="<?php echo $drh?>moduls/plupload/plupload/js/jquery.ui.plupload/jquery.ui.plupload.js"></script>

	<div id="uploader">
		<p>You browser doesn't have Flash, Silverlight, Gears, BrowserPlus or HTML5 support.</p>
	</div>

<script type="text/javascript">
// Convert divs to queue widgets when the DOM is ready
$(function() {
	$("#uploader").plupload({
		// General settings
		runtimes : 'flash,html5,browserplus,silverlight,gears,html4',
      url : '<?php echo $drh?>moduls/plupload/pujar_imatge.php?nom=<?php echo $this->nom?>&tipo=<?php echo $this->tipo ?>',
		max_file_size : '1000mb',
      max_file_count: <?php echo $this->limit_imatges?>, // user can add no more then 20 files at a time
		chunk_size : '1mb',
		unique_names : true,
		multiple_queues : true,
		rename : true,

		// Resize images on clientside if we can
		resize : {width : 320, height : 240, quality : 90},
		
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

	// Client side form validation
	$('form').submit(function(e) {
        var uploader = $('#uploader').plupload('getUploader');

        // Files in queue upload them first
        if (uploader.files.length > 0) {
            // When all files are uploaded submit form
            uploader.bind('StateChanged', function() {
                if (uploader.files.length === (uploader.total.uploaded + uploader.total.failed)) {
                    $('form')[0].submit();
                }
            });
                
            uploader.start();
        } else
            alert('You must at least upload one file.');

        return false;
    });
	 

});

</script>
