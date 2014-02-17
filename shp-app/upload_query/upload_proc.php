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
  set_time_limit(0);
  ini_set('memory_limit', -1);
  // include upload class
  require_once dirname(__FILE__)."/upload_class.php";
  require_once dirname(__FILE__)."/upload_utils.php";

  // include for shp file and mysql handling
  require_once dirname(__FILE__)."/../mysql_utils.php";
  require_once dirname(__FILE__)."/../mysql_inserts.php";
  require_once dirname(__FILE__)."/../../db2tile/db_credentials.php";
  require_once dirname(__FILE__)."/../../build_db/ShapeFile.inc.php";
  require_once dirname(__FILE__)."/../../build_db/WktUtils.inc.php";
  require_once dirname(__FILE__)."/../../viz/shp2tiles.php";

  $upload_class  = new Upload_Files;
  $upload_class -> temp_file_name_shp    = trim($_FILES['shpfile']['tmp_name']);
  $upload_class -> file_name_shp         = trim(strtolower($_FILES['shpfile']['name']));
  $upload_class -> temp_file_name_dbf    = trim($_FILES['dbffile']['tmp_name']);
  $upload_class -> file_name_dbf         = trim(strtolower($_FILES['dbffile']['name']));

  // change your upload directory if needed
  $upload_class -> upload_dir        = getcwd()."/upload/";
  // change your upload logs directory if needed
  $upload_class -> upload_log_dir    = getcwd()."/upload/upload_logs/";
  // maximum size is 5MB, consult your php.ini for maximum file size
  // updated 8MB * 1000
  $upload_class -> max_file_size     = 8092 * 1000 * 1000;
  // banned user array
  $upload_class -> banned_array      = array("");
  // permitted file extension array
  //$upload_class -> ext_array       = array(".doc",".jpeg",".jpg",".bmp",".png",".gif",".dbf",".shp");
  $upload_class -> ext_array         = array(".dbf",".shp");
 
 	// validate extension
  $valid_ext     = $upload_class->validate_extension();
  // validate size
  $valid_size    = $upload_class->validate_size();
  // validate user
  $valid_user    = $upload_class->validate_user();
  // get maximum file size
  $max_size      = $upload_class->get_max_size();
  // get file size
  $file_size_shp     = $upload_class->get_file_size_shp();
  $file_size_dbf     = $upload_class->get_file_size_dbf();
  // check if the file already exists
  $file_exists   = $upload_class->existing_file();

  //$shp_name = $upload_class->get_shp_name();

  // return path
	$extra = "<a href='JavaScript:history.go(-1);'>Back</a>";

	// start checking
  if (!$valid_ext) 
  { $result = "The file extension is invalid:Not registered one, please go back, input proper file.".$extra; }
  elseif (!$valid_size) 
  { $result = "The file size is invalid, please try again! The maximum file size is: $max_size and your file was: $file_size_shp or $file_size_dbf ".$extra; }
  elseif (!$valid_user) 
  { $result = "You have been banned from uploading to this server. ".$extra; }
  else {
    if($file_exists) { $result = "This file already exists on the server, Uploading skipped "; } 
    else {
      // Actual uploading!!
      $upload_file = $upload_class->upload_file_with_validation();
      if (!$upload_file) { $result = "Your file could not be uploaded! Start over ->".$extra; } 
      else { $result = "Your files have been successfully uploaded to the server. "; }
    }

    $geotype = Shape2Wkt::$GEOTYPE_MULTILINESTRING;

    $zoom_start = 2;
    $zoom_end = 10;
    list($shp_name, $tbl_dst, $tbl_dbf) = run_db_tile($upload_class, $db_host, $db_name, $db_pass, $database, $extra, $zoom_start, $zoom_end, $geotype, "pick");
    echo "*******************************************************************************************************</br>";
    //echo "<a href=\"http://localhost/wss_maps/shp-app/upload_query/query_proc.php?shpname=".$shp_name."&meta_tbl=".$tbl_dbf."\"> Move to Query</br>";
    $rep = "table";
  } 
  // end of checking
?>

<!--
/////////////////// two buttons for query and viz-records(all) //////////////////////////
-->
  
<form class='style1' name="move_to_query" id="move_to_query" enctype="multipart/form-data" method="get" target="_blank" action="query_proc.php">
  <input type="hidden" name="ShapeName" id="ShapeName" value="<?php echo $shp_name;?>" />
  <input type="hidden" name="TableNameDbf" id="TableNameDbf" value="<?php echo $tbl_dbf;?>" />
  <input type="hidden" name="zoom_start" id="zoom_start" value="<?php echo $zoom_start ;?>" />
  <input type="hidden" name="zoom_end" id="zoom_end" value="<?php echo $zoom_end; ?>" />
  <input type="hidden" name="rep" id="rep" value="<?php echo $rep; ?>" />
  <input type="submit" value="Move to query" /> </br>
</form>
   
<form class='style1' name="viz_record_set" id="viz_record_set" enctype="multipart/form-data" method="get" target="_blank" action="viz_records.php">
  <input type="hidden" name="ShapeName" id="ShapeName" value="<?php echo $shp_name;?>" />
  <input type="hidden" name="TableNameDst" id="TableNameDst" value="<?php echo $tbl_dst;?>" />
  <input type="hidden" name="TableNameDbf" id="TableNameDbf" value="<?php echo $tbl_dbf;?>" />
  <input type="hidden" name="zoom_start" id="zoom_start" value="<?php echo $zoom_start ;?>" />
  <input type="hidden" name="zoom_end" id="zoom_end" value="<?php echo $zoom_end; ?>" />
  <input type="submit" value="Viz all records in a map" /> </br>
</form>

   
<!--
/////////////////// List of 3 columns of meta data with link to individual range view //////////////////////////
<script type=\"text/javascript\">
function test(){
  alert("Name of shp:".'$shp_name');
}
</script>
-->
<?php

	require_once dirname(__FILE__)."/upload_utils.php";
	require_once dirname(__FILE__)."/../../db2tile/db_credentials.php";

	$conn = mysql_connect($db_host, $db_name, $db_pass);
	//or die("Couldn't select db - $tbl_dbf");

	$db_list = mysql_list_dbs($conn);
	@mysql_select_db($database);

	$b_tbl_exst = table_exist($database, $tbl_dbf);
	//or die("tble $tbl_dbf doesn't exist");

	echo_tilelink_table($shp_name, $tbl_dst, $tbl_dbf, 
		$zoom_start, $zoom_end);
	mysql_close($conn);

?>

</body>
</html>
