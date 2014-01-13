<?php
// GET THE idea how to communicate species_id from client
$species_id = $_GET["species"];
?>



<html>
  <head>
    
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<title>Sound Maps - Western Soundscape Archive</title>
	<link rel="stylesheet" href="http://westernsoundscape.org/css/wss.css" type="text/css" />
	<!-- Dublin Core Meta 
	<meta name="DC.Format" content="text/html" />
	<meta name="DC.Title" content="Western Soundscapes" />
	<meta name="DC.Subject" content="natural soundscape, nature's voice, nature of sound, voice of place, natural sound, biophony, Western Soundscapes, bird sounds, birds, mammal sounds, mammals, landscape sounds, amphibian sounds, reptile sounds" />
	<meta name="DC.Description" content="Main site for Western Soundscapes." />
	<meta name="DC.Creator" content="Western Soundscapes" />
	<meta name="DC.Date" content="2006-11-18" />
	<meta name="DC.Language" content="en" />
	<meta name="DC.Rights" content="Copyright (c) 2006 Western Soundscapes. All rights reserved." />
	-->
	<!-- Generic meta  
	<meta name="copyright" content="Copyright (c) 2006 Western Soundscapes. All rights reserved." />
	<meta name="Author" content="Western Soundscapes" />
	<meta name="Title" content="Main Site for Western Soundscapes" />
	<meta name="keywords" content="natural soundscape, nature's voice, nature of sound, voice of place, natural sound, biophony, Western Soundscapes, bird sounds, birds, mammal sounds, mammals, landscape sounds, amphibian sounds, reptile sounds" />
	<meta name="description" content="Main site for the Western Soundscapes." />
	-->

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
	<div id="wrapper">
		<div id="masthead"><img src="http://westernsoundscape.org/images/table_topshadow.gif" alt="shadowgraphic" width="855" height="10" /></div>
		<!-- end masthead -->
		<div id="banner">
			<table cellpadding="0" cellspacing="0">
				<tr>
					<td><div id="titlediv"><a href="http://www.westernsoundscape.org"><img src="http://westernsoundscape.org/images/westernsoundscapes.gif" alt="Western Soundscapes" width="500" height="67" /></a></div></td>
					<td valign="bottom"><div id="title2div"><img src="http://westernsoundscape.org/images/logos.gif" alt="logos" width="288" height="47" border="0" align="left" usemap="#Map2" /></div></td>
				</tr>
			</table>
		</div>

		<map name="Map2" id="Map2">
			<area shape="rect" coords="209,3,279,43" href="http://www.natureserve.org" target="_blank" alt="NatureServe" />
			<area shape="rect" coords="6,5,47,44" href="http://www.utah.edu/" target="_blank" />
			<area shape="rect" coords="48,6,193,42" href="http://www.lib.utah.edu/" target="_blank" />
		</map>
		<link rel="stylesheet" href="css/wss.css" type="text/css" />

<div id="navBar"><a href="http://westernsoundscape.org/index.php">Home</a> | <a href="http://westernsoundscape.org/about.php">About</a> | <a href="http://westernsoundscape.org/arctic/index.php">Arctic Collection</a> | <a href="http://westernsoundscape.org/spect/index.php">Spectrograms</a> | <a href="http://www.map.utah.edu/soundscapes/speciesList.jsp">Maps</a> | <a href="http://westernsoundscape.org/contact.php">Contact</a>
</div>
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
	  //var species_id = ".$species_id.";
	  var sid = "<?= $species_id ?>";
//document.write( "TEST writing: "+ sid +"<br/>");
</script>


<!-- variable_for_center -->
<?php
list($cmname, $scname, $xn, $xx, $yn, $yx, $npr, $npnt) = fetchMetaInfoFromDB($species_id);
$centerLng = ($xn + $xx) /2.0;
$centerLat = ($yn + $yx) /2.0;
?>

<font face="verdana" size = "2" color="green"> 
Species ID  : <?php echo $species_id; ?> <br/>
Species Name: <?php echo $cmname." / ".$scname; ?> <br/>
Center Location : (<?php echo $centerLng.", ".$centerLat; ?>)<br/>
Area Extent : Longitude(<?php echo $xn.", ".$xx;?>),
			  Latitude(<?php echo $yn.", ".$yx;?>) <br/>
Shapefile Info  : <?php echo $npr;?> inner/outer polygons, <?php echo $npnt;?> vertices <br/>
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
var initZoom = 8;


var map;

