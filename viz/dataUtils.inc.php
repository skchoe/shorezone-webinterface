<?php

function getPhpPolygonFromDB($wss_db, $species_id, $bMultiPolygon) {

  //connect to the database
  require(dirname(__FILE__)."../db2tile/db_credentials.php");
  $conn = mysql_connect($db_host, $db_name, $db_pass);
  //if($conn==FALSE) echo "db connection failed<br/>";
  //else echo "db connected<br/>";
  
  $select_db = mysql_select_db("wss", $conn);
  //if($select_db==FALSE) echo "db_selection failed<br/>";
  //else echo "db selectedcted<br/>";

  $query_forall_tbls = "SHOW TABLES FROM ".$wss_db;
  $resulttbls = mysql_query($query_forall_tbls);
  if ($resulttbls==FALSE) {
	//echo "DB Error, could not list tables\n";
	//echo 'MySQL Error: ' . mysql_error();
	exit;
  }
  //else echo "table exists<br/>";

  $b_tbl_exist = FALSE;
  while ($row = mysql_fetch_row($resulttbls)) {
	//echo "Table--------------: {$row[0]}<br />";
	if ($species_dst_tbl == $row[0]) {
	  //echo "test table exists<br />";
	  $b_tbl_exist = TRUE;
	  break;
	}
	//else echo "{$row[0]} isn't same as {$species_dst_tbl}. <br />";
  }

  mysql_free_result($resulttbls);
  //if ($b_tbl_exist == TRUE) echo "dst_tbl found<br/>";
  //else echo "Couldn't find table ---> error<br />";


  $pricol_name = "source_gri";
  $species_dst_tbl = "species_dst_tbl";
  //list($source_gri, $xmin, $xmax, $ymin, $ymax, $arrayParts);
  return getPhpPolygonFromTable($species_dst_tbl, $pricol_name, $species_id, $bMultiPolygon);
}

function getPhpPolygonFromTable($species_dst_tbl, $pricol_name, $species_id, $bMultiPolygon)
{ 
  //Retrieve the points (wkt format) by record($species_id)
  $sql = "SELECT *
          FROM ".$species_dst_tbl."
          WHERE ".$pricol_name."=\"".$species_id."\"
          ORDER BY ".$pricol_name."\"";

  $result = mysql_query($sql);
  $num_resultquery = mysql_numrows($result);

  if($result != FALSE)

	for ($count=0; $count < mysql_numrows($result); $count++) {
	  $num_resultquery++;

	  $source_gri = mysql_result($result, $count, "source_gri");

	  $cm_name =  stripslashes(mysql_result($result, $count, "cm_name"));
	  $sc_name =  stripslashes(mysql_result($result, $count, "sc_name"));
	  $xmin = mysql_result($result, $count, "xmin");
	  $xmax = mysql_result($result, $count, "xmax");
	  $ymin = mysql_result($result, $count, "ymin");
	  $ymax = mysql_result($result, $count, "ymax");
	  $nprt = mysql_result($result, $count, "numparts");
	  
	  $sql_polygon = "SELECT ASTEXT(polygons)
                      FROM ".$species_dst_tbl."
                      WHERE source_gri=\"".$species_id."\"
                      ORDER BY source_gri";

	  $res_mp = mysql_query($sql_polygon);
	  $cnt1 = mysql_numrows($res_mp);
	  $res_poly = mysql_result($res_mp, 0, "ASTEXT(polygons)");
	  mysql_free_result($res_mp);
/*
	  echo "Species_id: $source_gri <br />
	  	Extent: ($xmin)($xmax)($ymin)($ymax) <br/>
	  	Numparts: $nprt <br/>
	  	Count: $cnt1 <br />";
*/
	  // WKT text -> array of arrays (x,y)
	  require_once("./WktUtils.inc.php");
	  $wkt2polygon = new Wkt2PhpPolygon();

	  $arrayParts = 0;
	  if($bMultiPolygon==TRUE)
                // arrayParts is an array of parts, part is ..., xi, yi, x(i+1), y(i+1),...
		$arrayParts = $wkt2polygon->convert($res_poly);
	  else
 		// ..., xi, yi, x(i+1), y(i+1),...
		$arrayParts = $wkt2polygon->convert2simplePointArray($res_poly);
	  
	  mysql_free_result($result);
	  mysql_close();
	  $returnV = array($source_gri,/*$cm_name, */
				   $xmin, $xmax, $ymin, $ymax, $arrayParts);
	  return $returnV;
	}
  else $num_resultquery = 0;

  mysql_free_result($result);
  mysql_close();
  return array($source_gri, 0, 0, 0, 0, 0, FALSE);
}

function interiorPointsInTileRect($polygonArrayGeo, $swlat, $nelat, $swlng, $nelng) {
  
  $filteredPolygonArrayGeo = array();
  $notfiltered = array();
  $numpoints = sizeof($polygonArrayGeo) / 2;

  for($i=0;$i<$numpoints;$i++) {

	$j = 2*$i;
	$lng = $polygonArrayGeo[$j]; 
	$lat = $polygonArrayGeo[$j+1];

	if($swlng <= $lng && $lng < $nelng &&
	   $swlat <= $lat && $lat < $nelat) {
		$filteredPolygonArrayGeo[] = $lng;
		$filteredPolygonArrayGeo[] = $lat;
	}
	else {
		$notfiltered[] = $lng;
		$notfiltered[] = $lat;
	}
  }
  return array($notfiltered, $filteredPolygonArrayGeo);
}

