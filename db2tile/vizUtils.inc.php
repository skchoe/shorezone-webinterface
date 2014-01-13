<?php
require_once(dirname(__FILE__)."/../viz/dataUtils.inc.php");
require_once(dirname(__FILE__)."/../viz/GoogleMapUtility.php");
require_once(dirname(__FILE__)."/../viz/gdlib_colors.php");
require_once(dirname(__FILE__)."/../shp-app/clipping.php");


function create_folder_chmod($folder_path)
{
  if(!file_exists($folder_path))
  {
    mkdir ($folder_path);
    system ("chmod g+w ".$folder_path);
  }
}
function folder_ids($folder_id)
{
  return 'ids_'.$folder_id % 100;
}
function folder_id($folder_id)
{
  return 'id_'.$folder_id;
}
function zoom($zoom)
{
  return 'z_'.$zoom;
}

function folder_x($tx)
{
  return "x_".$tx;
}

function prepare_png_folder($tile_home_folder, $shp_name, $folder_ids, $folder_id, $folder_zm, $folder_x, $pickorviz)
{
  if($pickorviz=="pick")
  {
    echo "tilehome: $tile_home_folder, shpname: $shp_name, folder_ids: $folder_ids, folder_id: $folder_id, folder_zm: $folder_zm, folder_x: .$folder_x </br>";
    $upto_shp_name = $tile_home_folder.'/'.$shp_name.'_pick';
    create_folder_chmod($upto_shp_name);

    $upto_folder_ids = $upto_shp_name.'/'.$folder_ids;
    create_folder_chmod($upto_folder_ids);

    $upto_folder_id = $upto_folder_ids.'/'.$folder_id;
    create_folder_chmod($upto_folder_id);

    $upto_folder_zm = $upto_folder_id.'/'.$folder_zm;
    create_folder_chmod($upto_folder_zm);

    $upto_folder_x = $upto_folder_zm.'/'.$folder_x;
    create_folder_chmod($upto_folder_x);
  }
  else if($pickorviz=="viz")
  {
    $upto_shp_name = $tile_home_folder.'/'.$shp_name.'_viz';
    create_folder_chmod($upto_shp_name);

    $upto_folder_id = $upto_shp_name.'/'.$folder_id;
    create_folder_chmod($upto_folder_id);

    $upto_folder_zm = $upto_folder_id.'/'.$folder_zm;
    create_folder_chmod($upto_folder_zm);
  }
}


function generatePngFileFromDB($wss_db, $shp_name, $species_id, $Xname, $Yname, $zoom, $folder, $geotype, $pickorviz) 
{
  //echo "generatePNGfromDB ".$wss_db.", <br/>".$species_id.", ".$Xname.", <br/>".$Yname."<br/>".$zoom.", folder = ".$folder."<br/>";
  
  ////create an array of the size for each marker at each zoom level
  //$file = 'tiles/c'.md5($Xname.'|'.$Yname.'|'.$zoom.').'.png'; // serialize?

  $tile_home = dirname(__FILE__)."/../../tiles";
  prepare_png_folder($tile_home, $shp_name, folder_ids($folder), folder_id($folder), zoom($zoom), folder_x($Xname), $pickorviz);

  $file = $tile_home.'/'.$shp_name.'/'.$folder.'/'.$zoom.'/c_'.$species_id.'_'.$Xname.'_'.$Yname.'_'.$zoom.'.png';

  //check if the file already exists
  if(!file_exists($file)) {
	
    list($source_gri, $xmin, $xmax, $ymin, $ymax, $polygonArrayGeo, $cw_bbx_array)
      = getPhpPolygonFromDB($wss_db, $species_id, TRUE); // returns multipolygon

    if ($xmin == FALSE || $ymin == FALSE
        ||$xmax == FALSE || $ymax == FALSE) {
      echo "Polygon from DB has FALSE min,max</br>";
      exit;
    }

    create_pngfile($geotype, polygonArrayGeo, $cw_bbx_array, $Xname, $Yname, $zoom, $xmin, $xmax, $ymin, $ymax, $file);

    header('content-type:image/png;');
    echo file_get_contents($file);

  } else {
    //output the existing image to the browser
    header('content-type:image/png;');
    echo file_get_contents($file);
  }
}

