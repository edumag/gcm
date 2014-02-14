/**
 * @file      literales.js
 * @brief     Javascript para el manejo de literales.
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
 * Actualizar panel de literales
 */

function actualizar_literales_columna() {
   $("#panelLiterales").empty();
   $("#panelLiterales").load("?formato=ajax&m=literales&a=devolverLiterales");
   }

/**
 * Confirmación para la inserción de elementos
 */

function confirmar_literales_columna()
   {
   if (pedido.readyState == 4 ) {
      if ( pedido.status == 200 ) {
         actualizar_literales_columna();
         }
      }
   }

/**
 * Insertar literal nuevo
 */

function insertar_literal_columna()
   {
      var key = prompt('concepto','');
      pedirDatos('?formato=ajax&m=literales&a=anyadirLiteral&elemento='+key,'confirmar_literales_columna');
   }


/**
 * Eliminar literal
 *
 * @param key Clave del literal que queremos eliminar
 */

function eliminar_literal_columna(key) {
   pedirDatos('?formato=ajax&m=literales&a=eliminar_elemento&elemento='+key,'confirmar_literales_columna');
   }


/**
 * Modificar literales de los idiomas
 *
 * @param key Clave del literal que queremos modificar
 * @param val Valor actual del literal
 *
 */

function modificar_literal_columna(key,val) {
   var res = prompt('Modificaión de '+key,val);
   pedirDatos('?formato=ajax&m=literales&a=modificarLiteral&elemento='+key+'&valor='+res,'confirmar_literales_columna');
   }

/**
 * Actualizar panel de literales
 */

function actualizar_literales(panel) {
   panel = document.getElementById(panel);
   $(panel).empty();
   $(panel).load("?formato=ajax&m=literales&a=administrar");
   }

/**
 * Confirmación para las acciones
 */

function confirma()
   {
   if (pedido.readyState == 4 ) {
      if ( pedido.status == 200 ) {
         actualizar_literales('contenido');
         // alert('OK'); // DEV
         }
      }
   }

/**
 * Insertar literal nuevo
 */

function insertar_literal(admin) {
   var key = prompt('literal','');
   if (key) {
      pedirDatos('?formato=ajax&m=literales&a=anyadirLiteral&elemento='+key+'&admin='+admin,'confirma');
      }
   }


function eliminar_elemento(key,admin) {
   key = key.replace('lit_','');
   pedirDatos('?formato=ajax&m=literales&a=eliminar_elemento&elemento='+key+'&admin='+admin,'confirma');
   }

/**
 * Modificar literales de los idiomas
 *
 * @param key Clave del literal que queremos modificar
 * @param val Valor actual del literal
 *
 */

function modificar_literal(key,val,admin) {
   // Quitamos lit_ de la clave
   key = key.replace('lit_','');
   var res = prompt('Modificaión de '+key,val);
   if ( res ) {
      pedirDatos('?formato=ajax&m=literales&a=modificarLiteral&elemento='+key+'&valor='+res+'&admin='+admin,'confirma');
      }
   }

/**
 * Ocultamos literales que ya tienen contenido
 */

function filtra(elemento,panel) {

   var clase =  elemento.className;
   var panel =  '#'+panel;
   console.log(panel);

   if ( clase == 'boton_activo' ) {
      elemento.className='boton';
      $(panel + " .subpanel").css('display','');
   } else {
      elemento.className='boton_activo';
      $(panel + " .subpanel").css('display','none');
      }
   }

