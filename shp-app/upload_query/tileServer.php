<?php

//this script may require additional memory and time
ini_set('memory_limit',-1);

require_once(dirname(__FILE__)."/../../db2tile/vizUtils.inc.php");

// species_id for sql query
$shape_name = $_GET['ShapeName'];
$iddir_name = $_GET['IdDirName'];
$Xname = $_GET['x'];
$Yname = $_GET['y'];
$Zname = $_GET['zoom'];

$tile_home_folder = dirname(__FILE__).'/../../../tiles';

if (substr($iddir_name, 0, 3) == "id_") {
	$prikey = substr($iddir_name, 3);
	//$file = $tile_home_folder.'/'.$shape_name.'_viz/'.$iddir_name.'/z_'.$Zname.'/c_'.$prikey.'_'.$Xname.'_'.$Yname.'_'.$Zname.'.png';
	$file = composeTileFullPath($tile_home_folder, $shape_name, $prikey, $Zname, $Xname, $Yname, "viz");

/*
	echo "T dir entry = $iddir_name, $prikey </br>";
	echo "file: $file </br>";
	if(file_exists($file)) echo "exist</br>";
	else echo "not exist</br>";
*/
	//output the existing image to the browser
	header('content-type:image/png');
	echo file_get_contents($file);
}
else {
	//echo "F dir entry = $entry </br>";
}
?>