// given shp object $polygonArrayGeo w/ $cw_bbx_array, we iterate it's parts and draw polygon on given tile.
function create_pngfile($geotype, $polygonArrayGeo, $cw_bbx_array, $tileX, $tileY, $zoom, 
                        $gxmin, $gxmax, $gymin, $gymax, $file) 
{
  //get th lat/lng bounds of this tile from the utility function.
  // return abounds object with width, height, x, y.
  $tileRect = GoogleMapUtility::getTileRect((int)$tileX, (int)$tileY, (int)$zoom);

  //init some vars
  $extendx = 0.00000001;
  $extendy = 0.00000001;
  $nelat=$tileRect->y + $extendy;
  $swlat=$nelat+$tileRect->height - $extendy;
  $swlng=$tileRect->x + $extendx;
  $nelng=$swlng+$tileRect->width - $extendx;


  echo "tileRec.x: $tileRect->x, tileRec.y: $tileRect->y , tileRec.width: $tileRect->width, tileRec.height: $tileRect->height </br>";
  echo "swlat -> "; print_r($swlat); echo " nelat -> "; print_r($nelat); 
  echo "swlng -> "; print_r($swlng); echo " nelng -> "; print_r($nelng); 
  echo "</br>";

  echo "BBX for whole Shp object (union of parts) minx maxx,  miny maxy = ".$gxmin.", ".$gxmax.", ".$gymin.", ".$gymax."</br>";

  echo "createpngfile</br>";
  print_r($polygonArrayGeo);
  echo "<<<-those are input polygon</br>";
  
  // Filter out non-intersecting cases between tile and given global extent
  $b_bbx_tile = ($swlng > $gxmax) || ($nelng < $gxmin) || ($swlat < $gymin) || ($nelat > $gymax);

  if($b_bbx_tile) {
    echo "the tile is disjoint with global bounding box, b_bbx_tile: ".$b_bbx_tile."</br>";
    //$blank_filename = "../viz/blank_image.png";
    //echo file_get_contents($blank_filename);
    return FALSE;
  }
  
  // check if numpart and num-cwflag-array are same:
  $num_part = count($polygonArrayGeo); 
  $num_flag = count($cw_bbx_array);
  if ($num_part == $num_flag) echo "Geo part, CW array are same in length</br>";
  else echo "Geo part, CW array are not same in length</br>";

  echo "Num_Part: ".$num_part."</br>";


  //create a new image
  $img = imagecreate(GoogleMapUtility::TILE_SIZE,GoogleMapUtility::TILE_SIZE);
  
  $background = imagecolorallocate($img, 0, 0, 0);
  imagecolortransparent($img, $background);

  $interiorColor = imagecolorallocate($img, 255, 0, 0); // red

  // Draw tile borders
  //drawTileBorders($im, $swlat, $nelat, $swlng, $nelng, (int)$zoom, $interiorColor);

  // test drawing
  //imagefilledpolygon($img, array(100, 100, 200, 100, 200, 200, 100, 200), 4, $interiorColor);
  //imagefilledpolygon($img, array(125, 125, 175, 125, 175, 175, 125, 175), 4, $background);

  //echo "x($swlng,$nelng), y($swlat, $nelat)</br>";
  //$height = GoogleMapUtility::TILE_SIZE;
  //$pSW = GoogleMapUtility::toZoomedPixelCoords($swlat, $swlng, $zoom);
  //$pSE = GoogleMapUtility::toZoomedPixelCoords($swlat, $nelng, $zoom);
  //$pNE = GoogleMapUtility::toZoomedPixelCoords($nelat, $nelng, $zoom);
  //$pNW = GoogleMapUtility::toZoomedPixelCoords($nelat, $swlng, $zoom);
  //$parray = array(); 
  //$parray[] = $pSW->x;// - $pNW->x;
  //$parray[] = $pSW->y;// - $pNW->y;
  //$parray[] = $pNW->x;// - $pNW->x;
  //$parray[] = $pNW->y;// - $pNW->y;
  //$parray[] = $pNE->x;// - $pNW->x;
  //$parray[] = $pNE->y;// - $pNW->y;
  //$parray[] = $pSE->x;// - $pNW->x;
  //$parray[] = $pSE->y;// - $pNW->y;
  //$parray[] = $pSW->x;// - $pNW->x;
  //$parray[] = $pSW->y;// - $pNW->y;

  //print_r($parray);
  //echo "</br>";
  //imagepolygon($img, $parray, 2, $interiorColor);


  $major_cw = $cw_bbx_array[0]['CW'];
  for($npart = 0 ; $npart < $num_part ; $npart++)
  {
    $part = $polygonArrayGeo[$npart];
    $cwFlag = $cw_bbx_array[$npart]['CW'];
    $bbx = $cw_bbx_array[$npart]['bbx'];
    
    $numpt = count($part)/2;
    
    //echo "Sizeof part($npart) - Num points = ".$numpt."</br>";
    //echo "createpngfile----> part #: ".$npart."</br>";
    //print_r($part);
    //echo "<<<-those are input part of given polygon</br>";
    //print_r($bbx);
    //echo "<<<-bbx</br>";

    //draw_gd_polygon($part, $zoom, $img, $cwFlag, $interiorColor, $background, $major_cw, 0);

    //draw_tile_clipped_polygons($im, $part, $cwFlag, $bbx, $swlng, $nelng, $swlat, $nelat, $zoom, $interiorColor, $background); 
    //
    $orig = GoogleMapUtility::toZoomedPixelCoords($swlat, $swlng, $zoom);
    //echo "Origin: $orig->x, $orig->y </br>";

    $pixel_array = array();
    $numpt = count($part) / 2;
    for ($p = 0 ; $p < $numpt ; $p++) 
    {
      $c = $p * 2; 
      $x = $part[$c];
      $y = $part[$c+1];
      $pxl = GoogleMapUtility::toZoomedPixelCoords($y, $x, $zoom);
      $pixel_array[] = $pxl->x - $orig->x;
      $pixel_array[] = $pxl->y - $orig->y;
    }
    
    //echo "PHP GD image gen routine for geotype ".$geotype."</br>";
    //echo "INPUT GEOM: ";
    //print_r($pixel_array);
    //echo "</br>";
    
    if($geotype == Shape2Wkt::$GEOTYPE_MULTIPOLYGON)	
    {
      echo "create_pngfile: geotype is: polygon</br>";
      if($cwFlag == TRUE)
        imagefilledpolygon($img, $pixel_array, $numpt, $interiorColor);
      else
        imagefilledpolygon($img, $pixel_array, $numpt, $background);
    }
    else if($geotype == Shape2Wkt::$GEOTYPE_MULTILINESTRING)	
    {
      echo "create_pngfile: geotype is: linestring</br>";
      // Set the line thickness to 10
      imagesetthickness($img, 25);
      $numpixels = count($pixel_array) / 2;

      if(2 <= $numpixels)
      {
        $x1 = $pixel_array[0];
        $y1 = $pixel_array[1];
        for($p = 1 ; $p < $numpixels; $p++)
        {
          $idx = 2 * $p;
          $x2 = $pixel_array[$idx];
          $y2 = $pixel_array[$idx + 1];
          imageline($img, $x1, $y1, $x2, $y2, $interiorColor);
          $x1 = $x2;
          $y1 = $y2;
        }
      }
/* TEST POINTING
      for($p = 0 ; $p < $numpixels ; $p++)
      {
        $idx = 2 * $p;
        $x = $pixel_array[$idx];
        $y = $pixel_array[$idx + 1];
        $diameterPixel = 20;
        imagefilledellipse($img, $x, $y, $diameterPixel, $diameterPixel, $interiorColor);
      }
*/
    }
    else if($geotype == Shape2Wkt::$GEOTYPE_MULTIPOINT)	
    {
      echo "create_pngfile: geotype is: point</br>";
      // Set the point thickness to 10
      $diameterPixel = 50;
      $numpixels = count($pixel_array) / 2;
      for($p = 0 ; $p < $numpixels ; $p++)
      {
        $idx = 2 * $p;
        $x = $pixel_array[$idx];
        $y = $pixel_array[$idx + 1];
        imagefilledellipse($img, $x, $y, $diameterPixel, $diameterPixel, $interiorColor);
      }
    }
    else 
    {
      echo "geo type is not specified: ".$geotype."</br>";
      exit(1);
    }

  }
  
  if($img != null)
  {
    //output the new image to the file system and then send it to the browser
    $out_result = imagepng($img, $file, 9, PNG_ALL_FILTERS);
    
    imagecolordeallocate($img, $background );
    imagecolordeallocate($img, $interiorColor );
    
    imagedestroy($img);
  }
  else
  {
    echo "IMAGE is null--failed to generate</br>";
  }
}

