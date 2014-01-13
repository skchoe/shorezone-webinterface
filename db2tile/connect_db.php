<?php

set_time_limit(3600);
ini_set('memory_limit',-1);


$value = 1;
print "Try to connect ... .... ... ... ...".$value;
require_once(dirname(__FILE__).'/../db2tile/db_credentials.php');
$conn = mysql_connect($db_host, $db_name, $db_pass)
  or die("Couldn't connect to my sql server!");

if ($conn == FALSE) echo "FALSE - Fail to connect db";
 else echo "TRUE- connected to db";

$db_list = mysql_list_dbs($conn);

$i = 0;
$wss_db = $database;
$exist_db = FALSE;
$cnt = mysql_num_rows($db_list);
echo "<br />db list -> <br />";
while ($i < $cnt) {
  $db_name = mysql_db_name($db_list, $i);
  echo $db_name . "<br />";
  if ($db_name == $wss_db) {

	$exist_db = TRUE;
  }
  $i++;
 }


// DB creation, use
if ($exist_db == TRUE)
  echo $wss_db." exists <br />";
 else {
   echo $wss_db." not exists <br />";
   $query_wss = "CREATE DATABASE $wss_db";
   $dbc = mysql_query($query_wss);
   if($dbc==TRUE) echo "creation of db good<br />";
   else echo "creatoin of db failed <br />";
 }
@mysql_select_db($wss_db)
or die("Could not select database!");

echo "You're connected to a MySQL database!----".$conn."<br />";

// Table listup/creation
$query_forall_tbls = "SHOW TABLES FROM ".$wss_db;
$resulttbls = mysql_query($query_forall_tbls);
if (!$resulttbls) {
  echo "DB Error, could not list tables\n";
  echo 'MySQL Error: ' . mysql_error();
  exit;
 }

$species_dst_tbl = "species_dst_tbl";
$b_tbl_exist = FALSE;
while ($row = mysql_fetch_row($resulttbls)) {
  echo "Table--------------: {$row[0]}<br />";
  if ($species_dst_tbl == $row[0]) {
	echo "test table exists<br />";
	$b_tbl_exist = TRUE;
	break;
  }
  else echo "{$row[0]} isn't same as {$species_dst_tbl}. <br />";
 }

if ($b_tbl_exist == TRUE)
  echo "dst_tbl found<br/>";
 else {
   echo "Couldn't find table ---> creation<br />";
   $query_for_create_tbl
	 = "CREATE TABLE ".$species_dst_tbl." (
       source_gri char(16) NOT NULL,
       sc_name char(32) NOT NULL,
       cm_name char(32) NOT NULL,
       xmin double NOT NULL,
       ymin double NOT NULL,
       xmax double NOT NULL,
       ymax double NOT NULL,
       numparts int NOT NULL,
       numpoints int NOT NULL,
       polygons multipolygon NOT NULL,
       SPATIAL KEY (polygons),
       PRIMARY KEY (source_gri)
       );";

   $cr = mysql_query($query_for_create_tbl);
   if (cr==TRUE) echo "success_tbl _creation<br />";
   else echo "fail to create table <br />";
 }

// Create and execute query.
$query3 = "DESCRIBE ".$species_dst_tbl;
$result3 = mysql_query($query3);
if($result3 == FALSE) echo "Result of query ".$query3." : false ---output<br />";
 else {
   echo "Result of query ".$query3." : true<br />";
   $cnt = mysql_numrows($result3);
   for($i = 0 ; $i < $cnt ; $i++){
	 $Field = mysql_result($result3, $i, "Field");
	 $Type = mysql_result($result3, $i, "Type");
	 $Null = mysql_result($result3, $i, "Null");
	 $Key = mysql_result($result3, $i, "Key");
	 $Default = mysql_result($result3, $i, "Default");
	 $Extra = mysql_result($result3, $i, "Extra");
	 echo "$Field | $Type | $Null | $Key | Default | $Extra <br/>";
   }
 }

$query2 = "SELECT * FROM ".$species_dst_tbl." WHERE source_gri=-1 ORDER BY source_gri";
$result2 = mysql_query($query2);
if($result2 == FALSE) echo "false output<br />";
else
for ($count=0; $count < mysql_numrows($result2); $count++)
  {
	$source_gri = mysql_result($result2, $count, "source_gri");
	$xmin = mysql_result($result2, $count, "xmin");
	$xmax = mysql_result($result2, $count, "xmax");
	$ymin = mysql_result($result2, $count, "ymin");
	$ymax = mysql_result($result2, $count, "ymax");
	$res_mp = mysql_query("SELECT ASTEXT(polygons) FROM ".$species_dst_tbl);
	$cnt1 = mysql_numrows($res_mp);
	$res_poly = mysql_result($res_mp, 0, "ASTEXT(polygons)");
	echo "Species: $source_gri ($xmin)($xmax)($ymin)($ymax) :$cnt1 $res_poly<br />";
	flush();
	ob_flush();
 }


