<?php

function extract_shp_name($filename)
{
// structure of $filename would be seq of folders with / as separator.
  $paths = explode("/", $filename);
  $num_paths = count ($paths);
  $pure_filename = $paths[$num_paths-1];

  $bodies = explode(".", $pure_filename);
  return $bodies[0];
}

function table_exist($dbname, $tblname)
{
  // Table listup/creation
  $query_forall_tbls = "SHOW TABLES FROM ".$dbname;
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
    if ($tblname == $row[0]) {
  	//echo "test table exists: ".$tblname."<br />";
  	$b_tbl_exist = TRUE;
  	break;
    }
    //else echo "{$row[0]} isn't same as {$tblname}. <br />";
  }
  //mysql_free_result($resulttbls);
  
  //if ($b_tbl_exist == TRUE)
  //  echo "dst_tbl found<br/>";
  //else
  //  echo "Couldn't find table ---> creation<br />";
 
  return $b_tbl_exist;
}

function get_mysqltype_from_dbf_header($dbf_type, $dbf_length)
{
  $out_string = "";
  if($dbf_type == "number")
    $out_string = $out_string."int(".$dbf_length.")";
  else if ($dbf_type == "float")
    $out_string = $out_string."float(".$dbf_length.")";
  else if ($dbf_type == "double")
    $out_string = $out_string."float(".$dbf_length.")";
  else if ($dbf_type == "character")
    $out_string = $out_string."char(".$dbf_length.")";
  else {
    echo "get_mysqltype_from_dbf_header cannot handle type: ".$dbf_type."</br>";
    exit;
  }
  return $out_string;
}

function create_dst_tbl($dbname, $tblname, $pri_key_name, $geotype)
{
  if(table_exist ($dbname, $tblname))
  {
    echo "table ".$tblname." exist ... recreate</br>";
    $drop = "DROP TABLE ".$tblname.";";
    $dr = mysql_query($drop);
    if($dr == FALSE) die ("dropping dst tbl failed");
  }
  else 
  {  
    "table $tblname doesn't exist -> query for creation</br>";
  }

  $query_for_create_tbl
    = "CREATE TABLE ".$tblname." (".
      $pri_key_name." BIGINT UNSIGNED NOT NULL,
      xmin double NOT NULL,
      ymin double NOT NULL,
      xmax double NOT NULL,
      ymax double NOT NULL,
      numparts int NOT NULL,
      numpoints int NOT NULL,
      polygons ".$geotype." NOT NULL,
      color varchar(7) NOT NULL,
      SPATIAL KEY (polygons),
      PRIMARY KEY (".$pri_key_name.")
    ) ENGINE = MYISAM;";
  echo "bulid_db/shp2mysql: QUERY:".$query_for_create_tbl."</br>";
  $cr = mysql_query($query_for_create_tbl);
  if ($cr==TRUE) echo "success_dst_tbl _creation<br />";
  else echo "fail to create dst_table <br />";
  //mysql_free_result($cr); ->doesn't need because return is small

  return $tblname;
}

function create_dbf_tbl($dbname, $tblname, $dbfHeader, $pri_key_name)
{
  if(table_exist ($dbname, $tblname)){
    echo "table ".$tblname." already exist ... return the table name</br>";
    $drop = "DROP TABLE ".$tblname.";";
    $dr = mysql_query($drop);
    if($dr == FALSE) die ("dropping dst tbl failed");
  }

  //$column_string = $pri_key_name." BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,";
  $column_string = $pri_key_name." BIGINT UNSIGNED,"; //give up: identifierness, copy of record_number of shape file.
  for ($i = 0; $i < count($dbfHeader) ; $i++) {
    //foreach ($dbfHeader[$i] as $key => $value){
    //  echo $key . "=>" . $value . ", ";
    // }
    $name = $dbfHeader[$i]["name"];
    $type = $dbfHeader[$i]["type"];
    $len = $dbfHeader[$i]["length"];
    $mysql_type = get_mysqltype_from_dbf_header($type, $len);
    $column_string = $column_string." ".$name." ".$mysql_type.",";
  }
  $column_string = $column_string." PRIMARY KEY (".$pri_key_name.")";
  $query_for_create_tbl
    = "CREATE TABLE ".$tblname." (".$column_string.");";

  echo "query_for_create_tbl = ".$query_for_create_tbl."</br>";
  $cr = mysql_query($query_for_create_tbl);
  if ($cr==TRUE) echo "success_dbf_tbl _creation<br />";
  else { echo "fail to create dbf_table <br />";
        die("dbf tbl not created");
  }
  //mysql_free_result($cr); ->doesn't need because return is small

  return $tblname;
}

