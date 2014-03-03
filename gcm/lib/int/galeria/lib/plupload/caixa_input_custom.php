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

<script type="text/javascript" src="http://bp.yahooapis.com/2.4.21/browserplus-min.js"></script>

<script type="text/javascript" src="<?php echo $drh?>moduls/plupload/plupload/js/plupload.js"></script>
<script type="text/javascript" src="<?php echo $drh?>moduls/plupload/plupload/js/plupload.gears.js"></script>
<script type="text/javascript" src="<?php echo $drh?>moduls/plupload/plupload/js/plupload.silverlight.js"></script>
<script type="text/javascript" src="<?php echo $drh?>moduls/plupload/plupload/js/plupload.flash.js"></script>
<script type="text/javascript" src="<?php echo $drh?>moduls/plupload/plupload/js/plupload.browserplus.js"></script>
<script type="text/javascript" src="<?php echo $drh?>moduls/plupload/plupload/js/plupload.html4.js"></script>
<script type="text/javascript" src="<?php echo $drh?>moduls/plupload/plupload/js/plupload.html5.js"></script>

<div id="container">
    <div id="filelist">No runtime found.</div>
    <br />
    <a id="pickfiles" href="javascript:;">[Select files]</a> 
    <a id="uploadfiles" href="javascript:;">[Upload files]</a>
</div>


<script type="text/javascript">

// Custom example logic
function $(id) {
	return document.getElementById(id);	
}


var uploader = new plupload.Uploader({
	runtimes : 'gears,html5,flash,silverlight,browserplus',
	browse_button : 'pickfiles',
	container: 'container',
	max_file_size : '10mb',
	//url : '<?php echo $drh?>moduls/plupload/upload.php',
   url : '<?php echo $drh?>lib/pujar_imatge.php?nom=<?php echo $this->nom?>&tipo=<?php echo $this->tipo ?>',
	resize : {width : 320, height : 240, quality : 90},
	flash_swf_url : '<?php echo $drh?>/moduls/plupload/plupload/js/plupload.flash.swf',
	silverlight_xap_url : '<?php echo $drh?>/moduls/plupload/plupload/js/plupload.silverlight.xap',
	filters : [
		{title : "Image files", extensions : "jpg,gif,png,jpeg"},
		{title : "Zip files", extensions : "zip"}
	]
});

uploader.bind('Init', function(up, params) {
	$('filelist').innerHTML = "<div>Current runtime: " + params.runtime + "</div>";
});

uploader.bind('FilesAdded', function(up, files) {
	for (var i in files) {
		$('filelist').innerHTML += '<div id="' + files[i].id + '">' + files[i].name + ' (' + plupload.formatSize(files[i].size) + ') <b></b></div>';
	}
});

uploader.bind('UploadProgress', function(up, file) {
	$(file.id).getElementsByTagName('b')[0].innerHTML = '<span>' + file.percent + "%</span>";
});

$('uploadfiles').onclick = function() {
	uploader.start();
	return false;
};

uploader.init();
</script>
</body>
</html>
