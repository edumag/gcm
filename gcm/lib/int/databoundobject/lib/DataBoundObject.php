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
 *
 * @ingroup crud
 */


/**
 * @class DataBoundObject
 * @brief Abstracción para manipilación de datos con PDO
 *
 * @ingroup crud
 */

abstract class DataBoundObject {

   protected $ID;                          ///< Identificador de registro
   protected $objPDO;                      ///< PDO
   protected $strTableName;                ///< Nombre de tabla
   protected $arRelationMap;               ///< Array con las relaciones campo tabla->nombre_variable
   protected $blForDeletion;               ///< Para saber si se debe borrar el registro
   protected $blIsLoaded;                  ///< Saber si ya se fue ha buscar a la BD
   protected $arModifiedRelations;         ///< listado de campos modificados

   /**
    * Array con los nombres de los campo que hacen de indice de la tabla, por lo general 
    * suele ser id, pero dejamos las puertas abiertas a que sea otro nombre, he incluso que 
    * el indice de la tabla conste de más de un indice, permitiendo gestionar tablas con 
    * más de uno, como en el caso de tablas combinatorias, que se encargan de guardar las 
    * relaciones entre dos tablas diferentes.
    */

   protected $campos_indices = array();

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
    * Comprobar numero de indices de la tablas
    *
    * @see $numero_indices
    */

   function comprobar_indices() {

      $campo_indice = array_search('ID',$this->arRelationMap);

      $this->campos_indices = explode(",",$campo_indice);

      }

   /**
    * Constructor
    *
    * @param $objPDO Instancia de PDO
    * @param $id     Identificador de registro, sino es uno nuevo, el identificador
    *                puede ser de dos campos separados por comas para tablas con un
    *                indice de dos campos.
    */

   public function __construct(PDO $objPDO, $id = NULL) {

      $this->strTableName = $this->DefineTableName();
      $this->arRelationMap = $this->DefineRelationMap($objPDO);
      $this->objPDO = $objPDO;
      $this->blIsLoaded = false;
      if (isset($id) && ! empty($id) ) $this->ID = $id;
      $this->arModifiedRelations = array();
      $this->comprobar_indices();

      }

   /**
    * Buscar información en base de datos
    */

   public function Load() {

      if (isset($this->ID)) {

         $indices = explode(",",$this->ID);

         $strQuery = "SELECT ";
         foreach ($this->arRelationMap as $key => $value) {
            if ( $value == 'ID' ) {
               $strQuery .= $key . ",";
            } else {
               $strQuery .= '`'.$key . "`,";
               }
         }
         $strQuery = substr($strQuery, 0, strlen($strQuery)-1);
         $strQuery .= " FROM " . $this->strTableName . " WHERE ";

         $condicion = "";
         $conta = 0;
         foreach ( $this->campos_indices as $indice ) {

            // Si no hay valor en uno de los indices la condición 
            // afectara a uno de ellos solo.
            if ( isset($indices[$conta]) ) {
               $condicion .= $indice. "=:".$indice." AND ";
               }

            $conta++;
            }

         $condicion = rtrim($condicion,"AND ");

         $strQuery .= $condicion;

         registrar(__FILE__,__LINE__,$strQuery,'DEBUG');
         $objStatement = $this->objPDO->prepare($strQuery);

         $conta = 0;
         foreach ( $this->campos_indices as $indice ) {
            // Si no hay valor en uno de los indices la condición 
            // afectara a uno de ellos solo.
            if ( isset($indices[$conta]) ) {
               $objStatement->bindParam(':'.$indice, $indices[$conta],
                  PDO::PARAM_INT);
               }
            $conta++;
            }

         $objStatement->execute();
         $arRow = $objStatement->fetch(PDO::FETCH_ASSOC);

         if ( ! $arRow  ) {
            $this->ID = FALSE;
            return FALSE;
            }

         foreach($arRow as $key => $value) {
            // Si tenemos más de un indice, la tabla es combiatoria,
            // añadimos los indices al formulario para tener algo
            // que presentar.
            if ( in_array($key,$this->campos_indices) ) {
               $strMember = $key;
               $this->valores[$strMember] = $value;
            } else {
               $strMember = $this->arRelationMap[$key];
               $this->valores[$strMember] = $value;
               }
            }

         $this->blIsLoaded = true;
         return TRUE;
      }
   }

   /**
    * Guardar registro en base de datos
    */

