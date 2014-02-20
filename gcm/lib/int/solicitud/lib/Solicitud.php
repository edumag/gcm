<?php

/**
 * @file      Solicitud.php
 * @brief     Recoger solicitud
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 *   Created  04/02/11
 *  Revision  SVN $Id: $
 * Copyright  Copyright (c) 2011, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

require_once('constantes.php');
require_once('Restricciones.php');
require_once('RestriccionesFallidas.php');

/**
 * Recoger solicitud venga de GET, POST o Cookies
 *
 */

class Solicitud {

   private $_arGetVars;                                ///< Copia de GET
   private $_arPostVars;                               ///< Copia de POST
   private $_arCookieVars;                             ///< Copia de Cookies
   private $_arRequestVars;                            ///< Copia de REQUEST

   /**
    * Si se redirige de nuevo al usuario a la pagina original mediante el 
    * objeto solicitud como resultado de pasar parámetros que no pasan las
    * pruebas de restricción, esta variable contendra una copia del objeto
    * 'solicitud' suministrado.
    */

   private $_objOriginalRequestObject;

   /**
    * Determina si se ha creado 'Solicitud' como resultado de una redirección
    * tras el fallo de una prueba
    */

   private $_blIsRedirectFollowingConstraintFailure = FALSE;

   /**
    * Si falla la mencioniada prueba de restricción, ¿Se debe redirigir
    * automáticamente a la página origial o a otra url?
    */

   private $_blRedirectOnConstraintFailure;

   /**
    * Si se falla en una prueba de restricción y se ha establecido 
    * _blRedirectOnConstraintFailure a true, esta variable especifica si se 
    * debe desviar el objeto 'Solicitud' a una url determinada, si se deja
    * en blanco se utiliza la url de referencia.
    */

   private $_strConstraintFailureRedirectTargetURL;

   /**
    * Si no se establece _strConstraintFailureRedirectTargetURL, 
    * _blRedirectOnConstraintFailure se estable en true y no se encuentra 
    * disponible ninguna URL de referencia, esta variable especifica la url 
    * a la que se debe redigir.
    */

   private $_strConstraintFailureDefaultRedirectTargetURL;

   /**
    * Matriz de restricciones cuyos índices contienen tres elementos con la 
    * clave en un valor hash: el nombre del parámetro en el que se aplica la 
    * restricción, el método en el que espea que este parametro se pase 
    * (como una constante) y el objeto Restricción que expresa la prueba a 
    * aplicar
    */

   private $_arObjParameterMethodConstraintHash;

   /**
    * Matriz de objetos de fallo de restricción, si ha fallado alguna 
    * retricción, suponoendo que se ha ejecutado las pruebas.
    */

   private $_arObjConstraintFailure;

   /**
    * Booleana que expresa si las pruebas de restricción han pasado las pruebas
    */

   private $_hasRunConstraintTests;

   /**
    * Consultamos REQUEST, GET, POST y COOKIE y rellena las variables miembro.
    *
    * Se comprueba la existencia de una cookie llamada phprqcOriginalRequestObject, 
    * si existe se supone que otro objeto 'Solicitud' se ha pasado como resultado de 
    * una devolución tras los fallos.
    *
    * La cookie tiene un valor de null en esta fase, para evitar un bucle infinito 
    * cuando se crea el objeto 'Solicitud' original, posteriormente se anula su 
    * contenido con la función stripslashes en un nuevo objeto 'Solicitud', que 
    * posteriormente quedara disponoble para su interrogación a traves del método 
    * de acceso GetOriginalRequestObjectFollowingConstraintFailure.
    */

   function __construct($check_for_cookie = true) {

      // Import variables
      global $_REQUEST;
      global $_GET;
      global $_POST;
      global $_COOKIE;
      $this->_arGetVars = $_GET;
      $this->_arPostVars = $_POST;
      $this->_arCookieVars = $_COOKIE;
      $this->_arRequestVars = $_REQUEST;

      if ($check_for_cookie) {

         //if ( isset($this->_arCookieVars["phprqcOriginalRequestObject"]) ) {
         if ( isset($_SESSION['RESPUESTA_ERRONEA']) ) {

            //$cookieVal = $this->_arRequestVars["phprqcOriginalRequestObject"];
            $cookieVal = $_SESSION['RESPUESTA_ERRONEA'];
            unset($_SESSION['RESPUESTA_ERRONEA']);
            $this->_blIsRedirectFollowingConstraintFailure = true;

            if (strlen($cookieVal) > 0) {
               $strResult = setcookie ("phprqcOriginalRequestObject", "",time() - 3600,'/');
               $origObj = unserialize(stripslashes($cookieVal));
               $this->_objOriginalRequestObject = &$origObj;
               $this->_arRequestVars["phprqcOriginalRequestObject"] = "";
               $this->_arGetVars["phprqcOriginalRequestObject"] = "";
               $this->_arPostVars["phprqcOriginalRequestObject"] = "";
            };
            $this->_blIsRedirectOnConstraintFailure  = true;
         } else {
            $this->_blIsRedirectOnConstraintFailure  = false;
         };
      } else {
         $this->_blIsRedirectOnConstraintFailure  = false;
      };
      $this->_arObjParameterMethodConstraintHash = Array();
      $this->_arObjConstraintFailure = Array();
      $this->_blHasRunConstraintTests = false;
      $this->_strConstraintFailureDefaultRedirectTargetURL = $_SERVER["REDIRECT_URL"];
   }

