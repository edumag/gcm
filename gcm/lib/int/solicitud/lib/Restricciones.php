<?php

require_once("constantes.php");

class Restricciones {

  private $_intConstraintType;
  private $_strConstraintOperand;

  function __construct($intConstraintType, $strConstraintOperand) {
    $this->_intConstraintType = $intConstraintType;
    $this->_strConstraintOperand = $strConstraintOperand;
  }

  function GetConstraintType() {
    return($this->_intConstraintType);
  }

  function GetConstraintOperand() {
    return($this->_strConstraintOperand);
  }
}
?>
