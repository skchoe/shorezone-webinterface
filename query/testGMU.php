<?php

require_once('../viz/GoogleMapUtility.php');

//this script may require additional memory and time
set_time_limit(10);
ini_set('memory_limit',-1);


// species_id for sql query
$lat = $_GET["lat"];
$lng = $_GET["lng"];
$zoom = $_GET["zoom"];

echo "input lat = $lat, lng = $lng, zoom = $zoom <br/>";

$normalized1 = GoogleMapUtility::toMercatorCoords($lat, $lng);
echo "Normalized1 x y = ".$normalized1->x.", ".$normalized1->y." <br/>";

$normalized2 = GoogleMapUtility::toNormalisedMercatorCoords($normalized1);
echo "Normalized2 x y = ".$normalized2->x.", ".$normalized2->y." <br/>";

// (lat,lng) -> Corresponding Tile ID under a zoom
$tile_id = GoogleMapUtility::toTileXY($lat, $lng, $zoom);
// (lat,lng) -> pixel coordinate under a zoom
$pixel = GoogleMapUtility::getPixelOffsetInTile($lat, $lng, $zoom);

echo "TILE_ID = (".$tile_id->x.", ".$tile_id->y.")<br/>";
echo "PIXEL XY = (".$pixel->x.", ".$pixel->y.")<br/>";

echo "GMU $tile_id->x, $tile_id->y, tile and $pixel->x, $pixel->y th pixel <br/>";

// 155.97.130.88/wss_maps/query/testGMU.php?lng=-112.6054&lat=38.570278&zoom=11
?>
