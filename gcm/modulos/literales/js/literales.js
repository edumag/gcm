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
 * Modificar literales de los idiomas
 *
 * @param key 
 *   Clave del literal que queremos modificar.
 * @param val 
 *   Valor actual del literal.
 * @param proyecto_js 
 *   Literal de aplicación o de proyecto 1 0=aplicación 1=proyceto.
 *
 */

function modificar_literal(key,val,proyecto) {
   // Quitamos lit_ de la clave
   key = key.replace('lit_','');
   var res = prompt('Modificaión de '+key,val);
   if ( res ) {
      pedirDatos('?formato=ajax&m=literales&a=modificarLiteral&elemento='+key+'&valor='+res+'&proyecto='+proyecto,'confirma');
      }
   }

/**
 * Confirmación para las acciones
 */

function confirma()
   {
   if (pedido.readyState == 4 ) {
      if ( pedido.status == 200 ) {
         var datos = eval('['+pedido.responseText+']');
           var literal = datos[0]['elemento'];
           var valor = datos[0]['valor'];
           accion = typeof(datos[0]['accion']) != 'undefined' ? datos[0]['accion'] : 'modificado' ;
           console.log(accion);
           switch(accion) {
             case 'insertado':
               // alert('Literal insertado');
               break;
             
             case 'borrado':
               $('#lit_'+literal).text('');
               break;
             
             default:
               $('.literal_faltante_'+literal).each(function (index) {
                   $(this).removeClass();
                   $(this).text(valor);
            
               })
           }
           mostrar_avisos();
         }
      }
   }

/**
 * Administrar literales detectados sin traducir.
 */
function literales_faltantes() {
  $('.literal_faltante').each(function(index) {
    $(this).addClass('destaca_literal_faltante');
  });
  $('.literal_faltante').click(function(e) {
    var enlace_literal = $(this);
    var literal = enlace_literal.html();
    // console.log(literal);
    // console.log(enlace_literal);
    modificar_literal(literal,literal.replace('_',' ','g'),1);
    return false;
  });
}

/**
 * Insertar literal nuevo
 */

function insertar_literal(proyecto) {
  proyecto = typeof(proyecto) != 'undefined' ? proyecto : 1;
   var key = prompt('literal','');
   if (key) {
      pedirDatos('?formato=ajax&m=literales&a=insertar_literal&elemento='+key+'&proyecto='+proyecto,'confirma');
      }
   }

literales_faltantes();

/**
 * Eliminar literal
 */

function eliminar_literal(key,proyecto) {
  proyecto = typeof(proyecto) == 'undefined' ? 1 : proyecto;
   key = key.replace('lit_','');
   pedirDatos('?formato=ajax&m=literales&a=eliminar_literal&elemento='+key+'&proyecto='+proyecto,'confirma');
   }

/**
 * Filtro para listados.
 */

function filtra(elemento,panel) {

   var clase =  elemento.className;
   var panel =  '.'+panel;
   console.log(panel);

   if ( clase == 'boton_activo' ) {
      elemento.className='boton';
      $(panel).css('display','');
   } else {
      elemento.className='boton_activo';
      $(panel).css('display','none');
      }
   }
