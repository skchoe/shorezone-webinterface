<?php
set_time_limit(3600*24*3);
ini_set('memory_limit', -1);
//while (ob_get_level()) ob_end_flush();

// SHAPE FILE ACCESS
try {
require_once (dirname(__FILE__)."/../build_db/ShapeFile.inc.php");
require_once (dirname(__FILE__)."/../build_db/WktUtils.inc.php");
require_once (dirname(__FILE__)."/mysql_utils.php");
}
catch (Exception $e)
{
  //echo "Caught", $e->getMessage() , "</br>";
}

// WSS 831.. db done.
// tile
//$filename = "../shp/wss/RiceSoundscape_Species_Proje.shp";

//13766 .. db done
// tile
//$filename = "../shp/iucn_reptiles/REPTILES.shp";

// tile
// http://mapnik-utils.googlecode.com/svn/data/
// 246 .. db done
//$filename = "../shp/mapnik/tm_wgs84_sans_antarctica.shp";
//$filename = "../shp/mapnik/north_pacific_ecoregions.shp";
//$filename = "../shp/mapnik/states.shp";
//$filename = "../shp/mapnik/statep020.shp";
//$filename = "../shp/mapnik/world_borders.shp";
//$filename = "../shp/mapnik/world_boundaries_m.shp";

// 38 .. db done - only one row (check it)
// tile
//$filename = "../shp/48015_Austin/tl_2010_48015_arealm.shp";

// 5717 .. db done only one row.
// tile
//$filename = "../shp/48015_Austin/tl_2010_48015_areawater.shp";

// 20 .. db done only one row.
// tile
//$filename = "../shp/48015_Austin/tl_2010_48015_bg00.shp";

// 11550 db done
// tile - orientation mess up fron part CCW, rear part CW.
//$filename = "../shp/48015_Austin/tl_2010_48015_faces.shp";

////////////////////////////////////////////////////////////////
// Washington State -----NOT WGS84 (by checking range of values), but Lambert Conformal Conic projection
// landuse 1 db-no tile-no
//$filename = "../shp/State_of_Washington/trico_landuse10.shp";
// landuse 2 db-no tile-no
//$filename = "../shp/State_of_Washington/landuse10.shp";
//
// lake, db-ip, tile-no
//$filename = "../shp/State_of_Washington/lakes.shp";
//
// tribal area db-no tile-no
//$filename = "../shp/State_of_Washington/Tribal_poly.shp";
//$filename = "../shp/State_of_Washington/Tribal_arc.shp"; // tile generation has wrong tile range Max ~ Min.
//

//////////////////////////////////////////////////////////////////////////////////////
// Frgs
//19360 -> 19355 valid layers db done
//2011-11-13
// db-on done
//$filename = "../shp/iucn_amphibians/SPECIES1109_DBO_VIEW_AMPHIBIANS.shp";

// db-yet db-no tile-no
//$filename = "../shp/iucn_amphibians/AMPHANURA.shp";
// db-yet db-no tile-no
//$filename = "../shp/iucn_amphibians/AMPHCAUDATA.shp";
// db-done tile done(12)
// 325 -> first 324 valid, last one has no geometry db done
//$filename = "../shp/iucn_amphibians/AMPHGYMNOPHIONA.shp";

// Puget sound project : ShoreZone Inventory
#szpoly geom_type=3
#$filename = "../DNR_ShoreZone_Working/szinv_wgs84/szpoly.shp";
#szline geom_type=3
#$filename = "../DNR_ShoreZone_Working/szinv_wgs84/szline.shp";
#szpt geom_type=1
#$filename = "../DNR_ShoreZone_Working/szinv_wgs84/szpt.shp";
#szlnend geom_type=1
$filename = "../DNR_ShoreZone_Working/szinv_wgs84/szlnend.shp";

// defining table name
$shp_name = extract_shp_name ($filename);
$table_name_shp = $shp_name."_dst";
$table_name_dbf = $shp_name."_dbf";
?>
