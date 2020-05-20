<?php

/**
 * class db.class.php
 * Pour l'instancier : $objet = new db();

  ------------------------------------------------------
  METHODES
  ------------------------------------------------------

  Syntaxe :
  lafonctiopn($sql,$datas) -> $sql : string contenant la requête à executer
  -> $datas : variables (dans un array associatif) à passer à la requête

  Liste des méthodes :
  db_All() -> retourne un Array contenant TOUTES les lignes du résultat de la requête
  db_One() -> retourne un Array contenant LA PREMIERRE ligne du résultat de la requête
  db_Insert -> retourne l' ID nouvellement créé par une requête INSERT
  db_Exec() -> Pour les requêtes DELETE / UPDATE : retourne le nombre de lignes impactées


 */
class db {

  private $bdd = null;
  private $prepare = NULL;
  private $rowCount = 0;
  private $res = NULL;
  private $host = "localhost";
  private $user = "";
  private $pwd = "";
  private $dbname = "";
  private $port = "3306";
  private $charset = "utf8";

  /**
    Constructeur
   */
  function __construct ( $database = "dev" ) {
    if ( !empty ( $database ) ) {
      $jsonBDD = json_decode ( file_get_contents ( __DIR__ . "/../config/bdd.json" ) );
      $jsonDB = $jsonBDD->$database;
      $this->host = $jsonDB->host;
      $this->user = $jsonDB->username;
      $this->pwd = $jsonDB->password;
      $this->dbname = $jsonDB->dbname;
      $this->port = !empty ( $jsonDB->port ) ? $jsonDB->port : 3306;
      $this->charset = !empty ( $jsonDB->charset ) ? $jsonDB->charset : "utf8";
      // print_r($jsonDB);
      $this->cnx ();
    }
  }

  /**
   * connexion à la BDD
   */
  private function cnx () {
    $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname . ';charset=' . $this->charset . ';port=' . $this->port;
    try {
      $bdd = new PDO ( $dsn, $this->user, $this->pwd );
      // Activation des erreurs PDO
      $bdd->setAttribute ( PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION );
      // mode de fetch par défaut : FETCH_ASSOC / FETCH_OBJ / FETCH_BOTH
      $bdd->setAttribute ( PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC );
    } catch ( PDOException $e ) {
      echo "<br>dsn : $dsn";
      die ( 'Erreur : ' . $e->getMessage () );
    }
    $this->bdd = $bdd;
  }

  /**
   * Execute une requête SQL
   */
  private function dbQuery ( $sql, $datas = NULL ) {
    try {
      $this->prepare = $this->bdd->prepare ( $sql );
      $this->res = $this->prepare->execute ( $datas );
      $this->rowCount = $this->prepare->rowCount ();
    } catch ( Exception $e ) {
      // en cas d'erreur :
      echo "<br><b>Erreur ! " . $e->getMessage () . "</b>" . PHP_EOL;
      echo "<pre> La requete :" . $sql . "</pre>" . PHP_EOL;
      echo " <pre>Les datas : " . PHP_EOL;
      print_r ( $datas );
      echo "</pre>" . PHP_EOL;
    }
  }

  public function db_All ( $sql, $datas = NULL ) {
    $this->dbQuery ( $sql, $datas );
    return $this->prepare->fetchAll ();
  }

  public function db_One ( $sql, $datas = NULL ) {

    try {
      $this->dbQuery ( $sql, $datas );
      return $this->prepare->fetch ();
    } catch ( Exception $e ) {
      echo "Erreur " . $e->getMessage ();
      echo "<br> SQL :" . $sql;
      echo "<br> datas :" . print_r ( $datas, true );
    }
  }

  public function db_Insert ( $sql, $datas = NULL, $returnId = TRUE ) {
    $this->dbQuery ( $sql, $datas );
    return $returnId ? $this->bdd->lastInsertId () : $this->res;
  }

  public function db_Exec ( $sql, $datas = NULL ) {
    $this->dbQuery ( $sql, $datas );
    return array('$this->tbl' => $this->res, 'rowCount' => $this->rowCount);
  }

//-----------------------------------------------------------------------//
// METHODES
//-----------------------------------------------------------------------//

  public function showTables () {
    $sql = "show tables;";
    return $this->db_All ( $sql );
  }

  public function showColumns ( $table_name ) {
    $sql = "SHOW COLUMNS FROM `$table_name`";
    return $this->db_All ( $sql );
  }

  public function lockTable ( $tbl ) {
    // lock table to prevent other sessions from modifying the data and thus preserving data integrity
    $sql = 'LOCK TABLE `' . $tbl . '` WRITE';
    $this->db_Exec ( $sql );
  }