// SHAPE FILE ACCESS
include "./ShapeFile.inc.php";

$options = array('noparts' => false);
$shp = new ShapeFile("data/RiceSoundscape_Species_Proje.shp", $options); // along this file the class will use file.shx and file.dbf
$dbf_header_data = $shp->getDbfHeader();

if($dbf_header_data == FALSE) echo "DBF header is null__________<br/>";
$pri_key = $dbf_header_data[0]["name"];
$sci_name = $dbf_header_data[3]["name"];
$com_name = $dbf_header_data[4]["name"];

echo "DBF header: ".$pri_key.", ".$sci_name.", ".$com_name."<br/>";

// SHAPE DATA -> WKB DATA
include "./WktUtils.inc.php";
$shp2wkt = new Shape2Wkt();  

//Dump the ten first records
$k = -1;
while ($record = $shp->getNext()) {
  $k++;
  echo "----------------------".$k."th ReCORD<br/>";
  if($k < 90) continue;
  
  $shp_data = $record->getShpData();

  $dbf_data = $record->getDbfData();

  // dbf info
  $source_gri = $dbf_data[$pri_key];
  $scientific_name = $dbf_data[$sci_name];
  $common_name = $dbf_data[$com_name];
  
  // min-max -> shp_data["xmin"].......
  $xmin = $shp_data["xmin"];
  $ymin = $shp_data["ymin"];
  $xmax = $shp_data["xmax"];
  $ymax = $shp_data["ymax"];
  
  // pts, parts
  $shp_numpoints = $shp_data["numpoints"];
  $shp_numparts = $shp_data["numparts"];
  $part_array = $shp_data["parts"];
  // Multipolygon -> WKT
  $mp_string = $shp2wkt->convert ($shp_numparts, $part_array);
  
  //echo "VALUES---".$source_gri.", ".$scientific_name.", ".$common_name.", ".$xmin.", ".
  //$ymin.", ".$xmax.", ".$ymax.", ".$shp_numparts.", ".$shp_numpoints."<br/>";
  // {source_gri, min-max, n-parts, n-points, WKT} -> mysql record
  $query_insert = "INSERT INTO ".$species_dst_tbl." set
    source_gri='".$source_gri."',
    sc_name = '".$scientific_name."',
    cm_name = '".$common_name."',
    xmin=".$xmin.",
    ymin=".$ymin.",
    xmax=".$xmax.",
    ymax=".$ymax.",
    numparts=".$shp_numparts.",
    numpoints=".$shp_numpoints.",
    polygons=GEOMFROMTEXT('".$mp_string."')";
  $result_insert = mysql_query($query_insert);
  if($result_insert==TRUE) echo "insert ---***(((".$k."))) success $result_insert : <br/>";
  else echo "insert --- $result_insert : fails<br />";


 }


$query_retrieve = "SELECT * FROM ".$species_dst_tbl." ORDER BY source_gri";
$result_retrieve = mysql_query($query_retrieve);
if($result_retrieve == FALSE) echo "false output features<br />";
$result_mp_string = mysql_query("SELECT ASTEXT(polygons) FROM ".$species_dst_tbl." ORDER BY source_gri");
if($result_mp_string == FALSE) echo "false mp_string<br/>"; 
echo mysql_numrows($result_retrieve)."----------------------------------<br/>";
for ($count=0; $count < mysql_numrows($result_retrieve); $count++)
  {
	$source_gri = mysql_result($result_retrieve, $count, "source_gri");
	$sc_name = mysql_result($result_retrieve, $count, "sc_name");
	$cm_name = mysql_result($result_retrieve, $count, "cm_name");
	$xmin = mysql_result($result_retrieve, $count, "xmin");
	$xmax = mysql_result($result_retrieve, $count, "xmax");
	$ymin = mysql_result($result_retrieve, $count, "ymin");
	$ymax = mysql_result($result_retrieve, $count, "ymax");
	$res_poly = mysql_result($result_mp_string, $count, "ASTEXT(polygons)");
	//echo "Species: $source_gri $sc_name $cm_name ($xmin $xmax $ymin $ymax) : <br/> $res_poly<br />";
 }

mysql_close();
?>
