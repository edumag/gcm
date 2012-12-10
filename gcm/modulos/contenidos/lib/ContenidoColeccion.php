<?php

/**
 * @file
 * Colección para contenido
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 *   Created  23/11/09
 *  Revision  SVN $Id: ContenidoColeccion.php 469 2011-02-04 07:56:07Z eduardo $
 * Copyright  Copyright (c) 2009, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

if ( ! defined(GCM_DIR) ) define('GCM_DIR',dirname(__FILE__).'/../../../');

/**
 * @class Colección para Contenido
 * @version 0.1
 */

class ContenidoColeccion extends Modulos implements Iterator {

   private $elementos = NULL ;        ///< Contenidos

   function __construct() {

      $this->elementos = array();

      parent::__construct();

      }

   public function addItem($i){
      $this->elementos[] = $i;
      }

   public function removeItem($i){
      unset($this->elementos[array_search($i, $this->elementos)]);
      }

   public function rewind() {
       reset($this->elementos);
      }

   public function current() {
       return current($this->elementos);
      }

   public function key() {
       return key($this->elementos);
      }

   public function next() {
       return next($this->elementos);
      }

   public function valid() {
       return $this->current() !== false;
      }

   }

?>
