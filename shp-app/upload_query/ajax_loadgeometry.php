<?php
require_once(dirname(__FILE__)."/../../shp-app/mysql_utils.php");
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

$geometry_list = get_sql_geometry_list($db_connect_info, $tbl_shp, $swlng, $nelng, $swlat, $nelat);
if(0 < count($geometry_list))
{
	foreach($geometry_list as $geometryInfo)
	{
		$primary_key = $geometryInfo["primary_key"];
		$xmin = $geometryInfo["xmin"];
		$xmax = $geometryInfo["xmax"];
		$ymin = $geometryInfo["ymin"];
		$ymax = $geometryInfo["ymax"];
		$numparts = $geometryInfo["numparts"];
		$numpoints = $geometryInfo["numpoints"];
		$polygon = $geometryInfo["ASTEXT(polygons)"];
	}
}
?>

<HTML>
<HEAD>
</HEAD>
<BODY>
<?php
	$numPolygon = count($geometry_list);
	echo "Number of polygons returned: ".$numPolygon;
	flush();
	@ob_flush();
?>
</BODY>
</HTML>
