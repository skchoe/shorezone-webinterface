
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

  <div id="navBar"><a href="http://westernsoundscape.org/index.php">Home</a> | <a href="http://westernsoundscape.org/about.php">About</a> | <a href="http://westernsoundscape.org/arctic/index.php">Arctic Collection</a> | <a href="http://westernsoundscape.org/spect/index.php">Spectrograms</a> | <a href="http://westernsoundscape.org/maps/speciesList.php">Maps</a> | <a href="http://westernsoundscape.org/contact.php">Contact</a>
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

  <p>Click on the links below to view individual maps.<br/>
  <!-- ////////////////////////////////////////////////////////////////////////-->
  

<?php

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
$species_feature_tbl = "species_feature_tbl";
/*
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

////////////////////////////////////////////////////////////////////////////////
$query_kind = "SELECT source_gri, cm_name, sc_name, taxon_grou, animal_for, has_no_hab, taxon_gr_1 FROM ".$species_feature_tbl." ORDER BY cm_name";

$result_kind = mysql_query($query_kind);
/*
if($result_kind == FALSE) echo "false output-result of kind database<br />";
else
  echo "Numberof Record in wss feature database: ".mysql_numrows($result_kind)."<br/>";
*/
$species_vt5 = array(
 "MOUNTAIN YELLOW-LEGGED FROG", 
 "RELICT LEOPARD FROG",
 "CANYON TREEFROG",
 "WESTERN CHORUS FROG",
 "LOWLAND BURROWING TREEFROG",
 "MOJAVE FRINGE-TOED LIZARD",
 "SEDGE WREN",
 "TRICOLORED BLACKBIRD",
 "ARIZONA SHREW",
 "PYGMY SHREW",
 "NORTHERN GRASSHOPPER MOUSE",
 "BROWN BEAR",
 "WOLVERINE",
 "BISON",
	"NARROW-HEADED GARTER SNAKE",
	"COMMON BLACK-HAWK",
	"PAINTED REDSTART",
	"MOUNTAIN BEAVER",
	"VAUX'S SWIFT");
?>
<p>
Go to:
<a href='#Amphibian'>Amphibians</a> | 
<a href='#Bird'>Birds</a> | 
<a href='#Mammal'>Mammals</a> | 
<a href='#Reptile'>Reptiles</a>

<br/><br><br><a name='Amphibian'><font size='5'style='text-decoration:none;'><b>Amphibians</font></b><br>
<?php
echoByTaxonGroup($species_dst_tbl, $result_kind, 'A', $species_vt5);
?>

<br><br><br><a name='Bird'><font size='5'style='text-decoration:none;'><b>Birds</font></b><br>
<?php
echoByTaxonGroup($species_dst_tbl, $result_kind, 'B', $species_vt5);
?>

<br><br><br><a name='Mammal'><font size='5'style='text-decoration:none;'><b>Mammals</font></b><br>
<?php
echoByTaxonGroup($species_dst_tbl, $result_kind, 'M', $species_vt5);
?>

<br><br><br><a name='Reptile'><font size='5'style='text-decoration:none;'><b>Reptiles</font></b><br>
<?php
echoByTaxonGroup($species_dst_tbl, $result_kind, 'R', $species_vt5);
?>






<?php

function echoByTaxonGroup($dst_tbl, $result_kind, $taxon_grou, $except_array) {

  for ($count=0; $count < mysql_numrows($result_kind); $count++) {
    $tgrp_symbol = mysql_result($result_kind, $count, "taxon_grou");
  
    if($tgrp_symbol == $taxon_grou) {
      $source_gri = mysql_result($result_kind, $count, "source_gri");
	  if(0 != (int) strrev($source_gri)) { // Only representative species are listed: note (int) retniw102938 -> 0

        $n_cm = stripslashes(mysql_result($result_kind, $count, "cm_name"));
        $n_sc = stripslashes(mysql_result($result_kind, $count, "sc_name"));

		if (FALSE == in_array ($n_cm, $except_array)) {
//          echo "<a href=\"http://www.westernsoundscape.org/maps/speciesExtent.php?species=".$source_gri."\"> [".$source_gri."] ".$n_cm." / ".$n_sc."</a><br>";

           if(available($dst_tbl, $source_gri) == TRUE)
             echo "<a href=\"http://www.westernsoundscape.org/maps/speciesExtent.php?species=".$source_gri."\"> ".$n_cm." / ".$n_sc."</a><br>";
		   /*
           else
             echo "XXXXXXXXXX[".$source_gri."] ".$n_cm." / ".$n_sc.": Map is not available now.<br>";
			*/

		}
	  }
    }
  }
}


function available($dst_tbl, $src_id) {
  //echo "\tAVAILABLE = ".$src_id." in ".$dst_tbl."<br/>";

  $query_by_id = "SELECT source_gri 
               FROM ".$dst_tbl."
               WHERE source_gri=".$src_id."
               ORDER BY source_gri";

  $result_id = mysql_query($query_by_id);
  $source_gri = mysql_result($result_id, 0, "source_gri");
   
  
  if($source_gri != $src_id) {
    //echo "\t".$src_id."!=".$source_gri." is not availableXXXXXXXXXXXXXXXXXXXXX<br/>";
    return FALSE;
  }
  else  {
    //echo "\t".$src_id."==".$source_gri." is available00000000000000000000000000<br/>";
    return TRUE;
  }
}

?>


<!--------
<br><br><br><a name='Reptile'><font size='5'style='text-decoration:none;'><b>Dst_tbl</font></b><br>
<?php
$query_idset = "SELECT source_gri, cm_name, sc_name, numparts, numpoints FROM ".$species_dst_tbl;

$result_idset = mysql_query($query_idset);
echoByPolygon($result_idset);
?>
------->

<?php
function echoByPolygon($result_idset) {
//////////////////////////////////////////////////////////////////////////////////////
  if($result_idset == FALSE) echo "false output<br />";
  else {
    echo "Numberof Record in wss database: ".mysql_numrows($result_idset)."<br/>";
    for ($count=0; $count < mysql_numrows($result_idset); $count++) {

      $source_gri = mysql_result($result_idset, $count, "source_gri");
      $n_cm = mysql_result($result_idset, $count, "cm_name");
      $n_sc = mysql_result($result_idset, $count, "sc_name");
      $nparts = mysql_result($result_idset, $count, "numparts");
      $npoints = mysql_result($result_idset, $count, "numpoints");
      echo "<a href=\"http://www.westernsoundscape.org/maps/speciesExtent.php?species=".$source_gri."\"> [".$source_gri."] ".$n_cm." / ".$n_sc."</a><br>";

      //  echo "Species_id: [$source_gri] $n_cm / $n_sc :  $nparts, $npoints <br />";
    }
    mysql_free_result($result_idset);
  }
}
?>


<?php
mysql_close();
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
