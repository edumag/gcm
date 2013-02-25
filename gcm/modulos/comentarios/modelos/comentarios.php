<?php

/**
 * @file      comentarios.php
 * @brief     Modelo para comentarios
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 *   Created  05/05/10
 *  Revision  SVN $Id: usuarios.php 238 2010-05-13 08:25:10Z eduardo $
 * Copyright  Copyright (c) 2010, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

/** DataBoundObject */

require_once(GCM_DIR.'lib/int/databoundobject/lib/DataBoundObject.php');

/**
 * @class Comentarios_dbo.php
 * @brief Modelo para los comentarios de las entradas.
 * @version 0.1
 */

class Comentarios_dbo extends DataBoundObject {

   protected $Url;
   protected $Fecha;
   protected $Nombre;
   protected $Mail;
   protected $Contenido;
   protected $Comentario;

   protected function DefineTableName() {
      global $gcm;
      registrar(__FILE__,__LINE__,'Tabla: '.$gcm->sufijo.'comentarios','AVISO');
      return($gcm->sufijo.'comentarios');
      }

   protected function DefineRelationMap($pdo) {

      return (array(
         "id"=>"ID",
         "url"=>"Url",
         "fecha"=>"Fecha",
         "nombre"=>"Nombre",
         "mail"=>"Mail",
         "contenido"=>"Contenido",
         "comentario"=>"Comentario"
         ));

      }

   }

?>
