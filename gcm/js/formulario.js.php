<?php

/** imagenes.js.php 
*
* javascript para la administración de imagenes
*
* @author Eduardo Magrané
* @version 1.0
*/

header('Content-Type: text/javascript');

?>

/** Insertar texto
 *
 * Insertar texto en un textarea
 */

function insertar_texto(area_texto,texto) {

	var input = area_texto;
	if(typeof document.selection != 'undefined' && document.selection) {
		var str = document.selection.createRange().text;
		input.focus();
		var sel = document.selection.createRange();
		//sel.text = "[" + tag + "]" + str + "[/" +tag+ "]";
		sel.text = texto;
		sel.select();
		return;
	}else if(typeof input.selectionStart != 'undefined'){
		var start = input.selectionStart;
		var end = input.selectionEnd;
		var insText = input.value.substring(start, end);
		//input.value = input.value.substr(0, start) + '['+tag+']' + insText + '[/'+tag+']'+ input.value.substr(end);
		input.value = input.value.substr(0, start) + texto + input.value.substr(end);
		input.focus();
		input.setSelectionRange(start+2+tag.length+insText.length+3+tag.length,start+2+tag.length+insText.length+3+tag.length);
		return;
	}else{
		input.value+=' ['+tag+']Reemplace este texto[/'+tag+']';
		return;
	}
}
