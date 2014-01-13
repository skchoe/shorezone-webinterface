<?php



echo "TESTing<br/>";

require_once(dirname(__FILE__)."/dataUtils.inc.php");
$pointsOriginal = array(.25, -.25, .75, .25, 1.25, .75, .75, 1.5, .25, 1.5, -.25, .5, .25, -.25);
printArray($pointsOriginal);
echo "+++++++++++++++++++++++++++++++<br/>";

$resultArray  = arrayReindexCutEnd($pointsOriginal, 1);
printArray($resultArray);

$partArray = array();
$partArray [] = $pointsOriginal;
echo "sizeof partArray = ".sizeof($partArray)."<br/>";
echo "sizeof first elt array= ".sizeof($partArray[0])."<br/>";

echo "+++++++++++++++++++++++++++++++<br/>";

list($a, $b, $newMultiPolygon) = clipedMultiPolygonInTile($partArray, 0.0, 1.0, 0.0, 1.0);

echo "NOW....".sizeof($newMultiPolygon[0]).".....".$newMultiPolygon."<br/>";
printArray($newMultiPolygon[0]);


function printArray($array) {
  $numPoints = sizeof($array);
  for($i=0;$i<$numPoints;$i++) {
	echo $array[$i]."|";
  }
  echo "<br/>";
}

$result = 3<<4;
echo "...../".$result."<br/>";
?>
