<?php

/**
 * @file aplicar_SEO.php
 * @brief Módulo para facilitar la tarea de SEO
 *
 * @author     Eduardo Magrané eduardo@mamedu.com
 *
 * @internal
 *   Created  23/11/09
 *  Revision  SVN $Id: modulo.php 483 2011-03-30 08:56:20Z eduardo $
 * Copyright  Copyright (c) 2009, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

/**
 * @class Aplicar_SEO
 * @brief Facilitamos la tarea de SEO automatizando en lo posible
 *
 * Recordamos los pasos a seguir para tener un buen posicionamiento en buscadores
 * y que nos indexen correctamente.
 *
 * @version 0.1
 */

class Aplicar_SEO extends Modulos {

   /** Constructor */

   function __construct() {

      parent::__construct();

      }

   function seo($e, $args=FALSE) {

      global $gcm;

      $url = str_replace('seo','',$_SERVER['HTTP_REFERER']);

      $titulo = 'SEO';
      $contenido = '
         <p>
         Pendiente de hacer que este módulo compruebe y ayude a tener lo necesario para un buen
         posicionamineto.
         </p>

         Información sobre SEO en 
         <a href="http://localhost/subversion/gcm/trunk/proyecto/Documentacion/SEO.html">
         http://localhost/subversion/gcm/trunk/proyecto/Documentacion/SEO.html
         </a>

         <br /><br />
         <h2>Herraminetas</h2>
         <br />
         <a href="https://developers.google.com/speed/pagespeed/insights#url='.$url.'&mobile=false" >Test de velocidad</a>
         <br /><br />

         ';

      $gcm->contenido = $contenido;
      $gcm->event->anular('contenido','aplicar_SEO');
      $gcm->titulo = $titulo;
      $gcm->event->anular('titulo','aplicar_SEO');


      }

   }

?>
