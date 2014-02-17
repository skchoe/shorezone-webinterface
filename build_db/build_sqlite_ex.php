<?php

set_time_limit(3600*24*3);
ini_set('memory_limit', -1);
//while (ob_get_level()) ob_end_flush();

//////////////////////////////////////////////////////////////////////////////
// example Db constrction = to file db.sqlite
// create new database (OO interface) 
$db_name = "dbexample.sqlite";
$db = new SQLiteDatabase($db_name, 0666, $err); 
  if($err) { echo "SQLite not supported<br/>"; }
  else { echo "SQLite is supported<br/>"; }


// create table foo and insert sample data 
$tbl_name = "species_feature_tbl";

// clear first
$query_drop_tbl = "DROP TABLE $tbl_name";
$db->query($query_drop_tbl);

// define table.....
$query_create_tbl
    = "CREATE TABLE ".$tbl_name." (
       source_gri char(16),
       cm_name char(32),
       sc_name char(32),
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

echo ".....".$query_create_tbl."<br/><br/><br/><br/><br/>";

$db->query($query_create_tbl);
echo "<br/>.....".$query_create_tbl." -- is created.<br/><br/><br/><br/><br/>";


// table insert content, retrieve them. -- example
$source_gri = "aa";
$cm_name = "abrian";
$sc_name = "abb";
$tx_group = "A";
$animal4 = "amphibians";
$hab = 123;
$xmin = 13.4;
$xmax = 13.4;
$ymin = 13.4;
$ymax = 13.4;
$tx_gname = "AA";
$shp_area = 12.1;
$shp_length = 12.1;

insert_table($db, $tbl_name, $source_gri, $cm_name, $sc_name, $tx_group, $animal4, $hab, 
    $xmin, $xmax, $ymin, $ymax, $tx_gname, $shp_area, $shp_length);

// table insert content, retrieve them. -- example
$source_gri = "ba";
$cm_name = "brian";
$sc_name = "bbb";
$tx_group = "B";
$animal4 = "bird";
$hab = 223;
$xmin = 23.4;
$xmax = 23.4;
$ymin = 23.4;
$ymax = 23.4;
$tx_gname = "BB";
$shp_area = 22.1;
$shp_length = 22.1;

insert_table($db, $tbl_name, $source_gri, $cm_name, $sc_name, $tx_group, $animal4, $hab, 
    $xmin, $xmax, $ymin, $ymax, $tx_gname, $shp_area, $shp_length);

// table insert content, retrieve them. -- example
$source_gri = "maa";
$cm_name = "mbrian";
$sc_name = "mbb";
$tx_group = "M";
$animal4 = "maa";
$hab = 323;
$xmin = 3.4;
$xmax = 3.4;
$ymin = 3.4;
$ymax = 3.4;
$tx_gname = "MM";
$shp_area = 32.1;
$shp_length = 32.1;

insert_table($db, $tbl_name, $source_gri, $cm_name, $sc_name, $tx_group, $animal4, $hab, 
    $xmin, $xmax, $ymin, $ymax, $tx_gname, $shp_area, $shp_length);

// table insert content, retrieve them. -- example
$source_gri = "ra";
$cm_name = "rbrian";
$sc_name = "rbb";
$tx_group = "R";
$animal4 = "raa";
$hab = 423;
$xmin = 43.4;
$xmax = 43.4;
$ymin = 43.4;
$ymax = 43.4;
$tx_gname = "RR";
$shp_area = 42.1;
$shp_length = 42.1;

insert_table($db, $tbl_name, $source_gri, $cm_name, $sc_name, $tx_group, $animal4, $hab, 
    $xmin, $xmax, $ymin, $ymax, $tx_gname, $shp_area, $shp_length);

$result_q = $db->query("SELECT * FROM ".$tbl_name); 
iterate_retrieved_Rows($result_q);

/////////////////////////////////////////////////////////////////////////////////////////////////////////////
function insert_table($db, $tbl_name, 
    $source_gri, $cm_name, $sc_name, $tx_group, $animal4, $hab, 
    $xmin, $xmax, $ymin, $ymax, $tx_gname, $shp_area, $shp_length)
{
  $query_insert = "INSERT INTO ".$tbl_name."
    (source_gri, cm_name, sc_name, taxon_grou, animal_for, has_no_hab, 
     minx, maxx, miny, maxy, taxon_gr_1, shape_area, shape_len)
    VALUES 
    ('".$source_gri."', '".$cm_name."', '".$sc_name."', '".$tx_group."', '".$animal4."', '".$hab."'
    ,'".$xmin."', '".$xmax."', '".$ymin."', '".$ymax."', '".$tx_gname."', '".$shp_area."', '".$shp_length."'); 
    COMMIT;";

  $result_q0 = $db->query($query_insert); 
}

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
