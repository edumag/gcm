#!/bin/bash

## Cambiar permisos para los proyectos
##
## Hacer que el grupo de la carpeta del proyecto sea www-data
## y dar permisos de escritura al grupo
##
## @param $1 Carpeta del proyecto

proyecto=$1
usuario=$USER

sudo chown -R www-data:${usuario} "$proyecto"

sudo chmod -R 775 "$proyecto"
