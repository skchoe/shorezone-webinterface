<?php

// create image.
$my_img_width = 800;
$my_img_height = 800;

$species_id = $_GET['species'];
require_once(dirname(__FILE__).'/../db2tile/db_credentials.php');
$wss_db = $database;
require_once(dirname(__FILE__)."/dataUtils.inc.php");
list($source_gri, /*$name_string, */$xmin, $xmax, $ymin, $ymax, $polygonArrayGeo) 
= getPhpPolygonFromDB($wss_db, $species_id, TRUE);
//echo "xmin,ymin = (".$xmin.", ".$ymin."), xmax,ymax = (".$xmax.", ".$ymax.")<br/>";

$polygonArrayPixel = convertGeo2LatLngPixel($xmin, $xmax, $ymin, $ymax, $polygonArrayGeo, $my_img_width, $my_img_height);
//$polygonArray = getExamplePolygonArray();

// GD Library routines
$my_img = imagecreate( $my_img_width, $my_img_height );
$background = imagecolorallocate( $my_img, 0, 128, 255 );
$text_colour = imagecolorallocate( $my_img, 255, 255, 255 );
$line_colour = imagecolorallocate( $my_img, 255, 128, 128 );
//imagestring( $my_img, 6, 10, 15, $name_string, $text_colour );
//imagesetthickness ( $my_img, 1 );

foreach ($polygonArrayPixel as $polygon) {
  imagefilledpolygon( $my_img,
					  $polygon,
					  sizeof($polygon)/2,
					  $line_colour );
}

header( "Content-type: image/png" );
imagepng( $my_img );

imagecolordeallocate( $my_img, $line_color );
imagecolordeallocate( $my_img, $text_color );
imagecolordeallocate( $my_img, $background );
imagedestroy( $my_img );



function convertGeo2LatLngPixel($xmin, $xmax, $ymin, $ymax, $polygonArrayGeo, $img_width, $img_height) 
{
  $polygonArrayPixel = array();
  $numPolygons = sizeof($polygonArrayGeo);

  $width = $xmax - $xmin;
  $height = $ymax - $ymin;
  
  //echo "width, height = (".$width.", ".$height.")<br/>";
  //echo "img_width, img_height = (".$img_width.", ".$img_height.")<br/>";
  //echo "numPly = ".$numPolygons."<br/>";
  
  $ratio_width = $img_width / $width;
  $ratio_height = $img_height / $height;
  
  for($i=0;$i<$numPolygons;$i++) {
	$polygonPixel = array();

	$polygon = $polygonArrayGeo[$i];
	$numCoords = sizeof($polygon);
	$numPoints = $numCoords/2;
	for($j=0 ; $j < $numPoints ; $j++) {
	  $k = $j*2;
	  $gx = $polygon[$k];
	  $gy = $polygon[$k+1];
	  
	  $Lng = ($gx - $xmin) * $ratio_width;
	  $Lat = ($gy - $ymin) * $ratio_height;
	  //echo "gx, gy = (".$gx.", ".$gy."), Lat, Lng = (".$Lat.", ".$Lng.")<br/>";
	  $polygonPixel[] = $Lng;
	  $polygonPixel[] = $Lat;
	}
	
	$polygonArrayPixel[] = $polygonPixel;
  }
	
  return $polygonArrayPixel;
}

?>
