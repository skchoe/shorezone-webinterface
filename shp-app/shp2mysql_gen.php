<?php

set_time_limit(3600*24*3);
ini_set('memory_limit', -1);
while (ob_get_level()) ob_end_flush();

// SHAPE FILE ACCESS
try {
require_once (dirname(__FILE__)."/../build_db/ShapeFile.inc.php");
require_once (dirname(__FILE__)."/../build_db/WktUtils.inc.php");
require_once (dirname(__FILE__)."/mysql_inserts.php");
require_once (dirname(__FILE__)."/table_names.php");
require_once (dirname(__FILE__)."/../db2tile/db_credentials.php");
}
catch (Exception $e)
{
  echo "Caught", $e->getMessage() , "</br>";
}

$geotype = $_GET["geotype"]; // MultiPoint, MultiLineString, MultiPolygon

$options = array('noparts' => false);

echo "Filename: ".$filename.", tbl1: ".$table_name_shp.", tbl2: ".$table_name_dbf.", Geo Type: ".$geotype."</br>";

//echo "----DB login info:".$db_host.", ".$db_name.", ".$db_pass.", ".$database."</br>";
$db_connect_info = array();
$db_connect_info['host'] = $db_host;
$db_connect_info['user'] = $db_name;
$db_connect_info['passwd'] = $db_pass;
$db_connect_info['db'] = $database;
 
//print_r($db_connect_info);

// table creation
$shp_meta = new ShapeFile($filename, $options);
list ($db, $tbl_dst, $tbl_dbf, $pri_key_name) = connect_db_create_tbl($db_connect_info, $table_name_shp, $table_name_dbf, $shp_meta, $geotype);

// table insertion
$shp = new ShapeFile($filename, $options);
echo "Shape obj for $filename is created</br>";
list($tbl_name_shp, $tbl_name_dbf) = shp2insertquery($shp, $geotype, $tbl_dst, $tbl_dbf, $pri_key_name);

mysql_close();
echo "Database insertion completed.</br>";
?>
