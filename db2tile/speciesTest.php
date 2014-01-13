<?php

//require_once('./GoogleMapUtility.php');
//require_once("./dataUtils.inc.php");

//this script may require additional memory and time
set_time_limit(0);
ini_set('memory_limit', -1);


// species_id for sql query
$wss_db = 'wss';
$species_id = (int)$_GET['id'];
$Xname = $_GET['x'];
$Yname = $_GET['y'];
$Zname = $_GET['zoom'];

  $im  = imagecreatefrompng("./Screenshot.png");
  $black = imagecolorallocate($im,0,0,0);
  
  //write some info about the tile to the image for testing
  //imagestring($im,4,1,0, "$wss_db p ({$Xname},{$Yname}) @$Zname", $black);
  imagestring($im,4,1,60, "aux info = $species_id", $black);
  //imagestring($im,4,1,120, "IN:OUT=$num_filtered:$num_not_filtered_pt", $black);
  //output the new image to the file system and then send it to the browser
  imagepng($im,$file);
  
  header('content-type:image/png;');
  echo file_get_contents($file);
?>
