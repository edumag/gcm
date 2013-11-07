/**
 * @file      GcmConfigGui.js
 * @brief     Javascript para formulario
 *
 * Funciones para añadir eliminar o modificar variables y descripciones
 *
 * @author    Eduardo Magrané 
 * @ingroup GcmConfig
 *
 * @internal
 * Copyright  Copyright (c) 2010, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

var CONTA=100;

/** 
 * Eliminar elemento
 *
 * @param ID Identificador de elemento
 */

function eliminarElemento(ID) {

   var CAJA = document.getElementById(ID);
   CAJA.parentNode.removeChild(CAJA);

   }

/** 
 * Añadir nueva variable 
 *
 * @param idioma Idioma del formulario
 */

function nuevaVariable(idioma) {

   CONTA++;
   var descripcion = prompt('Nuevo elemento','');
   var valor = prompt('Valor','');
   var concepto = descripcion.replace(/ /g,'_');

   var cajaForm = document.getElementById('cajaForm_'+idioma);
   var subCaja = document.createElement('div');
   // subCaja.width='100%';
   subCaja.innerHTML = "<br /><b>"+concepto+"</b><br />";
   subCaja.id = concepto + "-" + CONTA;
   var newTextarea = document.createElement('textarea');
   var newText = document.createElement('input');
   newText.type='text';
   newText.value=descripcion;
   newText.name='descripcion_' + idioma + '[' + concepto + ']';;
   newTextarea.rows=3;
   newTextarea.name='escribir_' + idioma + '[' + concepto + ']';
   newTextarea.value = valor ;
   cajaForm.appendChild(subCaja);
   subCaja.appendChild(newText);
   subCaja.appendChild(newTextarea);

   }

/** 
 * Añadir una nueva variable
 *
 * @param idioma Idioma del formulario
 * @param CLAVE Nombre de la variable
 * @param NUM   Numero a asignar
 */

function anadirVariable(idioma,CLAVE,NUM) {
   var cajaDiv = document.getElementById("caja_"+idioma+"-"+CLAVE);
   CONTA++;

   var subCaja = document.createElement('div');
   //subCaja.innerHTML = "<br />[<a href=\"javascript:anadirVariable('"+idioma+"','"+CLAVE+"','"+CONTA+"')\" >+</a>][<a href=\"javascript:eliminarVariable('"+CLAVE+"-"+CONTA+"') \" >-</a>]<br />";
   subCaja.innerHTML = "<br />[<a href=\"javascript:eliminarVariable('"+CLAVE+"-"+CONTA+"') \" >-</a>]<br />";
   subCaja.id = CLAVE+"-"+CONTA;

   var nuevaVarTextarea = document.createElement('textarea');
   nuevaVarTextarea.rows=3;
   nuevaVarTextarea.name= 'escribir_'+idioma+'['+CLAVE+'][]';

   cajaDiv.appendChild(subCaja);
   subCaja.appendChild(nuevaVarTextarea);
   }

/** 
 * Eliminar variable
 *
 * @param NAME_VAR Nmbre de la variable
 */

function eliminarVariable(NAME_VAR) {
   var CAJA = document.getElementById(NAME_VAR);
   CAJA.parentNode.removeChild(CAJA);
   }

/**
 * Añadir nuevo grupo
 */

function anadirVariableGrupo(idioma, clave, num) {
   var ultimo_item = document.getElementById('item_'+clave+'-'+ (num - 1));
   var nuevo_item = ultimo_item.cloneNode(true);
   ultimo_item.parentNode.insertBefore(nuevo_item,ultimo_item);
   var legend = ultimo_item.childNodes[1];
   legend.innerHTML = clave +' ('+num+')';

   for (i=0;ele=ultimo_item.elements[i];i++) {
       if(/\item_valor\b/.test(ele.className)) {
         ele.value = "";
         ele.name = ele.name.replace('['+(num-1)+']','['+(num)+']');
          }
      }

   }

/**
 * Eliminar un grupo
 */

function eliminarGrupo(grupo) {
   var grupo = document.getElementById(grupo);
   grupo.parentNode.removeChild(grupo);


   }
