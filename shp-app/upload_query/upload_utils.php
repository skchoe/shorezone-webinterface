<?php
set_time_limit(3600*24*3);
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
//require_once dirname(__FILE__)."/../../viz/shp2tiles.php";


function run_db_tile($upload_class, $db_host, $db_name, $db_pass, $database, $extra, $zoom_start, $zoom_end, $geotype, $pickorviz)
{
   /*
   $upload_class->checkFilenameMatch(); 
   $upload_class->echoElements();
    */
 
  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  // SHAPE FILE ACCESS 
  $filename = $upload_class->get_shp_path();
  $shp_name = $upload_class->get_shp_name();
  $table_name_shp = $shp_name."_dst";
  $table_name_dbf = $shp_name."_dbf";

  $options = array('noparts' => false);

  //echo "Filename: ".$filename."</br>tbl1: ".$table_name_shp."</br>tbl2: ".$table_name_dbf."</br>";

  //echo "----DB login info:".$db_host.", ".$db_name.", ".$db_pass."</br>";
  $db_connect_info = array();
  $db_connect_info['host'] = $db_host;
  $db_connect_info['user'] = $db_name;
  $db_connect_info['passwd'] = $db_pass;
  $db_connect_info['db'] = $database;
 
  //print_r($db_connect_info);

  // table creation: create new db tbl.
  list ($db, $tbl_dst, $tbl_dbf, $pri_key_name) 
		= connect_db_create_tbl($db_connect_info, $table_name_shp, $table_name_dbf, new ShapeFile($filename, $options));

  //______________________________________________________________________________________________
  // shp -> table insertion
  list($tbl_name_shp, $tbl_name_dbf, $num_records, $num_pts, $num_parts, $shp_bbx) 
		= shp2insertquery(new ShapeFile($filename, $options), $table_name_shp, $table_name_dbf, $pri_key_name);
  mysql_close();

  $arrofarr = array();
  $arr_name = array("Shape Name", $shp_name);
  $arr_geom_table = array("Geometry Table", $table_name_shp);
  $arr_meta_table = array("Metadata Table", $table_name_dbf);
  $arr_records = array("Number of Records", $num_records);
  $arr_pts = array("Total Number of Vertices", $num_pts);
  $arr_parts = array("Total Number of Parts", $num_parts);
  $arr_bbx = array("Bounding Box", "X[".$shp_bbx[0].", ".$shp_bbx[1]."], Y[".$shp_bbx[2].", ".$shp_bbx[3]."]");
  array_push($arrofarr, $arr_name);
  array_push($arrofarr, $arr_geom_table);
  array_push($arrofarr, $arr_meta_table);
  array_push($arrofarr, $arr_records);
  array_push($arrofarr, $arr_pts);
  array_push($arrofarr, $arr_parts);
  array_push($arrofarr, $arr_bbx);

  echoTable(array(), $arrofarr);

  $b_valid_bbx = $upload_class->validate_bbx($shp_bbx);
  $bbx_check_1 = "* Geometry Check with bounding box : ";
  if (!$b_valid_bbx) { 
    $result = $bbx_check_1.": invalid.".$extra;
    echo $result;
    return FALSE;
  }
  else {

    $result = $bbx_check_1.": pass </br>";
    echo $result;
    echo "</br>";

    //______________________________________________________________________________________________
    // shp -> php tile generation via polygon conversion
    echo "zoom $zoom_start ~ $zoom_end </br>";
    list($shp_n_pt, $shp_n_prt) = shp2tiles($shp_name, new ShapeFile($filename, $options), $zoom_start, $zoom_end, $geotype, $pickorviz);

    return array($shp_name, $table_name_shp, $table_name_dbf);
  }
}

function echoTable($headers, $arrofarr)
{ 
  echo "<table width=100% height=10% cellspacing=\"0\" cellpadding=\"0\" border=\"1\"> <tr>";
  if(!empty($headers)) {
    foreach ($headers as $header) {
       echo "\t\t<td>
       <FONT face=\"Verdana,Helvetica\" SIZE=\"1\" COLOR=\"#000000\">
       <P ALIGN=Center>
       <B>$header</B>
       </FONT>
       </td>\n";
     }   

     echo "\t</tr>\n";
  }

  // loop in array
  $end = count($arrofarr);
  for($p=0;$p < $end; $p++) {
    echo "\t<tr>";
    foreach ($arrofarr[$p] as $content) {
      echo "\t\t<td>
      <!--FONT face=\"Verdana,Helvetica\" SIZE=\"2\" COLOR=\"#000000\">
      <P ALIGN=Center>"
      .$content."&nbsp;
      </FONT-->
      </td>\n";
    }   
    echo "\t</tr>\n";
  }
  echo "</table>";
}