function interiorMultiPointsInTileRect($polygonArrayGeo, $swlat, $nelat, $swlng, $nelng) {
  
  $filteredPolygonArrayGeo = array();
  $numparts = sizeof($polygonArrayGeo);

  $notfiltered = array();
  $filteredLinear = array();

  for($i=0;$i<$numparts;$i++) {
	
	$pointArray = $polygonArrayGeo[$i];
	$numpoints = sizeof($pointArray)/2;

	$filteredPointArray = array();
	for($j=0;$j<$numpoints;$j++) {

	  $k = 2*$j;
	  $lng = $pointArray[$k]; 
	  $lat = $pointArray[$k+1];

	  if($swlng <= $lng && $lng < $nelng &&
		 $swlat <= $lat && $lat < $nelat) {
		$filteredPointArray[] = $lng;
		$filteredPointArray[] = $lat;
		$filteredLinear[] = $lng;
		$filteredLinear[] = $lat;
	  }
	  else {
		$notfiltered[] = $lng;
		$notfiltered[] = $lat;
	  }
	}
	$filteredPolygonArrayGeo[] = $filteredPointArray;
  }
  return array($notfiltered, $filteredLinear, $filteredPolygonArrayGeo);
}

function clipedMultiPolygonInTile($polygonArrayGeo, $cwFlagArray,
				  $swlat, $nelat, $swlng, $nelng) {

  $numparts = sizeof($polygonArrayGeo);
  
  // 1. Given multipolygon, create same sized array
  // with [0..N](intersect), [-1]FALSE(disjoint) where i\in[0..N] is idx of
  // first interior point.
  // called intersectArray
  $intersectArray = array();
  // initialize with FALSE(-1)
  for($u=0;$u<$numparts;$u++) {
	$intersectArray[] = -1; 
  }
  
  // find index of interior points (if nothing idx=-1)
  for($i=0;$i<$numparts;$i++) {
    $pointArray = $polygonArrayGeo[$i];
    $numpoints = sizeof($pointArray)/2;

    for($j=0;$j<$numpoints;$j++) {
      $k = 2*$j;
      $lng = $pointArray[$k];  // x 
      $lat = $pointArray[$k+1];// y

      //echo "tile_coord: x<".$swlng.", ".$nelng.">, y<".$swlat.", ".$nelat."</br>";
      //echo "given val: x=".$lng.", y=".$lat.";</br>";

      if($swlng <= $lng && $lng < $nelng &&
        $swlat <= $lat && $lat < $nelat) {
        $intersectArray [$i] = $j; // $j th point is in interior
        break;
      }
    }
  }

/*
  echo "IntersectArray [i]= v where i-th part, v index of point which in the tile first.: </br>";
  echo "v==-1 means no points of given part are in the tile.</br>";
  print_r($intersectArray);
  echo "</br>";
*/
  // 2. Traverse each elt in $intersectArray, find non-(-1) elt.

  //$filename = "in-out".$swlat."_".$nelat."_".$swlng."_".$nelng.".txt";
  //$fp = fopen ($filename, "w+");

  $clipedPartArray = array();
  $clipedCWArray = array();
  $extraIntersectArray = array();
  $extraCWArray = array();

  for($v=0;$v<$numparts;$v++) {
    $interior1 = $intersectArray[$v];
    if($interior1 != -1) { // has interior pt.in this tile
	  $newArrayFromInterior = arrayReindexCutEnd($polygonArrayGeo[$v], $interior1);
	  echo "NUM points = ".sizeof($newArrayFromInterior)."<br/>";
	  if($newArrayFromInterior!=FALSE) {
		$clipedPart = clipArrayByRect($newArrayFromInterior,
					  $swlat, $nelat, $swlng, $nelng);
		if($clipedPart != FALSE) {
		  $clipedPartArray[] = $clipedPart;
		  $clipedCWArray[] = $cwFlagArray[$v];
                }
		else return array(0, 0, "ERROR1", 0, 0, 0);
	  }
    }
    else {


///////// ......



// $interior1 == -1
          //   : either polygon/tile->disjoint
          //         or polygon/tile->intersect w/o no polypt in the tile
          //                        -> some/all tile corners are in polygon
   /*
	if($cwFlagArray[$v] != TRUE) { // check if CCW
	  $tileCCW = TRUE;
          // TRUE-> make CCW tileCorners
	  $tileCorners = convertLatLngToVertices($swlat, $nelat, $swlng, $nelng, $tileCCW); 
		
	  //1. For each corner of tile, set TRUE/FALSE --IN/OUT by winding number
	  $bCornerArray //$crossEdge = Array(v0, v1); - bCornerArray is same array as input $tileCorners
	    = detectInOutTileCorners($polygonArrayGeo[$v], $tileCorners);

	  if($bCornerArray[0]==TRUE && $bCornerArray[1]==TRUE && $bCornerArray[2]==TRUE && $bCornerArray[3]==TRUE) {
	    $extraIntersectArray[] = $tileCorners;
	    $extraCWArray[] = FALSE; // input zero
	  }
	}
   */ 
	if($cwFlagArray[$v] == TRUE) {// for CW polygon Array
	  $tileCCW = FALSE;
          // FALSE-> make CW tileCorners
	  $tileCorners = convertLatLngToVertices($swlat, $nelat, $swlng, $nelng, $tileCCW); 
		
	  //1. For each corner of tile, set TRUE/FALSE --IN/OUT by winding number
	  $bCornerArray //$crossEdge = Array(v0, v1); - bCornerArray is same array as input $tileCorners
	    = detectInOutTileCorners($polygonArrayGeo[$v], $tileCorners);

  //fwrite($fp, "corner = (".$tileCorners[0].", ".$tileCorners[1]."), (".$tileCorners[2].", ".$tileCorners[3]."),(".$tileCorners[4].", ".$tileCorners[5]."),(".$tileCorners[6].", ".$tileCorners[7].")\n");
  //fwrite($fp, "part_".$v."_".$bCornerArray[0]."_".$bCornerArray[1]."_".$bCornerArray[2]."_".$bCornerArray[3]."\n");

	  if($bCornerArray[0]==TRUE && $bCornerArray[1]==TRUE && $bCornerArray[2]==TRUE && $bCornerArray[3]==TRUE) {
	    $extraIntersectArray[] = $tileCorners;
	    $extraCWArray[] = TRUE;
	  }
	}
    }
  }
//fclose($fp);
  return array($notfiltered, $filteredLinear, $clipedPartArray, $extraIntersectArray, $clipedCWArray, $extraCWArray);
}

