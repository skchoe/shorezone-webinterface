<?php
/* ----------cleaning some folder list-----
$folder_array = array('id_-3', 'id_XX');

foreach($folder_array as $folder)
{
	echo "To be erased: $folder<br/>";
	system("rm -rf $folder");
}
echo "---end-clean<br/>";
 */
$filename = '/tmp/tiles_11/id_625180/c_625180_427_833_11.png';
$size = filesize($filename);
echo "file:$filename = size($size)<br/>";
?>
