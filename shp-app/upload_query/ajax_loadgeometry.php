<?php
require_once(dirname(__FILE__)."/../../shp-app/mysql_utils.php");
require_once(dirname(__FILE__)."/../../build_db/WktUtils.inc.php");
require_once(dirname(__FILE__)."/../../db2tile/db_credentials.php");
//this script may require additional memory and time
set_time_limit(10);
ini_set('memory_limit',-1);

$db_connect_info = array();
$db_connect_info['host'] = $db_host;
$db_connect_info['user'] = $db_name;
$db_connect_info['passwd'] = $db_pass;
$db_connect_info['db'] = $database;

// species_id for sql query
$tbl_shp = $_GET["TableNameDst"];
list($swlat, $swlng) = explode(",", $_GET["sw"]);
list($nelat, $nelng) = explode(",", $_GET["ne"]);

print_r($swlat);
print_r($swlng);
print_r($nelat);
print_r($nelng);

$swlat = (float)$swlat;
$swlng = (float)$swlng;
$nelat = (float)$nelat;
$nelng = (float)$nelng;

$shp_geometry_list = get_sql_geometry_list($db_connect_info, $tbl_shp, $swlng, $nelng, $swlat, $nelat);
$geometry_list = array();
if(0 < count($shp_geometry_list))
{
	$wkt2PhpPolygon = new Wkt2PhpPolygon();
	for($i=0;$i<count($shp_geometry_list);$i++)
	{
		$geometry_info = $shp_geometry_list[$i];
		$primary_key = $geometry_info["primary_key"];
		$xmin = $geometry_info["xmin"];
		$xmax = $geometry_info["xmax"];
		$ymin = $geometry_info["ymin"];
		$ymax = $geometry_info["ymax"];
		$numparts = $geometry_info["numparts"];
		$numpoints = $geometry_info["numpoints"];
		$polygon = $geometry_info["ASTEXT(polygons)"];

		$jsonString = $wkt2PhpPolygon->convert2GeoJSON($polygon);	
		//array_push($geometry_list, $jsonString);
		array_push($geometry_list, $polygon);
	}
}



?>

<HTML>
<HEAD>
</HEAD>
<BODY>
<?php
	$numPolygon = count($shp_geometry_list);
	$polygonstr = implode(";", $geometry_list);
	echo "Number of polygons returned: ".$numPolygon.", ".$polygonstr;
	flush();
	@ob_flush();
?>
</BODY>
</HTML>
