<?php

   /** 
   * \file
   * Archivo base para abrir una ventana por separado
   * 
   * Este archivo nos sirve para lanzar una ventana con algun modulo y pueda comunicarse con
   * la ventana principal.
   *
   * @author Eduardo Magrané
   * @version 1.0
   */

   $MODULO = (!empty($_POST['modul'])) ? $_POST['modul'] : $_GET['modul'];

?>
<!DOCTYPE html public "-//w3c//dtd xhtml 1.0 transitional//en" "http://www.w3.org/tr/xhtml1/dtd/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<!--autor: eduardo magrane-->
<!--email: eduardo@mamedu.com-->
<!--inicio.php-->

<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta content="text/html; charset=utf-8" http-equiv="content-type" />
		<meta content="es-es" http-equiv="content-language" />
		<meta content="all" name="robots" />
		<link type="image/x-icon" href="favicon.ico" rel="shortcut icon" />
		<link title="GCM" />
		<meta content="Eduardo magrané." name="author" />
		<meta content="gpl" name="copyright" />
		
		<title><?=$MODULO ?></title>
      <style type="text/css" media="screen, projection">@import "../admin/includes/proyectos/general.css";</style>
      <style type="text/css" media="screen, projection">@import "./proyecto_css.php";</style>
      <script src='../gcm.js' type="text/javascript" language="JavaScript">  </script>
</head>
	
<body>

<div id="principal">
<div id="contenido">
<?php

if (!empty($MODULO)) {
	include("../admin/modulos/".$MODULO."/index.php");
} else {
  echo "No hay un modulo definido";
}
?>
</div>
</div>
</body>
</html>