// directory path can be either absolute or relative
// $dirPath: tiles/szpoly
function get_dirs_files ($dirPath)
{
  $dirs = array();
  $files = array();

  //if(file_exists($dirPath)) echo "EXIST: $dirPath </br>";
  //else echo "NOT EXIST: $dirPath </br>";

  // open the specified directory and check if it's opened successfully 
  if ($handle = opendir($dirPath)) {

    // keep reading the directory entries 'til the end 
    while (false !== ($file = readdir($handle))) {

      //echo "get_dirs_files: $file </br>";

      // just skip the reference to current and parent directory 
      if ($file != "." && $file != "..") {
        if (is_dir("$dirPath/$file")) {
          // found a directory, do something with it? 
          $dirs[] = $file;
        } else {
          // found an ordinary file 
          $files[] = $file;
        }   
      }   
    }   

    // ALWAYS remember to close what you opened 
    closedir($handle);
  }
  return array($dirs, $files);
}

function table_field_names($tblname)
{
	// describe table:
	$query_desc = "DESCRIBE ".$tblname;
	$res = mysql_query($query_desc);
	if(!$res) echo "DECRIBE FAIL.........</br>";
	
	$num_cols = mysql_numrows($res);
	//echo "Total num of cols = $num_cols </br>";

	$arr_field_name = array();
	for($i=0 ; $i<$num_cols; $i++)
	{
		$field_name = mysql_result($res, $i, "Field");
		$arr_field_name[] = $field_name;
	}

	//print_r($arr_field_name);
	//echo "</br>";

	return $arr_field_name;
}


/*
number of columns
array of column names
array of arry of cells
*/
function table_content($tblname, $max)
{
	$arr_field_name = table_field_names($tblname);

//print_r($arr_field_name);

	$field_count = count($arr_field_name);
	$fetch_count = 0;
	if($field_count < $max) 
		$fetch_count = $field_count;	
	else $fetch_count = $max;
	
	$fields_string = $arr_field_name[0];
	for($i=1;$i<$fetch_count;$i++) 
		$fields_string = $fields_string.", ".$arr_field_name[$i];

	$query_string = "SELECT ".$fields_string." FROM ".$tblname;
//echo "Query string: ".$query_string."</br>";

	$result = mysql_query($query_string);
	if($result == FALSE) die ("getting max elements failed");

	$num_rows = mysql_numrows($result);

//echo "num_row in upload_utils: $num_rows </br>";

	$arr_arr = array();
	for($i=0;$i<$num_rows;$i++) {
		//echo "Row $i  ";
		$arr_cell = array();
		for($j=0;$j<$fetch_count;$j++) {
			$ans = mysql_result($result, $i, $arr_field_name[$j]);
			//echo " ".$ans." ";
			array_push($arr_cell, $ans);
		}
		//echo "</br>";
		array_push($arr_arr, $arr_cell);
	}

	return array($fetch_count, $arr_field_name, $arr_arr);
}

function build_array($size, $val)
{
	$ret_arr = array();
	for($i=0;$i<$size;$i++)
		$ret_arr[] = $val;

	return $ret_arr;
}

function echo_tilelink_table($shp_name, $tbl_dst, $tbl_dbf, 
	$zoom_start, $zoom_end)
{
	$max = 5; // want to take 3 columns to display each record
	$tcont = table_content($tbl_dbf, $max);
	$field_cnt = $tcont[0];
	$field_names = $tcont[1];
	$arr_arr = $tcont[2];

	//echo "field count = $field_cnt </br>";
	echo "Warning </br>the location of bubble at the next page when you click the link in the cell of following table is not correct, sometimes</br>";
	$url_open = "";
	//$php_target = "http://96.126.100.16/wss_maps/shp-app/upload_query/viz_records.php";
	$php_target = "viz_records.php";
	$url_open_front = "<a href=\"".$php_target."?ShapeName=".$shp_name."&TableNameDst=".$tbl_dst."&TableNameDbf=".$tbl_dbf."&entity=id_"; 
	$url_open_rear = "&zoom_start=".$zoom_start."&zoom_end=".$zoom_end."\" target=\"_blank\">"; 
	$url_close = "</a>";
	
	$num_row = count($arr_arr);
	//echo "num-row: $num_row </br>";

	echo "<table border=\"1\">";
	echo "<tr>";
	for($i=0;$i<$field_cnt;$i++) {
		echo "<td>".$field_names[$i]."</td>";
	}
	echo "</tr>";
	for($i=0;$i<$num_row;$i++) {
		$row = $arr_arr[$i];
		echo "<tr>";
		$url_open = $url_open_front.$row[0].$url_open_rear; 
		for($j=0;$j < $field_cnt;$j++) {
			$val = $row[$j];
			echo "<td>".$url_open.$val.$url_close."</td>";
			//else 	echo "<td>"."<a href=\"http://www.cs.utah.edu/~skchoe/\">".$val."</a></td>";
		}
		echo "</tr>";
	}
	echo "</table>";
	echo "</br>";
}
?>