//http://local.wasp.uwa.edu.au/~pbourke/geometry/clockwise/index.htmlhttp://local.wasp.uwa.edu.au/~pbourke/geometry/clockwise/index.html
function isCWNEW($polygonRing) {
  //echo "before cut";
  //print_r($polygonRing);
  //echo "</br>";
  $newRing = removeHeadDupTail($polygonRing);
  //echo "after cut";
  //print_r($newRing);
  //echo "</br>";

  $numPts = count($newRing) / 2;
  if($numPts < 3) {
    echo "Num point for polygon is less than 3.</br>";
    return FALSE; //hope to skip processing this case, but just say it's CCW and let other module draw this case.
  }

  $area = signedPolygonArea($newRing);
  if($area < 0) return TRUE;  // area < 0 -> CW
  else return FALSE;           // > 0 -> CCW, =0 not draw (CCW is transparent)
}


//cut tail (x,y) if they are same as head (x, y).
//create new polygon
function removeHeadDupTail($polygon) {
  $num = sizeof($polygon);

  $tail_x = $polygon[$num-2];
  $tail_y = $polygon[$num-1];

  if ($tail_x != $polygon[0] || $tail_y != $polygon[1]) 
  {
    return $polygon;
  }
  else 
  {
    $resultArray = array();
    for($i=0;$i<$num-2;$i++) {
      $resultArray[] = $polygon[$i];
    }
    return $resultArray;
  }
}

// $polyddl is Doubly linked list with x y at tail is same those of head.
function removeHeadDupTailList($polyddl)
{
  $polyddl->deleteLastNode();
}

// $vPoints do not duplipcate head and tail points
// http://en.wikipedia.org/wiki/Polygon#Area_and_centroid
// Surveyor's Formula -> negative => CW
function signedPolygonArea ($vPoints) {
  $numPoints  = sizeof($vPoints)/2;
  $area = 0.0;
  for($i=0;$i<$numPoints;$i++) {
    $j = ($i+1) % $numPoints;

    $vix = $vPoints[2*$i];
    $viy = $vPoints[2*$i+1];
    $vjx = $vPoints[2*$j];
    $vjy = $vPoints[2*$j+1];
    $area += $vix * $vjy;
    $area -= $viy * $vjx;
    //echo "i=".$i." j=".$j.", area = ".$area."<br/>";
  }
  return $area/2.0;
}

function isCW($polygonRing) {
  $newArray = arrayReindexCutEnd($polygonRing, 0); // this only cut out last dup point

  $numpoints = sizeof($polygonRing) / 2;
  $miny = $polygonRing[1];
  $minidx = 0;
  for($i=0;$i<$numpoints;$i++) {
	$j = 2*$i;
	if($polygonRing[$j] < $miny) {
	  $miny = $polygonRing[$j];
	  $minidx = $i;
	}
  }

  $from = $minidx-2;
  if($minidx == 0) $from = 2*($numpoints-1);
  $to = $minidx+2;

  $v0 = array(($newArray[$minidx] - $newArray[$from]),
			  ($newArray[$minidx+1] - $newArray[$from+1]));
  $v1 = array(($newArray[$to] - $newArray[$minidx]),
			  ($newArray[$to+1] - $newArray[$minidx+1]));

  $extp = $v0[0] * $v1[1] - $v0[1] * $v1[0];

  if ($extp < 0 ) return TRUE;
  else return FALSE;
}


