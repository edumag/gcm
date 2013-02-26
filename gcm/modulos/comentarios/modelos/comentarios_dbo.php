<?php

/**
 * @file      comentarios_dbo.php
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

/* DataBoundObject */

require_once(GCM_DIR.'lib/int/databoundobject/lib/Crud.php');

/**
 * @class Comentarios_dbo
 * @brief Modelo para los comentarios de las entradas.
 * @ingroup modulo_comentarios
 */

class Comentarios_dbo extends Crud {

//   protected $Url;
//   protected $Fecha_creacion;
//   protected $Nombre;
//   protected $Mail;
//   protected $Contenido;
//   protected $Comentario;

   function DefineTableName() {
      global $gcm;
      return($gcm->sufijo.'comentarios');
      }

//    protected function DefineRelationMap($pdo) {
// 
//       return (array(
//          "id"=>"ID",
//          "url"=>"Url",
//          "fecha_creacion"=>"Fecha_creacion",
//          "nombre"=>"Nombre",
//          "mail"=>"Mail",
//          "contenido"=>"Contenido",
//          "comentario"=>"Comentario"
//          ));
// 
//       }

   }

?>
