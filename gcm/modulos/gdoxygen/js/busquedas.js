$(document).ready(function(){

$("#resultado_gdoxygen").load("File/es/Proyectos/MagScripts/gdoxygen/search.php", {query: "script", edad: 45}, function(){
      alert("recibidos los datos por ajax"); 
}

)}); 


