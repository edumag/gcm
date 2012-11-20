#!/bin/bash

NOMBRE=GCM
PHPDOC='../../documentacion/PhpDocumentor/phpdoc'
DIR_FINAL='docs/phpdoc'
DIR_COD='gcm/funciones,gcm/includes,gcm/lib/int/,gcm/modulos/'
IGNORAR='*ext*,.svn,gcm/modulos/gcm'
SALIDA='HTML:frames:DOM/phpdoc.de'


$PHPDOC -p -s -ric  -o "$SALIDA" -dn "$NOMBRE" -dc "$NOMBRE" -d "$DIR_COD" -i "$IGNORAR"  -t "$DIR_FINAL"
