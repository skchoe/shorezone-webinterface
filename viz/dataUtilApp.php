<?php

require_once (dirname(__FILE__)."/dataUtils.inc.php");
require_once (dirname(__FILE__)."/GoogleMapUtility.php");

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

//echo "size is ".sizeof($p)." => ".sizeof($new_p)."<br/>";
$st = array(1.0, 0.0);
$ed = array(0.0, 1.0);
$pos = array(1.0, 1.0);

$l = isLeft($st, $ed, $pos);
//echo "winding number = ".$wn.", left = ".$l."<br/>";

/*
if($isIn)
  echo $isIn." is inside =TRUE<br/>";
else
  echo $isIn." is inside =FALSE<br/>";
*/

$pverticesGeo1 = array(array(-2, 0), array(0, 2), array(2, 2), array(2, 1), array(0, 1), array(0, -1), array(2, -1), array(2, -2), array(0, -2)); //CW
$pverticesGeo2 = array(array(-2, 0),array(0, -2),array(2, -2), array(2, -1), array(0, -1), array(0, 1), array(2, 1), array(2, 2), array(0, 2));   //CCW
$pt = array(-1, 0);
$bin1 = isInside($pt, $pverticesGeo1);
$bin2 = isInside($pt, $pverticesGeo2);
echo "inside test = > ".$bin1.", ".$bin2."<br/>---------------------------------------<br/>";

$verticesGeo1 = array(-2, 0, 0, 2, 2, 2,  2, 1,  0, 1,  0, -1, 2, -1, 2, -2, 0, -2, -2, 0); //CW
$verticesGeo2 = array(-2, 0, 0, -2,2, -2, 2, -1, 0, -1, 0, 1,  2, 1,  2, 2,  0, 2,  -2, 0);  //CCW

$verticesGeo3 = array (0, 0, 1, 0, 2, 0, 3, 0, 4, 0, 2, 1, 0, 0);
$verticesGeo4 = array (0, 0, -2, 5, -4, 2, -3, 0, -2, 0, -1, -1, 0, 0);
$cw = isCW($verticesGeo4);
//$cw = isCWNEW($verticesGeo4);
if($cw) echo "-CW<br/>";
 else echo "NOT -CW<br/>";


$corners = array(-.5, 1, -2, 1, -2, -1, -.5, -1);
list($tl, $tr, $br, $bl) = detectInOutTileCorners($verticesGeo1, $corners); 
echo "corner included? ".$tl.", ".$tr.", ".$br.", ".$bl."<br/>";

// True means verticesGeo2 is CCW -> desn't matter.
list($tl, $tr, $br, $bl) = detectInOutTileCorners($verticesGeo2, $corners); 
echo "corner included? ".$tl.", ".$tr.", ".$br.", ".$bl."<br/>";

  $values = array(
            40,  50,  // Point 1 (x, y)
            20,  240, // Point 2 (x, y)
            60,  60,  // Point 3 (x, y)
            240, 20,  // Point 4 (x, y)
            50,  40,  // Point 5 (x, y)
            10,  10   // Point 6 (x, y)
            );

$arr = "";
foreach ($values as $v) {
  $arr .= $v."\t";
}
echo $arr."<br/>";
/*
writeBooleanArrayToFile($values, "test.txt");
function writeBooleanArrayToFile($array, $filename) {
  $fp=fopen($filename, "w+");
  $str = sizeof($array)."\n";
  foreach($array as $key => $value) {
    if($value==TRUE)
        $str .="1\t";
    else
        $str .="0\t";
  }
  fwrite($fp, $str."\n");
}
*/

$values2 = array(1.0, 1.0, 1.0, -1.0, -1.0, -1.0, -1.0, 1.0);
echo "AREA: ".signedPolygonArea($values2)."<br/>";

/*
// $vPoints doesn't haveduplipcate points
function Signed_PolygonArea ($vPoints) {
  $numPoints  = sizeof($vPoints)/2;
  $area = 0.0;
  for($i=0;$i<$numPoints;$i++) {
    $j = $i+1;
    if($j>=$numPoints) $j -= $numPoints;
    $vix = $vPoints[2*$i];
    $viy = $vPoints[2*$i+1];
    $vjx = $vPoints[2*$j];
    $vjy = $vPoints[2*$j+1];
    $area += $vix * $vjy;
    $area -= $viy * $vjx;
    echo "i=".$i." j=".$j.", area = ".$area."<br/>";
  }
  return $area/2.0;
}
*/
/*
  $imsize = GoogleMapUtility::TILE_SIZE * 1;
  $im = imagecreate($imsize, $imsize);
  $file = 'test.png';

  $trans = imagecolorallocate($im,255,0,0);
  imagefill($im,0,0,$trans);
  imagecolortransparent($im, $trans);
  $white = imagecolorallocate($im,255,255,255);

  //set up some colors for the markers.
  //each marker will have acolor based on the height of the tower
  $darkRed = imagecolorallocate($im,125,0,0);
  $red = imagecolorallocate($im,255,0,0);
  $darkGreen = imagecolorallocate($im,0,150,0);
  $green = imagecolorallocate($im,0,255,0);
  $darkBlue = imagecolorallocate($im,0,0,150);
  $blue = imagecolorallocate($im,0,0,255);
  $orange = imagecolorallocate($im,255,150,0);
  $white = imagecolorallocate($im, 255, 255, 255);
  $black = imagecolorallocate($im,0,0,0);

  imagefilledrectangle($im, 0, 0, $imsize-1, $imsize-1, $darkGreen);
  imagefilledpolygon($im, $values, 6, $red);
  imagestring($im,4,1,0, "({$imsize},{$imsize})", $black);

  imagepng($im,$file);
  header('content-type:image/png;');
  echo file_get_contents($file);

*/
?>
