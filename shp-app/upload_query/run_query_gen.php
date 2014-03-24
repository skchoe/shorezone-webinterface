<?php

require_once(dirname(__FILE__)."/../../viz/GoogleMapUtility.php");
require_once(dirname(__FILE__)."/../../viz/dataUtils.inc.php");
require_once(dirname(__FILE__)."/../../shp-app/mysql_utils.php");
require_once(dirname(__FILE__)."/upload_utils.php");

function stripSides($in_string, $str1, $str2)
{
	$regex = "/^".$str1."(.*)".$str2."$/";
	$instring_trim = trim($in_string);
	if(preg_match($regex, $in_string, $matches))
		return trim($matches[1]);
	else
		return -1;
}

//this script may require additional memory and time
set_time_limit(10);
ini_set('memory_limit',-1);

// species_id for sql query
$shpname = $_GET["ShapeName"];
$tblname = $_GET["TableNameDbf"];
$lat = $_GET["lat"];
$lng = $_GET["lng"];
$zoom = $_GET["zoom"];
$rep = $_GET["rep"]; // can be either "table" "json"
$pickorviz = $_GET["pickorviz"]; // pick or viz
$id = $_GET["id"]; // pick or viz
$json_meta_names = $_GET["meta_names"];

$meta_names = array();
//echo "0) json meta names: ".$json_meta_names."</br>";
$listPairs = explode(",", $json_meta_names);
//print_r($listPairs);
//echo "</br>";
$json_meta_names_nobr = stripSides($json_meta_names, "{", "}");
if(-1 != $json_meta_names_nobr)
{
	$pairList = explode(",", $json_meta_names_nobr);
	for($i=0;$i<count($pairList);$i++)
	{
		$pair = explode(":", $pairList[$i]);
		$key = stripSides($pair[0], "\"", "\"");
		$value = stripSides($pair[1], "\"", "\"");
		if(-1 != $key && -1 != $value)
			$meta_names[$key] = $value;
	}
}
//echo "</br>";

//echo "1) run_query_.php -- shpname: $shpname, tblname: $tblname, $lat, $lng, $zoom, $rep </br>";

// (lat,lng) -> Corresponding Tile ID under a zoom
$tile_id = GoogleMapUtility::toTileXY($lat, $lng, $zoom);
// (lat,lng) -> pixel coordinate under a zoom
$pixel = GoogleMapUtility::getPixelOffsetInTile($lat, $lng, $zoom);
//$pixel->x = $pixel->x - $tile_id->x *  GoogleMapUtility::TILE_SIZE;
//$pixel->y = $pixel->y - $tile_id->y *  GoogleMapUtility::TILE_SIZE;
//echo "2) TileID: ($tile_id->x, $tile_id->y), pixelId: ($pixel->x, $pixel->y) </br>";

// DB connection to get list of `source_gri'
require_once (dirname(__FILE__)."/../../db2tile/db_credentials.php");
//echo "3) host = $db_host, dbuser = $db_name, db_pass= $db_pass, dbname = $database </br>";
$conn = mysql_connect($db_host, $db_name, $db_pass)
or die("Could not select database! - runQueryZ11");

$db_list = mysql_list_dbs($conn);
@mysql_select_db($database);

//if($conn) echo "4) CONNECT Succeed among dbs: $db_list</br>";

$b_tbl_exst = table_exist($database, $tblname);
//if($b_tbl_exst) echo "Table $tblname Exists</br>";
//else echo "Table $tblname NONONOT exist</br>";

$arr_field_name = table_field_names($tblname);

$tiles_folder = dirname(__FILE__)."/../../../tiles/".$shpname."_".$pickorviz;

list($dirs, $files) =  get_dirs_files ($tiles_folder);

/*
echo "TILE_ID = (".$tile_id->x.", ".$tile_id->y.")<br/>"; echo "PIXEL XY = (".$pixel->x.", ".$pixel->y.")<br/>";
echo "5) Tiles folder = $tiles_folder  </br>";
echo "id: ".$id."</br>";
*/

$arr_of_metadata_array = collect_metadata($tblname, $arr_field_name, $tiles_folder, $zoom, $dirs, $tile_id, $pixel, $rep);


