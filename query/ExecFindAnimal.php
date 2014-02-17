<?php
// natureserve.org
//
// runs same as runQuery but output species are collected and displayed to table.
// Hover Dam, NV
// http://155.97.130.88/wss_maps/query/ExecFindAnimal.php?lat=36.015227&lng=-114.737778&zoom=11&amp=1&brd=1&mml=1&rtl=1
// Santa Fe, NM
// http://155.97.130.88/wss_maps/query/ExecFindAnimal.php?lat=35.657296&lng=-105.954895&zoom=11&amp=1&brd=1&mml=1&rtl=1
// Las Alamos Airport, NM
// http://155.97.130.88/wss_maps/query/ExecFindAnimal.php?lat=35.878897&lng=-106.269207&zoom=11&amp=1&brd=1&mml=1&rtl=1
// Rocky Mtn National Park, CO
// http://155.97.130.88/wss_maps/query/ExecFindAnimal.php?lat=40.284240&lng=-105.688133&zoom=11&amp=1&brd=1&mml=1&rtl=1
// Zion National Park, UT
// http://155.97.130.88/wss_maps/query/ExecFindAnimal.php?lat=37.289077&lng=-113.048973&zoom=11&amp=1&brd=1&mml=1&rtl=1
require_once('../viz/GoogleMapUtility.php');
require_once("../viz/dataUtils.inc.php");

//this script may require additional memory and time
set_time_limit(10);
ini_set('memory_limit',-1);


// species_id for sql query
$lat = $_GET["lat"];
$lng = $_GET["lng"];
$zoom = $_GET["zoom"];
$tg_a = $_GET["amp"]; // amphibeans taxonomy group
$tg_b = $_GET["brd"]; // bird taxonomy group
$tg_m = $_GET["mml"]; // mammal taxonomy group
$tg_r = $_GET["rtl"]; // raptile taxonomy group

// (lat,lng) -> Corresponding Tile ID under a zoom
$tile_id = GoogleMapUtility::toTileXY($lat, $lng, $zoom);
// (lat,lng) -> pixel coordinate under a zoom
$pixel = GoogleMapUtility::getPixelOffsetInTile($lat, $lng, $zoom);

// DB connection to get list of `source_gri'
require("../db2tile/db_credentials.php");
$conn = mysql_connect($db_host, $db_name, $db_pass);
//if($conn == FALSE) echo "false conn<br />";
//else echo "connection estabilished<br/>";
$db_list = mysql_list_dbs($conn);
@mysql_select_db($database)
or die("Could not select database!");

$species_tbl = "species_feature_tbl";

// output array =--> will be a table.
// we can use array_merge for each time, but this way is faster.
$outputArray = array(array("Group", "Name", "Map/ID"));
$numberOfReturnedSpecies = 0;
$oldNumber = 0;
if($tg_a=="1") 
{
  $where_conds = " taxon_gr_1 = \"Amphibian\" ";
  $result_idset = querySpecies($species_tbl, $where_conds);
  list($outputArray, $numberOfReturnedSpecies) = checkPixel($outputArray, $numberOfReturnedSpecies, $result_idset, $tile_id, $pixel, $zoom, $lat, $lng);
  mysql_free_result($result_idset);
}
$oldNumber = $numberOfReturnedSpecies;
echo "Number of  species in Grp Amp = $numberOfReturnedSpecies <br/>";

if($tg_b=="1") 
{
  $where_conds = " taxon_gr_1 = \"Bird\" ";
  $result_idset = querySpecies($species_tbl, $where_conds);
  list($outputArray, $numberOfReturnedSpecies) = checkPixel($outputArray, $numberOfReturnedSpecies, $result_idset, $tile_id, $pixel, $zoom, $lat, $lng);
  mysql_free_result($result_idset);
}
$newNumber = $numberOfReturnedSpecies - $oldNumber;
$oldNumber = $numberOfReturnedSpecies;
echo "Number of species in Grp Brd = $newNumber <br/>";

if($tg_m=="1") 
{
  $where_conds = " taxon_gr_1 = \"Mammal\" ";
  $result_idset = querySpecies($species_tbl, $where_conds);
  list($outputArray, $numberOfReturnedSpecies) = checkPixel($outputArray, $numberOfReturnedSpecies, $result_idset, $tile_id, $pixel, $zoom, $lat, $lng);
  mysql_free_result($result_idset);
}
$newNumber = $numberOfReturnedSpecies - $oldNumber;
$oldNumber = $numberOfReturnedSpecies;
echo "Number of species in Grp Mml = $newNumber <br/>";

if($tg_r=="1") 
{
  $where_conds = " taxon_gr_1 = \"Reptile\" ";
  $result_idset = querySpecies($species_tbl, $where_conds);
  list($outputArray, $numberOfReturnedSpecies) = checkPixel($outputArray, $numberOfReturnedSpecies, $result_idset, $tile_id, $pixel, $zoom, $lat, $lng);
  mysql_free_result($result_idset);
}
$newNumber = $numberOfReturnedSpecies - $oldNumber;
$oldNumber = $numberOfReturnedSpecies;
echo "Number of species in Grp Rep = $newNumber<br/>";
echo "Number of all species in all Grps Amp&Brd&Mml&Rep = $numberOfReturnedSpecies<br/>";

