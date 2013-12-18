#!/bin/bash

echo
echo Nombre del módulo en minusculas y sin espaciós ni acentos
echo
read -p 'Nombre del módulo: ' nombre

mayuscula=$(echo $nombre | awk ' { out = out" "toupper(substr($0,1,1))substr($0,2) } END{ print substr(out,2) } ')


cp -R plantillas/modulos gcm/modulos/

cd gcm/modulos/

mv modulos "$nombre" 
cd "$nombre"
mv lib/Modulo.php lib/${mayuscula}.php
mv lib/ModuloAdmin.php lib/${mayuscula}Admin.php
mv html/modulo.phtml html/${nombre}.phtml

sed -i -e "s/{modulo}/$nombre/g" `find -type f`
sed -i -e "s/{Modulo}/$mayuscula/g" `find -type f`

cd ../..

