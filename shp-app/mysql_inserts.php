<?php

set_time_limit(3600*24*3);
ini_set('memory_limit', -1);
while (ob_get_level()) ob_end_flush();

require_once(dirname(__FILE__).'/mysql_utils.php');

function connect_db_create_tbl($db_conn, $tbl_name_shp, $tbl_name_dbf, $shp_meta, $geotype)
{
  // how meny layer?
  $num_shpelt = 0;
  while ($record = $shp_meta->getNext()) $num_shpelt++;
  //echo "NUM_elt in Shp:". $num_shpelt."</br>";

  if ($num_shpelt <= 0) exit("SHP file has 0 layer in shp-app/mysql_inserts.php");

  //(1) metadata header
  $dbf_header_data = $shp_meta->getDbfHeader();

  if($dbf_header_data == FALSE) exit("DBF header is null__________");
  else {
	//echo "DBF header is loaded_______";
	//print_r($dbf_header_data);
	//echo "</br>";
  }


// print out metadata for shp
  /*
  $dbf_count = count($dbf_header_data); // number of metadata
  echo "metadata-count:". $dbf_count ."</br>";
  for($i=0; $i < $dbf_count ; $i++) {
    echo $i . "th subarray</br>";
    //print_r($dbf_header_data[$i]);
    foreach ($dbf_header_data[$i] as $key => $value){
      echo $key . "=>" . $value . ", ";
    }
    echo "</br>";
  }
   */
// end of header

  $shp_db = connect_db($db_conn);

  $pri_key_name = "primary_key";

  $dbf_tbl = create_dbf_tbl($shp_db, $tbl_name_dbf, $dbf_header_data, $pri_key_name);
  //echo "DBF tble structure : </br>";
  //describe_table($dbf_tbl);

  $dst_tbl = create_dst_tbl($shp_db, $tbl_name_shp, $pri_key_name, $geotype);
  //echo "DST tble structure : </br>";
  //describe_table($dst_tbl);

  return array ($shp_db, $dst_tbl, $dbf_tbl, $pri_key_name);
}

function extract_name($header_elt_array)
{
  return $header_elt_array["name"];
}