function computeBoundingBoxFromParts($array_bbx)
{
//echo "computeBbxFromParts: </br>";
//print_r($array_bbx);
//echo "</br>";

  $num_parts = count ($array_bbx);
  //echo "BBX count:".$num_parts."</br>";
  $arr_xmin = array();
  $arr_xmax = array();
  $arr_ymin = array();
  $arr_ymax = array();

  for($count = 0 ; $count < $num_parts ; $count++) 
  {
    list($pxmin, $pxmax, $pymin, $pymax) = $array_bbx[$count];
    //echo "Part: ".$count."th</br>";
    //echo "Min/Max for x,y = ".$pxmin."/".$pxmax.",  ".$pymin."/".$pymax."</br>";
   
    array_push($arr_xmin, $pxmin);
    array_push($arr_xmax, $pxmax);
    array_push($arr_ymin, $pymin);
    array_push($arr_ymax, $pymax);
  }
  $gxmin = min($arr_xmin);
  $gxmax = max($arr_xmax);
  $gymin = min($arr_ymin);
  $gymax = max($arr_ymax);

   return array($gxmin, $gxmax, $gymin, $gymax); 
}

function computeBoundingBoxPart($part)
{
  $num_elt = count($part);
  $num_pts = $num_elt / 2;
  $minx = $part[0];
  $maxx = $part[0];
  $miny = $part[1];
  $maxy = $part[1];
  //echo "Num pts in given part = ".$num_pts."</br>";
  for($i = 1 ; $i < $num_pts ; $i++)
  {
    //echo $i."th x y = ".$part[2*$i].", ".$part[2*$i+1]."</br>";
    if ($maxx < $part[2*$i]) $maxx = $part[2*$i];
    if ($part[2*$i] < $minx) $minx = $part[2*$i];
    if ($maxy < $part[2*$i+1]) $maxy = $part[2*$i+1];
    if ($part[2*$i+1] < $miny) $miny = $part[2*$i+1];
  }
  return array ($minx, $maxx, $miny, $maxy);
}

function computeBoundingBox($arrayParts)
{
  $num_parts = count ($arrayParts);
  //echo "BBX count:".$num_parts."</br>";
  $arr_xmin = array();
  $arr_xmax = array();
  $arr_ymin = array();
  $arr_ymax = array();

  for($count = 0 ; $count < $num_parts ; $count++) {
    list($pxmin, $pxmax, $pymin, $pymax) = computeBoundingBoxPart($arrayParts[$count]);
    //echo "Part th: ".$count."</br>";
    //echo "Min/Max for x,y = ".$pxmin."/".$pxmax.",  ".$pymin."/".$pymax."</br>";
    
    array_push($arr_xmin, $pxmin);
    array_push($arr_xmax, $pxmax);
    array_push($arr_ymin, $pymin);
    array_push($arr_ymax, $pymax);
  }
  $gxmin = min($arr_xmin);
  $gxmax = max($arr_xmax);
  $gymin = min($arr_ymin);
  $gymax = max($arr_ymax);
  return array ($gxmin, $gxmax, $gymin, $gymax); 
}

function computeTileImage($wss_db, $shp_name, $zoomLevels, $species_id, $imgLimitPerLevel, $pickorviz) {
  
  // static data from DB
  list($source_gri, $xmin, $xmax, $ymin, $ymax, $polygonArrayGeo)
	= getPhpPolygonFromDB($wss_db, $species_id, TRUE); // returns multipolygon

  //echo "XY min/max = X(".$xmin."-".$xmax."), Y(".$ymin."-".$ymax.")<br/>";

  $polygon_geo_info = array($xmin, $xmax, $ymin, $ymax, $polygonArrayGeo);

  for($i=0 ; $i < sizeof ($zoomLevels) ; $i++) {
	
    $zoom = $zoomLevels[$i];
    computeTileImageZoom($shp_name, $species_id, $polygon_geo_info, $zoom, $imgLimitPerLevel, $pickorviz);
  }
}


function geoinfo_ht_bbx ($cw_bbx_ht)
{
   $bbx = $cw_bbx_ht['bbx'];
   return $bbx;
}

function computeZoom ($minx, $maxx, $miny, $maxx) 
{
  $initZoom = 0;
  $min_p = GoogleMapUtility::getPixelOffsetInTile($miny, $minx, $initZoom);
  $max_p = GoogleMapUtility::getPixelOffsetInTile($maxy, $maxx, $initZoom);

  echo "min: $min_p->x , $min_p->y </br>";
  echo "max: $max_p->x , $max_p->y </br>";
  $num_pix_x = $max_p->x - $min_p->x;
  $num_pix_y = $max_p->y - $min_p->y;

  $pixLen = min(abs($num_pix_x), abs($num_pix_y)) + 1;
  echo "length-x : $num_pix_x , length-y : $num_pix_y </br>";
  $ratio = 12800 / $pixLen; // 10 times wide/high than monitor resolution
  $zoom = ceil (log (ceil($ratio), 2)) ;

  echo "Computezoom: pixel-x at zlevel:".$zoom."</br>";
  
  return $zoom;
}

