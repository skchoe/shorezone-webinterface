<?php

ini_set('memory_limit',-1);

$xmin = $_GET['xmin'];
$xmax = $_GET['xmax'];
$ymin = $_GET['ymin'];
$ymax = $_GET['ymax'];

if($xmax=='')
  echo "X(".$xmin.", NONE), Y(".$ymin.", ".$ymax.")<br/><br/><br/>";
else
  echo "X(".$xmin.", ".$xmax.") Y(".$ymin.", ".$ymax.")<br/><br/><br/>";

/*
$Xname = $_GET['x'];
$Yname = $_GET['y'];
$Zname = $_GET['zoom'];
*/
//get th lat/lng bounds of this tile from the utility function.
// return abounds object with width, height, x, y.

 require_once(dirname(__FILE__).'/GoogleMapUtility.php');
/*
 $tileRect = GoogleMapUtility::
  getTileRect((int)$Xname,
			  (int)$Yname,
			  (int)$Zname);
*/
$zoomLevels = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18);
//$zoomLevels = array(0, 1, 2, 3, 4, 5, 6, 7, 8);
$maxZoomLevel = sizeof($zoomLevels);
generateTileNumbers($zoomLevels, $xmin, $xmax, $ymin, $ymax);

/*
for($i=0;$i<sizeof($tileNumArray);$i++) {
  $arrZ = $tileNumArray[$i];
  for($j=0;$j<sizeof($arrZ);$j++) {
	$arrX = $arrZ[$j];
	for($k=0;$k<sizeof($arrX);$k++) {
	  echo "(".$arrX[$k][0].", ".$arrX[$k][1].") ";
	}
	echo "<br/>";
  }
  echo "<br/>";
  }
*/
function generateTileNumbers($zoomLevels, $xmin, $xmax, $ymin, $ymax) {

  foreach($zoomLevels as $z) {

	/*
	//operation on given zoom level
	$dim_x = pow(2, $z);
	$dim_y = $dim_x;
	
	$tileNumZ = array();
	$tileCornerZ = array();
	
	for($i=0;$i<$dim_y;$i++) {
	  $arrayNumY = array();
	  $tileCornerY = array();
	  
	  for($j=0;$j<$dim_x;$j++) {
		$arrayNumY [] = array($i, $j);

		$tileRect = GoogleMapUtility::
		  getTileRect((int)$j,
					  (int)$i,
					  (int)$z);
		$tileCornerY [] = $tileRect;
		
	  }
	  $tileNumZ [] = $arrayNumY;
	  $tileCornerZ [] = $tileCornerY;
	}
	
	// print tile index, corresponding geo coordinates
	echo "----------".$z."---------------<br/>";
	for($i0=0;$i0<sizeof($tileNumZ);$i0++) {
	  $arrY = $tileNumZ[$i0];
	  $cornY = $tileCornerZ[$i0];
	  for($j0=0;$j0<sizeof($arrY);$j0++)
		echo "<".$arrY[$j0][0].", ".$arrY[$j0][1]."> ";
	  echo "<br/>";
	  for($j1=0;$j1<sizeof($cornY);$j1++) {
		$tr = $cornY[$j1];
		$swlat=$tr->y;
		$nelat=$swlat+$tr->height;
		$swlng=$tr->x;
		$nelng=$swlng+$tr->width;

		echo "(".$swlng.",".$nelng.",".$swlat.",".$nelat.") ";
	  }
	  echo "<br/>";
	}
	echo "<br/>";
	
	unset($tilsNumZ);
	unset($tilsCornerZ);
	*/

	// find minimal curvering subtiles
	$nwTile = GoogleMapUtility::toTileXY($ymax, $xmin, $z);
	$seTile = GoogleMapUtility::toTileXY($ymin, $xmax, $z);

	$pixelx = 256*pow(2,$z);
	$pixely = 256*pow(2,$z);
	echo "Zoom - ".$z." Bounding interval - Pxl(".$pixelx.", ".$pixely.") X (".$nwTile->x.", ".$seTile->x."), for <".$xmin.", ".$xmax."> ";
	echo "Y (".$nwTile->y.", ".$seTile->y.") for <".$ymin.", ".$ymax."><br/>";
  }
  
}

?>
