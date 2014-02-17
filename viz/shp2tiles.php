<?php
require_once (dirname(__FILE__)."/Shp2PhpPolygon.php");
require_once (dirname(__FILE__)."/../db2tile/vizUtils.inc.php");
require_once (dirname(__FILE__)."/../shp-app/mysql_utils.php");

function shp2tiles($shp_name, $shp, $zoom_start, $zoom_end, $geotype, $pickorviz)
{

  $shp2php = new Shape2PhpPolygon();
  $prival = 0;

  $pth = dirname(__FILE__)."/../db2tile/vizUtils.inc.php";
  if(file_exists($pth)) echo "File exist </br>";
  else echo "File Not exists </br>";

  echo "SHP 2 TILES- in $pth </br>";

  //require_provide_test();

  $shp_numpts = 0;
  $shp_numprts = 0;

  while ($record = $shp->getNext()) 
  {
    // we create one id_XX for this geom_data.
    // XX is assumed by the order in shp
    $geom_data = $record->getShpData();
    $meat_data = $record->getDbfData();
    
    //pts, parts
    $rec_numpoints = $geom_data["numpoints"];
    $rec_numparts = $geom_data["numparts"];
    $part_array = $geom_data["parts"];
    //Multipolygon -> PhpPolygon
    $arrayParts = $shp2php->convert ($rec_numparts, $part_array);
    
    // update shp-data
    $shp_numpts += $rec_numpoints;
    $shp_numprts += $rec_numparts;

    if(empty($arrayParts)) continue;

    $record_identifier = $record->record_number;

    // real processing
    $cw_bbx_array = array_map('compute_bbx_bcw', $arrayParts);

    $array_bbx = array_map('geoinfo_ht_bbx', $cw_bbx_array); // bbx is an array (xmin, xmax, ymin, ymax)
    list($xmin, $xmax, $ymin, $ymax) = computeBoundingBoxFromParts($array_bbx);

    $shpelt_geo_info = array($xmin, $xmax, $ymin, $ymax, $arrayParts, $cw_bbx_array);
    $imgLimitPerLevel = 400000000;


    for($zoom = $zoom_start ; $zoom <= $zoom_end ; $zoom++) {
      echo ">$zoom ";
      computeTileImageZoom($shp_name, $record_identifier, $shpelt_geo_info, $zoom, $imgLimitPerLevel, $geotype, $pickorviz);
    }
	echo "</br>";

    //print_r($shpelt_geo_info);
    //echo "shp2tiles in viz </br>";
    echo "|<".$record_identifier.">";//."th ReCORD  ".$rec_numpoints." Has been accessed by reader<br/>";
  }
  echo "|DONE</br>";
  
  echo "SHP 2 TILE complete</br>";
  flush();
  ob_flush();

  return array($shp_numpts, $shp_numprts);
}
?>