   public function Save() {

      $debug = '';

      $indices = explode(",",$this->ID);

      if (isset($this->ID) && ! empty($this->ID) ) {

         $strQuery = 'UPDATE ' . $this->strTableName . ' SET ';

         $salida = FALSE;
         foreach ($this->arRelationMap as $key => $value) {
            //eval('$actualVal = &$this->' . $value . ';');
            $actualVal = ( isset($this->valores[$value]) ) ? $this->valores[$value] : NULL ;
            if (array_key_exists($value, $this->arModifiedRelations)) {
               $salida .= $key . " = :$value, ";
               };
            }

         // Si es una tabla combinatoria añadir los indices a modificar
         if ( count($this->campos_indices) > 1 ) {
            $conta = 0;
            foreach ( $this->campos_indices as $indice ) {
               if ( isset($indices[$conta]) ) {
                  $salida .= $indice. "=:post_".$indice.", ";
                  }
               $conta++;
               }
            }

         if ( $salida ) $strQuery .= substr($salida, 0, strlen($salida)-2);

         $condicion = " WHERE ";
         $conta = 0;
         foreach ( $this->campos_indices as $indice ) {

            // Si no hay valor en uno de los indices la condición 
            // afectara a uno de ellos solo.
            if ( isset($indices[$conta]) ) {
               $condicion .= $indice. "=:".$indice." AND ";
               }

            $conta++;
            }

         $condicion = rtrim($condicion,"AND ");

         $strQuery .= $condicion;

         unset($objStatement);
         registrar(__FILE__,__LINE__,$strQuery,'DEBUG');
         $objStatement = $this->objPDO->prepare($strQuery);

         $conta = 0;
         foreach ( $this->campos_indices as $indice ) {
            // Si no hay valor en uno de los indices la condición 
            // afectara a uno de ellos solo.
            if ( isset($indices[$conta]) ) {
               $objStatement->bindParam(':'.$indice, $indices[$conta],
                  PDO::PARAM_INT);
               }
            $conta++;
            }

         // Nuevos valores
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

         // Si es una tabla combinatoria añadir los indices a modificar
         if ( count($this->campos_indices) > 1 ) {
            $valores_indices = explode(',',$this->ID);
            $conta = 0;
            foreach ( $this->campos_indices as $indice ) {
               $objStatement->bindValue(':post_' . $indice, $valores_indices[$conta],PDO::PARAM_INT);
               $conta++;
               }
            }

         try {

            $objStatement->execute();

         } catch (PDOException $e) {

            $msg = $e->getMessage();
            registrar(__FILE__,__LINE__,$msg,'ERROR');
            return FALSE;
         }

         return TRUE;

      } else {  // Estamos insertando

         if ( $this->driverPDO() == 'mysql'  ) {
            $this->ID = NULL;
         } else {
            $this->ID = $this->ultimo_identificador() + 1;
            }
         $strValueList = "";
         if ( $this->driverPDO() == 'mysql'  ) {
            $strQuery = 'INSERT INTO ' . $this->strTableName . ' ( ';
         } else {
            $strQuery = 'INSERT INTO ' . $this->strTableName . ' ( '.array_search('ID',$this->arRelationMap).',';
            }

         // Si es una tabla combinatoria
         if ( count($this->campos_indices) > 1 ) {
            $conta = 0;
            foreach ( $this->campos_indices as $indice ) {
               $strQuery .= $indice . ', ';
               $strValueList .= ":post_$indice, ";
               $conta++;
               }
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
         
         registrar(__FILE__,__LINE__,$strQuery,'DEBUG');
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
                  }
               }
            }
         }

         // Si es una tabla combinatoria añadir los indices a modificar
         if ( count($this->campos_indices) > 1 ) {
            $valores_indices = explode(',',$this->valores['id']);
            $conta = 0;
            foreach ( $this->campos_indices as $indice ) {
               $objStatement->bindValue(':post_' . $indice, $valores_indices[$conta], PDO::PARAM_INT);
               $conta++;
               }
            }


         if ( $objStatement->execute() ) {
            $this->ID = $this->ultimo_identificador();
            return TRUE;
         } else {
            return FALSE;
            }
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

      if (isset($this->ID) && ! empty($this->ID) ) {   

         $indices = explode(",",$this->ID);

         if ($this->blForDeletion == true) {

            $strQuery = 'DELETE FROM ' . $this->strTableName . ' WHERE ';

            $condicion = "";
            $conta = 0;
            foreach ( $this->campos_indices as $indice ) {

               // Si no hay valor en uno de los indices la condición 
               // afectara a uno de ellos solo.
               if ( isset($indices[$conta]) ) {
                  $condicion .= $indice. "=:".$indice." AND ";
                  }

               $conta++;
               }

            $condicion = rtrim($condicion,"AND ");
            $strQuery .= $condicion;


            // if ( $this->strTableName == 'tv_rel_autors' ) {echo "<pre>info: " ; print_r($strQuery) ; echo "</pre>"; exit(); } // DEV

            registrar(__FILE__,__LINE__,$strQuery,'DEBUG');
            $objStatement = $this->objPDO->prepare($strQuery);

            $conta = 0;
            foreach ( $this->campos_indices as $indice ) {
               // Si no hay valor en uno de los indices la condición 
               // afectara a uno de ellos solo.
               if ( isset($indices[$conta]) ) {
                  $objStatement->bindParam(':'.$indice, $indices[$conta],
                     PDO::PARAM_INT);
                  }
               $conta++;
               }

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
    * @param $strFunction Nombre de la función
    * @param $arArguments Argumentos
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
      if ( $strMember == 'ID' ) $strMember = 'id';
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
      if ( $strMember == 'ID' ) $strMember = 'id';
      return ( isset($this->valores[$strMember])  ) ? $this->valores[$strMember] : FALSE ;
      }

   /**
    * Nos permite eliminar el valor de un campo, evitando así que al guardar las modificaciones
    * no modifique un campo que deseamos mantener como esta.
    *
    * Útil en el caso por ejemplo de las contraseñas donde en caso de no poner contenido en el
    * formulario implica que no se desea cambiar.
    *
    * @param $strMember Nombre del campo
    */

   function DelAccessor($strMember) {
      if ( $strMember == 'ID' ) $strMember = 'id';
      if ( isset($this->valores[$strMember]) ) unset($this->valores[$strMember]);

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
    * @param $orden     Orden
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

      $sql = "SELECT $seleccion FROM ".$this->strTableName;

      if ( $condicion ) $sql .= " WHERE $condicion";

      if ( isset($orden)  ) $sql .= " ORDER BY ".$orden;

      registrar(__FILE__,__LINE__,$sql,'DEBUG');
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