function computeTileImageZoom($shp_name, $shpelt_key, $shpelt_geo_info, $zoom, $imgLimitPerLevel, $geotype, $pickorviz)
{
  // those min-max for x,y are global extent (set of parts)
  list($xmin, $xmax, $ymin, $ymax, $polygonArrayGeo, $cw_bbx_array) = $shpelt_geo_info;
  

  //echo "computeTileImageZoom .......global(each shpelt=set of parts) bbx info...............</br>";
  //echo "zoom: $zoom </br>";
  //print_r($shpelt_geo_info);
  //print_r($cw_bbx_array); // array of hashtbl
  //echo "</br>";


  if($polygonArrayGeo==FALSE) { 
    //echo "computeTileImageZoom: - polygonArrayGeo is FALSE</b>"; 
    return FALSE;
  }

  // global extent null for given shpelt -> compute extent : union of bbx of each part.
  if($xmin==FALSE||$ymin==FALSE||$xmax==FALSE||$ymax==FALSE) {
    $array_bbx = array_map (geoinfo_ht_bbx, $cw_bbx_array); // bbx is an array (xmin, xmax, ymin, ymax)
    list($xmin, $xmax, $ymin, $ymax) = computeBoundingBoxFromParts($array_bbx);
    //echo "whole Min/Max for x,y (calc)= ".$xmin."/".$xmax.",  ".$ymin."/".$ymax."</br>";
  }

  /*
  echo "</br>PolygonArrayGeo: ".count($polygonArrayGeo).":</br>";
  print_r($polygonArrayGeo);
  echo "</br>";

  echo "</br>CWbbx: ".count($cw_bbx_array).":</br>";
  print_r($cw_bbx_array);
  echo "</br>";
   */

  //// we just make sure the geometry by drawing it 800x800 canvas - remove all echo to see pic.
  //echoPolygonArrayGeo($xmin, $xmax, $ymin, $ymax, $polygonArrayGeo, $cw_bbx_array);
  //echoPolygonArrayWithTiles($xmin, $xmax, $ymin, $ymax, $zoom, $polygonArrayGeo, $cw_bbx_array);
  
  computeNcreate_tile_imagefile($shp_name, $shpelt_key, $xmin, $xmax, $ymin, $ymax, $zoom, $imgLimitPerLevel, $polygonArrayGeo, $cw_bbx_array, $geotype, $pickorviz);
}


function echoPolygonArrayWithTiles($xmin, $xmax, $ymin, $ymax, $zoom, $polygonArrayGeo, $cw_bbx_array)
{
  // create image.
  $my_img_width = 800;
  $my_img_height = 800;
  
  $polygonArrayPixel = convertGeo2LatLngPixelScaled($polygonArrayGeo, $cw_bbx_array, $my_img_width, $my_img_height);
  //echo ">>>polygon in pixel: ";
  //print_r($polygonArrayPixel);
  //echo "</br>";

  // find minimal curvering subtiles
  $nwTile = GoogleMapUtility::toTileXY($ymax, $xmin, $zoom);
  $seTile = GoogleMapUtility::toTileXY($ymin, $xmax, $zoom);

  $numTileX = $seTile->x - $nwTile->x + 1;
  $numTileY = $seTile->y - $nwTile->y + 1;
  $totalTiles = $numTileX * $numTileY;

  $my_img = imagecreate( $my_img_width, $my_img_height );
  $background = imagecolorallocate( $my_img, 0, 128, 255 );
  $text_colour = imagecolorallocate( $my_img, 255, 255, 255 );
  $line_colour = imagecolorallocate( $my_img, 255, 128, 128 );

  foreach ($polygonArrayPixel as $polygon) {
    imagefilledpolygon( $my_img,
  					  $polygon,
  					  sizeof($polygon)/2,
  					  $text_colour );
  }

  for($tileX = $nwTile->x;$tileX <= $seTile->x;$tileX++) {
    for($tileY = $nwTile->y;$tileY <= $seTile->y;$tileY++) {

      $border = array();
      $tileRect = GoogleMapUtility::getTileRect((int)$tileX, (int)$tileY, (int)$zoom);
  
      //init some vars
      $extend = 0.0000001;
      $swlat=$tileRect->y + $extend;
      $nelat=$swlat+$tileRect->height - $extend;
      $swlng=$tileRect->x + $extend;
      $nelng=$swlng+$tileRect->width - $extend;

      list($lng, $lat) = convertGeoPoint2Pixel($swlng, $swlat, $xmin, $xmax, $ymin, $ymax, $my_img_width, $my_img_height);
      $border[] = $lng; $border[] = $lat;
      list($lng, $lat) = convertGeoPoint2Pixel($swlng, $nelat, $xmin, $xmax, $ymin, $ymax, $my_img_width, $my_img_height);
      $border[] = $lng; $border[] = $lat;
      list($lng, $lat) = convertGeoPoint2Pixel($nelng, $nelat, $xmin, $xmax, $ymin, $ymax, $my_img_width, $my_img_height);
      $border[] = $lng; $border[] = $lat;
      list($lng, $lat) = convertGeoPoint2Pixel($nelng, $swlat, $xmin, $xmax, $ymin, $ymax, $my_img_width, $my_img_height);
      $border[] = $lng; $border[] = $lat;
      list($lng, $lat) = convertGeoPoint2Pixel($swlng, $swlat, $xmin, $xmax, $ymin, $ymax, $my_img_width, $my_img_height);
      $border[] = $lng; $border[] = $lat;

      imagepolygon($my_img, $border, 5, $line_colour);
    }
  }

  header( "Content-type: image/png" );
  imagepng( $my_img );
  
  imagecolordeallocate( $my_img, $line_colour );
  imagecolordeallocate( $my_img, $text_colour );
  imagecolordeallocate( $my_img, $background );
  imagedestroy( $my_img );
}

function echoPolygonArrayGeo($xmin, $xmax, $ymin, $ymax, $polygonArrayGeo, $cw_bbx_array)
{
  // create image.
  $my_img_width = 800;
  $my_img_height = 800;
  
  $polygonArrayPixel = convertGeo2LatLngPixelScaled($polygonArrayGeo, $cw_bbx_array, $my_img_width, $my_img_height);
  
  /*
  echo "</br>PolygonArrayGeo-pixel: ";
  print_r($polygonArrayPixel);
  echo "</br>";
   */

  
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
  
  imagecolordeallocate( $my_img, $line_colour );
  imagecolordeallocate( $my_img, $text_colour );
  imagecolordeallocate( $my_img, $background );
  imagedestroy( $my_img );
  
}

