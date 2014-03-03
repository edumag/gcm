<?php

/**
 * @file DescripcionesGalerias.php
 * @brief Descripcions d'imatges
 */

/**
 * @class DescripcionesGalerias
 * @brief Descripcions d'imatges
 *
 * Guardar en base de dades els títols i subtítols de les imatges.
 *
 * Perquè funcioni hem de tenir una taula amb la següent estructura.
 *
 * @code
 * CREATE TABLE IF NOT EXISTS `desc_galeria_noticies` (
 *   `id` int(11) NOT NULL AUTO_INCREMENT,
 *   `galeria_id` int(11) NOT NULL,
 *   `nom_imatge` varchar(150) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
 *   `titol` varchar(50) CHARACTER SET utf8 COLLATE utf8_spanish_ci NOT NULL,
 *   `subtitol` varchar(200) CHARACTER SET utf8 COLLATE utf8_spanish_ci DEFAULT NULL,
 *   PRIMARY KEY (`id`)
 * );
 * @endcode
 *
 * Els noms dels camps són configurables
 */

class DescripcionesGalerias {

   private $galeria_id;            ///< Identificador de la galeria
   private $pdo;                   ///< Instancia de PDO
   private $taula;                 ///< Taula on es troba la informació
   private $cam_galeria_id;        ///< Nom del camp que porta l'identificador de l'imatge
   private $camp_titol;            ///< Nom del camp que porta el titol
   private $camp_subtitol;         ///< Nom del camp que porta el subtitol
   private $sql;                   ///< sql 

   public $descripcions;          ///< Array amb les descripcions de les imatges

   /**
    * Array amb configuració
    * 
    * Posibles configuracións:
    *
    * @code
    * array('cam_galeria_id' =>      'noticiaId',
    *       'camp_titol =>    'titol' ,
    *       'camp_subtitol => 'subtitol' ,
    *       );
    * @endcode
    *
    * Per defecta:
    *
    * - cam_galeria_id: galeria_id
    * - camp_titol: titol
    * - camp_subtitol: subtitol
    *
    * @param $taula            Taula de la base de dades
    * @param $galeria_id       Identificador de galeria
    * @param $configuracio     Array amb la configuracio
    * @param $pdo              Instancia de PDO, si no per defecta mysql
    */

   private $configuracio;

   function __construct($taula, $galeria_id = FALSE, $configuracio = FALSE, $pdo = FALSE) {

      $this->galeria_id = $galeria_id;
      $this->pdo = $pdo;
      $this->taula = $taula;


      $this->cam_galeria_id = "galeria_id"       ;
      $this->camp_titol     = "titol"     ;
      $this->camp_subtitol  = "subtitol"  ;

      $this->descripcions = FALSE;

      if ( $configuracio ) {
         foreach ( $configuracio as $conf_atr => $val_atr ) {
            $this->$conf_atr = $val_atr;
            }
         }

      }

   /**
    * Recupera informació
    */

   function load() {

      if ( $this->descripcions  ) return $this->descripcions;


      // Si tenim descripcións a POST les agafem
      if ( isset($_POST['imatgeGaleria_titol']) ) {
         $this->descripcions = array();
         foreach ( $_POST['imatgeGaleria'] as $key => $nom_imatge ) {
            if ( $_POST['imatgeGaleria_titol'][$key] )    $this->descripcions[$nom_imatge]['titol']    = $_POST['imatgeGaleria_titol'][$key];
            if ( $_POST['imatgeGaleria_subtitol'][$key] ) $this->descripcions[$nom_imatge]['subtitol'] = $_POST['imatgeGaleria_subtitol'][$key];
            }
         return;
         }

      if ( ! $this->galeria_id ) return FALSE;

      $sql = "SELECT id, nom_imatge, ".$this->camp_titol.", ".$this->camp_subtitol.
         " FROM ".$this->taula.
         " WHERE ".$this->cam_galeria_id."=".$this->galeria_id;

      if ( $pdo ) {

         try {

            $resultado=$this->pdo->prepare($sql);
            $resultado->execute();
            $arAll = $resultado->fetchAll(PDO::FETCH_ASSOC);

            } catch (Exception $ex) {
               registrar(__FILE__,__LINE__,"Error con la base de datos",'ERROR');
               registrar(__FILE__,__LINE__,"SQL: ".$sql."\n".$ex->getMessage(),'ADMIN');
               return FALSE;
               }

      } else {

         $resultado = mysql_query($sql) or die('Error amb '.$sql."\n\n".mysql_error());

         if (! $resultado || mysql_num_rows($resultado) < 1 ) return FALSE;

         while ( $arAll = mysql_fetch_array($resultado) ) {

            if ( !empty($arAll[2]) ) $this->descripcions[$arAll[1]]['titol']    = $arAll[2];
            if ( !empty($arAll[3]) ) $this->descripcions[$arAll[1]]['subtitol'] = $arAll[3];
            }

         }

      }

   /** 
    * guardar 
    *
    * @todo Buscar si hay anteriores antes de añadir más, si los hay borrarlos
    *
    * @param $id Identificador de registre
    */

   function guardar($id) {

      $this->load();

      $this->esborrar($id);

      if ( ! $this->descripcions ) return ;

      foreach ( $this->descripcions as $nom_imatge => $valors ) {

         $sql = "INSERT INTO ".$this->taula." (  ".$this->cam_galeria_id.", nom_imatge, ".$this->camp_titol.", ".$this->camp_subtitol." ) 
            VALUES ( '".$id."', '".$nom_imatge."' , '".$valors['titol']."', '".$valors['subtitol']."')";

         if ( $this->pdo ) {

            try {

               $resultado=$this->pdo->prepare($sql);
               $resultado->execute();

               } catch (Exception $ex) {
                  registrar(__FILE__,__LINE__,"Error con la base de datos",'ERROR');
                  registrar(__FILE__,__LINE__,"SQL: ".$sql."\n".$ex->getMessage(),'ADMIN');
                  return FALSE;
                  }

         } else {

            $resultado = mysql_query($sql) or die('Error amb '.$sql."\n\n".mysql_error());

            }

         }

      }

   /** 
    * esborrar
    *
    * @param $id Identificador de registre
    */

   function esborrar($id) {

      $sql_delete = "DELETE FROM ".$this->taula." WHERE ".$this->cam_galeria_id."=".$id;
      if ( $this->pdo ) {
         $this->pdo->query($sql_delete);
      } else {
         mysql_query($sql_delete);
         }

      }

   }

?>