// $newArrayFromInterior
function clipArrayByRect($newArrayFromInterior,
			 $swlat, $nelat, $swlng, $nelng) {
  $initX = $newArrayFromInterior[0];
  $initY = $newArrayFromInterior[1];
  $bChk = ($swlng <= $initX && $initX < $nelng)
	&& ($swlat <= $initY && $initY < $nelat);
  
  if($bChk==TRUE) {
	$swlatBoundArray = clipSwLat($newArrayFromInterior, $swlat);
	$nelatBoundArray = clipNeLat($swlatBoundArray, $nelat);
	$swlngBoundArray = clipSwLng($nelatBoundArray, $swlng);
	$clipedPart = clipNeLng($swlngBoundArray, $nelng);
	return $clipedPart;
  }
  else {
	//echo "first point is not in side<br/>";
	return FALSE;
  }
}

function clipSwLat($array, $swlat) {
  $outputArray = array();
  
  $numpoints = sizeof($array)/2;
  $prev_x = $array[2*($numpoints-1)];
  $prev_y = $array[2*($numpoints-1)+1];

  for($i=0;$i<$numpoints;$i++) {
	$j=2*$i;
	$curr_x = $array[$j];
	$curr_y = $array[$j+1];

	if($swlat <= $curr_y) {
	  if($prev_y < $swlat) {
		list($tprev_x, $tprev_y)
		  = interp($prev_x, $prev_y, $curr_x, $curr_y,
				   ($swlat - $prev_y), ($curr_y - $swlat));
		$outputArray[] = $tprev_x; 
		$outputArray[] = $tprev_y;
	  }
	  $outputArray[] = $curr_x;
	  $outputArray[] = $curr_y;
	}
	else // $curr_y < $swlat
	  if($swlat <= $prev_y) {
		list($tcurr_x, $tcurr_y)
		  = interp($curr_x, $curr_y, $prev_x, $prev_y,
				   ($swlat - $curr_y), ($prev_y - $swlat));
		$outputArray[] = $tcurr_x;
		$outputArray[] = $tcurr_y;
	  }
	$prev_x = $curr_x;
	$prev_y = $curr_y;
  }
  return $outputArray;
}
function clipNeLat($array, $nelat) {
  $outputArray = array();
  
  $numpoints = sizeof($array)/2;
  $prev_x = $array[2*($numpoints-1)];
  $prev_y = $array[2*($numpoints-1)+1];

  for($i=0;$i<$numpoints;$i++) {
	$j=2*$i;
	$curr_x = $array[$j];
	$curr_y = $array[$j+1];

	if($curr_y < $nelat) {
	  if($nelat <= $prev_y) {
		list($tprev_x, $tprev_y)
		  = interp($curr_x, $curr_y, $prev_x, $prev_y,
				   ($nelat - $curr_y), ($prev_y - $nelat));
		$outputArray[] = $tprev_x; 
		$outputArray[] = $tprev_y;
	  }
	  $outputArray[] = $curr_x;
	  $outputArray[] = $curr_y;
	}
	else // $nelat <= $curr_y
	  if($prev_y < $nelat) {
		list($tcurr_x, $tcurr_y)
		  = interp($prev_x, $prev_y, $curr_x, $curr_y,
				   ($nelat - $prev_y), ($curr_y - $nelat));
		$outputArray[] = $tcurr_x;
		$outputArray[] = $tcurr_y;
	  }
	$prev_x = $curr_x;
	$prev_y = $curr_y;
  }
  return $outputArray;
}
function clipSwLng($array, $swlng) {
  $outputArray = array();
  
  $numpoints = sizeof($array)/2;
  $prev_x = $array[2*($numpoints-1)];
  $prev_y = $array[2*($numpoints-1)+1];

  for($i=0;$i<$numpoints;$i++) {
	$j=2*$i;
	$curr_x = $array[$j];
	$curr_y = $array[$j+1];

	if($swlng <= $curr_x) {
	  if($prev_x < $swlng) {
		list($tprev_x, $tprev_y)
		  = interp($prev_x, $prev_y, $curr_x, $curr_y,
				   ($swlng - $prev_x), ($curr_x - $swlng));
		$outputArray[] = $tprev_x; 
		$outputArray[] = $tprev_y;
	  }
	  $outputArray[] = $curr_x;
	  $outputArray[] = $curr_y;
	}
	else // $curr_x < $swlng
	  if($swlng <= $prev_x) {
		list($tcurr_x, $tcurr_y)
		  = interp($curr_x, $curr_y, $prev_x, $prev_y,
				   ($swlng - $curr_x), ($prev_x - $swlng));
		$outputArray[] = $tcurr_x;
		$outputArray[] = $tcurr_y;
	  }
	$prev_x = $curr_x;
	$prev_y = $curr_y;
  }
  return $outputArray;
}

function clipNeLng($array, $nelng) {
  $outputArray = array();
  
  $numpoints = sizeof($array)/2;
  $prev_x = $array[2*($numpoints-1)];
  $prev_y = $array[2*($numpoints-1)+1];

  for($i=0;$i<$numpoints;$i++) {
	$j=2*$i;
	$curr_x = $array[$j];
	$curr_y = $array[$j+1];

	if($curr_x < $nelng) {
	  if($nelng <= $prev_x) {
		list($tprev_x, $tprev_y)
		  = interp($curr_x, $curr_y, $prev_x, $prev_y,
				   ($nelng - $curr_x), ($prev_x - $nelng));
		$outputArray[] = $tprev_x; 
		$outputArray[] = $tprev_y;
	  }
	  $outputArray[] = $curr_x;
	  $outputArray[] = $curr_y;
	}
	else // $nelng < $curr_x
	  if($prev_x < $nelng) {
		list($tcurr_x, $tcurr_y)
		  = interp($prev_x, $prev_y, $curr_x, $curr_y,
				   ($nelng - $prev_x), ($curr_x - $nelng));
		$outputArray[] = $tcurr_x;
		$outputArray[] = $tcurr_y;
	  }
	$prev_x = $curr_x;
	$prev_y = $curr_y;
  }
  return $outputArray;
}

