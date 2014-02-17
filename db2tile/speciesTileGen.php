<?php
require_once(dirname(__FILE__)."/dataUtils.inc.php");
/*
$xmin = -116.23999124126;
$xmax = -106.5152030141;
$ymin = 31.317209966419;
$ymax = 37.599457994109;
*/


$wss_db = "wss";
$species_id = -1;


$eps = 0.0000000000001;
list($source_gri, $xmin, $xmax, $ymin, $ymax, $polygonArrayGeo)
= getPhpPolygonFromDB($wss_db, $species_id, TRUE); // returns multipolygon

echo "<a href=\"http://localhost/viz/speciesTileImages.php?xmin=".$xmin."&xmax=".$xmax."&ymin=".$ymin."&ymax=".$ymax."&polygon=".$polygonArrayGeo."\"> click here </a>";


?>
