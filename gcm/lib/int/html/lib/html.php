<?php

/**
 * @file html.php
 * @brief Clase estatica para generar html
 *
 * @author Eduardo Magrané
 * @version 0.1
 */


class HTML {

   static function esc_atr($text) {
      return htmlspecialchars($text, ENT_QUOTES);
      }

   /**
    * Campo de texto
    *
    * Si el tamaño máximo del texto permitido no supera los 150 caracteres
    * se presenta como un imput text si lo supera se presenta como textarea
    *
    * @param $name Nombre del campo
    * @param $atr Atributos para el input o textarea
    */

   static function form_text($name, $atr=FALSE) {

      $maxlength = ( isset($atr['maxlength']) ) ? $atr['maxlength'] : FALSE ;
      $size = ( isset($atr['size']) ) ? $atr['size'] : FALSE ;

      $textarea = ( $maxlength && $maxlength > 150 ) ? TRUE : FALSE ;

      $salida = '';

      if ( $textarea ) { // textarea
         $salida .= '<textarea style="width: 100%; height: 400px"';
      } else {                                // input text
         $salida .= '<input type="text" ';
         }

      $salida .= ' name="'.$name.'"';
      $salida .= ' id="'.$name.'"';

      // Atributos

      foreach ( $atr as $atributo => $valor ) {

         if ( $atributo == 'required' ) {
            $salida .= ' required';
         } elseif ( $atributo == 'valor' && ! $textarea ) {
            $salida .= ' value="'.$valor.'"';
         } else {
            $salida .= ' '.$atributo.'="'.$valor.'"'; 
            }

         }

      if ( $textarea ) { // textarea

         $salida .= '>';

         if ( isset($atr['valor']) ) $salida .=  self::esc_atr($atr['valor']) ;

         $salida .= '</textarea>';

      } else {                                // input text

         $salida .= '>';
      
         }

      return $salida;

      }

   }



?>
