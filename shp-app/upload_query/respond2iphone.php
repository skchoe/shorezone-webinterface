<?php
$input1 = $_GET["input1"];
$input2 = $_GET["input2"];
//$json_data = array ('1'=> $input1, '2'=> $input2, '3'=>'3');
$json_data = array ('1'=> $input1, '2'=> $input2, '3'=>'3');

//$json_data = array ('1'=> $input1, '2'=> $input2, '3'=>'3');
echo json_encode($json_data);


?>
