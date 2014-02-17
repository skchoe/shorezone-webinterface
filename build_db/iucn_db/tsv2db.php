<?php

require_once(dirname(__FILE__)."/../db2tile/db_credentials.php");
require_once(dirname(__FILE__).'/../../shp-app/mysql_utils.php');


$data_tsv = "Amphibians_Higher_Taxanomy.csv";
//include('CsvIterator.class.php');


$db_connect_info = array();
$db_connect_info['host'] = $db_host;
$db_connect_info['user'] = $db_name;
$db_connect_info['passwd'] = $db_pass;
$db_connect_info['db'] = $database;


$dbname = connect_db($db_connect_info);

$amph_name_tbl = "amph_name_table";
$primary_key_name = "scientific_name";

if(table_exist($dbname, $amph_name_tbl)) {
  $drop = "DROP TABLE ".$amph_name_tbl.";";
  $dr = mysql_query($drop);
  if($dr == FALSE) die ("dropping tbl failed");
  else echo "table drop happened</br>";
}

$query_create_tbl
  = "CREATE TABLE ".$amph_name_tbl." (".
    $primary_key_name." VARCHAR(64),
    common_name VARCHAR(64),
    PRIMARY KEY (".$primary_key_name.")
  ) ENGINE = MYISAM;";
$cr = mysql_query($query_create_tbl);
if($cr == FALSE) {
	echo "fail to tble creation";
	die("fail");
}
else echo "create table succ: </br>";

// insertion
$row = 0;
if (($handle = fopen($data_tsv, "r")) !== FALSE) {
  while (($data = fgetcsv($handle, 0, "\t")) !== FALSE) {
	$row++;
	if($row==1) continue; // header skiped.

	/*
        $num = count($data);
        //echo "<p> $num fields in line $row: <br /></p>\n";
	
        for ($c=0; $c < $num; $c++) {
            echo $data[$c] . "<br />\n";
        }
	*/
	echo $data[6]." ".$data[7]."->".$data[14]."</br>";
	$sci_name = $data[6] . " " . $data[7];
	$com_name_en = $data[14];

        $query_insert = "INSERT INTO ".$amph_name_tbl." set ".
	  $primary_key_name."=\"".$sci_name."\",
          common_name=\"".$com_name_en."\";";
	$insert_res = mysql_query($query_insert);

	if($insert_res == TRUE) echo "succ ".$sci_name."</br>";
	else echo "fail ".$sci_name."</br>";
  }
  fclose($handle);
}
?>
