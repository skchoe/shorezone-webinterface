
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
  
  </head>
  <body>
  <div id="wrapper">
  
  <div id="masthead"><img src="http://westernsoundscape.org/images/table_topshadow.gif" alt="shadowgraphic" width="855" height="10" />
  </div> <!-- end "masthead" -->
  
  <div id="banner">
  <table cellpadding="0" cellspacing="0">
  <tr>
  <td><div id="titlediv"><a href="http://www.westernsoundscape.org"><img src="http://westernsoundscape.org/images/westernsoundscapes.gif" alt="Western Soundscapes" width="500" height="67" /></a></div></td>
  <td valign="bottom"><div id="title2div"><img src="http://westernsoundscape.org/images/logos.gif" alt="logos" width="288" height="47" border="0" align="left" usemap="#Map2" /></div></td>
  </tr>
  </table>
  </div> <!-- end "banner" -->

  <map name="Map2" id="Map2">
  <area shape="rect" coords="209,3,279,43" href="http://www.natureserve.org" target="_blank" alt="NatureServe" />
  <area shape="rect" coords="6,5,47,44" href="http://www.utah.edu/" target="_blank" />
  <area shape="rect" coords="48,6,193,42" href="http://www.lib.utah.edu/" target="_blank" />
  </map>
  <link rel="stylesheet" href="css/wss.css" type="text/css" />

  <div id="navBar"><a href="http://westernsoundscape.org/index.php">Home</a> | <a href="http://westernsoundscape.org/about.php">About</a> | <a href="http://westernsoundscape.org/arctic/index.php">Arctic Collection</a> | <a href="http://westernsoundscape.org/spect/index.php">Spectrograms</a> | <a href="http://www.map.utah.edu/soundscapes/speciesList.jsp">Maps</a> | <a href="http://westernsoundscape.org/contact.php">Contact</a>
  </div> <!-- end "navBar" -->
  
  <div style="padding: 0 13px 5px 13px;">
    <form action='speciesExtent.jsp' name='mapForm' id='mapForm'>
	<input type='hidden' name='xmin' value='0'>
	<input type='hidden' name='ymin' value='0'>
	<input type='hidden' name='xmax' value='0'>
	<input type='hidden' name='ymax' value='0'>
	<input type='hidden' name='action' value='extent'>
  
  <p>
  Below are estimated distribution maps for terrestrial vertebrate species across five western states including Arizona, Colorado, Nevada, New Mexico,
and Utah. The maps employ <a href="http://fws-nmcfwru.nmsu.edu/swregap/HabitatModels/default.htm">  animal
habitat models</a> from the <a href="http://fws-nmcfwru.nmsu.edu/swregap/">Southwest Regional
Gap Analysis Project</a> at the United States Geological Survey. Some species distributions may extend beyond the five-state boundary.
  </p>

  <p>Click on the links below to view individual maps.<br/><br/>
  <!-- ////////////////////////////////////////////////////////////////////////-->
  

<?php
set_time_limit(3600*24*7);
ini_set('memory_limit',-1);


// With species_id, Get all the info of it from db
  //  1. conenect db
  //  2. get wtk data, and others
  //  3. figure out how to draw the polygon on tile (constrctuion) - check wkt
  //echo "Try to connect ... .... ... ... ...database<br/>";
require_once(dirname(__FILE__).'/../db2tile/db_credentials.php');
$conn = mysql_connect($db_host, $db_name, $db_pass);

//$conn = @mysql_connect(db_host, $db_name, $db_pass)
//or die("Couldn't connect to my sql server!");

//if ($conn == FALSE) echo "FALSE - Fail to connect db";
// else echo "TRUE- connected to db";

$db_list = mysql_list_dbs($conn);

$i = 0;
$wss_db = "wss";
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
/*// DB creation, use
if ($exist_db == TRUE)
  echo $wss_db." exists <br />";
 else {
   echo $wss_db." not exists <br />";
   $query_wss = "CREATE DATABASE $wss_db";
   $dbc = mysql_query($query_wss);
   if($dbc==TRUE) echo "creation of db good<br />";
   else echo "creatoin of db failed <br />";
   mysql_free_result($dbc);
 }
*/
@mysql_select_db($wss_db)
or die("Could not select database!");

//echo "You're connected to a MySQL database!----".$conn."<br />";
$species_dst_tbl = "species_dst_tbl";

//////////////////////////////////////////////////////////////////////////////////////
require_once(dirname(__FILE__)."/vizUtils.inc.php");

$query_idset = "SELECT source_gri, cm_name, sc_name, numparts, numpoints
           FROM ".$species_dst_tbl;

$result_idset = mysql_query($query_idset);
if($result_idset == FALSE) echo "false output<br />";
else
  echo "Numberof Record in wss database: ".mysql_numrows($result_idset)."<br/>";

$zoomLevels = array(11);

// 8/28 6
// counts for number of species in the shp file.
$initCount = 387;   
$endCount = 459;
//$endCount = mysql_numrows($result_idset) - 1;
for ($count=$initCount; $count <= $endCount ; $count = $count + 3) {
  echo "count = ".$count.": ";
  $source_gri = mysql_result($result_idset, $count, "source_gri");
  $shp_name = "wss";
  //computeTileImage($wss_db, $shp_name, $zoomLevels, $source_gri, -1);
  computeTileImage($wss_db, $shp_name, $zoomLevels, $source_gri, 400000);
  //  echo "Species_id: [$source_gri] $n_cm / $n_sc :  $nparts, $npoints <br />";
 }
mysql_free_result($result_idset);
//mysql_close();


?>


  
  <!-- ////////////////////////////////////////////////////////////////////////-->
  </p>
  
  </form>
  </div> <!-- end style="padding: ..."-->


  
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