reset($outputArray);

///////////////////////////////////////////////////////////////////////////////////////////////////
// Start php page w/ html content

echo "<head>
<link rel=\"stylesheet\" type=\"text/css\" 
	media=\"only screen and (max-width: 480px)\" />
<!--[if IE]>
	<link rel=\"stylesheet\" type=\"text/css\" href=\"explorer.css\" media=\"all\" />
<![endif]-->
<meta name=\"viewport\" content=\"user-scalable=no, width=device-width\" />

<title> Species ($lat, $lng) </title>
</head>";


// Draw table 
// headers
$headers = $outputArray[0];
echo "<table border=1> <tr>";
foreach ($headers as $header) 
{
	echo "\t\t<td>
	<FONT face=\"Verdana,Helvetica\" SIZE=\"3\" COLOR=\"#000000\">
	<P ALIGN=Center>
	<B>$header</B>
	</FONT>
	</td>\n";
}

echo "\t</tr>\n";

// loop in array
$end = count($outputArray);
for($p=1;$p < $end; $p++)
{
	echo "\t<tr>";
	foreach ($outputArray[$p] AS $content) {
		echo "\t\t<td>
		<FONT face=\"Verdana,Helvetica\" SIZE=\"3\" COLOR=\"#000000\">
		<P ALIGN=Center>"
		.$content."&nbsp;
		</FONT>
		</td>\n";
	}
	echo "\t</tr>\n";
}
echo "</table>";



function querySpecies($species_tbl, $where_conds)
{
  $query_idset = "SELECT source_gri, cm_name, sc_name, taxon_gr_1
		FROM ".$species_tbl."
		WHERE ".$where_conds."	
		ORDER BY cm_name;";
  $result_idset = mysql_query($query_idset);
  if($result_idset == FALSE) echo "false output<br />";

  return $result_idset;
}


function checkPixel($outputArray, $numberOfReturnedSpecies, $result_idset, $tile_id, $pixel, $zoom, $lat, $lng)
{
  $initCount = 0;
  if($result_idset == TRUE)
    $endCount = mysql_numrows($result_idset) - 1;
  else echo "false output<br />";

  // LOOP through species
  //echo "TILE_ID = (".$tile_id->x.", ".$tile_id->y.")<br/>";
  //echo "PIXEL XY = (".$pixel->x.", ".$pixel->y.")<br/>";
  $tiles_dir = "../tiles/wss/tiles_11_1bit";
  for ($count=$initCount; $count <= $endCount ; $count = $count + 1) {
    $source_gri = mysql_result($result_idset, $count, "source_gri");
    $cm_name = mysql_result($result_idset, $count, "cm_name");
    $taxon_gr_1 = mysql_result($result_idset, $count, "taxon_gr_1");
    // Access tile image
    $filename = $tiles_dir."/id_".$source_gri."/c_".$source_gri."_".$tile_id->x."_".$tile_id->y."_".$zoom.".png";

    // Stage 1 checking
    if(file_exists($filename)) {
      $im = imagecreatefrompng($filename);
      $index = imagecolorat($im, $pixel->x, $pixel->y);
      $rgb = imagecolorsforindex($im, $index);
      $r = $rgb["red"];
      $g = $rgb["green"];
      $b = $rgb["blue"];

      /* error code
      $r = ($rgb >> 16) & 0xFF;
      $g = ($rgb >> 8) & 0xFF;
      $b = $rgb & 0xFF;
      */
      //echo "(((".$r.":".$g.":".$b.")))";

      // Stage 2 checking
      if(($r==0)&&($g==0)&&($b==0)) {
  	//echo "Failed final stage :";
      }
      else {
  	// These passed the 2 stage test
    	$casedCommonName = titleCase($cm_name);
        //http://www.googleguide.com/linking.html
	//$nameSearch = "<a href=\"http://images.google.com/images?q=".$casedCommonName."\">$casedCommonName</a>";
	$nameSearch = "<a href=\"http://www.google.com/search?q=".$casedCommonName."\">$casedCommonName</a>";
	$nameRep = substr($source_gri, 0, 6);
	$link = "<a href=\"http://www.westernsoundscape.org/maps/speciesExtentIphone.php?species=".$source_gri."&lat=".$lat."&lng=".$lng."\">$nameRep</a>";
	array_push($outputArray, array($taxon_gr_1, $nameSearch, $link));
	$numberOfReturnedSpecies++;
      }
    }
    //else {
    //  echo $source_gri." Failed stage 1 :";
    //}
  }

  //print_r($outputArray);
  return array($outputArray, $numberOfReturnedSpecies);
}

function titleCase($string) { 
  $len=strlen($string); 
  $i=0; 
  $last= ""; 
  $new= ""; 
  $string=strtoupper($string); 
  while ($i<$len): 
    $char=substr($string,$i,1); 
    if (ereg( "[A-Z]",$last)): 
      $new.=strtolower($char); 
    else: 
      $new.=strtoupper($char); 
    endif; 
    $last=$char; 
    $i++; 
  endwhile; 
  return($new); 
}; 
?>