function interp($small_x, $small_y, $big_x, $big_y, $dist_0, $dist_1) {
  $dist0 = abs($dist_0);
  $dist1 = abs($dist_1);
  
  $sum = $dist0 + $dist1;
  if($sum != 0)
	$val = array(($small_x*$dist1 + $big_x*$dist0)/$sum,
				 ($small_y*$dist1 + $big_y*$dist0)/$sum);
  else $val = array(($small_x+$big_x)/2.0, ($small_y+$big_y)/2.0);

  return $val;
}

function arrayReindexCutEnd($inArray, $intPtIdx) {
  $numPoint = sizeof($inArray)/2;
  
  if($intPtIdx < $numPoint-1) { // $numPoint-1: number of non-dup. pts.
	$newArray = array();
	for($i=$intPtIdx;$i<$numPoint-1;$i++) {
	  $j = $i*2;
	  $newArray [] = $inArray[$j];
	  $newArray [] = $inArray[$j+1];
	}
	for($i=0;$i<$intPtIdx;$i++) {
	  $j = $i*2;
	  $newArray [] = $inArray[$j];
	  $newArray [] = $inArray[$j+1];
	}
	return $newArray;
  }
  else
	return FALSE;
}

/*
 list($c0, $c1, $c2, $c3) = convertLatLngToVertices($swlat, $nelat, $swlng, $nelng);
 //1. For each corner of tile, set TRUE/FALSE --IN/OUT by winding number
 list($binTL, $binTR, $binBL, $binBR, $crossEdge) //$crossEdge = Array(v0, v1);
 = detectInOutTileCorners($polygonArrayGeo[$v], $tileCorners);
 $extraIntersect = computeTilePieceInPolygon($tileCorners,
 $binTL, $binTR, $binBL, $binBR, $crossEdge);
*/

function convertLatLngToVertices($swlat, $nelat, $swlng, $nelng, $b_ccw) {
  $ccwTileCorners = array($nelng, $nelat, $swlng, $nelat, $swlng, $swlat, $nelng, $swlat);
  $cwTileCorners = array($swlng, $nelat, $nelng, $nelat, $nelng, $swlat, $swlng, $swlat);

  if($b_ccw==TRUE)
    return $ccwTileCorners;
  else
    return $cwTileCorners;
}

function detectInOutTileCorners($polygon, $corners) {
  $temp_vert = convertPhpPolygonToArrayOfArray($polygon);
  $vertices = removeTailDuplicate($temp_vert); //makesure no dup.
  
  $b0 = isInside(array($corners[0], $corners[1]), $vertices);
  $b1 = isInside(array($corners[2], $corners[3]), $vertices);
  $b2 = isInside(array($corners[4], $corners[5]), $vertices);
  $b3 = isInside(array($corners[6], $corners[7]), $vertices);

  $returnArray = array($b0, $b1, $b2, $b3);
  return $returnArray;
}

function removeTailDuplicate($in_vertices) {
  $num = sizeof($in_vertices);
  $vBegin = $in_vertices[0];
  $xbegin = $vBegin[0];
  $ybegin = $vBegin[1];
  $vEnd = $in_vertices[$num-1];
  $xend = $vEnd[0];
  $yend = $vEnd[1];

  if($xend == $xbegin && $yend == $ybegin) {
	$out_vertices = array();
	for($i=0;$i<$num-1;$i++)
	  $out_vertices [] = $in_vertices[$i];
	return $out_vertices;
  }
  else return $in_vertices;
}

function convertPhpPolygonToArrayOfArray($polygon) {
  $vertices = array();
  for($i=0;$i<sizeof($polygon)/2;$i++) {
	$j = 2*$i;
	$point = array($polygon[$j], $polygon[$j+1]);
	$vertices [] = $point;
  }
  return $vertices;
}

// Under no condition on vertices orientations 
// inside -> output 1
// outside -> output 0, boundary -> 0 or 1.
//http://ozviz.wasp.uwa.edu.au/~pbourke/geometry/insidepoly/
function isInside($p, $polygon) {
  $size = sizeof($polygon);
  $result = FALSE;

  $xp = $p[0];
  $yp = $p[1];
  
  $angle = 0;
  // counter clock wise traverse
  // i <- j, j=i-1 in clockwise
  for($i=$size-1;$i>=0;$i--) {
	$j = $i-1;
	if($i==0) $j=$size-1;
	$pg_i = $polygon[$i];
	$pg_j = $polygon[$j];
	$p1 = array($pg_i[0] - $xp, $pg_i[1] - $yp);
	$p2 = array($pg_j[0] - $xp, $pg_j[1] - $yp);

	$currentAngle = angle2D($p1[0], $p1[1], $p2[0], $p2[1]);
	$angle += $currentAngle;
        //echo "current angle (".$pg_i[0].", ".$pg_i[1].") ~ (".$pg_j[0].", ".$pg_j[1].") = ".$currentAngle."<br/>";
  }

  if(abs($angle) < pi()) {
    return FALSE;
  }
  else {
    return TRUE;
  }
}

