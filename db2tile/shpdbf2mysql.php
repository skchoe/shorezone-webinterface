<?php

set_time_limit(3600*24*3);
ini_set('memory_limit', -1);
while (ob_get_level()) ob_end_flush();


require_once(dirname(__FILE__).'/../db2tile/db_credentials.php');
$db_host = "localhost";
$db_name = "root";
$db_pass = "seagal12";
$wss_db = "wss"; // name of database
print "Try to connect ... .... ... ... ...<br/>";
$conn = mysql_connect($db_host, $db_name, $db_pass)
  or die("Couldn't connect to my sql server!");

if ($conn == FALSE) echo "FALSE - Fail to connect db";
 else {
	echo "TRUE- connected to db";
	flush();
	ob_flush();
  }

$db_list = mysql_list_dbs($conn);

$exist_db = FALSE;
$cnt = mysql_num_rows($db_list);
echo "<br />db list -> <br />";
$i = 0;
while ($i < $cnt) {
  $db_name = mysql_db_name($db_list, $i);
  echo $db_name . "<br />";
  if ($db_name == $wss_db)
	$exist_db = TRUE;
  $i++;
 }
flush();
ob_flush();

// DB creation, use
if ($exist_db == TRUE)
  echo $wss_db." exists <br />";
 else {
   echo $wss_db." not exists <br />";
   $query_wss = "CREATE DATABASE $wss_db";
   $dbc = mysql_query($query_wss);
   if($dbc==TRUE) echo "creation of db good<br />";
   else echo "creatoin of db failed <br />";
   //mysql_free_result($dbc);
 }
@mysql_select_db($wss_db)
or die("Could not select database!");
echo "You're connected to a MySQL database!----".$conn."<br />";

$species_feature_tbl = "species_feature_tbl";
// Table listup/creation
$query_forall_tbls = "SHOW TABLES FROM ".$wss_db;
$resulttbls = mysql_query($query_forall_tbls);
if (!$resulttbls) {
  echo "DB Error, could not list tables\n";
  echo 'MySQL Error: ' . mysql_error();
  exit;
 }

$b_tbl_exist = FALSE;
while ($row = mysql_fetch_row($resulttbls)) 
{
  //echo "Table--------------: {$row[0]}<br />";
  if ($species_feature_tbl == $row[0]) {
	//echo "test table exists<br />";
	$b_tbl_exist = TRUE;
	break;
  }
  //else echo "{$row[0]} isn't same as {$species_feature_tbl}. <br />";
}
//mysql_free_result($resulttbls);

if ($b_tbl_exist != TRUE)
{
   //echo "Couldn't find table ---> creation<br />";
   $query_for_create_tbl
    = "CREATE TABLE ".$species_feature_tbl." (
       source_gri char(16) NOT NULL,
       cm_name char(32) NOT NULL,
       sc_name char(32) NOT NULL,
       taxon_grou char(4),
       animal_for char(16),
       has_no_hab int,
       minx double,
       maxx double,
       miny double,
       maxy double,
       taxon_gr_1 char(32),
       shape_area double,
       shape_len  double,
       PRIMARY KEY (source_gri)
       );";

   $cr = mysql_query($query_for_create_tbl);
   //if (cr==TRUE) echo "success_tbl _creation<br />";
   //else echo "fail to create table <br />";
	//mysql_free_result($cr);
}

// Create and execute query. to test table creation
$query3 = "DESCRIBE ".$species_feature_tbl;
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
flush();
ob_flush();

// SHAPE FILE ACCESS
include "./ShapeFile.inc.php";

$options = array('noparts' => false);
// along this file the class will use file.shx and file.dbf
$shp = new ShapeFile("data/RiceSoundscape_Species_Proje.shp", $options); 
$dbf_header_data = $shp->getDbfHeader();

