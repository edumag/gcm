#!/bin/bash

for file in gcm/modulos/*/eventos_*.php ; do 
   echo -e "\n$file\n" 
   cat $file | grep 'eventos\['
   echo "---------------------------------" 
done
