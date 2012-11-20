#/bin/bash

## Creamos array con la información del fichero /etc/mime.types para php
##
## Idea obtenida de http://scripts.ringsworld.com/content-management/bitweaver-1.1.0/bitweaver/util/mimetypes.php.html
##
## uso: mimetype.sh > gcm/lib/mimetype.php

echo '<?php'
echo "\$mimetypes=Array("

for ((i=2;i<9;i++)) ; do 
   
   egrep -v "^#" /etc/mime.types | awk '{ if ($'$i') { print "\"" $'$i' "\" => \"" $1 "\", "}}' 

done

echo ");"
echo '?>'