function describe_table($tblname)
{
  /* Create and execute query. */
  $query3 = "DESCRIBE ".$tblname;
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
}

function insert_dst_table($shp_record)
{

  $shp_data = $shp_record->getShpData();
  $dbf_data = $shp_record->getDbfData();

  $common_name = addslashes($dbf_data[$com_name]);
  $scientific_name = addslashes($dbf_data[$sci_name]);
  flush();
  ob_flush();

  // dbf info
  $source_gri = $dbf_data[$pri_key];
  
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
  
  echo "VALUES---".$source_gri.", ".$scientific_name.", ".$common_name.", ".$xmin.", ".
       $ymin.", ".$xmax.", ".$ymax.", ".$shp_numparts.", ".$shp_numpoints.", sizeof-parts".sizeof($part_array)."<br/>";

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
    polygons=GEOMFROMTEXT('".$mp_string."'),
    color=".$color."\"";
  $result_insert = mysql_query($query_insert);
  if($result_insert==TRUE) echo "insert ---***(((".$k."))) success ".$result_insert." : ".$shp_numpoints." points ".$shp_numparts." parts<br/>";
  else echo "insert --- $result_insert : fails<br />";

  flush();
ob_flush();


}
/*
$query2 = "SELECT * FROM ".$species_dst_tbl." ORDER BY source_gri";
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
	//echo "Species: $source_gri ($xmin)($xmax)($ymin)($ymax) :$cnt1 $res_poly<br />";
 }
//mysql_free_result($result2);
*/

/*
// SHAPE FILE ACCESS
include "ShapeFile.inc.php";

$options = array('noparts' => false);
$shp = new ShapeFile("data/RiceSoundscape_Species_Proje.shp", $options); // along this file the class will use file.shx and file.dbf
$dbf_header_data = $shp->getDbfHeader();

if($dbf_header_data == FALSE) echo "DBF header is null__________<br/>";
$pri_key = $dbf_header_data[0]["name"];
$com_name = $dbf_header_data[4]["name"]; ///////////////FIX '3' is common name, '4' is sci name
$sci_name = $dbf_header_data[3]["name"]; 

$taxon_grou = $dbf_header_data[5]["name"];
$animal_for = $dbf_header_data[6]["name"];
$taxon_gr_1 = $dbf_header_data[12]["name"];

echo "DBF header: ".$pri_key.", ".$sci_name.", ".$com_name."<br/>";

// SHAPE DATA -> WKB DATA
include "../build_db/WktUtils.inc.php";
$shp2wkt = new Shape2Wkt();  

$k = -1;
while ($record = $shp->getNext()) {
  $k++;
  
  echo "START: ".$k."   <br/>";
  flush();
  ob_flush();

  $val = 241;
  $val2 = 242;
  if($k < $val) continue;
  if($k >= $val2+1) break;

  $shp_data = $record->getShpData();
  $dbf_data = $record->getDbfData();
  echo "----------------------".$k."th ReCORD  ".$dbf_data[$com_name]." Has been accessed by reader<br/>";

  $common_name = addslashes($dbf_data[$com_name]);
  $scientific_name = addslashes($dbf_data[$sci_name]);
  flush();
  ob_flush();

  // dbf info
  $source_gri = $dbf_data[$pri_key];
  
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
  
echo "VALUES---".$source_gri.", ".$scientific_name.", ".$common_name.", ".$xmin.", ".
$ymin.", ".$xmax.", ".$ymax.", ".$shp_numparts.", ".$shp_numpoints.", sizeof-parts".sizeof($part_array)."<br/>";

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
  if($result_insert==TRUE) echo "insert ---***(((".$k."))) success ".$result_insert." : ".$shp_numpoints." points ".$shp_numparts." parts<br/>";
  else echo "insert --- $result_insert : fails<br />";

  flush();
ob_flush();
 }

mysql_close();
*/
?>
