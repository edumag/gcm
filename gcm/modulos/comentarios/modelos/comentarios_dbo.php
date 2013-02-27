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

/* añadimos modelo de usuarios ya que estan relacionados con comentarios */

require_once(GCM_DIR.'modulos/admin/modelos/usuarios.php');

/**
 * @class Comentarios_dbo
 * @brief Modelo para los comentarios de las entradas.
 * @ingroup modulo_comentarios
 */

class Comentarios_dbo extends Crud {

   function __construct(PDO $objPdo, $id = NULL) {

      global $gcm;

      parent::__construct($objPdo, $id);

      $this->url_ajax = '&formato=ajax';

      $this->sql_listado = 'SELECT c.id, c.fecha_creacion `fecha creación`, 
         c.url, c.contenido , 
         c.nombre, c.mail, c.comentario  
         FROM '.$gcm->sufijo.'comentarios c';

      $this->opciones_array2table = array(
         'op' => array (
            'ocultar_id'=>TRUE
            , 'eliminar'=>'eliminar'
            , 'fila_unica'=>'comentario'
            , 'enlaces'=> array(
               'url' => array(
                  'campo_enlazado'=>'contenido'
                  ,'titulo_columna'=>'Contenido'
                  ,'base_url'=>Router::$base
                  )
               )
            )
         );

      }

   function DefineTableName() {
      global $gcm;
      return($gcm->sufijo.'comentarios');
      }


   }

?>