  public function unLockTables () {
    // lock table to prevent other sessions from modifying the data and thus preserving data integrity
    $sql = 'UNLOCK TABLES';
    $this->db_Exec ( $sql );
  }

  public function db_add ( $tbl, $arrDatas = array(), $returnId = true ) {
    $leTableauAssociatifNomDuchamValeur = array();

    $tmp = array();
    foreach ( $arrDatas as $K => $V ) {
      $leTableauAssociatifNomDuchamValeur[':' . $K] = $V;
      $tmp[] = $K;
    }

    $lescolonnes = join ( '`,`', $tmp );
    $lesChampsNommes = join ( ',:', $tmp );

    $sql = "INSERT INTO `" . $tbl . "`
             (`" . $lescolonnes . "`)
             VALUES (:" . $lesChampsNommes . ")";
    $datas = $leTableauAssociatifNomDuchamValeur;

    //return array('sql' => $sql, 'datas' => $datas);
    return $this->db_Insert ( $sql, $datas, $returnId );
  }

  /**
   *
   * fieldsValues :  nomchamp1 = :nomchamp1 , nomchamp2 =:nomchamp2 ...etc...
   */
  public function db_edit ( $tbl, $arrDatas = array(), $whereValue = 0, $whereField = 'id' ) {
    $leTableauAssociatifNomDuchamValeur = array();

    $tmp = array();
    foreach ( $arrDatas as $K => $V ) {
      $leTableauAssociatifNomDuchamValeur[':' . $K] = $V;
      $tmp[] = $K . '=:' . $K;
    }
    $fieldsValues = join ( ',', $tmp );

    $sql = "UPDATE `" . $tbl . "`
            SET $fieldsValues
            WHERE " . $whereField . " = '" . $whereValue . "'";
    $datas = $leTableauAssociatifNomDuchamValeur;
    //return array($sql, $datas);
    return $this->db_Exec ( $sql, $datas );
  }

  public function db_removed ( $tbl, $whereValue = 0, $whereField = 'id' ) {
    $sql = "DELETE FROM " . $tbl . "
            WHERE " . $whereField . " = :" . $whereField;
    $datas = array(':' . $whereField => $whereValue);

    return $this->db_Exec ( $sql, $datas );
  }

  public function db_get ( $tbl, $orderBy = array(), $arrlimit = array() ) {
    $orderField = !empty ( $orderBy[0] ) ? $orderBy[0] : 'id';
    $order = !empty ( $orderBy[1] ) ? $orderBy[1] : 'ASC';
    $limit = !empty ( $arrlimit[0] ) ? $arrlimit[0] : '';
    $offset = !empty ( $arrlimit[1] ) ? $arrlimit[1] : '';

    $sql = "SELECT * FROM $tbl ORDER BY  $orderField $order $limit $offset";

    return $this->db_All ( $sql );
  }

  public function db_getBy_One ( $tbl, $field = 'id', $value = '', $orderBy = array(), $arrlimit = array() ) {
    $strOrder = "";
    if ( !empty ( $orderBy ) ) {
      $orderField = !empty ( $orderBy[0] ) ? $orderBy[0] : 'id';
      $order = !empty ( $orderBy[1] ) ? $orderBy[1] : 'ASC';
      $strOrder = "ORDER BY $orderField $order ";
    }
    $limit = !empty ( $arrlimit[0] ) ? $arrlimit[0] : '';
    $offset = !empty ( $arrlimit[1] ) ? $arrlimit[1] : '';

    $sql = "SELECT *
            FROM $tbl
            WHERE $field = :value
            $strOrder $limit $offset";

    $datas = array(':value' => $value);
    return $this->db_One ( $sql, $datas );
  }

  public function db_getBy_All ( $tbl, $field = 'id', $value = '', $orderBy = array(), $arrlimit = array() ) {
    $strOrder = "";
    if ( !empty ( $orderBy ) ) {
      $orderField = !empty ( $orderBy[0] ) ? $orderBy[0] : 'id';
      $order = !empty ( $orderBy[1] ) ? $orderBy[1] : 'ASC';
      $strOrder = "ORDER BY $orderField $order ";
    }
    $limit = !empty ( $arrlimit[0] ) ? $arrlimit[0] : '';
    $offset = !empty ( $arrlimit[1] ) ? $arrlimit[1] : '';

    $sql = "SELECT *
            FROM $tbl
            WHERE $field = :value
            $strOrder $order $limit $offset";

    $datas = array(':value' => $value);
    return $this->db_All ( $sql, $datas );
  }

}