if($dbf_header_data == FALSE) echo "DBF header is null__________<br/>";
$pri_key = $dbf_header_data[0]["name"];
$com_name = $dbf_header_data[3]["name"]; 
$sci_name = $dbf_header_data[4]["name"];
$taxon_grou = $dbf_header_data[5]["name"];
$animal_for = $dbf_header_data[6]["name"];
$has_no_hab = $dbf_header_data[7]["name"];
$min_x = $dbf_header_data[8]["name"];
$min_y = $dbf_header_data[9]["name"];
$max_x = $dbf_header_data[10]["name"];
$max_y = $dbf_header_data[11]["name"];
$taxon_gr_1 = $dbf_header_data[12]["name"];
$shape_area = $dbf_header_data[13]["name"];
$shape_len = $dbf_header_data[14]["name"];

echo "DBF header: ".$pri_key.", ".$sci_name.", ".$com_name."<br/>";

$k = -1;
while ($record = $shp->getNext()) {
  $k++;
  
  echo "START: ".$k."   <br/>";
  if($k>70) break;

  $shp_data = $record->getShpData();
  $dbf_data = $record->getDbfData();

  $common_name = $dbf_data[$com_name];
  $scientific_name = $dbf_data[$sci_name];
  echo "----------------------".$k."th ReCORD  ".$common_name."<br/>";
  flush();
  ob_flush();

  // id
  $source_gri = $dbf_data[$pri_key];
  // names
  $cm_name = $dbf_data[$com_name];
  $sc_name = $dbf_data[$sci_name];
  // class
  $tx_group = $dbf_data[$taxon_grou];
  $animal4  = $dbf_data[$animal_for];
  $hab = $dbf_data[$has_no_hab];

  // min-max 
  $xmin = $dbf_data[$min_x];
  $ymin = $dbf_data[$min_y];
  $xmax = $dbf_data[$max_x];
  $ymax = $dbf_data[$max_y];
  
  // extra info
  $tx_gname = $dbf_data[$taxon_gr_1];
  $shp_area = $dbf_data[$shape_area];
  $shp_length = $dbf_data[$shape_len];

echo "VALUES-FROM DBF ".$source_gri.", ".$cm_name.", ".$sc_name.", <br/>".$tx_group.", ".$animal4.", ".$hab.", <br/>".$xmin.", ".$ymin.", ".$xmax.", ".$ymax.", <br/>".$tx_gname.", ".$shp_area.", ".$shp_length."<br/>";

/*
$valueddd = 0.5;
$query_insert = "INSERT INTO species_feature_tbl (source_gri, cm_name, sc_name, taxon_grou, animal_for, has_no_hab, minx, maxx, miny, maxy, taxon_gr_1, shape_area, shape_len) VALUES ('22b', 'a', 'b', 'c', 'd', 3, $valueddd, 0.3, 0.2, 0.5, 'a', 0.1, 0.5)";
*/

  $query_insert = "INSERT INTO ".$species_feature_tbl." set
    source_gri='".$source_gri."',
    cm_name = '".$cm_name."',
    sc_name = '".$sc_name."',
    taxon_grou = '".$tx_group."',
    animal_for = '".$animal4."',
    has_no_hab = ".$hab.",
    minx=".$xmin.",
    maxx=".$xmax.",
    miny=".$ymin.",
    maxy=".$ymax.",
    taxon_gr_1= '".$tx_gname."',
    shape_area=".$shp_area.",
    shape_len=".$shp_length;

  $result_insert = mysql_query($query_insert);
  if($result_insert==TRUE) echo "insert ---***(((".$k."))) success ".$result_insert." : ".$shp_numpoints." points ".$shp_numparts." parts<br/>";
  else echo "insert --- $result_insert : fails<br />";

  flush();
ob_flush();

 }
/*
$query_retrieve = "SELECT * FROM ".$species_feature_tbl." ORDER BY source_gri";
$result_retrieve = mysql_query($query_retrieve);
if($result_retrieve == FALSE) echo "false output features<br />";
$result_mp_string = mysql_query("SELECT ASTEXT(polygons) FROM ".$species_feature_tbl." ORDER BY source_gri");
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

flush();
ob_flush();
*/
mysql_close();
?>
