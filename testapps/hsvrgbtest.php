<?php
ini_set('display_errors', 1);
error_reporting(~0);

require_once(dirname(__FILE__)."/../db2tile/tile_colors.php");

$num_colors = 20;
$crarray = generatergbcolors($num_colors, TRUE); // TRUE: random order, {["r"], ["g"], ["b"]}

$sizecrarray = count($crarray);
for($i = 0 ; $i < $sizecrarray ; $i++)
{
  $rgbs = $crarray[$i];
  $chex = rgb2html($rgbs["r"], $rgbs["g"], $rgbs["b"]);
}
$width = 360 / $num_colors;
$height = 100;
$im = imagecreatetruecolor(360, $height);
imagecolorallocate($im,0,0,0); //background
$xstart = $width / 2;

for ($i = 0; $i < 360; $i += $width) 
{
	$rgbs = array_pop($crarray);
	$c = rgbtophpcolor($im, $rgbs["r"], $rgbs["g"], $rgbs["b"]);
	imagelinethick($im, $i+$xstart, 0, $i+$xstart, $height, $c, $width) or die("oops");

	$c0=imagecolorallocate($im, 255, 255, 255);
	imagerectangle($im, $i, 0,$i+$width-1, $height-1, $c0);
}
header("Content-type: image/png");
imagepng($im);
?>
