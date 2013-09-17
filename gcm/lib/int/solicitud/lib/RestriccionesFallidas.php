<?php

class RestriccionesFallidas {

   private $_strParameterName;
   private $_intVerbMethod;
   private $_objFailedConstraintObject;
   private $_indice = FALSE;               // En caso de un array indicamos el indice, sino FALSE

   function __construct($strParameterName, $intVerbMethod,  
      $objFailedConstraintObject, $indice = FALSE) {
         $this->_strParameterName = $strParameterName;
         $this->_intVerbMethod = $intVerbMethod;
         $this->_objFailedConstraintObject = $objFailedConstraintObject;
         $this->_indice = $indice;
      }

   function GetParameterName() {
      return($this->_strParameterName);
   }

   function GetVerbMethod() {
      return($this->_intVerbMethod);
   }

   function GetFailedConstraintObject() {
      return($this->_objFailedConstraintObject);
   }
}
?>