if($rep == "table") {

?>
<html>
<head>
</head>


<body>
<?php
  $arr_of_metadata = $arr_of_metadata_array[0];
  $num_valids = count ($arr_of_metadata);
  $totalLayers = $arr_of_metadata_array[2];
  
  $styleTable = "style=\"border:2px solid black;\"";
  $styleSubTable = "style=\"border:1px solid black;\"";
  $styleCell = "style=\"border:1px solid green;\"";
  $htmlTable = "<TABLE ".$styleTable.">";
  $htmlTable .= "<TR><TD colspan='".$num_valids."'><FONT SIZE='4' COLOR='Teal'>\"".$shpname."\" SHAPE LAYERS: ".$num_valids." results from ".$totalLayers." layers.</FONT></TD></TR>";

  if($arr_of_metadata != NULL) 
  {
    //print_r($arr_of_metadata);
    $htmlTable.="<TR>";
    for($i = 0;$i < $num_valids;$i++) 
    {
      // viz data into table
      $metaarray = $arr_of_metadata[$i];
      
      // UNIT_ID check to be valid
      $idxUnitId = $meta_names[$id];
      if($metaarray[$idxUnitId] != "")
      {
        $htmlTable.="<TD>";
        $htmlTable.="<TABLE ".$styleSubTable.">";
	/*
        echo "..............".$idxUnitId.":, metaarray: </br>";
	print_r($metaarray);
	echo "</br>";
	*/
	$keyarr1 = array_keys($meta_names);
	$keyarr2 = array_keys($metaarray);
        foreach($meta_names as $desc => $idx)
        {
          $htmlTable.="<TR>";
	  if(in_array($desc, $keyarr1) && in_array($idx, $keyarr2)) 
	  {
          	$htmlTable.="<TD ".$styleCell.">".$desc."</TD><TD ".$styleCell.">".$metaarray[$idx]."</TD>";
	  }
	  else
	  {
		$htmlTable.="<TD ".$styleCell.">".$desc."</TD><TD></TD>";
	  }
          $htmlTable.="</TR>";
        }
        $htmlTable.="</TABLE>";
        $htmlTable.="</TD>";
      }
    }
    $htmlTable.="</TR>";
  }
  $htmlTable .= "</TABLE></br>";
  echo $htmlTable;
  flush();
  @ob_flush();
?>
</body>
</html>

<?php
}
else if ($rep == "json") {
  //echo "XXXXXXXXXXXXXXXX json: $rep </br>";

  $json_data = array();
  if($arr_of_metadata_array != NULL) {
    for($i = 0;$i < $num_valids;$i++) {
      // viz data into table
      $metaarray = $arr_of_metadata_array[$i];
      //echo "0th: $metaarray[0] , 3th: $metaarray[3] </br>";
      //$json_data[$metaarray[0]] = $metaarray[3]; 
      $json_data[] = $metaarray[3];
    }
  }
  //print_r($json_data);
  sort($json_data);
  //print_r($json_data);

  //$json_data = array ('1'=>'1', '2'=>'2', '3'=>'3');
  echo json_encode($json_data);
}
else {
  echo "XXXXXXXXXXXXXXXX neither table nor json: $rep </br>";
}