function convertGeoPoint2Pixel($gx, $gy, $xmin, $xmax, $ymin, $ymax, $img_width, $img_height)
{
  $width = $xmax - $xmin;
  $height = $ymax - $ymin;
  
  $ratio_width = $img_width / $width;
  $ratio_height = $img_height / $height;
  
  $pos_x = $gx - $xmin;
  $pos_y = $gy - $ymin;
	$Lng = $pos_x * $ratio_width;
	$Lat = $pos_y * $ratio_height;
	//echo "gx, gy = (".$gx.", ".$gy."), positivi: ( $pos_x, $pos_y ) Lat, Lng = (".$Lat.", ".$Lng.")<br/>";

  return array($Lng, $Lat);
}

function convertGeo2LatLngPixelScaled($polygonArrayGeo, $cw_bbx_array, $img_width, $img_height) 
{
  $polygonArrayPixel = array();
  $numPolygons = sizeof($polygonArrayGeo);

  $array_bbx = array_map(geoinfo_ht_bbx, $cw_bbx_array);
  list($xmin, $xmax, $ymin, $ymax) = computeBoundingBoxFromParts($array_bbx);

  /*
  echo "x($xmin, $xmax), y($ymin, $ymax)</br>";
  echo "width, height = (".$width.", ".$height.")<br/>";
  echo "img_width, img_height = (".$img_width.", ".$img_height.")<br/>";
  echo "numPly = ".$numPolygons."<br/>";
  */
  
  for($i=0;$i<$numPolygons;$i++) {
	  $polygonPixel = array();

	  $polygon = $polygonArrayGeo[$i];
	  $numCoords = sizeof($polygon);
	  $numPoints = $numCoords/2;
	  for($j=0 ; $j < $numPoints ; $j++) {
	    $k = $j*2;
	    $gx = $polygon[$k];
	    $gy = $polygon[$k+1];
	    
      list($px, $py) = convertGeoPoint2Pixel($gx, $gy, $xmin, $xmax, $ymin, $ymax, $img_width, $img_height);
	    $polygonPixel[] = $px;
	    $polygonPixel[] = $py;
  	}
	
  	$polygonArrayPixel[] = $polygonPixel;
  }
	
  return $polygonArrayPixel;
}

// ../../tiles, szpoly, 234, 12, 10, 10, "viz"
// Do not echo values. It bolocks returning full file name.
function composeTileFullPath($tile_home, $shp_name, $shpelt_key, $zoom, $tx, $ty, $pickorviz)
{
  $full_filename = "";
  $simple_filename = "";

  $folder_id = 'id_'.$shpelt_key;
  $folder_zm = 'z_'.$zoom;
  $simple_filename = 'c_'.$shpelt_key.'_'.$tx.'_'.$ty.'_'.$zoom.'.png';

  if($pickorviz=="pick") 
  {
    $folder_shp = $shp_name."_pick";
    $folder_ids = folder_ids($shpelt_key);
    $folder_x = folder_x($tx);

    $full_filename = $tile_home.'/'.$folder_shp.'/'.$folder_ids.'/'.$folder_id.'/'.$folder_zm.'/'.$folder_x.'/'.$simple_filename;
  }
  else if($pickorviz=="viz")
  {
    $folder_shp = $shp_name."_viz";
    $full_filename = $tile_home.'/'.$folder_shp.'/'.$folder_id.'/'.$folder_zm.'/'.$simple_filename;
  }
  else
  {
    echo "pickorviz need to be set as either pick or viz</br>";
  }
  return $full_filename;
}

// array_bbx : array of bounding boxes for each part
// $polygonArrayGeo : array of parts
// min-max for x,y are global bounding box
function computeNcreate_tile_imagefile($shp_name, $shpelt_key, $xmin, $xmax, $ymin, $ymax, $zoom, 
			$imgLimitPerLevel, $polygonArrayGeo, $cw_bbx_array, $geotype, $pickorviz)
{
  // find minimal curvering subtiles
  $nwTile = GoogleMapUtility::toTileXY($ymax, $xmin, $zoom);
  $seTile = GoogleMapUtility::toTileXY($ymin, $xmax, $zoom);

  $numTileX = $seTile->x - $nwTile->x + 1;
  $numTileY = $seTile->y - $nwTile->y + 1;
  $totalTiles = $numTileX * $numTileY;

  echo "_*_*___*________*_________________*_________________________________*________________________<br/>";
  echo $shpelt_key." [z:".$zoom."]X range = ".$nwTile->x." ~ ".$seTile->x."(".$numTileX.")<br/>";
  echo $shpelt_key." [z:".$zoom."]Y range = ".$nwTile->y." ~ ".$seTile->y."(".$numTileY.")<br/>";
  echo "Total # of tiles = ".$totalTiles." w/ Limit ".$imgLimitPerLevel.". -> ";
  echo "bbx for big shpobj: xmin/xmax, ymin/ymax: ".$xmin."/".$xmax.",   ".$ymin."/".$ymax."</br>";
  echo "PICK or Viz: ".$pickorviz."</br>";
  echo "GEO Type: ".$geotype."</br>";
  flush();
  ob_flush();

  $tile_home = dirname(__FILE__)."/../../tiles";
  $img_sn = 0;
  if($totalTiles <= $imgLimitPerLevel || $imgLimitPerLevel==-1) 
  {
    for($tx = $nwTile->x;$tx <= $seTile->x;$tx++) 
    {
      prepare_png_folder($tile_home, $shp_name, folder_ids($shpelt_key), folder_id($shpelt_key), zoom($zoom), folder_x($tx), $pickorviz);
      for($ty = $nwTile->y;$ty <= $seTile->y;$ty++) 
      {

        $filename = composeTileFullPath($tile_home, $shp_name, $shpelt_key, $zoom, $tx, $ty, $pickorviz);
        $img_sn++;
        // No overwrite tiles.
        if(! file_exists ($filename)) {

          //echo "Computing started...w/tilex(x,y) = ".$tx.", ".$ty."</br>";
          create_pngfile($geotype, $polygonArrayGeo, $cw_bbx_array, $tx, $ty, $zoom, $xmin, $xmax, $ymin, $ymax, $filename);
          //echo "...Ended<br/>";
        }
        //else echo "File exists: Computing skipped</br>";
      }
    }
    // Stamping that the generation is ended
    //$completeFile = $tile_home.'/'.$shp_name.'/'.$folder_id.'/'.$folder_zm.'/_complete.stamp';
//echo "Filename: $completeFile </br>";
    //$completeFileHandle = fopen($completeFile, 'w') or die("can't open file");
    //fclose($completeFileHandle);
  }
  else {
    echo "Computing skipped for total tile number is over the limit<br/>";
  }
}

