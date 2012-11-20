Sistema para el tratamiento de archivos de configuración
--------------------------------------------------------

gcm/lib/int/GcmConfig/

- html                                Plantillas 

  + formGcmConfigGui.html           Formulario para modificar contenido

- js                                  Ficheros Javascript

  + GcmConfigGui.js                 Javascript para el formulario

- lib                                 Librerías

  + GcmConfig.php                   Clase base
  + GcmConfigFactory.php            Patrón factory y singleton para poder recoger
                                    diferentes instancias y no repetir las mismas
  + GcmConfigGui.php                Interface

- test                                Tests

  + TestGcmConfig.php               phpunit 
  + TestGcmConfigFactory.php        phpunit 
  + TestGcmConfigGui.php            phpunit 
  + TestPhpGcmConfigGui.php         php

4 directories, 10 files