   /**
    * Si la página mostrada se ha cargado automáticamente siguiendo una 
    * solicitud de redirección 302 como resultado de un fallo, este 
    * método devuelve true
    */

   function IsRedirectFollowingConstraintFailure() {
      return($this->_blIsRedirectOnConstraintFailure);
   }

   /**
    * Si la página mostrada se ha cargado autometicamente siguiendo una 
    * solicitud de redirección 302 como resultado de un fallo este metodo 
    * devuelve el objeto 'Solicitud' que existía cuando se llamo a la página, 
    * justo antes de que fallara las pruebas
    */

   function GetOriginalRequestObjectFollowingConstraintFailure() {
      if ($this->_blIsRedirectOnConstraintFailure) {
         return($this->_objOriginalRequestObject);
      };
   }

   /**
    * Al aceptar true o false como parámetro, este método nos permite indicarle el
    * objeto solicitud si debe emitir una solicitud 302 ante el fallo de cualquier
    * prueba de restricción, El destino de esta redirección se establece mediante 
    * los métodos SetConstraintFailureRedirectTargetURL y 
    * SetConstraintFailureDefaultRedirectTargetURL
    *
    */

   function SetRedirectOnConstraintFailure($blTrueOrFalse) {
      $this->_blRedirectOnConstraintFailure  = $blTrueOrFalse;
   }

   /**
    * Establece el destino de una redirección 302
    *
    * Si no estable ninguno,o se utiliza la página de referencia (Normalmente el 
    * destino deseado) o bien, si no está disponible, se utiliza una url predeterminada 
    * (que se establece utilizando el método SetConstraintFailureDefaultRedirectTargetURL)
    */

   function SetConstraintFailureRedirectTargetURL($strURL) {
      $this->_strConstraintFailureRedirectTargetURL = $strURL;
   }

   /**
    * Si no se estable una url de destino con el método anterior se utiliza la página de 
    * referencia, si fuese necesario, como respuesta a un fallo, si no hay ninguna 
    * disponible (Por ejemplo como consecuencia de un usuario que hace una visita desde 
    * un marcador), se utiliza en su lugar el valor URL predeterminado especificado con 
    * este método.)
    */

   function SetConstraintFailureDefaultRedirectTargetURL($strURL) {
      $this->_strConstraintFailureDefaultRedirectTargetURL = $strURL;
   }

   /**
    * Devuelve el valor del parámetro especificado, este valor procede directamente de 
    * REQUEST, por lo que la precedencia de busqueda de variable GET, variable POST y 
    * variables cookie depende de la configuración de php.ini
    */

   function GetParameterValue($strParameter) {
      return($this->_arRequestVars[$strParameter]);
   }

   /** Devuelve un hash de parametros, basicamente una copia directa de REQUEST */

   function GetParameters() {
      return($this->_arRequestVars);
   }

   /** hash de COOKIES */

   function GetCookies() {
      return($this->_arCookieVars);
   }

   /** Hash de POST */

   function GetPostVariables() {
      return($this->_arPostVariables);
   }

   /** Hash de GET */

   function GetGetVariables() {
      return($this->_arGetVariables);
   }

   /**
    * Añade una restricción a esta solicitus. Una restricción se aplica a un solo 
    * parametro y especifica una condición que se tiene que satisfacer para que 
    * dicha restricción se considere coincidente. Este metodo acepta el ombre del 
    * parametro, el método de entrega (ENTRADAS_GET, ENTRADAS_POST o ENTRADAS_COOKIE 
    * como una constante) y el objeto Restricciones como sus parametros. Cuando se 
    * añade una restricción, no se puede eliminar. Se puede aplicar más de una 
    * restricción a un solo parámetro.
    */

