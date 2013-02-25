<?php

/**
 * @file      DataBoundObject.php
 * @brief     Abstracción para manipilación de datos con PDO
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 *   Created  05/05/10
 *  Revision  SVN $Id: DataBoundObject.php 648 2012-09-17 17:26:34Z eduardo $
 * Copyright  Copyright (c) 2010, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */


/**
 * @class DataBoundObject
 * @brief Abstracción para manipilación de datos con PDO
 * @version 0.1
 */

abstract class DataBoundObject {

   protected $ID;
   protected $objPDO;                      ///< PDO
   protected $strTableName;                ///< Nombre de tabla
   protected $arRelationMap;               ///< Array con las relaciones campo tabla->nombre_variable
   protected $blForDeletion;               ///< Para saber si se debe borrar el registro
   protected $blIsLoaded;                  ///< Saber si ya se fue ha buscar a la BD
   protected $arModifiedRelations;         ///< listado de campos modificados

   /**
    * Valores de los campos del registro
    */

   protected $valores;

   /**
    * Nombre de tabla
    */

   abstract protected function DefineTableName();

   /**
    * Relación entre campos de la tabla y variables a utilizar en el dominio
    */

   abstract protected function DefineRelationMap($objPDO);

   /**
    * Constructor
    *
    * @param $objPDO Instancia de PDO
    * @param $id     Identificador de registro, sino es uno nuevo
    */

   public function __construct(PDO $objPDO, $id = NULL) {

      $this->strTableName = $this->DefineTableName();
      $this->arRelationMap = $this->DefineRelationMap($objPDO);
      $this->objPDO = $objPDO;
      $this->blIsLoaded = false;
      if (isset($id)) {
         $this->ID = $id;
      };
      $this->arModifiedRelations = array();
      }

   /**
    * Buscar información en base de datos
    */

   public function Load() {

      if (isset($this->ID)) {

         $strQuery = "SELECT ";
         foreach ($this->arRelationMap as $key => $value) {
            $strQuery .= '`'.$key . "`,";
         }
         $strQuery = substr($strQuery, 0, strlen($strQuery)-1);
         $strQuery .= " FROM " . $this->strTableName . " WHERE
            id = :eid";
         $objStatement = $this->objPDO->prepare($strQuery);
         $objStatement->bindParam(':eid', $this->ID,
            PDO::PARAM_INT);
         $objStatement->execute();
         $arRow = $objStatement->fetch(PDO::FETCH_ASSOC);

         if ( ! $arRow  ) {
            trigger_error('No existe registro ['.$this->ID.'] en ['.$this->strTableName.']'."\nsql: ".$strQuery, E_USER_ERROR);
            return FALSE;
            }

         foreach($arRow as $key => $value) {
            $strMember = $this->arRelationMap[$key];
            $this->valores[$strMember] = $value;
         };
         $this->blIsLoaded = true;
      };
   }

   /**
    * Guardar registro en base de datos
    */

   public function Save() {

      $debug = '';

      if (isset($this->ID)) {

         $strQuery = 'UPDATE ' . $this->strTableName . ' SET ';

         foreach ($this->arRelationMap as $key => $value) {
            //eval('$actualVal = &$this->' . $value . ';');
            $actualVal = ( isset($this->valores[$value]) ) ? $this->valores[$value] : NULL ;
            if (array_key_exists($value, $this->arModifiedRelations)) {
               $strQuery .= $key . " = :$value, ";
            };
         }

         $strQuery = substr($strQuery, 0, strlen($strQuery)-2);
         $strQuery .= ' WHERE id = :eid';
         unset($objStatement);
         $objStatement = $this->objPDO->prepare($strQuery);
         $objStatement->bindValue(':eid', $this->ID, PDO::PARAM_INT);

         foreach ($this->arRelationMap as $key => $value) {

            $actualVal = ( isset($this->valores[$value]) ) ? $this->valores[$value] : NULL ; 

            if (array_key_exists($value, $this->arModifiedRelations)) {
               //if ((is_int($actualVal)) || ($actualVal == NULL)) {
               if ((is_int($actualVal))) {
                  $debug .= "\nNumero: $value : $actualVal";
                  $objStatement->bindValue(':' . $value, $actualVal,
                     PDO::PARAM_INT);
               } else {
                  if ( empty($actualVal) ) $actualVal = '';
                  $debug .= "\nCadena: $value : $actualVal";
                  $objStatement->bindValue(':' . $value, $actualVal,
                     PDO::PARAM_STR);
                  }
               }
            }

         // echo $debug; // DEV
         return $objStatement->execute();

      } else {

         if ( $this->driverPDO() == 'mysql'  ) {
            $this->ID = NULL;
         } else {
            $this->ID = $this->ultimo_identificador() + 1;
            }
         $strValueList = "";
         if ( $this->driverPDO() == 'mysql'  ) {
            $strQuery = 'INSERT INTO ' . $this->strTableName . '( ';
         } else {
            $strQuery = 'INSERT INTO ' . $this->strTableName . '( id,';
            }
         foreach ($this->arRelationMap as $key => $value) {
            //eval('$actualVal = &$this->' . $value . ';');
            $actualVal = ( isset($this->valores[$value]) ) ? $this->valores[$value] : NULL ;
            if (isset($actualVal)) {
               if (array_key_exists($value, $this->arModifiedRelations)) {
                  $strQuery .= $key . ', ';
                  $strValueList .= ":$value, ";
               };
            };
         }

         $strQuery = substr($strQuery, 0, strlen($strQuery) - 2);
         $strValueList = substr($strValueList, 0, strlen($strValueList) - 2);
         if ( $this->driverPDO() == 'mysql'  ) {
            $strQuery .= ") VALUES ( ";
         } else {
            $strQuery .= ") VALUES ( ".$this->ID.",";
            }
         $strQuery .= $strValueList;
         $strQuery .= ")";

         unset($objStatement);
         
         $objStatement = $this->objPDO->prepare($strQuery);

         foreach ($this->arRelationMap as $key => $value) {
            //eval('$actualVal = &$this->' . $value . ';');
            $actualVal = ( isset($this->valores[$value]) ) ? $this->valores[$value] : NULL ;
            if (isset($actualVal)) {   
               if (array_key_exists($value, $this->arModifiedRelations)) {
                  if ((is_int($actualVal)) || ($actualVal == NULL)) {
                     if ( ! $objStatement->bindValue(':' . $value, $actualVal, PDO::PARAM_INT) ) {
                        registrar(__FILE__,__LINE__,"Error añadiendo datos: [$value] [$actualVal]",'ADMIN');
                        }
                  } else {
                     if ( ! $objStatement->bindValue(':' . $value, $actualVal, PDO::PARAM_STR) ) {
                        registrar(__FILE__,__LINE__,"Error añadiendo datos: [$value] [$actualVal]",'ADMIN');
                        }
                  };
               };
            };
         }
         return $objStatement->execute();
      }
   }

