<?php
require_once(dirname(__FILE__)."/../db2tile/vizUtils.inc.php");

function extract_shp_name($filename)
{
// structure of $filename would be seq of folders with / as separator.
  $paths = explode("/", $filename);
  $num_paths = count ($paths);
  $pure_filename = $paths[$num_paths-1];

  $bodies = explode(".", $pure_filename);
  return $bodies[0];
}
function get_prikey_name_from_shp ($filename)
{
  $options = array('noparts' => false);
  $shp = new ShapeFile($filename, $options);
  $dbf_header_data = $shp->getDbfHeader();
  //if($dbf_header_data == FALSE) echo "get_p-key: DBF header is null__________<br/>";
  //else echo "get_p-key: DBF header is loaded_______</br>";

  $h_tbl_col0 = $dbf_header_data[0];
  //$array_keys = array_keys($h_tbl_col0);
  $array_vals = array_values($h_tbl_col0);
  ////echo "array_keys:".print_r($array_keys)."</br>";
  //echo "array_vals:".print_r($array_vals)."</br>";

  return $array_vals[0];
}
// in db-connect info = host, user, passwd, db
// out: connection
function connect_db($conn)
{
  //echo "ip,login.pwd:".$ip.", ".$login.", ".$pwd."</br>";
  $ip = $conn['host'];
  $login = $conn['user'];
  $pwd = $conn['passwd'];
  $dbname = $conn['db'];

  $conn = mysql_connect($ip, $login, $pwd)
  or die("Connect DB server error");

  /*
  if ($conn == FALSE) echo "FALSE - Fail to connect db server";
  else {
	  echo "TRUE- connected to db server of ".$dbname."</br>";
	  flush();
	  ob_flush();
  }
  */
  $db_list = mysql_list_dbs($conn);

  $i = 0;
  $exist_db = FALSE;
  $cnt = mysql_numrows($db_list);
  //echo "<br />db list -> <br />";
  while ($i < $cnt) {
    $db_name = mysql_db_name($db_list, $i);
    //echo $db_name . "<br />";
    if ($db_name == $dbname)
  	$exist_db = TRUE;
    $i++;
  }

  // DB creation, use
  if ($exist_db == TRUE)
    echo $dbname." exists <br />";
  else {
    echo $dbname." not exists <br />";
    $query_db_create = "CREATE DATABASE $dbname";
    $dbc = mysql_query($query_db_create);
    if($dbc==TRUE) echo "creation of db good<br />";
    else echo "creation of db failed <br />";
    //mysql_free_result($dbc); ->doesn't need : too light result.
  }
  @mysql_select_db($dbname)
  or die("Could not select database!");

  //echo "You're connected to a MySQL database!----".$conn."<br />";
  return $dbname;
}
function table_exist($dbname, $tblname)
{
  // Table listup/creation
  $query_forall_tbls = "SHOW TABLES FROM ".$dbname;
  $resulttbls = mysql_query($query_forall_tbls);
  if (!$resulttbls) {
    echo "DB Error, could not list tables\n";
    echo 'MySQL Error: ' . mysql_error();
    exit;
   }
  
  $b_tbl_exist = FALSE;
  while ($row = mysql_fetch_row($resulttbls)) {
    //echo "Table--------------: {$row[0]}<br />";
    if ($tblname == $row[0]) {
  	//echo "test table exists: ".$tblname."<br />";
  	$b_tbl_exist = TRUE;
  	break;
    }
    //else echo "{$row[0]} isn't same as {$tblname}. <br />";
  }
  //mysql_free_result($resulttbls);
  
  /*
  if ($b_tbl_exist == TRUE)
    echo "dst_tbl $tblname  found<br/>";
  else
    echo "Couldn't find table ---> creation $tblname <br />";
  */ 
  return $b_tbl_exist;
}
function get_mysqltype_from_dbf_header($dbf_type, $dbf_length)
{
  $out_string = "";
  if($dbf_type == "number")
    $out_string = $out_string."int(".$dbf_length.")";
  else if ($dbf_type == "float")
    $out_string = $out_string."float(".$dbf_length.")";
  else if ($dbf_type == "double")
    $out_string = $out_string."float(".$dbf_length.")";
  else if ($dbf_type == "character")
    $out_string = $out_string."char(".$dbf_length.")";
  else {
    echo "get_mysqltype_from_dbf_header cannot handle type: ".$dbf_type."</br>";
    exit;
  }
  return $out_string;
}
function create_dst_tbl($dbname, $tblname, $pri_key_name, $geotype)
{
  if(table_exist ($dbname, $tblname)){
    echo "table ".$tblname." exist ... recreate</br>";
    $drop = "DROP TABLE ".$tblname.";";
    $dr = mysql_query($drop);
    if($dr == FALSE) die ("dropping dst tbl failed");
  }
  else "table $tblname doesn't exist -> query for creation</br>";

  $query_for_create_tbl
    = "CREATE TABLE ".$tblname." (".
      $pri_key_name." BIGINT UNSIGNED,
      xmin double NOT NULL,
      ymin double NOT NULL,
      xmax double NOT NULL,
      ymax double NOT NULL,
      numparts int NOT NULL,
      numpoints int NOT NULL,
      polygons ".$geotype." NOT NULL,
      SPATIAL KEY (polygons),
      PRIMARY KEY (".$pri_key_name.")
    ) ENGINE = MYISAM;";
  echo "QUERY:".$query_for_create_tbl."</br>";
  $cr = mysql_query($query_for_create_tbl);
  if ($cr==TRUE) echo "success_dst_tbl _creation<br />";
  else echo "fail to create dst_table <br />";
  //mysql_free_result($cr); ->doesn't need because return is small

  return $tblname;
}
function create_dbf_tbl($dbname, $tblname, $dbfHeader, $pri_key_name)
{
  if(table_exist ($dbname, $tblname)){
    echo "table ".$tblname." already exist ... return the table name</br>";
    $drop = "DROP TABLE ".$tblname.";";
    $dr = mysql_query($drop);
    if($dr == FALSE) die ("dropping dst tbl failed");
  }

  //$column_string = $pri_key_name." BIGINT UNSIGNED NOT NULL AUTO_INCREMENT UNIQUE,";
  $column_string = $pri_key_name." BIGINT UNSIGNED,"; //give up: identifierness, copy of record_number of shape file.
  for ($i = 0; $i < count($dbfHeader) ; $i++) {
    //foreach ($dbfHeader[$i] as $key => $value){
    //  echo $key . "=>" . $value . ", ";
    // }
    $name = $dbfHeader[$i]["name"];
    $type = $dbfHeader[$i]["type"];
    $len = $dbfHeader[$i]["length"];
    $mysql_type = get_mysqltype_from_dbf_header($type, $len);
    $column_string = $column_string." ".$name." ".$mysql_type.",";
  }
  $column_string = $column_string." PRIMARY KEY (".$pri_key_name.")";
  $query_for_create_tbl
    = "CREATE TABLE ".$tblname." (".$column_string.");";

  echo "query_for_create_tbl = ".$query_for_create_tbl."</br>";
  $cr = mysql_query($query_for_create_tbl);
  if ($cr==TRUE) echo "success_dbf_tbl _creation<br />";
  else { echo "fail to create dbf_table <br />";
 	die("dbf tbl not created");
  }
  //mysql_free_result($cr); ->doesn't need because return is small

  return $tblname;
}
function describe_table($tblname)
{
  /* Create and execute query. */
  $query3 = "DESCRIBE ".$tblname;
  $result3 = mysql_query($query3);
  if($result3 == FALSE) echo "Result of query ".$query3." : false ---output<br />";
  else {
    echo "Result of query ".$query3." : true<br />";
    $cnt = mysql_numrows($result3);
    for($i = 0 ; $i < $cnt ; $i++){
      $Field = mysql_result($result3, $i, "Field");
      $Type = mysql_result($result3, $i, "Type");
      $Null = mysql_result($result3, $i, "Null");
      $Key = mysql_result($result3, $i, "Key");
      $Default = mysql_result($result3, $i, "Default");
      $Extra = mysql_result($result3, $i, "Extra");
      echo "$Field | $Type | $Null | $Key | Default | $Extra <br/>";
    }
  }
}