function angle2D($x1, $y1, $x2, $y2) {
  
  $theta1 = atan2($y1, $x1);
  $theta2 = atan2($y2, $x2);

  $delta = $theta2 - $theta1;
  //echo "theta1 = ".$theta1.", theta2 = ".$theta2."<br/>";
  while ($delta > pi())
	$delta -= 2*pi();
  while ($delta < -1 * pi())
	$delta += 2*pi();

  return $delta;
}

function windingNumberDLL($v, $dll)
{
  $pairarray = array();

  $node = $dll->getFirstNode();
  // While loop doens't include dup-head.
  while($node->next != FALSE) {
    $ptarray = array($node->x, $node->y);
    array_push($pairarray, $ptarray);
    $node = $node->next;
  }
  //print_r($pairarray); echo "</br>";
  return windingNumber($v, $pairarray);
}

// If $wktoply is CCW, winding number is positive
// else negative.
// Degerate case?($v is on edge of polygon) = returns FALSE.
function windingNumberWkt($v, $wktpoly)
{
  $pairarray = array();
  $num_pt = count($wktpoly)/2;

  for($i = 0 ; $i < $num_pt ; $i++) {
    $j = 2*$i;
    $ptarray = array();
    $ptarray[0] = $wktpoly[$j]; 
    $ptarray[1] = $wktpoly[$j+1];

    array_push($pairarray, $ptarray);
  }
  //print_r($pairarray);
  return windingNumber($v, $pairarray);
}

// polygon array( array(x,y) ...)
// same semantics as windiingNumberWkt.
function windingNumber($v, $polygon) {// polygon doesn't have dup-points inside,
                                      // array of array(x,y)
		
  $len = sizeof($polygon);

  // check if two ends are same coordinates:
  $head = $polygon[0];
  $tail = $polygon[$len-1];

  if(($head[0] == $tail[0]) && ($head[1] == $tail[1]))
    $len_polygon = $len - 1; 
  else
    $len_polygon = $len;
    

  $wn = 0;    // the winding number counter

  // loop through all edges of the polygon
  //echo "Num pt in polygon = ".$len_polygon."</br>";
  //print_r($polygon);
  //echo "</br>";

  for ($i=0; $i<$len_polygon; $i++) {   // edge from polygon[i] to polygon[i+1]
    $j=$i+1;
    if($j==$len_polygon) $j=0;
	
    //echo "XY position :(".$v[0].", ".$v[1].", in Y direction(".$polygon[$i][1].", ".$polygon[$j][1].")</br>";  
    if ($polygon[$i][1] <= $v[1]) {     // start y <= P.y
      if ($polygon[$j][1] > $v[1]) {      // an upward crossing
        $b_left = isLeft( $polygon[$i], $polygon[$j], $v);
        if ($b_left > 0)  // P left of edge
          ++$wn;          // have a valid up intersect
      }
    }
    else if($v[1] < $polygon[$i][1]) {
      if ($polygon[$j][1] <= $v[1]) {    // a downward crossing
        $b_left = isLeft( $polygon[$i], $polygon[$j], $v);
        if ($b_left < 0)  // P right of edge
          --$wn;          // have a valid down intersect
      }
    }
    // else // equal case doesn't affect winding number.
    //echo "poly (".$polygon[$i][0].", ".$polygon[$i][1].") --> (".$polygon[$j][0].", ".$polygon[$j][1].") for pt (".$v[0].", ".$v[1].")</br>";
    //echo "current wn[".$i."] = ".$wn."</br>";
  }

  return $wn;
}

// isLeft>0 TRUE, isLeft<0 FALSE, isLeft==0 on the edge
function isLeft($startV, $endV, $position) {
  $exp = ($startV[0] - $position[0]) * ($endV[1] - $position[1])
	- ($startV[1] - $position[1]) * ($endV[0] - $position[0]);
 
  //echo "ISLEFT: (".$startV[0].", ".$startV[1].") -> (".$endV[0].", ".$endV[1].") for pt (".$position[0].", ".$position[1].") = ".$exp."</br>";
  return $exp;
}

function drawFirst3PolygonByPart($im, $filteredPolygonArrayGeo, $z, $red) {

  $numParts = sizeof($filteredPolygonArrayGeo);

  for($i=0;$i<$numParts;$i++) {
	
	$pointArray = $filteredPolygonArrayGeo[$i];
	$numPoints = sizeof($pointArray)/2;
	if($numPoints >= 3) {
  
	  $partArray = array();
	  $k=0;
	  $point0 = GoogleMapUtility::getPixelOffsetInTile($pointArray[$k+1],
													   $pointArray[$k],
													   $z);
	  $partArray[] = $point0->x;
	  $partArray[] = $point0->y;
	  
	  $l=2*($k+1);
	  $point1 = GoogleMapUtility::getPixelOffsetInTile($pointArray[$l+1],
													   $pointArray[$l],
													   $z);
	  $partArray[] = $point1->x;
	  $partArray[] = $point1->y;

  
	  $m=2*($k+2);
	  $point2 = GoogleMapUtility::getPixelOffsetInTile($pointArray[$m+1],
													   $pointArray[$m],
													   $z);
	  $partArray[] = $point2->x;
	  $partArray[] = $point2->y;

	  imagefilledpolygon($im,
				   $partArray,
				   3,
				   $red);
	}
  }
}