function copyFileToTiles($srcfile, $species_id) {
  $folder_id = 'id_'.$species_id;
  $dir = '../../tiles/wss/'.$folder_id;
  if(is_dir($dir)) {
    //$filelist = array();
    $dh = opendir($dir); 
    if($dh != FALSE) {
      while(($zoom = readdir($dh)) != FALSE) {
	if($zoom != "." && $zoom != "..") {
          //chmod ($dir.'/'.$zoom, g+w);
	  system ("chmod g+w ".$dir."/".$zoom);
          $destfile = $dir.'/'.$zoom.'/'.$srcfile;
          $success = copy($srcfile, $destfile);  
          if($success==FALSE) echo "Copy2 ".$species_id.", z".$zoom." has failed<br />";
          else echo "Copy2 ".$species_id.", z(".$zoom.") has finished.<br />";
        }
      }
    }
  }
}

function computeTileZoomRng($species_id) {

  $folder_id = 'id_'.$species_id;
  $dir = '../../tiles/wss/'.$folder_id.'/';
  if(is_dir($dir)) {
    $filelist = array();
    $dh = opendir($dir); 
    if($dh != FALSE) {
      while(($file = readdir($dh)) != FALSE)
	if($file != "." && $file != "..")
          $filelist[] = $file;
    }
    //closedir($dir);
    return $filelist;
  }
  else {
    //closedir($dir);
    return FALSE;
  }
}

function computeTileImageSizeByZoom($wss_db, $species_id, $zoom) {

  $folder_id = 'id_'.$species_id;
  $dir = '../../tiles/wss/'.$folder_id.'/'.$zoom;
  if(is_dir($dir)) {
    $sz = exec("/usr/bin/du -sk $dir");
    return substr($sz, 0, strpos($sz, 9));
  }
  else {
    return 0.0;
  }
}


function computeTileCountByZoom($wss_db, $species_id, $zoom) {

  $folder_id = 'id_'.$species_id;
  $dir = '../../tiles/wss/'.$folder_id.'/'.$zoom;
  if(is_dir($dir)) {
    $sz = exec("ls -l $dir/*.png | wc -l");
    return $sz;
  }
  else {
    return 0;
  }
}


function computeAllTiles($zoomLevels, $polygonArrayGeo) {
  
  $tileSize = GoogleMapUtility::TILE_SIZE;

  $k=-1;
  foreach ($zoomLevels as $zoom) {
	$k++;
	echo "K = ".$k."/ ".sizeof($polygonArrayGeo)."<br/>";
	$polygonArrayPixel = convertGeo2LatLngPixel($polygonArrayGeo, $zoom);

	// GD Library routines
	$times = pow(2, $zoom);
	$imgsize = $tileSize * $times;
	$my_img = imagecreate($imgsize, $imgsize);
	$background = get_color_allocate($my_img, "slate_blue");
	$text_color = get_color_allocate($my_img, "white");
	$red = get_color_allocaate( $my_img, "red");
	imagesetthickness ($my_img, 1);
  
	foreach ($polygonArrayPixel as $polygon) {
	  imagefilledpolygon( $my_img,
                              $polygon,
                              sizeof($polygon)/2,
                              $red);
	}
	$file = '../../tiles/wss/c_'.$species_id.'_z'.$zoom.'.png';


	header( "Content-type: image/png" );
	imagecolortransparent($my_img, $background);
	imagepng($my_img, $file, 9, PNG_ALL_FILTERS);

	imagecolordeallocate( $my_img, $red);
	imagecolordeallocate( $my_img, $text_color );
	imagecolordeallocate( $my_img, $background );
	imagedestroy( $my_img );

	echo $species_id.": *****************Tile Material w/ ".$zoom." saved in tile:".$imgsize."<br/>";
  }
}


function convertGeo2LatLngPixel($polygonArrayGeo, $zoom) {
  
  $polygonArrayPixel = array();
  $numPolygons = sizeof($polygonArrayGeo);
  echo "numPolygon = ".$numPolygons."<br/>";
  for($i=0;$i<$numPolygons;$i++) {
	  $polygonPixel = array();

	  $polygon = $polygonArrayGeo[$i];
	  $numCoords = sizeof($polygon);
	  $numPoints = $numCoords/2;
	  for($j=0 ; $j < $numPoints ; $j++) {
	    $k = $j*2;
	    $gx = $polygon[$k];
	    $gy = $polygon[$k+1];
	  
	    //echo $zoom." beforeXY  = (".$gx.", ".$gy.")<br/>";
	    $pixel = GoogleMapUtility::toZoomedPixelCoords($gy, $gx, $zoom);
	    //echo $zoom."afterXY  = (".$pixel->x.", ".$pixel->y.")<br/>";

	    $polygonPixel[] = $pixel->x;
	    $polygonPixel[] = $pixel->y;
  	}
	
  	$polygonArrayPixel[] = $polygonPixel;
  }
	
  return $polygonArrayPixel;
}
function in_tile($x, $y, $swlat, $nelat, $swlng, $nelng)
{
  if ($swlng < $x && $x <= $nelng && $swlat < $y && $y <= $nelat)
    return TRUE;
  else
    return FALSE;
}

// output -1 if non of point are in tile.
// output i if ith index in $part are first point in tile. => (part[$i], part[$i+1]) 
function first_idx_in_tile ($part, $swlat, $nelat, $swlng, $nelng)
{
  $size_part = count($part);
  for($i=0;$i<$size_part/2;$i++) {
    $j = 2*$i;
    $x = $part[$j];
    $y = $part[$j+1];
    $b = in_tile($x, $y, $swlat, $nelat, $swlng, $nelng);
    if ($b) return $i;
  }
  return -1;
}

