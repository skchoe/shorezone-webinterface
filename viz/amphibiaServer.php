<?php

require_once(dirname(__FILE__).'/GoogleMapUtility.php');
require_once(dirname(__FILE__)."/dataUtils.inc.php");
require_once(dirname(__FILE__)."/../db2tile/db_credentials.php");

//this script may require additional memory and time
//set_time_limit(3600);
ini_set('memory_limit',-1);


// species_id for sql query
$wss_db = $database;
$species_id = (int)$_GET['id'];
$Xname = $_GET['x'];
$Yname = $_GET['y'];
$Zname = $_GET['zoom'];

$file = '../tiles/SPECIES1109_DBO_VIEW_AMPHIBIANS/id_'.$species_id.'/'.$Zname.'/c_'.$species_id.'_'.$Xname.'_'.$Yname.'_'.$Zname.'.png';
//$file = '/wss_maps/viz/tiles/id_'.$species_id.'/'.$Zname.'/c_'.$species_id.'_'.$Xname.'_'.$Yname.'_'.$Zname.'.png';

//output the existing image to the browser
header('content-type:image/png;');
echo file_get_contents($file);

//check if the file already existsi
// As optimization, we skip the existence checking
/*
if(file_exists($file)) {

  //output the existing image to the browser
  header('content-type:image/png;');
  //$newimg = imageFileResize($file, GoogleMapUtility::TILE_SIZE);
  //imagepng($newimg);
  echo file_get_contents($file);

}
else { // file doesn't exist -> create by Tile info and {polygon}

  //get th lat/lng bounds of this tile from the utility function.
  // return abounds object with width, height, x, y.
  $tileRect = GoogleMapUtility::
    getTileRect((int)$Xname,
  	  (int)$Yname,
  	  (int)$Zname);

  ////create an array of the size for each marker at each zoom level
  //$file = 'tiles/c'.md5(
  //	serialize($markerSizes).
  //	serialize($tileRect).'|'.
  //	$_GET['x'].'|'.
  //	$_GET['y'].'|'.
  //	$_GET['zoom']).'.png';

  // corners of tiles
  $swlat=$tileRect->y;
  $nelat=$swlat+$tileRect->height;
  $swlng=$tileRect->x;
  $nelng=$swlng+$tileRect->width;

  //create a new image
  $im = imagecreate(GoogleMapUtility::TILE_SIZE,GoogleMapUtility::TILE_SIZE);

  $background = imagecolorallocate($im,0,0,0);
  imagefill($im,0,0,$background);
  imagecolortransparent($im, $background);

  //set up some colors for the markers.
  //each marker will have acolor based on the height of the tower
  $white = imagecolorallocate($im,255,255,255);
  $darkRed = imagecolorallocate($im,125,0,0);
  $red = imagecolorallocate($im,255,0,0);
  $darkGreen = imagecolorallocate($im,0,150,0);
  $green = imagecolorallocate($im,0,255,0);
  $lightgreen = imagecolorallocate($im,125,255,125);
  $darkBlue = imagecolorallocate($im,0,0,150);
  $blue = imagecolorallocate($im,0,0,255);
  $orange = imagecolorallocate($im,255,150,0);
  $white = imagecolorallocate($im, 255, 255, 255);  
  $black = imagecolorallocate($im,0,0,0);
  
  // Draw tile borders
  //drawTileBorders($im, $swlat, $nelat, $swlng, $nelng, (int)$Zname, $lightgreen);

  //////////////////////////////////////////////////////////
  // - extends-> linear plot of data points
  //list($source_gri, $xmin, $xmax, $ymin, $ymax, $polygonArrayGeo)
  //= getPhpPolygonFromDB($wss_db, $species_id, FALSE);

  //list($notFiltered, $filteredLinear, $filteredPolygonArrayGeo) 
	//= interiorPointsInTileRect($polygonArrayGeo, $swlat, $nelat, $swlng, $nelat);
  ////list($id, $Xmin, $lng $Xmax, $Ymin, $lat, $Ymax) 
	//= interiorPointsInTileRect($polygonArrayGeo, $swlat, $nelat, $swlng, $nelng);

  //// plot by part 
  //list($source_gri, $xmin, $xmax, $ymin, $ymax, $polygonArrayGeo)
  //= getPhpPolygonFromDB($wss_db, $species_id, TRUE); // returns multipolygon
  //list($notFiltered, $filteredLinear, $filteredPolygonArrayGeo)
  //= interiorMultiPointsInTileRect($polygonArrayGeo, $swlat, $nelat, $swlng, $nelng);

  //get the number of points in this tile
  $num_not_filtered_pt = sizeof($notFiltered) / 2;
  $num_filtered = sizeof($filteredLinear) / 2;
  
  ///////////////////////TEST//////////////////////////
  //testElipseOnMap($im, $countInTile, $filteredPolygonArrayGeo, 
	//(int)$Zname, $red, $orange, $black, $white, $markerSizes);
  //drawElipseOnMap($im, $num_filtered, $filteredPolygonArrayGeo, 
	//(int)$Zname, $red, $orange, $black, $white, $markerSizes);

  ///////////////////////GET data from DB//////////////////////////
  list($source_gri, $xmin, $xmax, $ymin, $ymax, $polygonArrayGeo)
	= getPhpPolygonFromDB($wss_db, $species_id, TRUE); // returns multipolygon

  // maintain cw/ ccw/ info for later rendering.
  $cwFlagArray = computeCWFlagArray ($polygonArrayGeo);
  //writeBooleanArrayToFile($cwFlagArray, $Xname."_".$Yname."output1.txt");

  $b_bbx_tile = ($swlng > $xmax) || ($nelng < $xmin) || 
	($nelat < $ymin) || ($swlat > $ymax);
  if(!$b_bbx_tile) {
    $eps = 0.0000000000001;
    list($notFiltered, $filteredLinear, $filteredPolygonArrayGeo, 
	$extraTilePieceArray, $filteredCWArray, $filteredExtraCWArray)
      = clipedMultiPolygonInTile($polygonArrayGeo, $cwFlagArray, 
	$swlat+$eps, $nelat-$eps, $swlng+$eps, $nelng-$eps);
   
   //writeBooleanArrayToFile($filteredCWArray, $Xname."_".$Yname."output2.txt");

    $polygonColor = $red; 
    //$transColor = $blue;
    $transColor = $background;
    $npart = 0;
    $npart = drawPolygonByPart($im, $extraTilePieceArray, 
    	$filteredExtraCWArray, (int)$Zname, $polygonColor, $transColor);
    $npart += drawPolygonByPart($im, $filteredPolygonArrayGeo, 
    	$filteredCWArray, (int)$Zname, $polygonColor, $transColor);
  
    //write some info about the tile to the image for testing
    //imagestring($im,2,1,1, "@$Zname", $black);
    //imagestring($im,4,1,0, "({$Xname},{$Yname}) @$Zname", $black);
    //$sz = sizeof($cwFlagArray);
    //imagestring($im,4,1,16, "polygarraygeo = $sz", $black);
  
    //output the new image to the file system and then send it to the browser
    //
    //// scale down by zoom factor : $zoom == 15 -> new size =>2^4.
    //$newSizeSmall = GoogleMapUtility::TILE_SIZE / pow(2, round(((int)$Zname+1)/4.0));
    //$imgsmall = imageGDResize($im, $newSizeSmall);
    //imagepng($imgsmall,$file);


    $folder_id = 'id_'.$species_id;
    $folder_zm = $Zname;

    if(!file_exists('tiles/'.$folder_id))
        mkdir ('tiles/'.$folder_id);

    if(!file_exists('tiles/'.$folder_id.'/'.$folder_zm))
        mkdir ('tiles/'.$folder_id.'/'.$folder_zm);
	
    imagepng($im,$file);
  
    header('content-type:image/png;');
    
    //$newimg = imageFileResize($file, GoogleMapUtility::TILE_SIZE);
    //imagepng($newimg);
    
    //imagepng($im);
    echo file_get_contents($file);

  }
  else { 
    // This case: Far from where polygons are -> just pick the blank image, draw.
    // wonder if not return anything.
    header('content-type:image/png;');
    $blank_filename = "./blank_image.png";
    echo file_get_contents($blank_filename);
  }
}
*/