function load()
{
  if (GBrowserIsCompatible()) {
	map = new GMap2(document.getElementById("wss_map"));
	map.addMapType(G_PHYSICAL_MAP);
	map.setMapType(G_PHYSICAL_MAP);
	
	// control for zoom/pan
	map.addControl(new GLargeMapControl());

	// control for viz type
	var mapControl = new GMapTypeControl();
	map.addControl(mapControl);

	// map center
	var point = new GLatLng(centerLat, centerLng);
	map.setCenter(point, initZoom);

	// defining new maptype
	var copyright = new GCopyright(1,
								   new GLatLngBounds(new GLatLng(-90, -180),
													 new GLatLng(90, 180)),
								   0,
								   "©2009 www.lib.utah.edu");

	var copyrightCollection = new GCopyrightCollection('TEST');
	copyrightCollection.addCopyright(copyright);

    // Create the tile layer overlay and
	// implement the three abstract methods
	var tilelayer = new GTileLayer(copyrightCollection, 4, 5);

	//tilelayer.getTileUrl = function() { return "./ULogoForHead1.png"; };
	

	tilelayer.getTileUrl = function (tile, zoom) {
	  var tileUrl = "speciesServer.php?id="+sid+"&x="+tile.x+"&y="+tile.y+"&zoom="+zoom;
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

<div id="wss_map" style="width: 800px; height: 600px"></div>
  
<?php


function fetchMetaInfoFromDB($sp_id)  {
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

 $species_dst_tbl = "species_dst_tbl_from089";
  /*
  $b_tbl_exist = FALSE;
  while ($row = mysql_fetch_row($resulttbls)) {
	echo "Table--------------: {$row[0]}<br />";
	if ($species_dst_tbl == $row[0]) {
	  echo "test table exists<br />";
	  $b_tbl_exist = TRUE;
	  break;
	}
	else echo "{$row[0]} isn't same as {$species_dst_tbl}. <br />";
	}
	mysql_free_result($resulttbls);
	

  if ($b_tbl_exist == TRUE)
	echo "dst_tbl found<br/>";
  else 
	echo "Couldn't find table ---> error<br />";
  */

if(is_numeric($sp_id))
  $query2 = "SELECT cm_name, sc_name, xmin, xmax, ymin, ymax, numparts, numpoints
           FROM ".$species_dst_tbl."
           WHERE source_gri=".$sp_id."
           ORDER BY source_gri";
else
  $query2 = "SELECT cm_name, sc_name, xmin, xmax, ymin, ymax, numparts, numpoints
           FROM ".$species_dst_tbl."
           WHERE source_gri='".$sp_id."'
           ORDER BY source_gri";

  $result2 = mysql_query($query2);
  if($result2 == FALSE) echo "false output<br />";
  for ($count=0; $count < mysql_numrows($result2); $count++) {
	$n_cm = mysql_result($result2, $count, "cm_name");
	$n_sc = mysql_result($result2, $count, "sc_name");
	$xmin = mysql_result($result2, $count, "xmin");
	$xmax = mysql_result($result2, $count, "xmax");
	$ymin = mysql_result($result2, $count, "ymin");
	$ymax = mysql_result($result2, $count, "ymax");
	$nprt = mysql_result($result2, $count, "numparts");
	$npnt = mysql_result($result2, $count, "numpoints");
  }

  mysql_free_result($result2);
  mysql_close();
  return array ($n_cm, $n_sc, $xmin, $xmax, $ymin, $ymax, $nprt, $npnt);
}

?>

</form>
</div>

<font face="verdana" size = "1" color="green"> 
<a href="http://libtools.library.utah.edu/maps/viz/speciesListTest.php">CLICK HERE TO RETURN TO SPECIES LIST</a>
</font>
<br/>
  
<div id="footer">
  &copy; The University of Utah | J.<a href="http://www.lib.utah.edu/"> Willard Marriott Library</a>, Digital Technologies, 295 South 1500 East, Salt Lake City, UT  84112
		   </div><!-- end footer -->
		   
		   </div><!-- end page wrapper -->
	
		   <!-- WebTrends Javascript and noscript content -->
		   <script language="javascript" type="text/javascript" src="http://westernsoundscape.org/javascripts/wss_sdc_init.js"></script>
	<script language="javascript" type="text/javascript" src="http://westernsoundscape.org/javascripts/wss_sdc.js"></script>
	<noscript>
		<img alt="" border="0" name="DCSIMG" width="1" height="1" src="http://sdc.utah.edu/dcspv16g7gu2iz8aa3o68cg1a_9r6d/njs.gif?dcsuri=/nojavascript&amp;WT.js=No&amp;WT.tv=8.0.2">
	</noscript>
	<!-- end WebTrends content -->
</body>
</html>
