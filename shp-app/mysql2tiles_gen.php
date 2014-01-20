<?php

try
{
  require_once (dirname(__FILE__)."/mysql_utils.php");
  require_once (dirname(__FILE__)."/table_names.php");
  require_once (dirname(__FILE__)."/../db2tile/db_credentials.php");
  require_once (dirname(__FILE__)."/../db2tile/tile_colors.php");
  require_once (dirname(__FILE__)."/../db2tile/vizUtils.inc.php");
}
catch (Exception $e)
{
  echo "Caught".$e->getMessage()."</br>";
}
set_time_limit(3600*24*7); // a week.
ini_set('memory_limit',-1);

//echo "----DB login info:".$db_host.", ".$db_name.", ".$db_pass."</br>";

$db_connect_info = array();
$db_connect_info['host'] = $db_host;
$db_connect_info['user'] = $db_name;
$db_connect_info['passwd'] = $db_pass;
$db_connect_info['db'] = $database;

print_r($db_connect_info);
$geotype = $_GET["geotype"]; // either "pick" or "viz"
$pickorviz = $_GET["pickorviz"]; // either "pick" or "viz"


$tile_folder_name = extract_shp_name($filename);
$zoomLevelMin = 18;//3;
$zoomLevelMax = 18;//19;
$imgLimitPerLevel = 4000000; // check later why needed.

echo ": ".$filename."->shpname: ".$shp_name.", ".$table_name_shp." ".$table_name_dbf."</br>";
echo "Filename: ".$filename.", tbl1: ".$table_name_shp.", tbl2: ".$table_name_dbf."</br>";
echo "Pick or viz: ".$pickorviz."</br>";

$neighbor_bound = 0; // not used
geom_in_db_to_tile_range($db_connect_info, $table_name_shp, $table_name_dbf, $neighbor_bound, $tile_folder_name, $zoomLevelMin, $zoomLevelMax, $imgLimitPerLevel, $geotype, $pickorviz);

?>
