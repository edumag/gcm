<?php

/**
 * @file actualizar_galeria.php
 * @brief Actualizar galeria con ajax
 */

/* Comprobar directorio temporal */
   
$ultima = count($this->imatges) -1;

// Comprovar que no hem arribat al màxim 

if ( $ultima < 0 ) {

   $strMessage = literal("No hay imagen subida",3);
   echo "<script>var mess=document.getElementById('messageBoardDIV'); mess.innerHTML('".stripslashes($strMessage)."'); mess.style.display = 'block';</script>";
   exit();

} elseif ( count($this->imatges) > $this->limit_imatges ) {

   $strMessage = literal("Limit de imatges superat",3);
   echo "<script>var mess=document.getElementById('messageBoardDIV'); mess.innerHTML('".stripslashes($strMessage)."'); mess.style.display = 'block';</script>";
   exit();

   }

$this->presentaImatgeEditar($this->imatges[$ultima],$ultima);

