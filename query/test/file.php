
<?php

$myFile = "testfile.txt";
/*
echo "TEST Filename = ".$myFile."<br/>";

$fh = fopen($myFile, 'w') or die("can't open file");
fclose($fh);
*/
unlink($myFile);
?>