function convertPolygonToPixelOffset($part_geo, $zoom)
{
  $pixelOffsetArray = array();
  $numPoints = count($part_geo) / 2;

  $current_px = GoogleMapUtility::getPixelOffsetInTile($part_geo[1], $part_geo[0], $zoom);
  $cpx_x = $current_px->x;
  $cpx_y = $current_px->y;
  $pixelOffsetArray[] = $cpx_x;
  $pixelOffsetArray[] = $cpx_y;

  for($l=1;$l < $numPoints;$l++) 
  {
    $m = 2*$l;
    $pixelOffset = GoogleMapUtility::getPixelOffsetInTile($part_geo[$m+1], $part_geo[$m], $zoom);

    $px = $pixelOffset->x;
    $py = $pixelOffset->y;

    //if($cpx_x != $px || $cpx_y != $py) {
    //  $cpx_x = $px;
    //  $cpx_y = $py;
    //}
    $pixelOffsetArray[] = $px;
    $pixelOffsetArray[] = $py;
  }
  return $pixelOffsetArray;
}

// convert x,y  in array ( array (x,y,...) ...) into pixel offsets
// maintain same structure
function convertGeoPartArrayToPixelOffset($polygonArrayGeo, $zoom) {
  
  $numParts = sizeof($polygonArrayGeo);
  $pixelOffsetPartArray = array();
  
  for($k=0;$k<$numParts;$k++) {
    $pixelOffsetPartArray[] = convertPolygonToPixelOffset($polygonArrayGeo[$k], $zoom);
  }

  return $pixelOffsetPartArray;
}


function drawTileBorders($im, $swlat, $nelat, $swlng, $nelng, $zoom, $c) 
{
  //echo "latlng bound = $swlat, $nelat, $swlng, $nelng </br>";
  $e = .010001;
  $g = array();
  $g[] = $swlng; $g[] = $nelat; // 0 0
  $g[] = $nelng; $g[] = $nelat; // 1 0
  $g[] = $nelng; $g[] = $swlat; // 1 1 
  $g[] = $swlng; $g[] = $swlat; // 0 1
  $g[] = $swlng; $g[] = $nelat; // 0 0

  $width_2 = ($nelng - $swlng)/2.0;
  $height_2 = ($nelat - $swlat)/2.0;

  $ge = array();
  /*
  $ge[] = $swlng;          $ge[] = $nelat; // 0 0
  $ge[] = $swlng+$width_2; $ge[] = $nelat; // 1 0
  $ge[] = $nelng-$width_2; $ge[] = $nelat-$height_2; // 1 1 
  $ge[] = $swlng;          $ge[] = $nelat-$height_2; // 0 1
  $ge[] = $swlng;          $ge[] = $nelat; // 0 0
  */

  $ge[] = $swlng+$e; $ge[] = $nelat-$e; // 0 0
  $ge[] = $nelng-$e; $ge[] = $nelat-$e; // 1 0
  $ge[] = $nelng-$e; $ge[] = $swlat+$e; // 1 1 
  $ge[] = $swlng+$e; $ge[] = $swlat+$e; // 0 1
  $ge[] = $swlng+$e; $ge[] = $nelat-$e; // 0 0
  
  $p = array();
  $numBorder = sizeof($g)/2; //- 5 points

  for($i=0;$i<$numBorder;$i++) {
	  $j=2*$i;
    $point = GoogleMapUtility::getPixelOffsetInTile(
      $g[$j+1],
		  $g[$j],
			$zoom);
	  $p[] = $point->x;
	  $p[] = $point->y;
	}
  

  for($k=0;$k<$numBorder;$k++) {
	  $l=2*$k;
	  $point = GoogleMapUtility::getPixelOffsetInTile($ge[$l+1],
													$ge[$l],
													$zoom);
	  $p[] = $point->x;
	  $p[] = $point->y;
	}

  //print_r($p);
  //echo "</br>";
  imagepolygon($im, $p, 5, $c);
}
  

// multipolygon (array of polygon) -> array of boolean
// check CW or not. TRUE if CW.
function computeCWFlagArray($polygonArrayGeo) {
  $numpart = count($polygonArrayGeo);
  //echo "computeCWFlagArray: numpart:".$numpart."</br>";

  //check if pag is array of array (parts)
  $cwArray = array();
  for($cnt = 0; $cnt < $numpart ; $cnt++)
  { 
    $b = isCWNEW($polygonArrayGeo[$cnt]);
    if($b==TRUE) $cwArray[] = TRUE;
    else $cwArray[] = FALSE;
  }
  return $cwArray;
}


