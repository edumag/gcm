/**
 * @file  listado_reindexado.js
 * @brief Reindexar contenido
 *
 * Generamos javascript que nos vaya reindexando archivo por archivo
 * y informando del estado del mismo.
 *
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 *   Created  04/02/11
 *  Revision  SVN $Id: Indexador.php 496 2011-04-15 09:47:21Z eduardo $
 * Copyright  Copyright (c) 2011, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

var errores_indexando = 0;
indexados = 0;

function indexar(item) {
   var caja_id = document.getElementById('caja_'+item);
   caja_id.innerHTML = '<br />Indexando..';
   $.get('',{ formato: "ajax", e: "indexar", url: item }, function(data){ presentar_respuesta_indexador(item,data);});
   }

function presentar_respuesta_indexador(item,data) {

   var caja = document.getElementById(item);
   var caja_id = document.getElementById('caja_'+item);
   var panel_indexado = document.getElementById('panel_indexado');

   var res = '<div class="aviso">';
   res += data;
   res += '</div>';
   caja_id.innerHTML = res ;
   indexados++;
   panel_indexado.innerHTML = 'Indexados: '+indexados;
   caja.style.display = "none";
   }

function ejecutar_reindexado() {

   var items = document.getElementsByClassName('por_indexar');
   var num_items_ri = items.length;
   var salida;

   for (i=0;i<num_items_ri;i++) {
      salida = salida + "\n" + i + ': ' + items[i].id;
      indexar(items[i].id);
      }

   }

