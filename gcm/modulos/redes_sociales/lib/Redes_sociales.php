<?php

/**
 * @file Redes_sociales.php
 * @brief
 *
 * @ingroup modulo_redes_sociales
 * @author    Eduardo Magrané 
 *
 * @internal
 * Copyright  Copyright (c) 2011, Eduardo Magrané
 *
 * This source code is released for free distribution under the terms of the
 * GNU General Public License as published by the Free Software Foundation.
 */

/**
 * @class Redes_sociales
 * @brief
 */

class Redes_sociales extends Modulos {

  public $config = array();

  protected $metatags = array();

  /**
    Para conocer si ya hemos añadido el contenido genérico de la pagina
   */

  static $contenido_general_incluido = FALSE;

  /**
    Para conocer si ya hemos añadido los metatags
   */

  static $metatags_incluido = FALSE;

   /** Constructor */

  function __construct() {

    global $gcm;

    parent::__construct();

    $this->config['usuario_facebook']    = 
      $gcm->config('redes_sociales','usuario_facebook');
    $this->config['usuario_twitter']     =
      $gcm->config('redes_sociales','usuario_twitter');
    $this->config['usuario_tripadvisor'] =
      $gcm->config('redes_sociales','usuario_tripadvisor');
    $this->config['tripadvisor_enlace_perfil'] =
      $gcm->config('redes_sociales','tripadvisor_enlace_perfil');
    $this->config['tripadvisor_enlace_compartir'] =
      $gcm->config('redes_sociales','tripadvisor_enlace_compartir');

    $this->config['color'] =
      $gcm->config('redes_sociales','color');
  }

   function botones($e, $args=FALSE) {
      
      global $gcm;

      $usuario_facebook          = $this->config['usuario_facebook'];
      $usuario_twitter           = $this->config['usuario_twitter'];
      $tripadvisor_enlace_perfil = $this->config['tripadvisor_enlace_perfil'];

      include ($gcm->event->instancias['temas']->ruta('redes_sociales','html','redes_sociales.phtml'));
      }

  /**
   * Insertar boton de facebook
   *
   * La forma de llamar este metodo desde otro módulo sería:
   *
   * <code>
   * <?php echo $gcm->event->lanzar_accion_modulo(
   *   'redes_sociales','insert_button','insertar_boton', 
   *   array('color' => 166)); ?>
   * </code>
   *
   * Si se esta administrando se mostrará el botón de "compartir" de facebook
   * sino solo el de "me gusta".
   * Personalizar color del botón de facebook
   * http://members.chello.nl/~sgm.jansen/facebook-button-colorizer/
   *
   * @param $e    Evento
   * @param $args Array con parametros:
   *     url: Url del contenido
   *     color: Color personalizado, en caso de no llegar se coge de la 
   *       configuración
   */

   function insert_button($e, $args = FALSE) {

     global $gcm;

     $permisos = ( permiso('editar','contenidos') ) ? 'true' : 'false';

     $url = $args['url'];
     $color = ( isset($args['color']) ) ? $args['color'] : FALSE ;
     $title = ( isset($args['title']) ) ? $args['title'] : $url ;
     $tripadvisor_enlace_perfil = $this->config['tripadvisor_enlace_perfil'];
     $tripadvisor_enlace_compartir = $this->config['tripadvisor_enlace_compartir'];
     $pretitle = ( isset($args['pretitle']) ) ? $args['pretitle'] : FALSE ;
     $posttitle = ( isset($args['posttitle']) ) ? $args['posttitle'] : FALSE ;

     if ( ! $color ) { $color = $this->config['color']; }

      ?>
      <span class="botones_redes_sociales">
      <?php

     // Botón de facebook

     if ( ! $this->contenido_general_incluido ) {
       $this->contenido_general_incluido = TRUE;

       $idioma = conversion_idioma(Router::$i);
      ?>
      <div id="fb-root"></div>
      <script type="text/javascript">
      (function() {
      var element = document.createElement('script');
      element.type = "text/javascript";
      element.async = true;
      element.id = "facebook-jssdk"
      element.src = "//connect.facebook.net/<?php echo $idioma ?>/all.js#xfbml=1";
      var s = document.getElementsByTagName('script')[0];
      s.parentNode.insertBefore(element, s);
      })();
      </script>
      <?php

     }

      ?>
        <div class="fb-like" 
          data-href="<?php echo $url ?>" 
          data-layout="button" 
          data-action="like" 
          data-show-faces="true" 
          data-share="<?php echo $permisos ?>" 
          data-colorscheme="dark">
        </div>
      <?php
   
     if ( $color ) {  // No se ve bien lo quitamos
        ?>
        <!-- Personalizamos colores del botón de facebook -->
        <svg height="0" width="0">
          <filter id="fb-filter">
            <feColorMatrix type="hueRotate" values="<?php echo $color ?>"/>
          </filter>
        </svg>
        <style>
          /* .fb-like, .fb-send, .fb-share-button { */
          .fb-like {
            -webkit-filter: url(#fb-filter); 
            filter: url(#fb-filter);
            z-index: 100;
          }
        </style>
        <?php 
      } 

     // Botón de twitter
      ?>
      <a href="https://twitter.com/share" class="twitter-share-button" data-url="<?php echo $url ?>" data-text="<?php echo $pretitle.$title.$posttitle ?>" data-lang="<?php echo Router::$i ?>" data-count="none">Twittear</a>
      <script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?'http':'https';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+'://platform.twitter.com/widgets.js';fjs.parentNode.insertBefore(js,fjs);}}(document, 'script', 'twitter-wjs');</script>
      <?php

     // Botón de tripadvisor
     if ( $tripadvisor_enlace_compartir ) {
        ?>
        <a class="a_tripadvisor" target="_blank" href="https://www.tripadvisor.co.uk/UserReviewEdit?args=d5218514">
           <img class="img_tripadvisor" width="24" height="24"  alt="pic" src="<?php echo Router::$base.$gcm->event->instancias['temas']->ruta('redes_sociales','iconos','tripadvisor2.png') ?>" />
        </a>
        <?php
     }

      ?>
      </span>
      <?php

    }

   function insert_metatags($e, $args=FALSE) {

      global $gcm;

      $this->metatags = array_merge($this->metatags, $args);
   
    }

  /**
   * Insertar metatags para redes socilaes
   *
   * Ejemplos de https://developers.facebook.com/docs/opengraph/using-objects
   *
   * <code>
   * <meta property="fb:app_id"          content="1234567890" /> 
   * <meta property="og:type"            content="social-cookbook:recipe" /> 
   * <meta property="og:url"             content="http://samples.ogp.me/136756249803614" /> 
   * <meta property="og:title"           content="Chocolate Pecan Pie" /> 
   * <meta property="og:image"           content="https://fbcdn-dragon-a.akamaihd.net/hphotos-ak-xpa1/t39.2178-6/851565_496755187057665_544240989_n.jpg" /> 
   * <meta property="cookbook:author"    content="http://samples.ogp.me/390580850990722" />
   * </code>
   */

   function presentar_heads_dinamicos($e, $args = FALSE) {

     global $gcm;

     if ( ! $this->metatags_incluido ) {
       $this->metatags_incluido = TRUE;
       echo "\n".'<meta property="og:type" content="website" />';
       echo "\n".'<meta property="fb:admins" content="'.$this->config['usuario_facebook'].'" />';

        foreach ( $this->metatags as $name => $valor ) {

           echo "\n".'<meta property="og:'.$name.'" content="'.$valor.'" />';

           }
       }
    }

   }

?>
