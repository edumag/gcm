#!/bin/bash

# vim: set sw=4 ts=4 et:
# wirtten by katja socher <katja@linuxfocus.org>
# and guido socher <guido@linuxfocus.org>
#
#
# PENDIENTE las imagenes de los botones se colocan en una 
# carpeta a parte. botones
# 
# Posicion de los botones ON/OFF y si se apretan en estado on 
# no se repite

ver="0.1"
titulo=""
M="index.html"
G="grande.html"
img1="izquierda2.gif"
img2="automatic.gif"
img3="stop.gif"
img4="derecha2.gif"
prog=`basename $0`
ruta_galeria='../galeria/'
# Cabecera propia
CS="<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Transitional//EN\"\n\"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd\">"
CS="${CS}\n<!--Autor: Eduardo Magrane-->\n<!--Email: edu@lesolivex.com-->\n<html xmlns=\"http://www.w3.org/1999/xhtml\">\n<head>"
CS="${CS}\n<script language=\"JavaScript\"  type=\"text/javascript\" src=\"automatic.js\" ></script>"
CS="${CS}\n<script language=\"JavaScript\" type=\"text/javascript\" src=\"${ruta_galeria}galeria.js\" ></script>"
CS="${CS}\n<style type=\"text/css\" media=\"screen, projection\">@import \"${ruta_galeria}galeria.css\";</style>"
CS="${CS}\n</head>\n<body style='background-color: #ddd; color: black'>"
final_propio="</body>\n</html>"

help() {

   cat <<HELP

$prog -- genera miniaturas y codigo html. 
Para cualquier numero de imagenes

USO: $prog [-h] [-l|-html] -cabecera <fich> -final <fuch> -t 'cadena' image1 image2 ...

OPCIONES: -h esta ayuda.
          -l genera lineas html.
          -m genera las minuaturas.
          -t titulo para la pagina html.
          -c fichero con la cabecera de la pagina html.
          -f fichero con el final de la pagina html.
          -html crea las paginas html, miniaturas.html grandes.html, esto supone que 
           las miniaturas ya estan creadas y en el directorio miniaturas.
          --ruta-galeria <Directorio relativo para galeria.js, por defecto ../>

EJEMPLO: $prog -m -html *jpg

Este programa utiliza identify utilidad de ImageMagick

versión $ver

Esta es una modificación sobre imagesrclines de la revista
linuxfocus. www.linuxfocus.org

HELP
    echo base $0
        exit 0
    }
error()     {
    echo "$1"
    exit "$2"
    }
