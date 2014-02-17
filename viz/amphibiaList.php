
<html>
  <head>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<title>Amphibia  Map List </title>
  </head>
  <body>
  <div id="wrapper">
  
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
echo "db name: $wss_db </br>";
$exist_db = FALSE;
$cnt = mysql_num_rows($db_list);
echo "<br />db(".$cnt.") list -> <br />";

while ($i < $cnt) {
  $db_name = mysql_db_name($db_list, $i);
  if ($db_name == $wss_db) {
	$exist_db = TRUE;
  }
  $i++;
 }
@mysql_select_db($wss_db)
or die("Could not select database!");

//echo "You're connected to a MySQL database!----".$conn."<br />";
$amphibia_dst_tbl = "SPECIES1109_DBO_VIEW_AMPHIBIANS_dst";
$amphibia_feature_tbl = "SPECIES1109_DBO_VIEW_AMPHIBIANS_dbf";

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
  if ($amphibia_dst_tbl == $row[0]) {
	echo "test table exists<br />";
	$b_tbl_exist = TRUE;
	break;
  }
  else echo "{$row[0]} isn't same as {$amphibia_dst_tbl}. <br />";
  }
  mysql_free_result($resulttbls);

if ($b_tbl_exist == TRUE)
  echo "dst_tbl found<br/>";
else 
  echo "Couldn't find table ---> error<br />";
*/

////////////////////////////////////////////////////////////////////////////////
//$query_kind = "SELECT primary_key, OBJECTID, ID_NO, BINOMIAL, PRESENCE, ORIGIN, SEASONAL COMPILER YEAR CITATION SOURCE DIST_COMM ISLAND SUBSPECIES SUBPOP TAX_COMMEN LEGEND SHAPE_Leng SHAPE_Area from ".$amphibia_feature_tbl." ORDER BY primary_key";
$query_kind = "SELECT primary_key, BINOMIAL from ".$amphibia_feature_tbl." ORDER BY primary_key";
echo "query kind: $query_kind </br>";

$result_kind = mysql_query($query_kind);

if($result_kind == FALSE) echo "false output-result of kind database<br />";
else
  echo "Numberof Record in wss feature database: ".mysql_numrows($result_kind)."<br/>";

$species_vt5 = array();

for ($count=0; $count < mysql_numrows($result_kind); $count++) {
  $primary_key = mysql_result($result_kind, $count, "primary_key");
  $binomial = mysql_result($result_kind, $count, "BINOMIAL");
  
  echo "$primary_key <a href=\"http://firenze.lib.utah.edu/westernsoundscape.org/maps/map_extends/viz/amphibiaExtent.php?primary_key=".$primary_key."&binomial=".$binomial."&dst_tbl=".$amphibia_dst_tbl."\"> ".$binomial."</a><br>";
}


?>


<?php
mysql_close();
?>


  
  <!-- ////////////////////////////////////////////////////////////////////////-->
  </p>
  
  </form>
  </div> <!-- end style="padding: ..."-->


  
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
