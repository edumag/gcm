/**
 * @file      constantes.js
 * @brief     Javascript para el manejo de constantes.
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 *   Created  23/04/10
 *  Revision  SVN $Id: $
 * Copyright  Copyright (c) 2010, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

/**
 * Actualizar panel de constantes
 */

function actualizar_constantes() {
   $("#panelConstantes").empty();
   $("#panelConstantes").load("?formato=ajax&m=constantes&a=devolverConstantes");
   }

/**
 * Confirmación para la inserción de elementos
 */

function confirmarAnaydirConstante()
   {
   if (pedido.readyState == 4 ) {
      if ( pedido.status == 200 ) {
         actualizar_constantes();
         }
      }
   }

/**
 * Insertar constante nuevo
 */

function insertarConstante()
   {
      var key = prompt('concepto','');
      pedirDatos('?formato=ajax&m=constantes&a=anyadirConstante&elemento='+key,'confirmarAnaydirConstante');
   }

/**
 * Eliminar constante
 *
 * @param key Clave del constante que queremos eliminar
 */

function eliminarConstante(key) {
   pedirDatos('?formato=ajax&m=constantes&a=eliminarConstante&elemento='+key,'confirmarAnaydirConstante');
   }


/**
 * Modificar constantes de los idiomas
 *
 * @param key Clave del constante que queremos modificar
 * @param val Valor actual del constante
 *
 */

function modificarConstante(key,val) {
   var res = prompt('Modificaión de '+key,val);
   pedirDatos('?formato=ajax&m=constantes&a=modificarConstante&elemento='+key+'&valor='+res,'confirmarAnaydirConstante');
   }

/*
* Presentamos los constantes para poder ser insertados en el formulario
* de tiny.
*/

function presentaConstantes(){

   if (pedido.readyState == 4 ) {
      if ( pedido.status == 200 ) {
         var container = document.getElementById('panelConstantes');
         var salida = '<br />' ;
         var datos = eval('['+pedido.responseText+']');
         if ( datos ) {
            for ( var elemento in datos ) {
               for ( var subelemento in datos[elemento] ) {
                  //salida += '<p class="subpanel" style="text-align:left">';
                  salida += '<p class="subpanel" >';
                  salida += '<a href="javascript:;" onmousedown="tinyMCE.execCommand(\'mceInsertContent\',false,\'{C{' + subelemento + '}}\');" ';
                  salida += 'title="' + datos[elemento][subelemento][0] + ' | ' + datos[elemento][subelemento][1] + '" >';
                  salida += subelemento + '</a>';
                  salida += '</p>';
                  }
               }
            container.innerHTML = salida ;
            }
         }
      }
   }
