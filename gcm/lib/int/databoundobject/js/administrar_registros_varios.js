/**
 * @file administrar_registros_varios.js
 * @brief Script para facilitar la administración de registros varios 
 *        relacionados con un padre.
 */

function Administrar_registros_varios(identificador, total_registros) {

   // Variables privadas
   var _identificador    = identificador;
   var _total_registros  = total_registros;
   var _boton_insertar   = "<a class='boton_insertar_registro' onclick='"+_identificador+".insertar();'>";

   this.Literal_insertar = 'Insertar nuevo registro';

   /** Último formulari que debe ser el de entrar un nuevo registro */
   this.Ultimo_form = false;

   /** Una copia del último formulario */
   this.Form_insertar = false;

   /** Caja contenedora delboton insertar */
   this.Caja_boton = document.createElement("p");

   this.inicia = function() {

      var nombre_elemento = 'caja_registro_'+_identificador+'-'+_total_registros
      this.Ultimo_form = document.getElementById(nombre_elemento);
      this.Form_insertar = this.Ultimo_form.cloneNode(true);

      this.Caja_boton.innerHTML = _boton_insertar + this.Literal_insertar + '</a>';
      this.Ultimo_form.parentNode.appendChild(this.Caja_boton);
      this.Ultimo_form.parentNode.removeChild(this.Ultimo_form);

      // console.debug(this);

      };

   // Al iniciar combinatorias ponemos los campos de formulario
   // en solo lectura

   this.inicia_combinatorio = function() {

      this.inicia();

      console.debug('Id: '+_identificador);

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
      var nuevo = document.createElement("p");
      var input_id = document.createElement("input");
      input_id.name = _identificador+'_'+_identificador+'_id[]';
      input_id.type = 'hidden';
      input_id.value = id;
      var texto = document.createElement("input");
      texto.value = nombre;
      texto.name = 'nombre';
      nuevo.appendChild(input_id);
      nuevo.appendChild(texto);

      this.Caja_boton.parentNode.insertBefore(nuevo,this.Caja_boton);
      }
   }

