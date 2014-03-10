<?php

$drh = $this->dir_base.$this->dir_mod;

?>
/** Editando galería */

	.item{ padding:4px 0;}
	.name{ min-width:100px;padding-right: 6px;}
	.remove{ color:#F00; cursor: pointer;}
.presentaImatgeEditar {
    background: none repeat scroll 0 0 #F5F5F5;
    border: 1px solid;
    border-radius: 3%;
    float: left;
    margin: 4px;
    padding: 14px;
    text-align: center;
}
   #container {border: 1px solid #efefef;background: #eee; margin-bottom:8px;}
   #missatge {border: 1px solid #fff;background: #efefef;padding: 5px;}
	#uploader {margin: 4px;font-family:Verdana, Geneva, sans-serif;font-size:13px;color:#333;background:url(<?php echo $drh?>lib/plupload/bg.jpg);}
   #filelist {margin: 4px;font-family:Verdana, Geneva, sans-serif;font-size:13px;color:#333;background:url(<?php echo $drh?>lib/plupload/bg.jpg);}
   .boto {margin: 6px;margin-botom: 116px;padding: 3px;background: white;border: 1px solid white;display: inline-block;}


/** Visualizando galería */

.presentaImatges img {
    height: 100px;
   width: auto;
}
.presentaImatges {
    background: none repeat scroll 0 0 #F5F5F5;
    border: 1px solid;
    border-radius: 3%;
    float: left;
    text-align: center;
    margin: 4px;
    padding: 4px;
}
.presentaImatges span {
    display: block;
}