function is_tilecorner_in_part ($part, $cwFlag, $swlat, $nelat, $swlng, $nelng)
{
  // we set tile CW/CCW be same as that of $part to satisfy:
  // if a pt in $part is in tile, and tile corner is in $part, the point_in_polygon show same result.
  $tileCorners = convertLatLngToVertices($swlat, $nelat, $swlng, $nelng, $cwFlag);

  list($b0, $b1, $b2, $b3) = detectInOutTileCorners($part, $tileCorners);
  $is_inpart = $b0||$b1||$b2||$b3;

  return $is_inpart;
}

function slope_from_to($from_pt, $to_pt)
{
  $fx = $from_pt[0]; $fy = $from_pt[1];
  $tx = $to_pt[0]; $ty = $to_pt[1];

  if ($tx == $fx) return "INF";
  else return ($ty - $fy) / ($tx - $fx);
}

// input in_pt : array of x,y inside of tile ($swlat, $nelat, $swlng, $nelng)
// input out_pt: point outside of tile connected to in_pt
// returns: array ( 'x/y', 'value') which corresponds x=value / y=value.
function segment_crossing_axis($in_pt, $out_pt, $swlat, $nelat, $swlng, $nelng)
{
  $in_x = $in_pt[0];   $in_y = $in_pt[1];
  $out_x = $out_pt[0]; $out_y = $out_pt[1];
  // $out_pt \in [swlng, nelng)
  if ($swlng <= $out_x && $out_x < $nelng) {
     if($in_y < $out_y) return array('y', $nelat);
     else if($out_y < $in_y) return array('y', $swlat);
     else { echo "1. error</br>"; return array(); }
  }
  // $out_pt \in [swlat, nelat)
  else if($swlat <= $out_y && $out_y < $nelat) {
    if($in_x < $out_x) return array('x', $nelng);
    else if ($out_x < $in_x) return array('x', $swlng);
    else { echo "2. error</br>"; return array(); } 
  }
  // x, y are out-range in positive-direction
  else {
    $slope2out_pt = slope_from_to($in_pt, $out_pt);
    // <1>
    if ($nelng <= $out_x && $nelat <= $out_y) {
      $corner = array($nelng, $nelat);
      $slope2corner = slope_from_to($in_pt, $corner);
      if($slope2out_pt < $slope2corner) return array('x', $nelng);
      else if($slope2out_pt == $slope2corner) return $corner;
      else return array('y', $nelat);
    }
    // <2>
    else if ($out_x <= $swlng && $nelat <= $out_y) {
      $corner = array($swlng, $nelat);
      $slope2corner = slope_from_to($in_pt, $corner);
      if($slope2out_pt < $slope2corner) return array('x', $swlng);
      else if ($slope2out_pt == $slope2corner) return $corner;
      else return array('y', $nelat);
    }
    // <3>
    else if ($out_x <= $swlng && $out_y <= $swlat) {
      $corner = array($swlng, $swlat);
      $slope2corner = slope_from_to($in_pt, $corner);
      if($slope2out_pt < $slope2corner) return array('x', $swlng);
      else if($slope2out_pt == $slope2corner) return $corner;
      else return array('y', $swlat);
    }
    // <4>
    else if ($nelng <= $out_x && $out_y <= $swlat) {
      $corner = array($nelng, $swlat);
      $slope2corner = slope_from_to($in_pt, $corner);
      if($slope2out_pt < $slope2corner) return array('x', $nelng);
      else if ($slope2out_pt == $slope2corner) return $corner;
      else return array('y', $nelng);
    }
    else { echo "3. error</br>"; return array(); }
  }
}

// $seg_head - > interior pt to tile
// $seg_tail - > exterior pt to tile
function intersect_segment_axis($seg_head, $seg_tail, $axis)
{
  $axis_name = $axis[0];
  $axis_value = $axis[1];

  $slope = slope_from_to($seg_head, $seg_tail);
  
  if($slope == "INF")
    if($axis_name == "y") {
      return array($seg_head[0], $axis_value);
    }
    else if($axis_name == "x") {
      echo "Error for intersection to axis 1</br>";
      return array();
    }
    else {
      echo "Unknown axis name 1</br>";
      return array();
    }

  else if($slope == 0)
    if($axis_name == "y") {
      echo "Error for intersection to axis 2</br>";
      return array();
    }
    else if($axis_name == "x") {
      return array($axis_value, $seg_head[1]);
    }
    else {
      echo "Unknown axis name 2</br>";
      return array();
    }

  else {
    if($axis_name == "y") {
      return array($seg_head[0] + ($axis_value - $seg_head[1]) / $slope, $axis_value);
    }
    else if($axis_name == "x") {
      return array($axis_value, $seg_head[1] + ($axis_value - $seg_head[0]) * $slope);
    }
    else {
      echo "Unknown axis name 3</br>";
      return array();
    }
  }
}

