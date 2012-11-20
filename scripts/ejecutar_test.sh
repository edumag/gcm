#!/bin/bash

## Ejecutar los tests que coincidan con '*Test\.php'

LANG=es_ES.UTF-8
salida=/tmp/test_salida.txt
DIR_TMP=/tmp/phpunit/
DIR_ACTUAL=`pwd`
EMAIL=eduardo@localhost

declare -a errores

listado=scripts/tests_ejecutar.txt    ##< Fichero con lista de test a realizar

# Descomentar tipo de informe si se desea alguno

#informe='--testdox-html  ${DIR_TMP}${testunit}.html'
#informe='--report ${DIR_TMP}$testunit' 
#informe='--testdox-text ${DIR_TMP}${testnom}.txt'

[[ -d "$DIR_TMP" ]] || mkdir "$DIR_TMP" && rm -fr "$DIR_TMP/*"

for testunit in `find -name '*Test\.php' -type f` ; do

   ERROR=''
   testnom="`basename $testunit`"
   tests="$tests $testunit"
   cd `dirname $testunit`
   #echo -e "- $testnom\t$testunit"

   eval "cmd=\"phpunit2 $informe $testnom\""
   #echo "Comando: $cmd"
   $cmd | tee $salida

   if [ $? != 0 ] ; then
      ERROR="Error en la ejecución del test $testnom"
   fi

   if [ "`grep -n Incomplete "$salida"`" != "" ] ; then
      ERROR="Test $testnom Incompleto"
   fi

   if [ "`grep -n Errors: "$salida"`" != "" ] ; then
      ERROR="Test $testnom con errores"
   fi

   if [ "$ERROR" != "" ] ; then
      nError=${#errores[*]}
      errores[$nError]="\n$ERROR\n\n$(cat $salida)\n\n"
   fi

   cd $DIR_ACTUAL

done

if [ ${#errores[*]} -gt 0 ] ; then
   echo ${#errores[*]} Error/es, enviamos email con errores
   echo -e ${errores[*]} | mail -s "${#errores[*]} error/es en tests" $EMAIL
fi

if [ "$informe" != "" ] ; then
   echo Informe de test en $DIR_TMP
fi

