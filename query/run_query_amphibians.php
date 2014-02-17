<html>
<head>
<!--
<style type="text/css">  
table { table-layout: fixed;  
        margin-left: 2em;  
        margin-right: 2em }   
</style> 
-->
</head>


<body>
<?php

require_once(dirname(__FILE__)."/../viz/GoogleMapUtility.php");
require_once(dirname(__FILE__)."/../viz/dataUtils.inc.php");
require_once(dirname(__FILE__)."/../shp-app/mysql_utils.php");
require_once(dirname(__FILE__)."/../shp-app/upload_query/upload_utils.php");
require_once (dirname(__FILE__)."/../db2tile/db_credentials.php");

//this script may require additional memory and time
set_time_limit(10);
ini_set('memory_limit',-1);

// species_id for sql query
$shpname = $_GET["ShapeName"];
$tblname = $_GET["TableNameDbf"];
$lat = $_GET["lat"];
$lng = $_GET["lng"];
$zoom = $_GET["zoom"];

//echo "shpname: $shpname, tblname: $tblname, $lat, $lng, $zoom </br>";

// (lat,lng) -> Corresponding Tile ID under a zoom
$tile_id = GoogleMapUtility::toTileXY($lat, $lng, $zoom);
// (lat,lng) -> pixel coordinate under a zoom
$pixel = GoogleMapUtility::getPixelOffsetInTile($lat, $lng, $zoom);
//$pixel->x = $pixel->x - $tile_id->x *  GoogleMapUtility::TILE_SIZE;
//$pixel->y = $pixel->y - $tile_id->y *  GoogleMapUtility::TILE_SIZE;
//echo "TileID: ($tile_id->x, $tile_id->y), pixelId: ($pixel->x, $pixel->y) </br>";

// DB connection to get list of `source_gri'
//echo "host = $db_host, dbuser = $db_name, db_pass= $db_pass, dbname = $database </br>";
$conn = mysql_connect($db_host, $db_name, $db_pass)
or die("Could not select database! - run_query_amphibians");

$db_list = mysql_list_dbs($conn);
@mysql_select_db($database);

//if($conn) echo "CONNECT Succeed among dbs: $db_list</br>";

$b_tbl_exst = table_exist($database, $tblname);
//if($b_tbl_exst) echo "Table $tblname Exists</br>";
//else echo "Table $tblname NONONOT exist</br>";

$arr_field_name = table_field_names($tblname);

// viz for columns
//echoTable($arr_field_name, array());

$tiles_folder = dirname(__FILE__)."/../tiles/".$shpname;

list($dirs, $files) =  get_dirs_files ($tiles_folder);
//echo "DIRRRRRRRR--";
//print_r($dirs);
//echo "</br>";
$initCount = 0;
$endCount = count($dirs);
$returnedSerialNumber = 0;

//echo "TILE_ID = (".$tile_id->x.", ".$tile_id->y.")<br/>";
//echo "PIXEL XY = (".$pixel->x.", ".$pixel->y.")<br/>";
//echo "Tiles folder = $tiles_folder  </br>";

// LOOP through layers(records)
for ($count=$initCount; $count < $endCount ; $count = $count + 1) 
{
  $id = $count + 1; // corresponds to primary_key
   
  // Access tile image
  // Tile y adjustment (currently shift down 1)
  $tile_id_y = $tile_id->y - 1;
  $filename = $tiles_folder."/id_".$id."/".$zoom."/c_".$id."_".$tile_id->x."_".$tile_id_y."_".$zoom.".png";

  //if(file_exists($filename)) echo "File Exists = $filename </br>";
  //else echo "File Not exist: $filename </br>";

  // Stage 1 checking
  if(file_exists($filename)) {
    //echo $source_gri." Passed stage 1 ---> ";
    $im = imagecreatefrompng($filename);
    $index = imagecolorat($im, $pixel->x, $pixel->y);
    $rgb = imagecolorsforindex($im, $index);
    $r = $rgb["red"];
    $g = $rgb["green"];
    $b = $rgb["blue"];

    //echo $filename."</br>(((".$r.":".$g.":".$b."))) </br>";

    // Stage 2 checking
    if(($r==0)&&($g==0)&&($b==0)) $r+1;//echo "Failed final stage :";
    else {
     	$returnedSerialNumber++;

      $sql_query = "SELECT * FROM ".$tblname." WHERE primary_key = ".$id;
      $result = mysql_query($sql_query);
      if(!$result) echo "Query failed for $id </br>";
      else {
        $arr_res = build_array(count($arr_field_name), $result);
        $arr_meta = array_map(col2val, $arr_res, $arr_field_name);
        //echoTable(array(), array($arr_meta));
        $sci_name = $arr_meta[3];
        $queryTerm = create_link_external($sci_name);
	$vizTerm = create_layer_link($id, $sci_name, "SPECIES1109_DBO_VIEW_AMPHIBIANS_dst");
  	echo $sci_name." ";
        echo "<a href='".$queryTerm."' target='_blank'>[Details in AmphibiaWeb]</a> <a href='".$vizTerm."' target='_blank'> [Check map in Zoom=11] </a> </br></br>";
        flush();
        @ob_flush();

        mysql_free_result($result);
      }
    }
  }
  else {
    //echo $source_gri." Failed stage 1 :";
  }
}

echo "___________________________________________________________________<br/>";
//echo "Number of returned species :$returnedSerialNumber out of $endCount <br/>";
echo "Number of returned species :$returnedSerialNumber out of about 8000 <br/>";

function create_link_external($name)
{
  $ref_string = "http://amphibiaweb.org/cgi-bin/amphib_query?where-scientific_name=";
  $ref_google_string = "http://www.google.com/#hl=en&q=";

  $arr_nm = explode(" ", $name);
  if (count($arr_nm) == 0) 
    return "http://amphibiaweb.org";
  else if (count($arr_nm) == 1) 
    return $ref_string.$arr_nm[0];
  else if (count($arr_nm) == 2) 
    return $ref_string.$arr_nm[0]."+".$arr_nm[1];
  else 
    return $ref_google_string.$name;
}

function create_layer_link ($key, $name, $tbl_name)
{
  $ref_string = "http://155.97.130.88/wss_maps/viz/amphibiaExtent.php";
  $arr_nm = explode(" ", $name);
  if (count($arr_nm) == 0) 
    return $ref_string;
  else if (count($arr_nm) == 1) 
    return $ref_string."?primary_key=".$key."&sc_name=".$arr_nm[0]."&dst_tbl=".$tbl_name;
  else if (count($arr_nm) == 2) 
    return $ref_string."?primary_key=".$key."&sc_name=".$arr_nm[0]."%20".$arr_nm[1]."&dst_tbl=".$tbl_name;
  else 
    return $ref_string."?primary_key=".$key."&sc_name=".$arr_nm[0]."%20".$arr_nm[1]."%20".$arr_nm[2]."&dst_tbl=".$tbl_name;
}

function col2val($res, $col) 
{ 
  /*echo "res: $res, col: $col </br>";*/ 
  return mysql_result($res, 0, $col); 
}

?>
</body>
</html>