lineas(){
    ## Genera linea en html de las imagenes.
    # process each image
    #echo "Generando Lineas"
    for imgfile in $* ;do
        if [ ! -r "$imgfile" ]; then
            echo "ERROR: no puedo leer $imgfile\n"
        else
            geometry=`identify $imgfile | awk '{print $3}'`
            # geometry can be 563x144+0+0 or 75x98
            # get rid of the +0+0
            width=`echo $geometry | sed 's/[^0-9]/ /g' | awk '{print $1}'`
            height=`echo $geometry | sed 's/[^0-9]/ /g' | awk '{print $2}'`
            echo "<img src=\"$imgfile\" width=\"$width\" height=\"$height\" alt=\"[]\">"
        fi
    done
}
miniaturas(){
    ## Generar miniaturas.
    mkdir miniaturas
    for imgfile in $* ;do
        if [ ! -r "$imgfile" ]; then
             echo "ERROR: no puedo leer $imgfile\n"
        else
            NOMBRE_IMAGEN=`basename $imgfile`
            echo "Creando miniatura de $imgfile"
            convert -geometry x105 $imgfile miniaturas/$NOMBRE_IMAGEN
        fi
    done
}
imagen600(){
    ## Generar imagenes a tamaÃ±o web
    ## 600 de alto
    mkdir imagenes
    for imgfile in $* ;do
        if [ ! -r "$imgfile" ]; then
             echo "ERROR: no puedo leer $imgfile\n"
        else
            NOMBRE_IMAGEN=`basename $imgfile`
            echo "Creando imágenes para web de $imgfile"
            convert -geometry 600x $imgfile imagenes/$NOMBRE_IMAGEN
        fi
    done
}
generarHTML(){
    ## Generamos el codigo de las paginas miniaturas.html y grande.html

    # si existe ya $M destino preguntamos que hacer.
    if [ -e "$M" ] ; then
        rm -f "$M"
    fi
    # si existe ya $G destino preguntamos que hacer.
    if [ -e "$G" ] ; then
        rm -f "$M"
    fi
    # si existe ya automatic.js destino preguntamos que hacer.
    if [ -e automatic.js ] ; then
        rm -f automatic.js
    fi

    # cabecera del archivo
    echo -e "$CS" > $M
    echo -e "$CS" > $G

    # cabecera de usuario
    if [ -e "$cabecera" ] ; then
        cat "$cabecera" >> $M
        cat "$cabecera" >> $G
    fi

    if [ "$titulo" != "" ] ; then
      echo "<h1>$titulo</h1>" >> $M
      echo "<h1>$titulo</h1>" >> $G
    fi

    NUM=$#
    echo "<i>Numero de imatges</i>: $#" >> $M
    echo "<i>Numero de imatges</i>: $#" >> $G
    echo "<br /><hr/>" >> $M
    echo "<br /><hr/>" >> $G
    echo "<center>" >> $M
    echo "<center>" >> $G
    conta=1
    conta2=1
    conta3=$#

    echo -n "var lista = [" >> automatic.js

    for imgfile in $* ;do
        NOMBRE_IMAGEN=`basename $imgfile`
        if [ ! -r "$imgfile" ]; then
             echo "ERROR: no puedo leer $imgfile\n"
        else
            echo -e ".\c"
            conta3=$(($conta3-1))
            NOMBRE_IMAGEN=`basename $imgfile`
            #echo -e "$imgfile - \c"
            # Buscamos las dimensiones de las imagenes
            geometry=`identify miniaturas/$NOMBRE_IMAGEN | awk '{print $3}'`
            geometryG=`identify $imgfile | awk '{print $3}'`
            ancho=`echo $geometry | sed 's/[^0-9]/ /g' | awk '{print $1}'`
            anchoG=`echo $geometryG | sed 's/[^0-9]/ /g' | awk '{print $1}'`
            alto=`echo $geometry | sed 's/[^0-9]/ /g' | awk '{print $2}'`
            altoG=`echo $geometryG | sed 's/[^0-9]/ /g' | awk '{print $2}'`
            #ancho="80"
            #alto="80"

            # Añadimos el codigo
			echo "<table style='display: inline; margin: 5px;	text-align: center'><tr>" >> $M
			echo "<td style='border: 1px solid #000; padding:2px; width: 0;'>">> $M
			#Nombre imgen
			echo $NOMBRE_IMAGEN >> $M
			echo "</td></tr><td style='border: 1px solid #000; padding:2px; width: 0;'>">> $M
            echo "<a href=\"grande.html?id=$conta2\">" >> $M
            echo "<img src=\"miniaturas/$NOMBRE_IMAGEN\" width=\"$ancho\" height=\"$alto\" alt=\"[$NOMBRE_IMAGEN]\" />" >> $M
            echo "</a>" >> $M
			echo "</td></tr></table>">> $M
            
            # Creamos el fichero automatic.js donde estara el listado de todas
            # las imagenes.
            echo -n "[\"`basename $imgfile`\"," >> automatic.js
            echo -n "\"$imgfile\"," >> automatic.js
            echo -n "\"./miniaturas/`basename $imgfile`\"," >> automatic.js
            echo -n "$anchoG]," >> automatic.js
            
            if [ "$conta" = "4" ] ; then 
                conta=0
            fi
            conta=$(($conta+1))
            conta2=$(($conta2+1))
            
            
        fi
    done

    echo  "];" >> automatic.js

    anadirJavaScript

    echo "</center>" >> $M
    echo "</center>" >> $G
    
    if [ -e "$final" ] ; then
        cat "$final" >> $M
        cat "$final" >> $G
    else 
        echo -e "$final_propio" >> $M
        echo -e "$final_propio" >> $G
    fi

    }

anadirJavaScript(){
    ## Añadimos el codigo javascript necesario para grande.html

    # CODIGO PARA LEER LAS VARIABLES
    echo "<script language='javascript'>" >> $G
    echo 'presentar_contenido();' >> $G
    echo "</script>" >> $G

    }


while [ -n "$1" ]; do
case $1 in
    -h) help;shift 1;;
    -c) shift 1 ; cabecera="$1" ; shift 1 ;;
    -f) shift 1 ; final="$1" ; shift 1 ;;
    -t) shift 1 ; titulo="$1" ; shift 1 ;;
    -l) shift 1 ; LINEAS="SI" ;;
    -m) shift 1 ; MINIATURAS="SI" ;;
    -html) shift 1 ; HTML="SI" ;;
    -600) shift 1 ; imagen600 $* ;;
    --ruta-galeria) ruta_galeria=$2 ; shift 2 ;;
    --) break;;
    -*) echo "error: sin opciones $1. -h para ayuda";exit 1;;
    *)  break;;
esac
done

if [ -z "$1" ];then
    error "No se ha especificado ninguna opción, -h para ayuda" 1
fi

if [ "$MINIATURAS" = "SI" ] ; then
    miniaturas $*
fi

if [ "$HTML" = "SI" ] ; then
    generarHTML $*
fi

if [ "$LINEAS" = "SI" ] ; then
    lineas $*
fi

echo "Acabat"
exit


