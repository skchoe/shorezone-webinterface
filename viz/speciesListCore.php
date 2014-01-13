
<html>
  <head>
    <title>Sound Maps - Western Soundscape Archive</title>

    <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
    <STYLE TYPE="text/css">
	.boldtable, .boldtable TD, .boldtable TH
	{
	font-family:sans-serif;
	font-size:12pt;
	color:white;
	background-color:navy;
	}
	.italictable, .italictable TD, .italictable TH
	{
	font-family:"Times New Roman";
	font-size:12pt;
	font-style:italic;
	color:black;
	}
	.animalformcell, .animalformcell TD, .animalformcell TH
	{
	table.width:400pt;
	}
    </STYLE>

  </head>
  <body>
<!--
  <map name="Map2" id="Map2">
  <area shape="rect" coords="209,3,279,43" href="http://www.natureserve.org" target="_blank" alt="NatureServe" />
  <area shape="rect" coords="6,5,47,44" href="http://www.utah.edu/" target="_blank" />
  <area shape="rect" coords="48,6,193,42" href="http://www.lib.utah.edu/" target="_blank" />
  </map>
-->

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
// DB creation, use
if ($exist_db == TRUE)
  echo "Database: ".$wss_db." -  connection successfull <br />";
 else {
   echo $wss_db." not exists <br />";
   $query_wss = "CREATE DATABASE $wss_db";
   $dbc = mysql_query($query_wss);
   if($dbc==TRUE) echo "creation of db good<br />";
   else echo "creatoin of db failed <br />";
   mysql_free_result($dbc);
 }

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
//$query_kind = "SELECT source_gri, cm_name, sc_name, taxon_grou, animal_for, has_no_hab, taxon_gr_1 animal_for FROM ".$species_feature_tbl." ORDER BY  cm_name";
$query_kind = "SELECT source_gri, cm_name, sc_name, taxon_grou, animal_for, has_no_hab, taxon_gr_1 FROM ".$species_feature_tbl." ORDER BY animal_for, cm_name";

$result_kind = mysql_query($query_kind);

if($result_kind == FALSE) echo "false output-result of kind database<br />";
else
  echo "Number of Record in wss feature table: ".mysql_numrows($result_kind)."<br/>";

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
<a href='#Amphibian'>AMPHIBIANS</a> | 
<a href='#Bird'>BIRDS</a> | 
<a href='#Mammal'>MAMMALS</a> | 
<a href='#Reptile'>REPTILES</a>

<br/><br><br>
<a name='Amphibian'>
<font size='5'style='text-decoration:none;'><b>AMPHIBIANS</font></b><br>
<?php
echoByTaxonGroup($species_feature_tbl, 'A', $species_dst_tbl, $species_vt5);
?>

<br><br><br><a name='Bird'>
<font size='5'style='text-decoration:none;'><b>BIRDS</font></b><br>
<?php
echoByTaxonGroup($species_feature_tbl, 'B', $species_dst_tbl, $species_vt5);
?>

<br><br><br><a name='Mammal'>
<font size='5'style='text-decoration:none;'><b>MAMMALS</font></b><br>
<?php
echoByTaxonGroup($species_feature_tbl, 'M', $species_dst_tbl, $species_vt5);
?>

<br><br><br><a name='Reptile'>
<font size='5'style='text-decoration:none;'><b>REPTILES</font></b><br>
<?php
echoByTaxonGroup($species_feature_tbl, 'R', $species_dst_tbl, $species_vt5);
?>


<?php

function pass_error_test($sg, $cn, $dst_tbl, $except_array)
{
  //if(0==(int) strrev($sg)) return FALSE; // to filter out xxxxxxsufix form of source_gri
  if (!is_numeric($sg)) return FALSE; // filter out if source_gri is a variant form: ex. 123456w 234567breeding 445676winter
  elseif (TRUE == in_array ($cn, $except_array)) return FALSE; 
  elseif(available($dst_tbl, $sg) == FALSE) return FALSE;
  else return TRUE;
}