// $shp: ShapeFile object
// output: mysql query for inputting all things from $shp
function shp2insertquery($shp, $geotype, $tbl_name_shp, $tbl_name_dbf, $pri_key_name)
{
  // number of layers with non-zero points
  $valid_layer_counter=0;
  $GEOTYPE_POINT = 1;

  //(2) metadata content
  // Creating Wkt multi-polygon
  $accum_time = microtime();
  $accum_numpoints = 0;
  $dbf_header_data = $shp->getDbfHeader();
  $shp2wkt = new Shape2Wkt($geotype);

  // collection fo bbx(array())
  $arr_bbx = array();
  $shp_numpts = 0;
  $shp_numparts = 0;

  $record_count = 0;
  echo "SHP2MYSQL->";

  // INSERT geom(shp) metadata(dbf) in a loop
  while ($record = $shp->getNext()) 
  {
    // clock in
    $current_time = microtime(TRUE); // output is second

    $record_identifier = $record->record_number;

    $geo_type = $record->record_shape_type;

    echo "|".$record_count."th: ID[".$record_identifier."]:TYPE[".$geo_type."]</br>";
    flush();
    @ob_flush();

    // geometry - a record in shp-file
    $geom_data = $record->getShpData();

	// if no geometry, we skip putting geom, metadata into DB
	echo "Geom data </br>";
	print_r($geom_data);
    if(empty($geom_data)) continue;	

    $xmin = $ymin = $xmax = $ymax = 0;
    if($geo_type != $GEOTYPE_POINT) // not Point
    {
      // min-max -> geom_data["xmin"].......
      $xmin = $geom_data["xmin"];
      $ymin = $geom_data["ymin"];
      $xmax = $geom_data["xmax"];
      $ymax = $geom_data["ymax"];
    }
    else
    {
      $xmin = $geom_data["x"];
      $ymin = $geom_data["y"];
      $xmax = $geom_data["x"];
      $ymax = $geom_data["y"];
    }

    // pts, parts
    $rec_numpoints = 1;
    if($geo_type != $GEOTYPE_POINT) // not Point
    {
      $rec_numpoints = $geom_data["numpoints"];
    }

    // no points? -> go ahead, be carefull in computation
    if($rec_numpoints>0) $valid_layer_counter ++;
    //else continue;
    $valid_layer_counter ++;

    $rec_numparts = 1;
    $part_array = [];
    if($geo_type != $GEOTYPE_POINT)
    {
      $rec_numparts = $geom_data["numparts"];
      $part_array = $geom_data["parts"];
    }
    else
    {
      $points[0] = $geom_data;
      $part["points"] = $points;
      $part_array[0] = $part;	
    }

    $arr_bbx[] = array($xmin, $xmax, $ymin, $ymax);
    $shp_numpts += $rec_numpoints;
    $shp_numparts += $rec_numparts;

    //echo "before convert to wkt</br>";

    // Multipolygon -> WKT
    $mg_string = $shp2wkt->convert ($rec_numparts, $part_array);
    echo "after convert to wkt to:</br>";

    // {source_gri, min-max, n-parts, n-points, WKT} -> mysql record
    $query_insert_dst = "INSERT INTO ".$tbl_name_shp." set ".
      $pri_key_name."=".$record_identifier.",
      xmin=".$xmin.",
      ymin=".$ymin.",
      xmax=".$xmax.",
      ymax=".$ymax.",
      numparts=".$rec_numparts.",
      numpoints=".$rec_numpoints.",
      polygons=GEOMFROMTEXT('".$mg_string."')";

    echo "Query to insert DST</br>";
    echo $query_insert_dst."</br>";
    // insert into table
    $result_insert_dst = mysql_query($query_insert_dst);
    
	if($result_insert_dst==TRUE) echo "insert ---(shp)***(((".$valid_layer_counter."))) success ".$result_insert_dst." : ".$rec_numpoints." points ".$rec_numparts." parts<br/>";
	else echo "insert (dst)--- ".$result_insert_dst." : fails<br />";
	flush();
	@ob_flush();

    $dbf_count = count($dbf_header_data); // number of metadata

    //echo "metadata-count:". $dbf_count ."</br>";
    //print_r($dbf_header_data);
    //echo "</br>";

    $name_array = array_map("extract_name", $dbf_header_data);

    // dbf data
    $meta_data = $record->getDbfData();
    $query_insert_dbf = "INSERT INTO ".$tbl_name_dbf." set ".$pri_key_name."=".$record_identifier.", ";
    for($i=0; $i < $dbf_count ; $i++) {
      $col_name = $name_array[$i];
      $col_value = trim(addslashes($meta_data[$col_name]));
      //echo $valid_layer_counter." Name/Value of col[".$i."]=".$col_name."=>".addslashes($meta_data[$col_name])."</br>";
      if($i==$dbf_count-1) $query_insert_dbf = $query_insert_dbf.$col_name."='".$col_value."'";
      else                 $query_insert_dbf = $query_insert_dbf.$col_name."='".$col_value."', ";
    }
    //echo "Query string for insertion : ".$query_insert_dbf."</br>";

    // insert into table
    echo "Query to insert DBF</br>";
    echo $query_insert_dbf."</br>";
    $result_insert_dbf = mysql_query($query_insert_dbf);
    
	if($result_insert_dbf==TRUE) echo "insert ---(dbf)***(((".$valid_layer_counter."))) success ".$result_insert_dbf." <br/>";
	else echo "insert (dbf)--- ".$result_insert_dbf." : fails<br />";

    flush();
    @ob_flush();
     
  	$pri_key_name = "primary_key";

	$q_primary_dst ="SELECT ".$pri_key_name." FROM ".$tbl_name_shp." WHERE ".$pri_key_name."='".$record_identifier."'";
	$p_res_dst = mysql_query($q_primary_dst);
	
	$q_primary_dbf ="SELECT ".$pri_key_name." FROM ".$tbl_name_dbf." WHERE ".$pri_key_name."='".$record_identifier."'";
	$p_res_dbf = mysql_query($q_primary_dbf);

echo $q_primary_dst."</br>";
echo $q_primary_dbf."</br>";

	$key_dst = @mysql_result($p_res_dst, 0);
	$key_dbf = @mysql_result($p_res_dbf, 0);

	echo "($key_dst , $key_dbf ) </br>";

    // clock out
    $new_time = microtime(TRUE);
    $time_diff = $new_time - $current_time;
    $accum_time = $accum_time + $time_diff;
    
    echo $valid_layer_counter." insertion-spent :".$time_diff."(sec)</br>";
    flush();
    @ob_flush();
    echo "-------------------------------------------------------------</br>";
	
    $record_count++;
  }
  echo "|DONE</br>";
  
  if($valid_layer_counter != 0) {
    $secperlayer = $accum_time / $valid_layer_counter;
    $ptsperlayer = $accum_numpoints / $valid_layer_counter;
    echo "DB Insertion statistics</br>";
    echo "TIME & NUM layer(DSTandDBF insertion):".$accum_time."(sec) for ".$valid_layer_counter." layers --> ".$secperlayer." sec/layer</br>";
    echo "POINTS layer(DSTandDBF insertion):".$accum_numpoints."(points) for ".$valid_layer_counter." layers --> ".$ptsperlayer." points/layer</br>";
  }
  
   echo "Compute Bounding box from parts.....</br>";
   $shp_bbx = computeBoundingBoxFromParts($arr_bbx);
   echo "Boundingbox computed</br>";
/*
	$bbox_from_shapeclass = $shp->getBoundingBox();
	$shp_bbx[0] = $bbx_from_shapecleass["xmin"];
	$shp_bbx[1] = $bbx_from_shapecleass["xmax"];
	$shp_bbx[2] = $bbx_from_shapecleass["ymin"];
	$shp_bbx[3] = $bbx_from_shapecleass["ymax"];
   echo "Boundingbox fetched from shp</br>";
*/
  
  return array ($tbl_name_shp, $tbl_name_dbf, $record_count, $shp_numpts, $shp_numparts, $shp_bbx);
}

?>
