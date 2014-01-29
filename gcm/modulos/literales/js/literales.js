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

function actualizar_literales() {
   $("#panelLiterales").empty();
   $("#panelLiterales").load("?formato=ajax&m=literales&a=devolverLiterales");
   }

/**
 * Ocultamos literales que ya tienen contenido
 */

function filtra() {
   $("#panelLiterales .subpanel").css('display','none');
   }

/**
 * Confirmación para la inserción de elementos
 */

function confirmarAnaydirLiteral()
   {
   if (pedido.readyState == 4 ) {
      if ( pedido.status == 200 ) {
         actualizar_literales();
         }
      }
   }

/**
 * Insertar literal nuevo
 */

function insertarLiteral()
   {
      var key = prompt('concepto','');
      pedirDatos('?formato=ajax&m=literales&a=anyadirLiteral&elemento='+key,'confirmarAnaydirLiteral');
   }


/**
 * Traducir literales de los idiomas que no son por defecto
 *
 * @param key Clave del literal que queremos traducir
 *
 */

function traducirLiteral(key) {
   var res = prompt('Traducción','');
   pedirDatos('?formato=ajax&m=literales&a=anyadirLiteral&elemento='+key+'&valor='+res,'confirmarAnaydirLiteral');
   }

/**
 * Eliminar literal
 *
 * @param key Clave del literal que queremos eliminar
 */

function eliminarLiteral(key) {
   pedirDatos('?formato=ajax&m=literales&a=eliminarLiteral&elemento='+key,'confirmarAnaydirLiteral');
   }


/**
 * Modificar literales de los idiomas
 *
 * @param key Clave del literal que queremos modificar
 * @param val Valor actual del literal
 *
 */

function modificarLiteral(key,val) {
   var res = prompt('Modificaión de '+key,val);
   pedirDatos('?formato=ajax&m=literales&a=modificarLiteral&elemento='+key+'&valor='+res,'confirmarAnaydirLiteral');
   }

/*************************************** NUEVO ***********************************************/

function eliminar_elemento(key) {
   pedirDatos('?formato=ajax&m=literales&a=eliminar_elemento&elemento='+key,'confirma');
   }

/**
 * Confirmación para las acciones
 */

function confirma()
   {
   if (pedido.readyState == 4 ) {
      if ( pedido.status == 200 ) {
         // actualizar_literales();
         alert('OK'); // DEV
         }
      }
   }