function echoByTaxonGroup($feature_tbl, $taxon_grou, $dst_tbl, $except_array) 
{
  // 1. Collecting sound Animal_Form from ....
  // Loop by Animal_for field
  $query_animal_for = "SELECT DISTINCT animal_for FROM ".$feature_tbl." WHERE taxon_grou = '".$taxon_grou."' ORDER BY animal_for";
  $result_animal_for = mysql_query($query_animal_for);

  // array_forms['TODD'] = array(source_gri => array(cn=> cm_name, sn => sc_name));
  $array_forms = array();
  while($form_row = mysql_fetch_assoc($result_animal_for))
  {
    $form_name = $form_row['animal_for'];
    $query_species = "SELECT source_gri, cm_name, sc_name, taxon_grou FROM ".$feature_tbl." WHERE taxon_grou = '".$taxon_grou."' AND animal_for = '".$form_name."' ORDER BY cm_name";
    $result_species = mysql_query($query_species);
    
//echo "Num spec. in form:".$form_name."---".mysql_numrows($result_species)."</br>";
    $sg_array = array();
    while($species_row = mysql_fetch_assoc($result_species))
    {
      $sg = $species_row['source_gri'];
      $cn = stripslashes($species_row['cm_name']);

      if(pass_error_test($sg, $cn, $dst_tbl, $except_array))
      {
        $sn = $species_row['sc_name'];
        $tg = $species_row['taxon_grou'];
        $name_array = array('cn'=>$cn, 'sn'=>$sn);
        $sg_array[$sg] = $name_array;
      }
    }
    if($form_name=='') $form_name = "Unknown Form";
    $array_form[$form_name] = $sg_array;
  }

/*  Print array elements
  echo "___________________sizeof array= ".count($array_form)."</br>";
  echo "___________________content array= ".$array_form."</br>";
  foreach($array_form as $form => $species_array)
  {
    echo "key=".$form.", value=".$species_array."</br>";
    foreach($species_array as $source_gri => $name_array)
      echo ">>> source gri=".$source_gri.", cn = ".$name_array['cn'].", sn = ".$name_array['sn']."</br>";
  }
*/

  echo "<table cellspacing=\"0\" cellpadding=\"0\" border=\"1\" >
  <thead class=\"boldtable\">
  <tr> <th class=\"animalformcell\"> Animal Form </th> <th> Common Name </th> <th> Scientific Name </th> </tr>
  </thead>
  <tbody>";

  foreach($array_form as $form => $species_array)
  {
    $new_form_flag = TRUE;
    $form_size = count($species_array);
    //echo "key=".$form.", value=".$species_array.", size = ".$form_size."</br>";
    foreach($species_array as $source_gri => $name_array) {

      $form_html = ucfirst(strtolower($form));
      $cn_html = ucfirst(strtolower($name_array['cn']));
      $sn_html = $name_array['sn'];

      if($new_form_flag) {
        //echo "<tr><th rowspan=\"".$form_size."\"> ".$form. "</th><th> ".$name_array['cn']." </th><th><font color=white face=\"Geneva, Arial\" size=6> ".$name_array['sn']." </font></th></tr>" ;// one row of the table
        echo "<tr><th class=\"animalformcell\" rowspan=\"".$form_size."\"> ".$form_html. "</th><th> <a href=\"http://www.westernsoundscape.org/maps/speciesExtent.php?species=".$source_gri."\">".$cn_html."</a> </th><th class=\"italictable\"> <a href=\"http://www.westernsoundscape.org/maps/speciesExtent.php?species=".$source_gri."\">".$sn_html."</a> </th></tr>" ;// one row of the table
        $new_form_flag = FALSE;
      }
      else
        echo "<tr><th> <a href=\"http://www.westernsoundscape.org/maps/speciesExtent.php?species=".$source_gri."\">".$cn_html." </a></th><th class=\"italictable\"> <a href=\"http://www.westernsoundscape.org/maps/speciesExtent.php?species=".$source_gri."\"> ".$sn_html." </a></th></tr>" ;// one row of the table
      //echo ">>> source gri=".$source_gri.", cn = ".$name_array['cn'].", sn = ".$name_array['sn']."</br>";
    }
  }

  echo"</tbody>
  </table><br/>";

  mysql_free_result($result_animal_for);
}


function available($dst_tbl, $src_id) {
  //echo "\tAVAILABLE = ".$src_id." in ".$dst_tbl."<br/>";

  $query_by_id = "SELECT source_gri 
               FROM ".$dst_tbl."
               WHERE source_gri=".$src_id."
               ORDER BY source_gri";

  $result_id = mysql_query($query_by_id);

  if($result_id && (mysql_num_rows($result_id) > 0)) 
  {
    //echo "resul_id = ".$result_id."with num row=".mysql_num_rows($result_id)."</br>";
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
  else 
    return TRUE;
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
		   
</body>
</html>