function get_sql_result($db_conn, $tbl_shp, $prikey_name)
{
  $db_host = $db_conn['host'];
  $db_name = $db_conn['user'];
  $db_pass = $db_conn['passwd'];
  $database = $db_conn['db'];

//echo "----DB login info:".$db_host.", ".$db_name.", ".$db_pass."</br>";
//echo "DB login info:".$db_host.", ".$db_name.", ".$db_pass.", DB: ".$database."</br>";
  $db_conn = mysql_connect($db_host, $db_name, $db_pass);
  @mysql_select_db($database) or die ("Could not select db");

  echo "get_sql_result ----- tblshp: ".$tbl_shp."</br>";

  // 1) Get PriVal from DB.
  $key_sql = "SELECT ".$prikey_name." FROM ".$tbl_shp; 

  echo "key-sql: ".$key_sql."</br>";
  $result_keys = mysql_query($key_sql);

  return $result_keys;
}

function get_sql_geometry($tbl_shp, $prikey_name, $prival)
{
  $dst_sql = "SELECT xmin, xmax, ymin, ymax, numparts, numpoints, ASTEXT(polygons)     
        FROM ".$tbl_shp." 
        WHERE ".$prikey_name."=\"".$prival."\"";
  //echo "_get_sql_geom query: $dst_sql </br>";

  $result_dst = mysql_query($dst_sql);
  if($result_dst == FALSE) echo "ERROR ".$count." query for dst elt failed</br>";
  //else echo "OK ".$count." from query for dst element fetch</br>";

  // extent are not accurate
  $xmin = mysql_result($result_dst, 0, "xmin");
  $xmax = mysql_result($result_dst, 0, "xmax");
  $ymin = mysql_result($result_dst, 0, "ymin");
  $ymax = mysql_result($result_dst, 0, "ymax");
  $nprt = mysql_result($result_dst, 0, "numparts");
  $npt  = mysql_result($result_dst, 0, "numpoints");
  $pg   = mysql_result($result_dst, 0, "ASTEXT(polygons)");
  mysql_free_result($result_dst);

  return array($xmin, $xmax, $ymin, $ymax, $nprt, $npt, $pg);
}

