Cambios que se deben realizar en los módulos
--------------------------------------------
  
- Archivos de tema actual: $gcm-tema->ruta('indexador','css','botonera.html')

General
-------

- Las búsquedas solo afectan al idioma predeterminado. ¿?

- Cambio de tema dinamico:

  + Crear una variable de sesión que nos indique el tema actual.

  + Definir tema visualización y tema administración, para jugar con los dos a
    la hora de administrar la aplicación.

Eventos
-------

- Eventos: debe informar si se ha ejecutado un evento o no

- Al lanzar un evento guardar el estado del mismo por cada acción realizada.

   - finalizado correctamente.
   - finalizado con avisos.
   - finalizado con aviso importante.
   - finalizado con errores.
   - sin acciones asociadas.
   - ...

- Ejemplo evento buscar.

   Diferentes módulos lo utilizan y si uno no encuentra algo pero otro si no se
   debe presentar mensaje de que no hubo resultados.

Varios
------

- Hacer con constantes lo mismo que con literales

Borrado automático de cache, se multiplican los archivos.

Hacer que valide con javascript los formularios combinados, definir la forma de seleccionar registros combinados y recuperar datos de combinados para plantilla.

Documentación
-------------

- Seguir lo realizado en GcmConfig y Crud para tener todas las librerías internas
  agruadas.

- Hacer los mismo con los módulos y seguir lo realizado con el módulo
  Comentarios.

