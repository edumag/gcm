<?php

/** Directorio del módulo html */

$drh = $this->dir_base.$this->dir_mod;

?>

<style type="text/css">
	body {
		font-family:Verdana, Geneva, sans-serif;
		font-size:13px;
		color:#333;
		background:url(<?php echo $drh?>moduls/plupload/bg.jpg);
	}
</style>
<link rel="stylesheet" href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.9/themes/base/jquery-ui.css" type="text/css" />
<!-- <link rel="stylesheet" href="<?php echo $drh?>moduls/plupload/plupload/js/jquery.ui.plupload/css/jquery.ui.plupload.css" type="text/css" /> -->
<link rel="stylesheet" href="<?php echo $drh?>moduls/plupload/plupload/js/jquery.plupload.queue/css/jquery.plupload.queue.css" type="text/css" media="screen" />


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
<!-- 
<script type="text/javascript" src="<?php echo $drh?>moduls/plupload/plupload/js/jquery.ui.plupload/jquery.ui.plupload.js"></script>
-->
<script type="text/javascript" src="<?php echo $drh?>moduls/plupload/plupload/js/jquery.plupload.queue/jquery.plupload.queue.js"></script>

	<h3>Log messages</h3>
	<textarea id="log" style="width: 100%; height: 150px; font-size: 11px" spellcheck="false" wrap="off"></textarea>
	<h3>Queue widget</h3>
	<div id="uploader" style="width: 450px; height: 330px;">You browser doesn't support upload.</div>
	<a id="clear" href="#">Clear queue</a>

<script type="text/javascript">
$(function() {
	function log() {
		var str = "";

		plupload.each(arguments, function(arg) {
			var row = "";

			if (typeof(arg) != "string") {
				plupload.each(arg, function(value, key) {
					// Convert items in File objects to human readable form
					if (arg instanceof plupload.File) {
						// Convert status to human readable
						switch (value) {
							case plupload.QUEUED:
								value = 'QUEUED';
								break;

							case plupload.UPLOADING:
								value = 'UPLOADING';
								break;

							case plupload.FAILED:
								value = 'FAILED';
								break;

							case plupload.DONE:
								value = 'DONE';
								break;
						}
					}

					if (typeof(value) != "function") {
						row += (row ? ', ': '') + key + '=' + value;
					}
				});

				str += row + " ";
			} else { 
				str += arg + " ";
			}
		});

		$('#log').val($('#log').val() + str + "\r\n");
	}

	$("#uploader").pluploadQueue({
		// General settings
		runtimes: 'html5,gears,browserplus,silverlight,flash,html4',
      url : '<?php echo $drh?>moduls/plupload/pujar_imatge.php?nom=<?php echo $this->nom?>&tipo=<?php echo $this->tipo ?>',
		max_file_size: '10mb',
		chunk_size: '1mb',
		unique_names: true,

		// Resize images on clientside if we can
		resize: {width: 320, height: 240, quality: 90},

		// Specify what files to browse for
		filters: [
			{title: "Image files", extensions: "jpg,gif,png"},
			{title: "Zip files", extensions: "zip"}
		],

		// Flash settings
		flash_swf_url : '<?php echo $drh?>moduls/plupload/plupload/js/plupload.flash.swf',

		// Silverlight settings
		silverlight_xap_url : '<?php echo $drh?>moduls/plupload/plupload/js/plupload.silverlight.xap',

		// PreInit events, bound before any internal events
		preinit: {
			Init: function(up, info) {
				log('[Init]', 'Info:', info, 'Features:', up.features);
			},

			UploadFile: function(up, file) {
				log('[UploadFile]', file);

				// You can override settings before the file is uploaded
				// up.settings.url = 'upload.php?id=' + file.id;
				// up.settings.multipart_params = {param1: 'value1', param2: 'value2'};
			}
		},

		// Post init events, bound after the internal events
		init: {
			Refresh: function(up) {
				// Called when upload shim is moved
				log('[Refresh]');
			},

			StateChanged: function(up) {
				// Called when the state of the queue is changed
				log('[StateChanged]', up.state == plupload.STARTED ? "STARTED": "STOPPED");
			},

			QueueChanged: function(up) {
				// Called when the files in queue are changed by adding/removing files
				log('[QueueChanged]');
			},

			UploadProgress: function(up, file) {
				// Called while a file is being uploaded
				log('[UploadProgress]', 'File:', file, "Total:", up.total);
			},

			FilesAdded: function(up, files) {
				// Callced when files are added to queue
				log('[FilesAdded]');

				plupload.each(files, function(file) {
					log('  File:', file);
				});
			},

			FilesRemoved: function(up, files) {
				// Called when files where removed from queue
				log('[FilesRemoved]');

				plupload.each(files, function(file) {
					log('  File:', file);
				});
			},

			FileUploaded: function(up, file, info) {
				// Called when a file has finished uploading
				log('[FileUploaded] File:', file, "Info:", info);
			},

			ChunkUploaded: function(up, file, info) {
				// Called when a file chunk has finished uploading
				log('[ChunkUploaded] File:', file, "Info:", info);
			},

			Error: function(up, args) {
				// Called when a error has occured

				// Handle file specific error and general error
				if (args.file) {
					log('[error]', args, "File:", args.file);
				} else {
					log('[error]', args);
				}
			}
		}
	});

	$('#log').val('');
	$('#clear').click(function(e) {
		e.preventDefault();
		$("#uploader").pluploadQueue().splice();
	});
});
</script>
