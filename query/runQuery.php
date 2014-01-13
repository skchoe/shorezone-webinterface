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

// (lat,lng) -> Corresponding Tile ID under a zoom
$tile_id = GoogleMapUtility::toTileXY($lat, $lng, $zoom);
// (lat,lng) -> pixel coordinate under a zoom
$pixel = GoogleMapUtility::getPixelOffsetInTile($lat, $lng, $zoom);
//$pixel->x = $pixel->x - $tile_id->x *  GoogleMapUtility::TILE_SIZE;
//$pixel->y = $pixel->y - $tile_id->y *  GoogleMapUtility::TILE_SIZE;

// DB connection to get list of `source_gri'
require("../db2tile/db_credentials.php");
$conn = mysql_connect($db_host, $db_name, $db_pass);
$db_list = mysql_list_dbs($conn);
@mysql_select_db($database)
or die("Could not select database!");
$species_feature_tbl = "species_feature_tbl";
$query_idset = "SELECT source_gri, cm_name, sc_nam, taxon_gr_1
           FROM ".$species_feature_tbl;
$result_idset = mysql_query($query_idset);
if($result_idset == FALSE) echo "false output<br />";

$initCount = 0;
$endCount = mysql_numrows($result_idset) - 1;



// LOOP through species
echo "TILE_ID = (".$tile_id->x.", ".$tile_id->y.")<br/>";
echo "PIXEL XY = (".$pixel->x.", ".$pixel->y.")<br/>";
$returnedSerialNumber = 0;
for ($count=$initCount; $count <= $endCount ; $count = $count + 1) {
  $source_gri = mysql_result($result_idset, $count, "source_gri");
  $cm_name = mysql_result($result_idset, $count, "cm_name");
  $animal4 = mysql_result($result_idset, $count, "animal_for");
  // Access tile image
  $filename = "../tiles/wss/tiles_11_1bit/id_".$source_gri."/".$zoom."/c_".$source_gri."_".$tile_id->x."_".$tile_id->y."_".$zoom.".png";

  // Stage 1 checking
  if(file_exists($filename)) {
    //echo $source_gri." Passed stage 1 ---> ";
    $im = imagecreatefrompng($filename);
    $index = imagecolorat($im, $pixel->x, $pixel->y);
    $rgb = imagecolorsforindex($im, $index);
    $r = $rgb["red"];
    $g = $rgb["green"];
    $b = $rgb["blue"];

    /* error code
    $r = ($rgb >> 16) & 0xFF;
    $g = ($rgb >> 8) & 0xFF;
    $b = $rgb & 0xFF;
    */
    //echo "(((".$r.":".$g.":".$b.")))";

    // Stage 2 checking
    if(($r==0)&&($g==0)&&($b==0)) $r+1;//echo "Failed final stage :";
    else {
	$returnedSericalNumber++;
	echo "$cm_name: $animal4";
  	echo "<a href=\"http://155.97.130.88/wss_maps/viz/speciesExtent.php?species=".$source_gri."\">$source_gri</a><br/>";
    }
  }
  else {
    //echo $source_gri." Failed stage 1 :";
  }
}

        //$completeFile = '../tiles/wss/tiles_11_1bit/'.$folder_id.'/'.$folder_zm.'/_complete.stamp';
        $completeFile = '_complete.stamp';
        $completeFileHandle = fopen($completeFile, 'w') or die("can't open file");
        fclose($completeFileHandle);

echo "___________________________________________________________________<br/>";
echo "Number of returned species :$returnedSerialNumber out of $endCount <br/>";

mysql_free_result($result_idset);
?>