function mysqltxt_to_wktpolygon($pgn)
{
  $wkt2polygon = new Wkt2PhpPolygon();

  $arrayParts = 0;
  /////////////////////////////////////////////////////////////
  $bMultiPolygon = TRUE; // currently every geom is multipolygon.
  $geotype = 0;
  if($bMultiPolygon==TRUE)
  {
      // arrayParts is an array of parts, part is ..., xi, yi, x(i+1), y(i+1),...
      $arrayParts = $wkt2polygon->convert($pgn);
      $geotype = $wkt2polygon->getGeoType($pgn);
  }
  else
  {
      // ..., xi, yi, x(i+1), y(i+1),...
      $arrayParts = $wkt2polygon->convert2simplePointArray($pgn);
  }
  // currently filtering is not computed for performance reason.
  //$array_part_filtered = eliminate_tooclose_points($arrayParts, $neighbor_bound);
  $array_part_filtered = $arrayParts;

  echo "FINAL geo type: ".$geotype." in mysqltxt_to_wktpolygon</br>";
  return $array_part_filtered;
}
 
// dst_tbl, colu. name for primary key -> array of  n array
// An array = array(prikey, minx, maxx, miny, maxy, polygonarray)
function get_geometry_from_shptable($db_conn, $tbl_shp, $neighbor_bound)
{
  $prikey_name = "primary_key";
  $result_keys = get_sql_result($db_conn, $tbl_shp, $prikey_name);

  if($result_keys == FALSE) { 
//    echo "XXXXXget_num_tbl error: query FALSE</br>"; 
    return FALSE; 
  }
  //else echo "NOT false query for select geom w/ result: ".$result_keys."</br>";
  $num_geom = mysql_num_rows($result_keys);

  if($num_geom <= 0) { echo "XXXXXXget_prikey_geom_tbl error: return Geom".$num_geom." <=0</br>"; return FALSE; }

  // 2) Access DB for the prival value.
  //echo "Num geom returned ".$num_geom."</br>"; 
  // We assume pri-key is identifier for geometry. Fix if not the case.
  //$arr_xn = array(); $arr_xx = array(); $arr_yn = array(); $arr_yx = array(); 
    
  //$arr_prt = array(); $arr_pt = array(); $arr_poly = array();
  $arr_pk_geom = array();
  for ($count=0; $count < $num_geom; $count++) 
  {
    $prival = mysql_result($result_keys, $count, "primary_key");

    list($xmin, $xmax, $ymin, $ymax, $nprt, $npt, $pg) = get_sql_geometry($tbl_shp, $prikey_name, $prival);

    //$arr_xn[$prival] = $xmin; $arr_xx[$prival] = $xmax; $arr_yn[$prival] = $ymin; $arr_yx[$prival] = $ymax; 
    //$arr_prt[$prival] = $nprt; $arr_pt[$prival] = $npt;
    //$arr_poly[$prival] = $pg;

    /*
    echo "__________________________________</br>";
    echo "Species_serial id: $prival, 
      //Extent: ($xmin)($xmax)($ymin)($ymax) <br/>
      Numparts: $nprt 
      Numpoint: $npt <br />";
      //print_r($pg); // too big
      //echo "</br>__________________________________</br>";
      flush();
      ob_flush();
     */

    $array_part_filtered = mysqltxt_to_wktpolygon ($pg);

    $cw_bbx_array = array_map('compute_bbx_bcw', $array_part_filtered);

    $array_bbx = array_map ('geoinfo_ht_bbx', $cw_bbx_array); // bbx is an array (xmin, xmax, ymin, ymax)
    list($xmin, $xmax, $ymin, $ymax) = computeBoundingBoxFromParts($array_bbx);

    $arr_pk_geom[$prival] = array($xmin, $xmax, $ymin, $ymax, 
                                  $array_part_filtered, $cw_bbx_array);
  }
  return $arr_pk_geom;
}

