/**
 * @file administrar_registros_varios.js
 * @brief Script para facilitar la administración de registros varios 
 *        relacionados con un padre.
 */

function Administrar_registros_varios(identificador) {

    // Variables privadas
    var _identificador
    var _total_registros;
 
    // Propiedades
    this.Nombre = nombre;
 
    this.setNombreCompleto = function() {
        _nombreCompleto = "Este Animal es un " + this.Nombre + " de la especie de los " + this.Especie;        
    }
 
    this.getNombreCompleto = function() {
        this.setNombreCompleto();
        return _nombreCompleto;
    }
}
