<?php

set_time_limit(3600*24*3);
ini_set('memory_limit', -1);
//while (ob_get_level()) ob_end_flush();

//////////////////////////////////////////////////////////////////////////////
// example Db constrction = to file db.sqlite
// create new database (OO interface) 
//$db_name = "db.sqlite";
$db_name = "dbsqlite0406.sql";
try
{
  $db = new SQLiteDatabase($db_name, 0666, $err); 
}
catch(Exception $e)
{
  die($err);
}

if($err) { echo "SQLite not supported<br/>"; }
else { echo "SQLite is supported<br/>"; }

// create table foo and insert sample data 
$tbl_name = "species_feature_tbl";

// clear first
$query_drop_tbl = "DROP TABLE $tbl_name";
$db->query($query_drop_tbl);

//add Movie table to database
$query = 'CREATE TABLE '.$tbl_name.
         '(Title TEXT, Director TEXT, Year INTEGER)';
if(!$db->queryExec($query, $error))
{
  die($error);
}
/*
// define table.....
$query_create_tbl
    = "CREATE TABLE ".$tbl_name." (
       source_gri VARCHAR(16) PRIMARY KEY,
       cm_name VARCHAR(32),
       sc_name VARCHAR(32),
       taxon_grou VARCHAR(4),
       animal_for VARCHAR(16),
       has_no_hab INTEGER,
       minx DOUBLE,
       maxx DOUBLE,
       miny DOUBLE,
       maxy DOUBLE,
       taxon_gr_1 VARCHAR(32),
       shape_area DOUBLE,
       shape_len  DOUBLE
       );";

echo ".....".$query_create_tbl."<br/><br/><br/><br/><br/>";

$db->query($query_create_tbl);

*/

/*
// table insert content, retrieve them. -- example
$source_gri = "aa";
$cm_name = "brian";
$sc_name = "bb";
$tx_group = "a";
$animal4 = "aa";
$hab = 123;
$xmin = 3.4;
$xmax = 3.4;
$ymin = 3.4;
$ymax = 3.4;
$tx_gname = "AA";
$shp_area = 2.1;
$shp_length = 2.1;

$query_insert = "INSERT INTO ".$tbl_name."
  (source_gri, cm_name, sc_name, taxon_grou, animal_for, has_no_hab, 
   minx, maxx, miny, maxy, taxon_gr_1, shape_area, shape_len)
  VALUES 
  ('".$source_gri."', '".$cm_name."', '".$sc_name."', '".$tx_group."', '".$animal4."', '".$hab."'
  ,'".$xmin."', '".$xmax."', '".$ymin."', '".$ymax."', '".$tx_gname."', '".$shp_area."', '".$shp_length."'); 
  COMMIT;";

$result_q0 = $db->query($query_insert); 

$result_q = $db->query("SELECT * FROM ".$tbl_name); 
iterate_retrieved_Rows($result_q);
*/

echo "<br/>.....".$query_create_tbl." -- is created.<br/><br/><br/><br/><br/>";

//////////////////////////////////////////////////////////////////////////////////
// SHAPE FILE ACCESS
/*
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
echo "DBF header: ".$sci_name.", ".$taxon_grou.", ".$animal_for."<br/>";
echo "DBF header: ".$taxon_ar_1.", ".$min_x.", ".$min_y."<br/>";
echo "DBF header: ".$shape_area.", ".$max_x.", ".$max_y."<br/>";
echo "DBF header: ".$shape_len."<br/>";

$k = -1;
$record = $shp->getNext();
//while ($record = $shp->getNext()) {
  $k++;
  
  echo "START: ".$k."   <br/>";

  flush();
  ob_flush();
  $shp_data = $record->getShpData();
  $dbf_data = $record->getDbfData();

  $common_name = $dbf_data[$com_name];
  $scientific_name = $dbf_data[$sci_name];
  echo "----------------------".$k."th ReCORD  ".$common_name."<br/>";

  // id
  $source_gri = $dbf_data[$pri_key];
  // names
  $cm_name = addslashes($dbf_data[$com_name]);
  $sc_name = addslashes($dbf_data[$sci_name]);
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

  echo "VALUES-FROM DBF ".$source_gri.", ".$cm_name.", ".$sc_name.", <br/>"
    .$tx_group.", ".$animal4.", ".$hab.", <br/>"
    .$xmin.", ".$ymin.", ".$xmax.", ".$ymax.", <br/>"
    .$tx_gname.", ".$shp_area.", ".$shp_length."<br/>";

  $query_insert = "INSERT INTO ".$tbl_name."
    (source_gri, cm_name, sc_name, taxon_grou, animal_for, has_no_hab, 
     minx, maxx, miny, maxy, taxon_gr_1, shape_area, shape_len)
    VALUES 
    ('".$source_gri."', '".$cm_name."', '".$sc_name."', '".$tx_group."', '".$animal4."', '".$hab."'
    ,'".$xmin."', '".$xmax."', '".$ymin."', '".$ymax."', '".$tx_gname."', '".$shp_area."', '".$shp_length."'); 
    COMMIT;";

  $result_q0 = $db->query($query_insert); 
  flush();
  ob_flush();

//}
*/
// not generally needed as PHP will destroy the connection 
unset($db); 


function iterate_retrieved_Rows($result)
{
  // iterate through the retrieved rows 
  while ($result->valid()) { 
    // fetch current row 
    $row = $result->current();      
    print_r($row);
    echo "<br/>";
    //echo $row."<br/>"; 
  // proceed to next row 
    $result->next(); 
  }
} 
?>
