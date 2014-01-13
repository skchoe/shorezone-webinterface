<?php

require_once('../viz/GoogleMapUtility.php');
require_once("../viz/dataUtils.inc.php");

//this script may require additional memory and time
set_time_limit(10);
ini_set('memory_limit',-1);


// species_id for sql query
$lat = $_GET["lat"];
$lng = $_GET["lng"];
$zoom = $_GET["zoom"];
$kind = $_GET["kind"];

// (lat,lng) -> Corresponding Tile ID under a zoom
$tile_id = GoogleMapUtility::toTileXY($lat, $lng, $zoom);
// (lat,lng) -> pixel coordinate under a zoom
$pixel = GoogleMapUtility::getPixelOffsetInTile($lat, $lng, $zoom);
//$pixel->x = $pixel->x - $tile_id->x *  GoogleMapUtility::TILE_SIZE;
//$pixel->y = $pixel->y - $tile_id->y *  GoogleMapUtility::TILE_SIZE;

// DB connection to get list of `source_gri'
require("../db2tile/db_credentials.php");
//echo "host = $db_host, dbuser = $db_name, db_pass= $db_pass, dbname = $database </br>";
$conn = mysql_connect($db_host, $db_name, $db_pass)
or die("Could not select database! - runQueryZ11");
$db_list = mysql_list_dbs($conn);
@mysql_select_db($database);


$species_feature_tbl = "species_feature_tbl";
if($kind == 0) // all species
  $query_idset = "SELECT source_gri, cm_name, sc_name, taxon_gr_1, animal_for FROM ".$species_feature_tbl." ORDER BY taxon_gr_1, animal_for, cm_name";
else if($kind == 1) // Amphibians
  $query_idset = "SELECT source_gri, cm_name, sc_name, taxon_gr_1, animal_for FROM ".$species_feature_tbl." WHERE taxon_grou = \"A\" ORDER BY taxon_gr_1, animal_for, cm_name";
else if($kind == 2) // Birds
  $query_idset = "SELECT source_gri, cm_name, sc_name, taxon_gr_1, animal_for FROM ".$species_feature_tbl." WHERE taxon_grou = \"B\" ORDER BY taxon_gr_1, animal_for, cm_name";
else if($kind == 3) // Mammals
  $query_idset = "SELECT source_gri, cm_name, sc_name, taxon_gr_1, animal_for FROM ".$species_feature_tbl." WHERE taxon_grou = \"M\" ORDER BY taxon_gr_1, animal_for, cm_name";
else if($kind == 4) // Reptile
  $query_idset = "SELECT source_gri, cm_name, sc_name, taxon_gr_1, animal_for FROM ".$species_feature_tbl." WHERE taxon_grou = \"R\" ORDER BY taxon_gr_1, animal_for, cm_name";
else
  $query_idset = "SELECT source_gri, cm_name, sc_name, taxon_gr_1, animal_for FROM ".$species_feature_tbl;



$result_idset = mysql_query($query_idset);
if($result_idset == FALSE) echo "false output<br />";

$initCount = 0;
$endCount = mysql_numrows($result_idset) - 1;



// LOOP through species
echo "TILE_ID = (".$tile_id->x.", ".$tile_id->y.")<br/>";
echo "PIXEL XY = (".$pixel->x.", ".$pixel->y.")<br/><br/>";

$returnedSerialNumber = 0;

$tiles_folder = "../tiles/wss/tiles_11";
for ($count=$initCount; $count <= $endCount ; $count = $count + 1) {
  $source_gri = mysql_result($result_idset, $count, "source_gri");
  $cm_name = mysql_result($result_idset, $count, "cm_name");
  $taxon_gr_1 = mysql_result($result_idset, $count, "taxon_gr_1");
  $animal_for = mysql_result($result_idset, $count, "animal_for");
  // Access tile image
  $filename = $tiles_folder."/id_".$source_gri."/c_".$source_gri."_".$tile_id->x."_".$tile_id->y."_".$zoom.".png";

  // Stage 1 checking
  if(file_exists($filename)) {
    //echo $source_gri." Passed stage 1 ---> ";
    $im = imagecreatefrompng($filename);
    $index = imagecolorat($im, $pixel->x, $pixel->y);
    $rgb = imagecolorsforindex($im, $index);
    $r = $rgb["red"];
    $g = $rgb["green"];
    $b = $rgb["blue"];

    //echo "(((".$r.":".$g.":".$b.")))";

    // Stage 2 checking
    if(($r==0)&&($g==0)&&($b==0)) $r+1;//echo "Failed final stage :";
    else {
	$returnedSerialNumber++;

	echo ucfirst(strtolower($animal_for))." -<TAB> ".ucfirst(strtolower($cm_name)).": <TAB>";
        echo "<a href=\"http://westernsoundscape.org/maps/speciesExtent.php?species=".$source_gri."\">$source_gri</a><br/>";
        echo "\r\n";
        flush();
        ob_flush();
    }
  }
  else {
    //echo $source_gri." Failed stage 1 :";
  }
}

        //$completeFile = $tiles_folder. '/'.$folder_id.'/_complete.stamp';
        //$completeFile = '_complete.stamp';
        //$completeFileHandle = fopen($completeFile, 'w') or die("can't open file");
        //fclose($completeFileHandle);

echo "___________________________________________________________________<br/>";
echo "Number of returned species :$returnedSerialNumber out of $endCount <br/>";


mysql_free_result($result_idset);
?>
