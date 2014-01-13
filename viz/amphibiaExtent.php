<?php
// GET THE idea how to communicate primary_key from client
// http://155.97.130.88/wss_maps/viz/amphibiaExtent.php?primary_key=1&sc_name=Alytes%20cisternasii&dst_tbl=SPECIES1109_DBO_VIEW_AMPHIBIANS_dst
$primary_key = $_GET["primary_key"];
$sc_name = $_GET["binomial"];
$dst_tbl_name = $_GET["dst_tbl"];

//echo "Received values: $primary_key , $sc_name, $dst_tbl_name </br></br>";
?>



<html>
<head>
    
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Amphibian Maps </title>
<link rel="stylesheet" href="http://westernsoundscape.org/css/wss.css" type="text/css" />

<script language='javascript'>

	function setAction(action)
	{
	  document.mapForm.action.value = action;
	  if (action == "extent") document.mapForm.submit();
	
	  document.images["zoomIn"].className = "toolIcon";
	  document.images["zoomOut"].className = "toolIcon"; 
	  document.images["pan"].className = "toolIcon";
	  document.images["extent"].className = "toolIcon";
	  document.images[action].className = "toolIconSelected";  	  
	}
	function mapRequest()
	{
	  if (document.mapForm.action.value != "extent") document.mapForm.submit();
	}

	function on(img)
	{
	  if (document.mapForm.action.value == img && img != "extent") document.images[img].className = "toolIconOverSelected";
	  else document.images[img].className = "toolIconOver";
	}

	function off(img)
	{
	  if (document.mapForm.action.value == img && img != "extent") document.images[img].className = "toolIconSelected";
	  else document.images[img].className = "toolIcon"; 
	}
	

</script>
    
<style>
	.toolIcon
        {
	  border:1px solid white; 
	}
	.toolIconSelected
	{
	  border:1px inset black;

	}
	.toolIconOver
	{
	  border:1px dotted red;
	}
	.toolIconOverSelected
	{
	  border:1px inset red;
	}
	
</style>   


</head>

<body>

<div style="padding: 0 13px 5px 13px;">
    <form action='speciesExtent.jsp' name='mapForm' id='mapForm'>
	<input type='hidden' name='xmin' value='0'>
	<input type='hidden' name='ymin' value='0'>
	<input type='hidden' name='xmax' value='0'>
	<input type='hidden' name='ymax' value='0'>
	<input type='hidden' name='action' value='extent'>
	
	
<!-- PHP of JS?  --->

<script type="text/javascript" 
	src="http://www.google.com/maps?file=api
        &amp;v=2
        &amp;key=ABQIAAAAaRE4sBdQ5nlphMnkzzl5rxTBFUyo3DeViPXYnfsfPeq-p9LZ2hQzStuxMBsejAgeAKqObPRd35J-Bw">
</script>
	  

<!-- variable from php to javascript -->
<script type="text/javascript">
	  //var primary_key = ".$primary_key.";
	  var sid = "<?= $primary_key ?>";
//document.write( "TEST writing: "+ sid +"<br/>");
</script>


<!-- variable_for_center -->
<?php
list($xn, $xx, $yn, $yx, $npr, $npnt) = fetchMetaInfoFromDB($dst_tbl_name, $primary_key);
$centerLng = ($xn + $xx) /2.0;
$centerLat = ($yn + $yx) /2.0;


//list($zoomMin, $zoomMax) = getTileZoomRng("tiles", $primary_key, "_complete.stamp");
$zoomMin = 3;
$zoomMax = 13; // 11
?>

<font face="verdana" size = "2" color="green"> 
<?php
  echo "
  Primary Key: ".$primary_key."<br/>
  Habitat Geometry Info  : ".$npr." inner/outer polygon(s) with ".$npnt." vertices <br/>";

  $file = '../tiles/SPECIES1109_DBO_VIEW_AMPHIBIANS/id_'.$primary_key.'/'.$zoomMax.'/c_'.$primary_key.'_Zx_Zy_'.$zoomMax.'.png';

  echo "File name format: $file </br>";
?>

  Scientific Name : <?php echo $sc_name; ?><br/>
  Center Location : (<?php echo $centerLng.", ".$centerLat; ?>)<br/>
  Area Extent : Longitude(<?php echo $xn.", ".$xx;?>),
			  Latitude(<?php echo $yn.", ".$yx;?>) <br/>

