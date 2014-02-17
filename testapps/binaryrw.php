<?php
echo "XX</br>";
// get contents of a file into a string
$filename = "something.txt";
$handle = fopen($filename, "r");
$contents = fread($handle, filesize($filename));
echo "contents: ".$contents."</br>";
fclose($handle);
echo "YY</br>";
?>
