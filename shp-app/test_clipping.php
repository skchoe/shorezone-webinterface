<?php
 
echo "XXXXXXXXXXXXXX";
require_once(dirname(__FILE__)."/clipping.php");
$part = array(-.2, .5, .4, .2, .6, .5, 1.5, .3, 1.5, 1.3, .4, 1.3, -.2, .5); // CCW
$part = array(-.2, .5, .4, 1.3, 1.5, 1.3, 1.5, .3, .6, .5, .4, .2, -.2, .5); // CW
$bbx = array(-.2, 1.5, .2, 1.3);
// CWFlag should be showing right value from $part
$arr_subpart = clipping_algorithm($part, TRUE, $bbx, 0, 1, 0, 1);


?>
