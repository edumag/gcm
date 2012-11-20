<?php

/**
 * @file      GcmPDO.php
 * @brief     Extensión para PDO
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 *   Created  14/05/10
 *  Revision  SVN $Id: PaginarPDO.php 278 2010-07-13 12:24:14Z eduardo $
 * Copyright  Copyright (c) 2010, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */


/**
 * @class GcmPDO
 * @brief Extensión para PDO
 * @version 0.1
 */

class GcmPDO {

   protected $resultado;                    ///< Recorset con el resultado
   protected $sql;                          ///< sql generado
   protected $pdo;                          ///< Instancia de PDO
   protected $num_total_registros;          ///< Número total de registro

   function __construct (PDO $pdo, $sql) {

      $this->num_total_registros = 0;
      $this->pdo = $pdo;
      $this->sql = $sql;

      $consulta_count = "SELECT COUNT(*) FROM ($this->sql) as conta";

      /* Mirar número de resultados */

      if ( ! $result = $this->pdo->prepare($consulta_count) ) {
         $error = $result->errorInfo();
         registrar(__FILE__,__LINE__,"ERROR ".$error[2]."\nsql:\n".$consulta_count,'ERROR');
         return FALSE;
         }

      if ( ! $result->execute()  ) {
         $error = $result->errorInfo();
         registrar(__FILE__,__LINE__,"ERROR ".$error[2]."\nsql:\n".$consulta_count,'ERROR');
         return FALSE;
         }

      $filas = $result->fetch();

      foreach($filas as $key => $value) {
         $this->num_total_registros = $value;
         }

      }

   /**
    * Devolver el numero total de registros
    */

   function numero_registros() {
      return $this->num_total_registros;
      }

   /**
    * Validar que hemos obtenido resultados
    */

   function validar() {

      if ( $this->num_total_registros > 0 ) {
         return TRUE;
      } else {
         return FALSE;
         }
      }

   /**
    * Devolver recordset con el resultado
    */

   function resultado() {

      if ( $this->resultado  ) return $this->resultado;

      try {

         $this->resultado=$this->pdo->prepare($this->sql);
         $this->resultado->execute();

         } catch (Exception $ex) {
            registrar(__FILE__,__LINE__,"Error con la base de datos",'ERROR');
            registrar(__FILE__,__LINE__,"SQL: ".$this->sql."\n".$ex->getMessage(),'ADMIN');
            return FALSE;
            }

      return $this->resultado;

      }

   /**
    * Devolver resultado como array
    */

   function to_array() {

      if ( ! $this->resultado  ) $this->resultado();

      $retorno = array();

      $arAll = $this->resultado->fetchAll(PDO::FETCH_ASSOC);

      $conta = 0;
      foreach ( $arAll as $arRow ) {
         foreach($arRow as $key => $value) {
            $retorno[$conta][$key] = $value;
            }
         $conta++;
         }

      return $retorno;

      }

   }
?>
