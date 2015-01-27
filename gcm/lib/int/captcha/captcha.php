<?php

/** 
 * @file captcha.php
 * @brief Formas de evitar el spam
 * @defgroup captcha Captcha para evitar spam
 * @ingroup librerias
 * Fem un camp de formulari amb un nom aleatori amb el valor de la data actual
 * per despres comparar amb el temps de resposte i sapiguer si es molt curt, possiblement
 * es tracti de una màquina
 * @{
 */

/** Afegir camp de captcha al formulari
 *
 * @param $nom Nom del captcha per poger diferencia entre captchas
 * @param $retorn true/false Ens Dona la posibilitat de retorna el text per incloura,
 *        per defecta lo imprimeix.        
 *
 */

function anyadirCaptcha($nom='default', $retorn='false') {

   $NC=$nom."_cf_".caracter_aleatorio();
   $_SESSION[$nom.'_cf_'] = $NC;

   if ( $retorn == 'false' ) {
      echo '<input type="hidden" name="'.$NC.'" value="'.time().'" />';
   } else {
      return '<input type="hidden" name="'.$NC.'" value="'.time().'" />';
   }

   }

/** Verifiquem
 * 
 * @param seg Segons minims per defecta 10
 * @param $nom Nom del captcha per poger diferencia entre captchas
 *
 * @return Si pasa bien el captcha TRUE si no FALSE.
 */

function verificarCaptcha($seg=10, $nom='default'){

   if ( ! isset($_SESSION[$nom.'_cf_'])  ) return FALSE;
   if ( ! isset($_POST[$_SESSION[$nom.'_cf_']]) )  return FALSE;

   $actual = time();
   $form = $_POST[$_SESSION[$nom.'_cf_']];
   $diferencia = $actual - $form ;

   if ( $diferencia < $seg || empty($form) ) {
      return FALSE;
   } else {
      return TRUE;
      }

   }

/**
 * CAptcha amb imatge
 */

function anyadirCaptchaImagen() {

   global $_ADMIN ;

   $captcha_texto = "";

   for ($i = 1; $i <= 4; $i++) {
         $captcha_texto .= caracter_aleatorio();
   }

   $_SESSION["captcha_texto_session"] = $captcha_texto;

   ?>

   <img width='110px' height='30px' src="<?php echo "../lib/imgCaptcha.php?".SID ; ?>" /><br>
<input name="texto_ingresado" type="text" id="texto_ingresado" style='width: 55px' size="6" maxlength="4" />
     <?php

}

/**
 * Generem caracter aleatoti
 */

function caracter_aleatorio() {

   mt_srand((double)microtime()*1000000);

   $valor_aleatorio = mt_rand(1,3);

   switch ($valor_aleatorio) {
    case 1:
        $valor_aleatorio = mt_rand(97, 122); 
        break;
    case 2:
        $valor_aleatorio = mt_rand(48, 57);
        break;
    case 3:
        $valor_aleatorio = mt_rand(65, 90);
        break;
   }

   return chr($valor_aleatorio);
}

/** Comprovem captcha */

function verificarCaptchaImagen(){


   $texto_ingresado = strtolower($_POST["texto_ingresado"]);

   $captcha_texto = strtolower($_SESSION["captcha_texto_session"]);

   if ($texto_ingresado != $captcha_texto) {

      return FALSE ;
      /*
      echo "El texto ingresado no coincide. Por favor intentelo de nuevo!";
      exit();
      */

   }



   unset($_SESSION["captcha_texto_session"]);
   return TRUE;

}

/** @} */
?>
