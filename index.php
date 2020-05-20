<?php

require_once "./class/db.class.php";

$oDb = new db();

//exécuter une requête Select retournant plusieurs lignes de résultat
$sql = "SELECT * FROM maTable WHERE libelle =:libelle";
$datas = array(':libelle'=>'test1');
$res = $odb->db_All($sql,$datas);

//exécuter une requête Select retournant une seule ligne de résultat
$sql = "SELECT * FROM maTable WHERE id =:id";
$datas = array(':id'=>2);
$res2 = $oDb->db_One($sql,$datas);