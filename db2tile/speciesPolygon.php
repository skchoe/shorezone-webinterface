<?php
//this script may require additional memory and time
set_time_limit(3600);
ini_set('memory_limit',-1);
while (ob_get_level()) ob_end_flush();

$wss_db = "wss";
$species_id = $_GET['species'];
echo "<br/>species_id = ".$species_id."<br/>";
require_once(dirname(__FILE__)."/dataUtils.inc.php");
require_once(dirname(__FILE__)."/GoogleMapUtility.php");
require_once(dirname(__FILE__)."/vizUtils.inc.php");

list($source_gri, $xmin, $xmax, $ymin, $ymax, $polygonArrayGeo)
  = getPhpPolygonFromDB($wss_db, $species_id, TRUE);
echo "id(".$species_id."), size(".sizeof($polygonArrayGeo).") xmin,ymin = (".$xmin.", ".$ymin."), xmax,ymax = (".$xmax.", ".$ymax.")<br/>";
flush();
ob_flush();

  
$zoomLevels = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15);
//$zoomLevels = array(13);

// approach 2.
// make big image containing all habitat, ready for cut into tiles.
//computeAllTiles($zoomLevels, $polygonArrayGeo);

// approach 3.
// create tile individually.
$shp_name = "wss";
computeTileImage($wss_db, $shp_name, $zoomLevels, $species_id, 10000, "viz");

?>