// actual search through images happen
// $rep is used for displaying progress bar in web-page or iphone table
/*
function collect_metadata($tblname, $arr_field_name, $tiles_folder, $zoom, $dirs, $tile_id, $pixel, $rep)
{
  $aofa = NULL;

  $numDirs = count($dirs);
  $returnedSerialNumber = 0;
  // LOOP through layers(records)
  $old_progress = -1;

//echo "6) in collect_metadata </br>";

  for ($i=0; $i < $numDirs ; $i = $i + 1) 
  {
    // Access tile image by grabbing folder names, extract id from it.
    $fname = $dirs[$i];
    $id = substr($fname, strlen("id_"));
    $filename = $tiles_folder."/".$fname."/".$zoom."/c_".$id."_".$tile_id->x."_".$tile_id->y."_".$zoom.".png";
  
    // evaluate file existence, pixel value
    $b_loc = is_non_zero_pixel($filename, $pixel->x, $pixel->y);
    if ($b_loc) {
      $returnedSerialNumber++;
  
      // get data from table
      $arr_meta = get_metadata_array($tblname, $arr_field_name, $id);
      if($arr_meta != NULL) {
        if ($aofa == NULL) $aofa = array();
	array_push($aofa, $arr_meta);
      }
    }
  }
  //echo "</br>";
  return $aofa;
}
*/
function collect_metadata($tblname, $arr_field_name, $tiles_folder, $zoom, $dirs, $tile_id, $pixel, $rep)
{
  $aofa = NULL;
  $ltm = NULL;

  $numIdsDirs = count($dirs); //
  $num_species = 0;
  $old_progress = -1;

  for ($i=0; $i < $numIdsDirs ; $i = $i + 1)
  {
    $time_begin = microtime();

    // Access tile image by grabbing folder names, extract id from it.
    $gname = $dirs[$i]; // "ids_XXXX"

    $tiles_ids_folder = $tiles_folder."/".$gname;
    list($subdirs, $subfiles) = get_dirs_files($tiles_ids_folder);
    $numIdDirs = count($subdirs);
    // loop for {id_xxxitems}
    for($j=0; $j<$numIdDirs ; $j++)
    {
      $num_species++;
      $fname = $subdirs[$j];
      $id = substr($fname, strlen("id_"));

      $filename = ""; // amphibians has hierarchical structure by longitude
      $filename = $tiles_ids_folder."/".$fname."/z_".$zoom."/x_".$tile_id->x."/c_".$id."_".$tile_id->x."_".$tile_id->y."_".$zoom.".png";

//echo "file name: ".$filename."</br>";
      // evaluate file existence, pixel value
      $b_loc = is_non_zero_pixel($filename, $pixel->x, $pixel->y);
      if ($b_loc) {
        // get data from table
        $arr_meta = get_metadata_array($tblname, $arr_field_name, $id);
        if($arr_meta != NULL) {
          // metadata
          if ($aofa == NULL) $aofa = array();
          array_push($aofa, $arr_meta);

          // elapsed time
          if ($ltm == NULL) $ltm = array();
          $time_end = microtime();
          array_push($ltm, $time_end - $time_begin);
        }
      }
    }
  }
  return array($aofa, $ltm, $num_species);
}


// $tblname : table name in DB
// $id primary key name
// output array of whole row
// global var: connection parameters to mysql table.
function get_metadata_array($tblname, $arr_field_name, $id)
{
  $sql_query = "SELECT * FROM ".$tblname." WHERE primary_key = ".$id;
  $result = mysql_query($sql_query);
  if(!$result) {
    //echo "Query failed for $id </br>";
    mysql_free_result($result);
    return NULL;
  }
  else {
    $col2val = function($res, $col) {
      //echo "res: $res, col: $col </br>";
      return mysql_result($res, 0, $col);
    };
    $arr_res = build_array(count($arr_field_name), $result);
    $arr_meta = array_map($col2val, $arr_res, $arr_field_name);
    mysql_free_result($result);
    return $arr_meta;
  }
}

// $filename - full path string for filename
// $x, $y pixel value
// detect 1. file existence, 2 evaluate pixel if it is (0 0 0) -> FALSE, else TRUE
function is_non_zero_pixel($filename, $x, $y)
{
  // Stage 1 checking
  if(file_exists($filename)) {
    //echo $filename." Passed stage 1 ---> ";
    $im = imagecreatefrompng($filename);
    $index = imagecolorat($im, $x, $y);
    $rgb = imagecolorsforindex($im, $index);
    $r = $rgb["red"];
    $g = $rgb["green"];
    $b = $rgb["blue"];

    //echo $filename."</br>(((".$r.":".$g.":".$b."))) </br>";

    // Stage 2 checking
    if(($r==0)&&($g==0)&&($b==0)) return FALSE;  //echo "Failed final stage </br>";
    else return TRUE;
  }
  else {
    //echo $filename." Failed stage 1 :";
  }
}

?>