</font>

<script type="text/javascript">
  /*	  
// Lake Powel
var centerLat = 37.0610334;
var centerLng = -111.265668;
  */
  // Extent for id=-1(-105.86833253402)(-103.20231249998)
  // (36.935015909389)(38.633705724297)
  /*
var centerLat = (36.935015909389 + 38.633705724297) / 2.0;
var centerLng = (-105.86833253402 + -103.20231249998) / 2.0;
  */
var centerLat = "<?= $centerLat ?>";
var centerLng = "<?= $centerLng ?>";
var Xmin = "<?= $xn ?>";
var Xmax = "<?= $xx ?>";
var Ymin = "<?= $yn ?>";
var Ymax = "<?= $yx ?>";
var zoomMin = "<?= $zoomMin ?>";
var zoomMax = "<?= $zoomMax ?>";

var map;

function load()
{
  //window.moveTo(0, 0); window.resizeTo(screen.width,screen.height-20);
  //window.moveTo(0, 0); window.resizeTo(screen.width,950);

  if (GBrowserIsCompatible()) {
	map = new GMap2(document.getElementById("amphibia_map"));
	map.addMapType(G_PHYSICAL_MAP);
	map.setMapType(G_PHYSICAL_MAP);
	
        // ====== Restricting the range of Zoom Levels =====
        // Get the list of map types      
        var mt = map.getMapTypes();
        // Overwrite the getMinimumResolution() and getMaximumResolution()
        for (var i=0; i<mt.length; i++) {
          mt[i].getMinimumResolution = function() {return zoomMin;}
          mt[i].getMaximumResolution = function() {return zoomMax;}
        }

	// control for zoom/pan
	map.addControl(new GLargeMapControl());

	// control for viz type
	var mapControl = new GMapTypeControl();
	map.addControl(mapControl);

    	// Control for overview                                                                                                                                                 
    	var ovControl = new GOverviewMapControl(null);
    	map.addControl(ovControl);

    	var bottomControlHeights = 3;
    	var pos = new GControlPosition(G_ANCHOR_BOTTOM_LEFT, new GSize(80,bottomControlHeights));
    	map.addControl(new GScaleControl(), pos);

    	// bind a search control to the map, suppress result list                                           
    	//map.addControl(map.LocalSearch(), new GControlPosition(G_ANCHOR_BOTTOM_RIGHT, new GSize(10,20)));         

    	map.addControl(new GNavLabelControl(),
                   new GControlPosition(G_ANCHOR_BOTTOM_LEFT, new GSize(195,bottomControlHeights)));


	// map center
	var point = new GLatLng(centerLat, centerLng);
	var threshold = 1.0;
	var min_pos = new GLatLng(Xmin-threshold, Ymin-threshold, true);
	var max_pos = new GLatLng(Xmax+threshold, Ymax+threshold, true);
	var bound = new GLatLngBounds(min_pos, max_pos);
    	var initZoom = map.getBoundsZoomLevel(bound);
    	var properZoom = initZoom - 1;

	map.setCenter(point, initZoom);

	// defining new maptype
	var copyright = new GCopyright(1,
		new GLatLngBounds(new GLatLng(-90, -180),
		new GLatLng(90, 180)),
		0,
		"©2002 www.lib.utah.edu");

	var copyrightCollection = new GCopyrightCollection('Digital Operations');
	copyrightCollection.addCopyright(copyright);

        // Create the tile layer overlay and
	// implement the three abstract methods
	var tilelayer = new GTileLayer(copyrightCollection, 4, 5);

	tilelayer.getTileUrl = function (tile, zoom) {
	  var tileUrl = "amphibiaServer.php?id="+sid+"&x="+tile.x+"&y="+tile.y+"&zoom="+zoom;
	  return tileUrl;
	  }

	tilelayer.isPng = function() { return true;};
	tilelayer.getOpacity = function() { return 0.5; }
	
	var myTileLayer = new GTileLayerOverlay(tilelayer);
	map.addOverlay(myTileLayer);
  }}


window.onload=load;
window.onunload=GUnload;
</script>

<div id="amphibia_map" style="width: 1200px; height: 600px"></div>
  
<?php