// output array of 
// array['prev'] = (prev_x, prev_y)
// array['interior'] = (vx1, vy1, ..... vxK, vyK).. array of interior points
// array['next'] = (next_x, next_y).
function decompose_into_subpolygons($gdpoly, $cw_flag, $swlat, $nelat, $swlng, $nelng, $ini_idx)
{
  // $arr_in_out is array of boolean (TRUE if (x,y) is in the tile).
  $arr_in_out = array();
  $numcoord = count($gdpoly);
  $num_pts = $numcoord / 2;
  for($i = 0 ; $i < $num_pts ; $i++) {
    $j = 2 * $i;
    $x = $gdpoly[$j];
    $y = $gdpoly[$j+1];
    $arr_in_out[] = in_tile($x, $y, $swlat, $nelat, $swlng, $nelng);
  }
  //echo "IN_OUT array:</br>";
  //print_r($arr_in_out);
  //echo "</br>";

  $arr_intileset = array();
  for($k = 0 ; $k < $num_pts ; $k++) {
    $b_in_out = $arr_in_out[$k];

    if($b_in_out) {
      if($k == 0) $prev_k = $num_pts - 1;
      else $prev_k = $k - 1;
  
      if($k == $num_pts - 1) $next_k = 0;
      else $next_k = $k + 1;
      
      $b_io_prev = $arr_in_out[$prev_k];
      $b_io_next = $arr_in_out[$next_k];

      $l = 2 * $k;
      if($k==0) $prev_coidx = $numcoord - 2;
      else $prev_coidx = $l - 2;
   
      if($k==$num_pts - 1) $next_coidx = 0;
      else $next_coidx = $l + 2;

      if(!$b_io_prev) {
        $int_arr_info = array();
        $int_arr = array();
        $int_arr_info['prev'] = array($gdpoly[$prev_coidx], $gdpoly[$prev_coidx + 1]);
      }

      $int_arr[] = $gdpoly[$l];
      $int_arr[] = $gdpoly[$l+1];

      if(!$b_io_next) {
        $int_arr_info['interior'] = $int_arr;
        $int_arr_info['next'] = array($gdpoly[$next_coidx], $gdpoly[$next_coidx + 1]);
        $arr_intileset[] = $int_arr_info;
      }
    }
  }
  //echo "ARR In-Tile SET : </br>";
  //print_r ($arr_intileset);
  //echo "</br>";

  return $arr_intileset;
}

function drawPolygonByPart($im, $filteredPolygonArrayGeo, $filteredCWArray, $z, $interiorColor, $holeColor) 
{
  $pixelOffsetPartArray = convertGeoPartArrayToPixelOffset($filteredPolygonArrayGeo, $z);
//echo "PixelOffsetPart:</br>";
//print_r($pixelOffsetPartArray);
//echo "</br>";
//echo "filteredCWArray:</br>";
//print_r($filteredCWArray);
//echo "</br>";

  $numPartArray = sizeof($pixelOffsetPartArray);
  $outnum = 0;

  //$boolCW = TRUE; // CW major
  $boolCW = TRUE; // CCW major

  //draw order : interior part
  for($i=0;$i<$numPartArray;$i++) {
    $outnum += draw_px_polygon ($im, $poxelOffsetPartArray[$i], $filteredCWArray[$i], 
                                $interiorColor, $holeColor, $boolCW, $outnum);
  }
 
  return $outnum;
}

function draw_px_polygon($im, $px_poly, $cw_flag, $interiorColor, $holeColor, $major_cw, $outnum)
{
  $num_pt = count($px_poly) / 2;

  if($major_cw == $cw_flag)
    $color = $interiorColor;
  else
    $color = $holeColor;

  imagefilledpolygon($im, $px_poly, $num_pt, $color);
}

function draw_gd_polygon ($gd_polygon, $zoom, 
         $im, $cw_flag, $interiorColor, $holeColor, $major_cw, $outnum)
{
  $px_polygon = convertPolygonToPixelOffset($gd_polygon, $zoom);
  /*
  echo "draw_gd_polygon w/ polygon size = ".count($gd_polygon).", px_polygonsize:".count($px_polygon)."</br>";
  echo "gd_polygon: ";
  print_r($gd_polygon); echo "</br>";
  echo "px_polygon: ";
  print_r($px_polygon); echo "</br>";
  */
  draw_px_polygon($im, $px_polygon, $cw_flag, $interiorColor, $holeColor, $major_cw, $outnum);
}

function eliminate_tooclose_points($polygonArrayGeo, $threshold)
{
  $part_array = Array();
  for($prt = 0 ; $prt < count($polygonArrayGeo) ; $prt++)
  {
    $ring_array = Array();
    $ring = $polygonArrayGeo[$prt];
    $ini_x = $ring[0];
    $ini_y = $ring[1];
    $ring_array[] = $ini_x;
    $ring_array[] = $ini_y;
    for ($pt = 1 ; $pt < count($ring) / 2 ; $pt++)
    {
      $i = 2 * $pt;
      $curr_x = $ring[$i];
      $curr_y = $ring[$i+1];

      $bv = (abs ($ini_x-$curr_x) < $threshold &&
         abs ($ini_y-$curr_y) < $threshold); // TRUE => they are too close

      if(!$bv) {
        $ring_array[] = $curr_x;
        $ring_array[] = $curr_y;

        $ini_x = $curr_x;
        $ini_y = $curr_y;
      }
    }
    $part_array[] = $ring_array;
  }

  return $part_array;
}
?>