function intile_info_to_polygon ($intile_info, $cwFlag,
         $swlat, $nelat, $swlng, $nelng)
{
  echo "lat = (".$swlat.", ".$nelat."), lng = (".$swlng.", ".$nelng.")</br>";
  echo "Entile info...</br>";
  print_r($intile_info);
  echo "</br>";

  $pt_prev = $intile_info['prev'];
  $pt_next = $intile_info['next'];
  $arr_interior = $intile_info['interior'];

  $num_coord = count($arr_interior);
  $num_interior = $num_coord / 2;

  if ($num_interior < 1) return array();

  // detect tile border that intersect given interior polyline.
  $interior_head = array($arr_interior[0], $arr_interior[1]);
  $interior_tail = array($arr_interior[$num_coord-2], $arr_interior[$num_coord-1]);
  $ax_val_prev = segment_crossing_axis($interior_head, $pt_prev,  
                                                $swlat, $nelat, $swlng, $nelng);
  $ax_val_next = segment_crossing_axis($interior_tail, $pt_next,  
                                                $swlat, $nelat, $swlng, $nelng);

  $borderpt_head = intersect_segment_axis($interior_head, $pt_prev, $ax_val_prev);
  $borderpt_tail = intersect_segment_axis($interior_tail, $pt_next, $ax_val_next);
  
  // border pts are on same axis
  if($ax_val_prev[0] == $ax_val_next[0]) {
    if($ax_val_prev[1] == $ax_val_next[1]) {
      return array_merge($arr_interior, 
                         array_merge($borderpt_tail, $borderpt_head));
    }
    else if ($ax_val_prev[0] == "x") {
      if($ax_val_prev[1] < $ax_val_next[1]) 
        if($csFlag) // CW
          return array_merge($arr_interior, 
                             array_merge($borderpt_tail, 
                                         array($nelng, $swlat),
                                         array($swlng, $swlat),
                                         $borderpt_head));
        else // CCW
          return array_merge($arr_interior,
                             array_merge($borderpt_tail, 
                                         array($nelng, $nelat),
                                         array($swlng, $nelat),
                                         $borderpt_head));
      else if($ax_val_next[1] < $ax_val_prev[1])  
        if($csFlag) // CW
          return array_merge($arr_interior,
                             array_merge($borderpt_tail, 
                                         array($swlng, $nelat),
                                         array($nelng, $nelat),
                                         $borderpt_head));
        else // CCW
          return array_merge($arr_interior,
                             array_merge($borderpt_tail, 
                                         array($swlng, $swlat),
                                         array($nelng, $swlat),
                                         $borderpt_head));
      else {
        echo "Error for extra points 1</br>";
        return array_merge($arr_interior, array_merge($borderpt_tail, $borderpt_head));
      }
    } 
    else if ($ax_val_prev[0] == "y") {
      if($ax_val_prev[1] < $ax_val_next[1]) 
        if($csFlag) // CW
          return array_merge($arr_interior,
                             array_merge($borderpt_tail, 
                                         array($nelng, $nelat),
                                         array($nelng, $swlat),
                                         $borderpt_head));
        else // CCW
          return array_merge($arr_interior,
                             array_merge($borderpt_tail, 
                                         array($swlng, $nelat),
                                         array($swlng, $swlat),
                                         $borderpt_head));
      else if($ax_val_next[1] < $ax_val_prev[1])  
        if($csFlag) // CW
          return array_merge($arr_interior,
                             array_merge($borderpt_tail, 
                                         array($swlng, $swlat),
                                         array($swlng, $nelat),
                                         $borderpt_head));
        else // CCW
          return array_merge($arr_interior,
                             array_merge($borderpt_tail, 
                                         array($nelng, $swlat),
                                         array($nelng, $nelat),
                                         $borderpt_head));
      else {
        echo "Error for extra points 2</br>";
        return array_merge($arr_interior, array_merge($borderpt_tail, $borderpt_head));
      }
    }
    else {
      "echo Unknown axis name in intile_info to polygon</br>";
    }
  }
  else {// two axes are not same.



  }



/*
  echo "Intersecting axis info...  first tile borders</br>";
  echo "x dir = ".$swlng.", ".$nelng." y dir = ".$swlat.", ".$nelat."</br>"; 
  print_r($pair_axis_value_prev);
  echo "</br> => </br>";
  print_r($boderpt_head);
  echo "</br>";
  print_r($pair_axis_value_next);
  echo "</br> => </br>";
  print_r($boderpt_tail);
  echo "</br>";
*/
  
  // we add extra corner(s) of tile to close in-tile polygon.
  
  return 1;

}
// 
function drawPartInTile($im, $part, $cwFlag, $part_bbx, $swlat, $nelat, $swlng, $nelng, 
                        $zoom, $interiorColor, $holeColor)
{
  $major_cw = TRUE;
  $gd_polygon = removeHeadDupTail($part);
  // gd_polygon is considered closed with Head != Tail

  // case 1: part is inside of tile -> compare only bounding box with tile
  list($pxmin, $pxmax, $pymin, $pymax) = $part_bbx;
  //echo "....swlat/nelat/swlng/nelng ".$swlat.", ".$nelat.", ".$swlng.", ".$nelng."</br>";
  //echo "drawpartInTile:</br>";
  //print_r($part_bbx);
  //echo "</br>";
  if($swlng < $pxmin && $pxmax < $nelng && $swlat < $pymin && $pymax < $nelat) 
  {
    // draw all part;
    // polygon in GD library doesn't duplicate Head and Tail
    echo "CASE 1</br>";
    draw_gd_polygon ($gd_polygon, $zoom, 
                     $im, $cwFlag, $interiorColor, $holeColor, $major_cw, 0);
    //print_r($px_polygon);
    //echo "</br>";

    return 1;
  }
  // case 3.1: (intersect) a point from part is in the tile 
  //           -> iterate pt, check bbx inclusion with inequality
  else {
    $int_idx = first_idx_in_tile($part, $swlat, $nelat, $swlng, $nelng);
    if (-1 != $int_idx) {
      // draw subpart of part cut by tile. (cw/ccw)
      echo "CASE 3.1</br>";
      $arr_intile_info = decompose_into_subpolygons($gd_polygon, $cwFlag, 
                                          $swlat, $nelat, $swlng, $nelng, $int_idx);
      
      $arr_subpolygons = array();
      for ($i=0 ; $i< count($arr_intile_info) ; $i++) {
        $intile_info = $arr_intile_info[$i];
        $arr_subpolygons[] = intile_info_to_polygon($intile_info, $csFlag,
                                            $swlat, $nelat, $swlng, $nelng);
      }

      return 1;
    }
    // any point in part are not in the tile
    //  ==> either 
    // case 3.2: (intersect) else of 3.1 (no point is in the tile)
    else if (TRUE == is_tilecorner_in_part($part, $cwFlag,  $swlat, $nelat, $swlng, $nelng))
    {
      echo "CASE 3.2</br>";
      return 1;
    }
    //      or
    // case 2: tile is inside of the part
    else {
      echo "CASE 2</br>";
      draw_gd_polygon(array_merge(array($swlng, $swlat), array($nelng, $swlat),
                                  array($nelng, $nelat), array($swlng, $nelat)),
                      $im, $csFlag, $interiorColor, $holeColor, $major_cw, 0);
      return "draw_whole_tile";
    }
  }
}


function require_provide_test()
{
	echo "REQ/PROV at vizUtiles.php</br>";
}

?>
