/**
 * @file administrar_registros_varios.js
 * @brief Script para facilitar la administración de registros varios 
 *        relacionados con un padre.
 * @ingroup crud
 */

/**
 * Mecanismo para facilitar la gestión de registros vinculados 
 * a otro registro
 */

function Administrar_registros_varios(identificador, total_registros) {

   var _degug = true;

   if ( _degug ) console.debug('Id: '+identificador);
   if ( _degug ) console.debug('total_registros: '+total_registros);

   // Variables privadas
   var _identificador    = identificador;
   var _total_registros  = total_registros;
   var _boton_insertar   = "<a class='boton_insertar_registro' onclick='"+_identificador+".insertar();'>";

   this.caja_posibles = document.getElementById('posibles_registros_'+identificador);
   this.caja_relacionados = document.getElementById('relacionados_'+identificador);

   this.Literal_insertar = 'Insertar nuevo registro';

   /** Último formulari que debe ser el de entrar un nuevo registro */
   this.Ultimo_form = false;

   /** Una copia del último formulario */
   this.Form_insertar = false;

   /** Caja contenedora delboton insertar */
   this.Caja_boton = document.createElement("p");

   this.inicia = function() {

      var nombre_elemento = 'caja_registro_'+_identificador+'-'+_total_registros

      if ( _degug ) console.debug('nombre_elemento: '+nombre_elemento);

      this.Ultimo_form = document.getElementById(nombre_elemento);
      this.Form_insertar = this.Ultimo_form.cloneNode(true);

      this.Caja_boton.innerHTML = _boton_insertar + this.Literal_insertar + '</a>';
      this.Ultimo_form.parentNode.appendChild(this.Caja_boton);
      this.Ultimo_form.parentNode.removeChild(this.Ultimo_form);

      };

   // Al iniciar combinatorias ponemos los campos de formulario
   // en solo lectura

   this.inicia_combinatorio = function() {

      if ( _degug ) console.debug('Id: '+_identificador);

      this.inicia();

      var frm = document.getElementById('forms_'+_identificador);
      var nombre_boton_eliminar = _identificador+'_eliminar';

      for (i=0;i<frm.elements.length;i++) {

         var name = frm.elements[i].name;

         if (name.indexOf(nombre_boton_eliminar) == -1) {
            frm.elements[i].readOnly=true;
            }

         }

      };

   this.insertar = function() {
      var nuevo = this.Form_insertar.cloneNode(true);
      this.Caja_boton.parentNode.insertBefore(nuevo,this.Caja_boton);
      }

   this.insertar_registro = function(id,nombre) {
      var nuevo = document.createElement("li");
      var input_id = document.createElement("input");
      var texto = document.createElement("input");
      var elemento_borrar = document.getElementById('li_posible_' + _identificador + '-' + id);
      nuevo.id = 'li_relacionado_' + _identificador + '-' + id;
      input_id.name = _identificador+'_'+_identificador+'_id[]';
      input_id.type = 'hidden';
      input_id.value = id;
      texto.value = nombre;
      texto.type = 'hidden';
      texto.name = 'nombre';
      contenido = '<a href="javascript:' + _identificador + '.eliminar_registro(' + id + ',\'' + nombre + '\')">';
      contenido += nombre + '</a>'; 
      nuevo.innerHTML = contenido;
      nuevo.appendChild(input_id);
      nuevo.appendChild(texto);
      elemento_borrar.parentNode.removeChild(elemento_borrar);
      this.caja_relacionados.appendChild(nuevo,this.caja_relacionados);
      }
   this.eliminar_registro = function(id,nombre) {
      var nuevo = document.createElement("li");
      var elemento_borrar = document.getElementById('li_relacionado_' + _identificador + '-' + id);
      nuevo.id = 'li_posible_' + _identificador + '-' + id;
      contenido = '<a href="javascript:' + _identificador + '.insertar_registro(' + id + ',\'' + nombre + '\')">';
      contenido += nombre + '</a>' + nuevo.innerHTML;
      nuevo.innerHTML = contenido;
      elemento_borrar.parentNode.removeChild(elemento_borrar);
      this.caja_posibles.appendChild(nuevo,this.caja_relacionados);
      }
   }

