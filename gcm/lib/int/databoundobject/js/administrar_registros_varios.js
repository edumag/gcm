/**
 * @file administrar_registros_varios.js
 * @brief Script para facilitar la administración de registros varios 
 *        relacionados con un padre.
 */

function Administrar_registros_varios(identificador, total_registros) {

   // Variables privadas
   var _identificador    = identificador;
   var _total_registros  = total_registros;
   var _boton_insertar   = "<a class='boton_insertar_registro' onclick='"+_identificador+".insertar();'>Insertar nuevo registro</a>";

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
      console.debug('Elemento: '+nombre_elemento);

      this.Caja_boton.innerHTML = _boton_insertar;
      this.Ultimo_form.parentNode.appendChild(this.Caja_boton);
      this.Ultimo_form.parentNode.removeChild(this.Ultimo_form);

      console.debug(this);

      };

   this.insertar = function() {
      var nuevo = this.Form_insertar.cloneNode(true);
      this.Caja_boton.parentNode.insertBefore(nuevo,this.Caja_boton);
      }
   }