function geom_in_db_to_tile($db_connect_info, $table_name_shp, $table_name_dbf, $neighbor_bound, 
                            $tile_folder_name, $zoomLevel, $imgLimitPerLevel, $geotype, $pickorviz)
{
  echo "In geom_in_db_to_tile: tblname: $table_name_shp </br>";
  $prikey_name = "primary_key";
  $result_keys_shp = get_sql_result($db_connect_info, $table_name_shp, $prikey_name);
  $result_binomial_dbf = get_sql_result($db_connect_info, $table_name_dbf, $prikey_name);

  if($result_keys_shp == FALSE) 
  { 
    echo "XXXXXget_num_tbl error: query FALSE</br>"; 
    return FALSE; 
  }
  else 
  {
    echo "NOT false query for select geom w/ result: ".$result_keys_shp."</br>";
  }
  $num_geom = mysql_num_rows($result_keys_shp);

  if($num_geom <= 0) { echo "XXXXXXget_prikey_geom_tbl error: return Geom--".$num_geom." <=0</br>"; return FALSE; }
  else { echo "Total Num geometry returned from DB: ".$num_geom."</br>"; }

  $init_cntr = 0;
  $end_cntr = $num_geom - 1;

  for ($count=$init_cntr; $count <= $end_cntr; $count++) 
  {
    $prival = mysql_result($result_keys_shp, $count);
    $binomial = mysql_result($result_binomial_dbf, $count);

    echo "prival: $prival from idx:$count </br>";
    list($xmin, $xmax, $ymin, $ymax, $nprt, $npt, $pg) = get_sql_geometry($table_name_shp, $prikey_name, $prival);

    echo "element order in shp: $count, numpart: $nprt, numpoint: $npt, prival: $prival, zoomlevel: $zoomLevel </br>";
    echo "binomial: $binomial </br>";

    //if($nprt < 2) continue; // line for generating tiles again for multipolygon

    $array_part_filtered = mysqltxt_to_wktpolygon ($pg);

    $cw_bbx_array = array_map('compute_bbx_bcw', $array_part_filtered);

    $array_bbx = array_map ('geoinfo_ht_bbx', $cw_bbx_array); // bbx is an array (xmin, xmax, ymin, ymax)
    list($xmin, $xmax, $ymin, $ymax) = computeBoundingBoxFromParts($array_bbx);

    $shpelt_geo_info = array($xmin, $xmax, $ymin, $ymax, $array_part_filtered, $cw_bbx_array);
    
/*
    echo "__________________________________</br>";
    echo "Species_serial id: $prival, 
      Extent: ($xmin)($xmax)($ymin)($ymax) <br/>
      Numparts: $nprt 
      Numpoint: $npt <br />";
      print_r($pg); // too big
      echo "</br>__________________________________</br>";
      flush();
      ob_flush();
 */  
    computeTileImageZoom($tile_folder_name, $prival, $shpelt_geo_info, $zoomLevel, $imgLimitPerLevel, $geotype, $pickorviz);
  }
  mysql_close();
}

function geom_in_db_to_tile_range($db_connect_info, $table_name_shp, $table_name_dbf, $neighbor_bound, $tile_folder_name, $zoomLevelMin, $zoomLevelMax, $imgLimitPerLevel, $geotype, $pickorviz)
{
echo "zoom min: $zoomLevelMin, max: $zoomLevelMax </br>";

	for ($zoom=$zoomLevelMin;$zoom<=$zoomLevelMax;$zoom++) {
		geom_in_db_to_tile($db_connect_info, $table_name_shp, $table_name_dbf, $neighbor_bound, $tile_folder_name, $zoom, $imgLimitPerLevel, $geotype, $pickorviz);
	}
}

function compute_bbx_bcw ($part_geom)
{
  $geoinfo_hash = array();
  $geoinfo_hash['CW'] = isCWNEW($part_geom);
  $geoinfo_hash['bbx'] = computeBoundingBoxPart($part_geom);
  return $geoinfo_hash;
}

function print_output_geom_from_shptable($array_pk_geom)
{
  print_r(array_keys($array_pk_geom));
  $num_part = count($array_pk_geom);
  for($cnt = 0; $cnt < $num_part ; $cnt++)
  {
    $key_arr = array_keys($array_pk_geom);
    $object_id = $key_arr[$cnt];
    list ($xmin, $xmax, $ymin, $ymax, $polygonArrayGeo) = $array_pk_geom[$object_id];
    $out = $array_pk_geom[$object_id];
    echo $cnt.": prikey : ".$object_id."</br>";
    echo "xmin,xmax (".$xmin.", ".$xmax.") ymin,ymax (".$ymin.", ".$ymax.")</br>";
    print_r ($polygonArrayGeo);
    echo "</br>";
  }
}


?>
