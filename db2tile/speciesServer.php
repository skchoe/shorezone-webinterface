<?php

require_once(dirname(__FILE__).'/../viz/GoogleMapUtility.php');
require_once(dirname(__FILE__).'/vizUtils.inc.php');

//this script may require additional memory and time
set_time_limit(3600);
ini_set('memory_limit',-1);

// species_id for sql query
$wss_db = 'wss';
$species_id = (int)$_GET['id'];
$Xname = $_GET['x'];
$Yname = $_GET['y'];
$zoom = $_GET['zoom'];
$folder = $_GET['folder'];

$geotype = $_GET['geotype'];
$pickorviz = $_GET['pickorviz'];
 
$shp_name = "wss";
generatePngFileFromDB($wss_db, $shp_name, $species_id, $Xname, $Yname, $zoom, $folder, $geotype, $pickorviz);


?>
