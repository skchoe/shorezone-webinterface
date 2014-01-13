<?php

require_once (dirname(__FILE__)."/dataUtils.inc.php");

// $v = origin
$v = array();
$v[0] = 0.0;
$v[1] = 0.0;

// p = polygon with p0 p1...
$p0 = array();
$p1 = array();
$p2 = array();
$p3 = array();
$p4 = array();
$p5 = array(); 
$p0[0] = 1.0; $p0[1] = 0.0;
$p1[0] = 0.0; $p1[1] = 1.0;
$p2[0] = 0.5; $p2[1] = 0.0;
$p3[0] = 0.0; $p3[1] = -0.5;
$p4[0] = -1.0;$p4[1] = 0.0;
$p5[0] = 0.0; $p5[1] = -1.0;
$p6[0] = 1.0; $p6[1] = 0.0;


$p = array ($p0, $p1, $p2, $p3, $p4, $p5, $p6);

$wn = windingNumber($v, $p);

$new_p = removeTailDuplicate($p);

echo "size is ".sizeof($p)." => ".sizeof($new_p)."<br/>";
$st = array(1.0, 0.0);
$ed = array(0.0, 1.0);
$pos = array(1.0, 1.0);

$l = isLeft($st, $ed, $pos);
echo "winding number = ".$wn.", left = ".$l."<br/>";

$p0 = array(-2,0);
$p1 = array(0,2);
$p2 = array(2,2);
$p3 = array(2,1);
$p4 = array(0,1);
$p5 = array(0,-1);
$p6 = array(2,-1);
$p7 = array(2,-2);
$p8 = array(0,-2);

$vertices = array($p0, $p1, $p2, $p3, $p4, $p5, $p6, $p7, $p8);
$vertices2 = array($p8, $p7, $p6, $p5, $p4, $p3, $p2, $p1, $p0);

$q0 = array(-.5,1);
$q1 = array(-2,1);
$q2 = array(-2,-1);///////////////////////////////////////////////////
$q3 = array(-.5,-1);
$corners = array($q1, $q0, $q3, $q2);
$wn = windingNumber($q2, $vertices);
echo "winding number = ".$wn."<br/>";


$r0 = isInside($q0, $vertices);
$r1 = isInside($q1, $vertices);
$r2 = isInside($q2, $vertices);
$r3 = isInside($q3, $vertices);




$ppp = array(-1.0, .5);
$isIn = isInside($ppp, $vertices);
if($isIn)
  echo $isIn." is inside =TRUE<br/>";
else
  echo $isIn." is inside =FALSE<br/>";

$verticesGeo = array(-2, 0, 0, 2, 2, 2, 2, 1, 0, 1, 0, -1, 2, -1, 2, -2, 0, -2);
$verticesGeo2 = array(0, -2, 2, -2, 2, -1, 0, -1, 0, 1, 2, 1, 2, 2, 0, 2, -2, 0); 
$cw = isCW($verticesGeo2);
if($cw) echo "CW<br/>";
 else echo "NOT CW<br/>";


list($tl, $tr, $br, $bl) = detectInOutTileCorners($verticesGeo2, $corners);
echo "corner included? ".$tl.", ".$tr.", ".$br.", ".$bl."<br/>";
if($r0==FALSE&&$r1==FALSE&&$r2==FALSE&&$r3==FALSE)
echo "isInside included? ".$r0.", ".$r1.", ".$r2.", ".$r3."<br/>";

?>
