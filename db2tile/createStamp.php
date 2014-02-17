
<html>
  <head>
    
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
	<title>createStamp.php</title>
  
  </head>
  <body>
  <div id="wrapper">
  
  <link rel="stylesheet" href="css/wss.css" type="text/css" />

  <div style="padding: 0 13px 5px 13px;">
    <form action='speciesExtent.jsp' name='mapForm' id='mapForm'>
	<input type='hidden' name='xmin' value='0'>
	<input type='hidden' name='ymin' value='0'>
	<input type='hidden' name='xmax' value='0'>
	<input type='hidden' name='ymax' value='0'>
	<input type='hidden' name='action' value='extent'>
  
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
@mysql_select_db($wss_db)
or die("Could not select database!");

//echo "You're connected to a MySQL database!----".$conn."<br />";
$species_dst_tbl = "species_dst_tbl";

//////////////////////////////////////////////////////////////////////////////////////
require_once(dirname(__FILE__)."/vizUtils.inc.php");


$query_idset = "SELECT source_gri
           FROM ".$species_dst_tbl;

$result_idset = mysql_query($query_idset);
if($result_idset == FALSE) echo "false output<br />";
else
  echo "Numberof Record in wss database: ".mysql_numrows($result_idset)."<br/>";

// 8/28 6
$initCount = 0;
$endCount = mysql_numrows($result_idset) - 1;
$filename = "_complete.stamp";
for ($count=$initCount; $count <= $endCount ; $count = $count + 1) {
  echo "count = ".$count.": ---------------------------<br/>";
  $source_gri = mysql_result($result_idset, $count, "source_gri");
  copyFileToTiles($filename, $source_gri);
  echo "Species_id: [$source_gri] <br />";
 }
mysql_free_result($result_idset);
mysql_close();

?>


  
  <!-- ////////////////////////////////////////////////////////////////////////-->
  </p>
  
  </form>
  </div> <!-- end style="padding: ..."-->


  
</div><!-- end page wrapper -->
	
</body>
</html>
