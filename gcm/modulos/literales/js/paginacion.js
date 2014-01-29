/**
 * @file      paginacion.js
 * @brief     Javascript para paginar la lista
 *
 * @author    Eduardo Magrané 
 *
 * @internal
 * Copyright  Copyright (c) 2010, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

<?php include(GCM_DIR.'lib/ext/pajinate/jquery.pajinate.js'); ?>

$(document).ready(function(){
   $('.GcmConfigCajaForm').pajinate({
      nav_label_first : '<<',
      nav_label_last : '>>',
      nav_label_prev : '<',
      nav_label_next : '>'
   });
});	

