<?php

require_once(dirname(__FILE__)."/../../viz/GoogleMapUtility.php");
require_once(dirname(__FILE__)."/../../viz/dataUtils.inc.php");
require_once(dirname(__FILE__)."/../../shp-app/mysql_utils.php");
require_once(dirname(__FILE__)."/upload_utils.php");

//this script may require additional memory and time
set_time_limit(10);
ini_set('memory_limit',-1);

// species_id for sql query
$shpname = $_GET["ShapeName"];
$tblnameDst = $_GET["TableNameDst"];
$json_meta_names = $_GET["meta_names"];

echo "0000000000000000000000000000shp name: ".$shpname