/* moved dataUtiles.inc.php
function computeCWFlagArray($polygonArrayGeo) {
  $numpart = sizeof($polygonArrayGeo);

  //check if pag is array of array (parts)
  $cwArray = array();
  if($numpart > 1) {
    foreach ($polygonArrayGeo as $polygon) {
      $b = isCWNEW($polygon);
      if($b==TRUE) $cwArray[] = TRUE;
      else $cwArray[] = FALSE;
    }
    return $cwArray;
  }
  else {
    $bcw = isCWNEW($polygonArrayGeo);
    if($bcw==TRUE) $cwArray[] = TRUE;
    else $cwArray[] = FALSE;
    return $cwArray;
  }
}
*/
function writeBooleanArrayToFile($array, $filename) {
  $fp=fopen($filename, "w+");
  $str = "SIZE: ".sizeof($array)."\n";
  foreach($array as $key => $value) {
    if($value==TRUE)
	$str .="1\t";
    else
	$str .="0\t";
  }
  fwrite($fp, $str."\n");
}

function imageGDResize($sourceimg, $newSize) {

  // Resize
  imagecopyresized($newimg, $sourceimg, 0, 0, 0, 0, 
    $newSize, $newSize, 
    GoogleMapUtility::TILE_SIZE, GoogleMapUtility::TILE_SIZE);

  return $newimg;
}

function imageFileResize($file, $newSize) {

  list($width, $height) = getimagesize($file);

  // Load
  $newimg = imagecreatetruecolor($newwidth, $newheight);
  $source = imagecreatefrompng($file);

  // Resize
  imagecopyresized($newimg, $source, 0, 0, 0, 0, $newSize, $newSize, $width, $height);

  return $newimg;
}