function fetchMetaInfoFromDB($dst_tbl_name, $primary_key)  {
  require_once(dirname(__FILE__).'/../db2tile/db_credentials.php');
  $conn = mysql_connect($db_host, $db_name, $db_pass)
  or die("Couldn't connect to my sql server!");
  
  
/*
  if ($conn == FALSE) echo "FALSE - Fail to connect db";
  else echo "TRUE- connected to db";
 */ 
  $db_list = mysql_list_dbs($conn);

  $i = 0;
  $wss_db = $database;
  $exist_db = FALSE;
  $cnt = mysql_num_rows($db_list);
  //echo "<br />db(".$cnt.") list -> <br />";
  
  while ($i < $cnt) {
	$db_name = mysql_db_name($db_list, $i);
	if ($db_name == $wss_db) {
	  $exist_db = TRUE;
	}
	$i++;
  }
  // DB creation, use
  if ($exist_db == TRUE)
	//echo $wss_db." exists <br />";
	$a=1; // dummy statement
  else {
	//echo $wss_db." not exists <br />";
   $query_wss = "CREATE DATABASE $wss_db";
   $dbc = mysql_query($query_wss);
   //if($dbc==TRUE) echo "creation of db good<br />";
   //else echo "creatoin of db failed <br />";
   mysql_free_result($dbc);
  }

  @mysql_select_db($wss_db)
	or die("Could not select database!");

  //echo "You're connected to a MySQL database!----".$conn."<br />";

  // Table listup/creation
  $query_forall_tbls = "SHOW TABLES FROM ".$wss_db;
  $resulttbls = mysql_query($query_forall_tbls);
  if (!$resulttbls) {
	echo "DB Error, could not list tables\n";
	echo 'MySQL Error: ' . mysql_error();
	exit;
  }

  
  $b_tbl_exist = FALSE;
  while ($row = mysql_fetch_row($resulttbls)) {
	//echo "Table--------------: {$row[0]}<br />";
	if ($dst_tbl_name == $row[0]) {
	  //echo "test table exists<br />";
	  $b_tbl_exist = TRUE;
	  break;
	}
	//else echo "{$row[0]} isn't same as {$dst_tbl_name}. <br />";
	}
	mysql_free_result($resulttbls);
	

/*
  if ($b_tbl_exist == TRUE)
	echo "dst_tbl found<br/>";
  else 
	echo "Couldn't find table ---> error<br />";
*/
  

  $query2 = "SELECT xmin, xmax, ymin, ymax, numparts, numpoints
           FROM ".$dst_tbl_name."
           WHERE primary_key=\"".$primary_key."\"
           ORDER BY primary_key";

  $result2 = mysql_query($query2);
  if($result2 == FALSE) {
    echo "The species is not available in DB<br />";
    return FALSE;
  }

  else {
   
    for ($count=0; $count < mysql_numrows($result2); $count++) {
	$xmin = mysql_result($result2, $count, "xmin");
	$xmax = mysql_result($result2, $count, "xmax");
	$ymin = mysql_result($result2, $count, "ymin");
	$ymax = mysql_result($result2, $count, "ymax");
	$nprt = mysql_result($result2, $count, "numparts");
	$npnt = mysql_result($result2, $count, "numpoints");
    }

    mysql_free_result($result2);
    mysql_close();
    return array ($xmin, $xmax, $ymin, $ymax, $nprt, $npnt);
  }
}

function getTileZoomRng($tilesPath, $primary_key, $stamp_file) {

  $zoomMin = 3;
  $zoomMax = 10;

  $speciesZoomPath = $tilePath."/id_".$primary_key."/";
  if(is_dir($speciesZoomPath)) {
    $zmList = array();
    $dh = opendir($speciesZoomPath);
    if($dh != FALSE) {
      while(($zm = readdir($dh)) != FALSE)
        if($zm != "." && $zm != "..")
          $zmList[] = $zm;
    }

    if($zmList != FALSE) { 
      $zoomMin = min($zmList);
      $tempZ = max($zmList);
      if (file_exists($speciesZoomPath.$tempZ."/".$stamp_file))
        $zoomMax = $tempZ;
      else {
        if ($zoomMin < $tempZ) $zoomMax = $tempZ - 1;
        else $zoomMax = $zoomMin;
      }
    }
  }
  return array($zoomMin, $zoomMax);
}

?>

</form>
</div>


</body>
</html>