   /**
    * Marcar registro para ser borrado 
    */

   public function MarkForDeletion() {
      $this->blForDeletion = true;
      }

   /**
    * Destructor borra los registros marcados para borrar, si queremos que lo haga
    * al instante se le puede llamar directamente
    */

   public function __destruct() {
      if (isset($this->ID)) {   
         if ($this->blForDeletion == true) {
            $strQuery = 'DELETE FROM ' . $this->strTableName . ' WHERE
                         id = :eid';
            $objStatement = $this->objPDO->prepare($strQuery);
            $objStatement->bindValue(':eid', $this->ID, PDO::PARAM_INT);   

            try {
               $objStatement->execute();
            } catch (Exception $ex) {
               registrar(__FILE__,__LINE__,'Error con BD: '.$ex->getMessage(),'ERROR');
               }
         };
      }
      }

   /**
    * Para acceder o modificar los valores de los campos
    *
    * @deprecated
    */

   public function __call($strFunction, $arArguments) {

      $strMethodType = substr($strFunction, 0, 3);
      $strMethodMember = substr($strFunction, 3);
      switch ($strMethodType) {
         case "set":
            return($this->SetAccessor($strMethodMember, $arArguments[0]));
            break;
         case "get":
            return($this->GetAccessor($strMethodMember));   
      };
      return(false);   
      }

   /**
    * Modificar valor de campos
    *
    * @param $strMember Campo a modificar
    * @param $strNewValue Nuevo valor
    */

   function SetAccessor($strMember, $strNewValue) {
      $this->valores[$strMember] = $strNewValue;
      $this->arModifiedRelations[$strMember] = "1";
      }

   /**
    * Acceder al valor del campo
    *
    * @param $strMember Campo al que accedemos
    */

   function GetAccessor($strMember) {
      if ($this->blIsLoaded != true) {
         $this->Load();
         }
      return ( isset($this->valores[$strMember])  ) ? $this->valores[$strMember] : FALSE ;
      }

   /**
    * Devolver último id registrado
    */

   public function ultimo_identificador() {

      switch ( $this->driverPDO() ) {

         case 'sqlite':
            $sql = "select id from ".$this->strTableName." order by id desc limit 1;";
            $result = $this->objPDO->query($sql)->fetch(PDO::FETCH_ASSOC);
            $id = $result['id'];
            break;

         case 'pgsql':
            $id =  $this->objPDO->lastIsertId($this->strTableName . "_id_seq");
            break;

         default:
            $id = $this->objPDO->lastInsertId();
            break;

         }

      return $id;
      }
   
   /**
    * Devolver driver utilizado por PDO
    */

   function driverPDO() {
      return $this->objPDO->getAttribute(constant("PDO::ATTR_DRIVER_NAME"));
      }

   /**
    * Buscar por condición o en caso de no recibirla todos.
    *
    * @param $condicion Condición de busqueda
    * @param $campos    Array con los campos a presentar
    * @param $condicion Condición de busqueda
    *
    * @return Array con resultado
    */

   function find($condicion=NULL, $campos=NULL, $orden=NULL) {

      $retorno = array();

      if ( $campos  ) {
         $seleccion = '';
         for ($x=0; $x<=count($campos)-1; $x++) {
            $seleccion .= " $campos[$x]";
            if ( $x < count($campos)-1  ) $seleccion .= ',';
            }
      } else {
         $seleccion = '*';
         }

      $sql = "Select $seleccion from ".$this->strTableName;

      if ( $condicion ) $sql .= " WHERE $condicion";

      if ( isset($orden)  ) $sql .= " ORDER BY ".$orden;

      $objStatement = $this->objPDO->prepare($sql);
      $objStatement->execute();

      $arAll = $objStatement->fetchAll(PDO::FETCH_ASSOC);

      $conta = 0;
      foreach ( $arAll as $arRow ) {
         foreach($arRow as $key => $value) {
            $retorno[$conta][$key] = $value;
            }
         $conta++;
         }

      return ( empty($retorno) ) ? FALSE : $retorno;

      }

   /**
    * Devolver listao con id y nombre para poder ser utilizado en 
    * select de formulario
    */

   public function listado_para_select() {

      return $this->find(null,array('id', 'nombre'),'id desc');

      }

   }

?>