/* moved dataUtiles.inc.php
function drawPolygonByPart($im, $filteredPolygonArrayGeo, $filteredCWArray, $z, $interiorColor, $holeColor) {
  $pixelOffsetPartArray = convertGeoPartArrayToPixelOffset($filteredPolygonArrayGeo, $z);
  $numPartArray = sizeof($pixelOffsetPartArray);
  $outnum = 0;

  $polygonColor = 0;

  //draw order : interior part 
  for($i=0;$i<$numPartArray;$i++) {
    if($filteredCWArray[$i] == TRUE) {
        // color Setting
        $polygonColor  = $interiorColor;

	$polygon = $pixelOffsetPartArray[$i];
	$numPoints = sizeof($polygon)/2;

	if($numPoints >= 3) {
	   imagefilledpolygon($im,
			  $polygon,
			  $numPoints,
			  $polygonColor);
	   $outnum++;
	}
	else $outnum--;
    }
  }
  //draw order : hole part
  for($i=0;$i<$numPartArray;$i++) {
    if($filteredCWArray[$i] != TRUE) {
        // color Setting
	$polygonColor = $holeColor;

	$polygon = $pixelOffsetPartArray[$i];
	$numPoints = sizeof($polygon)/2;

	if($numPoints >= 3) {
	   imagefilledpolygon($im,
			  $polygon,
			  $numPoints,
			  $polygonColor);
	   $outnum++;
	}
	else $outnum--;
    }
  }
  return $outnum;
}
*/
function drawElipseOnMap($im, $countElt, $filteredPolygonArrayGeo, $z, $red, $orange, $black, $white, $markerSizes) {
  
  $filled=array();
  if($countElt>0) {
	for($i=0;$i<$countElt;$i++) {

	  // get part
	  $pointArray = $filteredPolygonArrayGeo[$i];
	  $numpoints = sizeof($pointArray)/2;
	  for($j=0;$j<$numpoints;$j++){
		$k = 2*$j;
		//get the x,y coordinate of the marker in the tile
		$point = GoogleMapUtility::getPixelOffsetInTile($pointArray[$k+1],
														$pointArray[$k],
														$z);
	  
		//check if the marker was already drawn there
		if($filled["{$point->x},{$point->y}"]<2) {

		  //pick acolor based on the structure's height
		  $c = $red;

		  //if there is aready apoint there, make it orange
		  if($filled["{$point->x},{$point->y}"]==1) $c=$orange;

		  //get the size
		  $size = $markerSizes[$z];
		
		  //draw the marker
		  if($z<2) imagesetpixel($im, $point->x, $point->y, $c );
		  elseif($z<12) {
			imagefilledellipse($im, $point->x, $point->y, $size, $size, $c );
			imageellipse($im, $point->x, $point->y, $size, $size, $black );
		  } else {
			imageellipse($im, $point->x, $point->y, $size-1, $size-1, $c );
			imageellipse($im, $point->x, $point->y, $size-2, $size-2, $c );
			imageellipse($im, $point->x, $point->y, $size+1, $size+1, $white );
			imageellipse($im, $point->x, $point->y, $size, $size, $black );
		  }
		
		  //record that we drew the marker
		  $filled["{$point->x},{$point->y}"]++;
		}
	  }
	}
  }
}

function testElipseOnMap($im, $countInTile, $filteredPolygonArrayGeo, $z, $red, $orange, $black, $white, $markerSizes) {
  $filled=array();
  if($countInTile>0) {
	for($i=0;$i<$countInTile;$i++) {
	  $j = 2*$i;
	  //get the x,y coordinate of the marker in the tile
	  $point = GoogleMapUtility::getPixelOffsetInTile($filteredPolygonArrayGeo[$j],
													  $filteredPolygonArrayGeo[$j+1],
													  $z);
	  
	  //check if the marker was already drawn there
	  if($filled["{$point->x},{$point->y}"]<2) {

		//pick acolor based on the structure's height
		$c = $red;

		//if there is aready apoint there, make it orange
		if($filled["{$point->x},{$point->y}"]==1) $c=$orange;

		//get the size
		$size = $markerSizes[$z];
		
		//draw the marker
		if($z<2) imagesetpixel($im, $point->x, $point->y, $c );
		elseif($z<12) {
		  imagefilledellipse($im, $point->x, $point->y, $size, $size, $c );
		  imageellipse($im, $point->x, $point->y, $size, $size, $black );
		} else {
		  imageellipse($im, $point->x, $point->y, $size-1, $size-1, $c );
		  imageellipse($im, $point->x, $point->y, $size-2, $size-2, $c );
		  imageellipse($im, $point->x, $point->y, $size+1, $size+1, $white );
		  imageellipse($im, $point->x, $point->y, $size, $size, $black );
		}
		
		//record that we drew the marker
		$filled["{$point->x},{$point->y}"]++;
	  }
	}
  }
}

?>
