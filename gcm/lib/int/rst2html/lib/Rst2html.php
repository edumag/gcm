<?php

/**
 * @file Rst2html.php
 * @brief Transfomar texto rst a html
 *
 * @category   Gcm
 * @subpackage Rst2html
 * @author     Eduardo Magrané eduardo@mamedu.com
 *
 * @internal
 *   Created  23/11/09
 *  Revision  SVN $Id: modulo.php 483 2011-03-30 08:56:20Z eduardo $
 * Copyright  Copyright (c) 2009, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

/**
 * @class Rst2html
 * @brief rst2html
 *
 * Código extraido de:
 *
 * reStructuredText to HTML
 * version 0.1b (2008-01-31)
 * by Paul Kippes
 *
 * Requires local installation of reStructuredText parser
 * See: http://docutils.sourceforge.net/rst.html
 *
 * Update $rst2html for the appropriate location of rst2html.py
 *
 * @version 0.1
 */

class Rst2html {

   static function convertir($input) {

      $rst2html = '/usr/bin/rst2html';

      if (!is_executable($rst2html)) {
         return "<strong class='error'>" .
            "reStructureText extension: No executable at $rst2html" .
            "</strong>";
         }

      # If pipe errors are reported, enable output to the file.
      # But make certain the file doesn't already exist or else
      # the webserver may not have permission to create it.
      $io_desc = array(
         0 => array('pipe', 'r'),
         1 => array('pipe', 'w'),
               /* 2 => array('file', '/tmp/error-output.txt', 'a') */);

      $res = proc_open($rst2html . " --stylesheet-path='' " .
         "--initial-header-level=2 --no-doc-title " .
         "--no-file-insertion --no-raw",
         $io_desc, $pipes, '/tmp', NULL);

      if (is_resource($res)) {

         fwrite($pipes[0], $input);
         fclose($pipes[0]);

         $html = stream_get_contents($pipes[1]);
         fclose($pipes[1]);

         $html = preg_replace('/.*<body>\n(.*)<\/body>.*/s', '${1}', $html);

      } else {

         $html = "<strong class='error'>" .
            "reStructureText extension: error opening pipe" .
            "</strong>";
         }

      return $html;
      }

   }

?>
