<?php
$arr = array('a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5);
$arr1 = array(1, 2, 3, 4, 5);
$shp_name = 'states';

$phparray = array();
if($dir_handler = opendir(dirname(__FILE__)."/../../tiles/".$shp_name)) {
	while (FALSE != ($ntt = readdir($dir_handler))) {
		if($ntt != "." && $ntt != "..") { 
			echo $ntt."</br>";
			array_push($phparray, $ntt);
		}                                          
	}                                           
}  
echo json_encode($arr);
echo "</br>";
echo json_encode($arr1);
echo "</br>";
echo json_encode($phparray);
echo "</br>";
?>