   function AddConstraint($strParameter, $intMethod, $objConstraint) {
      $newHash["PARAMETER"] = $strParameter;
      $newHash["METHOD"] = $intMethod;
      $newHash["CONSTRAINT"] = $objConstraint;
      $this->_arObjParameterMethodConstraintHash[] = $newHash;
      }

   /**
    * Para devolver de forma facil el valor de un parametro
    *
    * @param $parametro
    *
    * @return Valor del parametro o FALSE
    */

   function valor_parametro($parametro) {

      if (isset($this->_arCookieVars[$parametro])) {
         return $this->_arCookieVars[$parametro];
      } elseif (isset($this->_arGetVars[$parametro])) {
         return $this->_arGetVars[$parametro];
      } elseif (isset($this->_arPostVars[$parametro])) {
         return $this->_arPostVars[$parametro];
      } else {
         return FALSE;
         }
      }

   /**
    * Prueba cada restricción una a una y toma de cualquier fallo rellenando la 
    * variable miembro GetConstraintFailures. Si _blRedirectOnConstraintFailure 
    * se estable a true, se redirige automáticamente a la URL apropiada, tras 
    * enviar primero una cookie temporal que ses una representación del objeto 
    * 'Solicitud' actual.
    */

   function TestConstraints() {

      $this->_blHasRunConstraintTests = true;
      $anyFail = false;

      for ($i=0; $i<=sizeof($this->_arObjParameterMethodConstraintHash) -1; $i++) {

         $strThisParameter = 
            $this->_arObjParameterMethodConstraintHash[$i] ["PARAMETER"];
         $intThisMethod = $this->_arObjParameterMethodConstraintHash[$i]["METHOD"];
         $objThisConstraint = 
            $this->_arObjParameterMethodConstraintHash[$i] ["CONSTRAINT"];
         $varActualValue = "";
         if ($intThisMethod == ENTRADAS_COOKIE) {
            $varActualValue = $this->_arCookieVars[$strThisParameter];
         };
         if ($intThisMethod == ENTRADAS_GET) {
            $varActualValue = $this->_arGetVars[$strThisParameter];
         };
         if ($intThisMethod == ENTRADAS_POST) {
            $varValorActual = $this->_arPostVars[$strThisParameter];
         };
         $intConstraintType = $objThisConstraint->GetConstraintType();
         $strConstraintOperand = $objThisConstraint->GetConstraintOperand();
         $thisFail = false;

         /** El valor puede ser un array con valores */
         $indices = ( is_array($varValorActual) ) ? count($varValorActual) : 1 ; 

         $conta = 0;

         while ( $conta < $indices ) {

            $indice = ( is_array($varValorActual) ) ? $conta : FALSE ;

            $varActualValue = ( is_array($varValorActual) ) ? $varActualValue = $varValorActual[$conta] : $varActualValue = $varValorActual; 

            $objFailureObject = new RestriccionesFallidas($strThisParameter, $intThisMethod, $objThisConstraint, $indice);

            switch ($intConstraintType) {

            case RT_MAIL:
               if (! filter_var( (string) $varActualValue, FILTER_VALIDATE_EMAIL) ) {
                  $thisFail = true;
                  registrar(__FILE__,__LINE__,'El email no parece valido','ERROR');
                  }
               break;

            case RT_LONG_MIN:
               if (strlen((string)$varActualValue) < (integer) $strConstraintOperand) {
                  $thisFail = true;
                  registrar(__FILE__,__LINE__,$strThisParameter.' su longitud es menor a '.$strConstraintOperand,'ERROR');
                  }
               break;

            case RT_LONG_MAX:
               if (strlen((string)$varActualValue) > (integer) $strConstraintOperand) {
                  $thisFail = true;
                  registrar(__FILE__,__LINE__,$strThisParameter.' su longitud es mayor a '.$strConstraintOperand,'ERROR');
                  }
               break;

            case RT_CARACTERES_PERMITIDOS:
               for ($j=0; $j<=strlen($varActualValue)-1; $j++) {
                  $thisChar = substr($varActualValue, $j, 1);
                  if (strpos($strConstraintOperand, $thisChar) === false) {
                     $thisFail = true;
                     registrar(__FILE__,__LINE__,$strThisParameter.' con caracteres no permitidos','ERROR');
                  };
               };
               break;

            case RT_CARACTERES_NO_PERMITIDOS:
               for ($j=0; $j<=strlen($varActualValue)-1; $j++) {
                  $thisChar = substr($varActualValue, $j, 1);
                  if (!(strpos($strConstraintOperand, $thisChar) === false)) {
                     $thisFail = true;
                     registrar(__FILE__,__LINE__,$strThisParameter.' con caracteres no permitidos','ERROR');
                  };
               };
               break;

            case RT_MENOR_QUE:
               if ($varActualValue >= $strConstraintOperand) {
                  $thisFail = true;
                  registrar(__FILE__,__LINE__,$strThisParameter.' menor a '.$strConstraintOperand,'ERROR');
                  }
               break;

            case RT_MAYOR_QUE:
               if ($varActualValue <= $strConstraintOperand) {
                  $thisFail = true;
                  registrar(__FILE__,__LINE__,$strThisParameter.' mayor a '.$strConstraintOperand,'ERROR');
                  }
               break;

            case RT_IGUAL_QUE:
               if ($varActualValue != $strConstraintOperand) {
                  $thisFail = true;
                  registrar(__FILE__,__LINE__,$strThisParameter.' igual que '.$strConstraintOperand,'ERROR');
                  }
               break;

            case RT_NO_IGUAL:
               if ($varActualValue == $strConstraintOperand) {
                  $thisFail = true;
                  registrar(__FILE__,__LINE__,$strThisParameter.' no es igual a '.$strConstraintOperand,'ERROR');
                  }
               break;

            case RT_PASA_EXPRESION_REGULAR:
               if (!(preg_match($strConstraintOperand, $varActualValue))) {
                  $thisFail = true;
                  registrar(__FILE__,__LINE__,$strThisParameter.' pasa exp '.$strConstraintOperand,'ERROR');
                  }
               break;

            case RT_NO_PASA_EXPRESION_REGULAR:
               if (preg_match($strConstraintOperand, $varActualValue)) {
                  $thisFail = true;
                  registrar(__FILE__,__LINE__,$strThisParameter.' no pasa exp'.$strConstraintOperand,'ERROR');
                  }
               break;

            case RT_NO_ES_NUMERO:
               if ( ! is_numeric($varActualValue) ) {
                  $thisFail = true;
                  registrar(__FILE__,__LINE__,$strThisParameter.' menor a '.$strConstraintOperand,'ERROR');
                  }
               break;

            case RT_REQUERIDO:
               if ( ! isset($varActualValue) || ( $varActualValue !== "0" && empty($varActualValue) ) ) {
                  $thisFail = true;
                  registrar(__FILE__,__LINE__,literal($strThisParameter).' '.literal('es requerido'),'ERROR');
                  }
               break;

            case RT_PASSWORD:
               if ( ! isset($_POST['verificacion']) ) {
                  $thisFail = true;
                  registrar(__FILE__,__LINE__,'Se necesita un campo "verificacion" para poder verificar la contraseña ','ERROR');
                  }
               if ( $varActualValue != $_POST['verificacion'] ) {
                  $thisFail = true;
                  registrar(__FILE__,__LINE__,$strThisParameter.' contraseña diferente a verificación '.$strConstraintOperand,'ERROR');
                  registrar(__FILE__,__LINE__,$varActualValue.' != '.$_REQUEST['verificacion'],'ADMIN');
                  }
               break;

            };

         if ($thisFail) {
            $anyFail = true;
            $this->_arObjConstraintFailure[] = $objFailureObject;
         };

         $conta++;
         } // Acaba while 

      };

      if ($anyFail) {

         if ($this->_blRedirectOnConstraintFailure) {

            if ($this->_strConstraintFailureRedirectTargetURL) {
               $targetURL = $this->_strConstraintFailureRedirectTargetURL;
            };
            if (!$targetURL) {
               if ($this->_strConstraintFailureDefaultRedirectTargetURL) {
                  $targetURL = $this->_strConstraintFailureDefaultRedirectTargetURL;
               }
            };

            if ($targetURL) {
               $objToSerialize = $this;
               $strSerialization = serialize($objToSerialize);
               //setcookie ("phprqcOriginalRequestObject", $strSerialization, time() + 3600,'/');
               $_SESSION['RESPUESTA_ERRONEA'] = $strSerialization;
               registrar(__FILE__,__LINE__,"Recargamos página por errores en formulario");
               header("Location: $targetURL");
               exit(0);
            };
         };
      };
      return(!($anyFail));  // Returns TRUE if all tests passed, otherwise returns
      //  FALSE
   }

   /**
    * Cuando una llamada a TestConstraints() indica que han fallado este método 
    * devolvera una matriz de objetos RestriccionesFallidas que representan todos
    * estos fallos, a este método se tiene que acceder desde el objeto 'Solicitud' 
    * original, al que se ha accedido utilizando 
    * GetOriginalRequestObjectFollowingConstraintFailure
    */

   function GetConstraintFailures() {
      if (!$this->_blHasRunConstraintTests) {
         $this->TestConstraints();
      };
      return($this->_arObjConstraintFailure);
   }
}

?>
