<?php

global $gcm;

$editor = array();

foreach ( $gcm->event->eventos as $evento ) {
   foreach ( $evento as $modulo => $accion ) {
      if ( isset($editor[$modulo]) && in_array(key($accion),$editor[$modulo]) ) continue;
      $editor[$modulo][] = key($accion);
      }
   }
