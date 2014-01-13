
<?php

require_once('../../viz/GoogleMapUtility.php');

echo "TEST<br/>";
$lng = -106.452020;
$lat = 35.047687;
$zoom =  10;

// (lat,lng) -> Corresponding Tile ID under a zoom
$tile_id = GoogleMapUtility::toTileXY($lat, $lng, $zoom);
// (lat,lng) -> pixel coordinate under a zoom
//$pixel = GoogleMapUtility::toZoomedPixelCoords($lat, $lng, $zoom);
$pixel = GoogleMapUtility::getPixelOffsetInTile($lat, $lng, $zoom);
echo "Tileid = ".$tile_id->x.", ".$tile_id->y."<br/>";
echo "pixel = ".$pixel->x.", ".$pixel->y."<br/>";
$im = imagecreatefrompng("./c_173912_209_405_10.png");
imagetruecolortopalette($im, false, 255);
list($width, $height, $type, $attr) = getimagesize("./c_173912_209_405_10.png");
echo "size = ".$width.", ".$height."<br/>";

//The following will only work for truecolor images.
$index = imagecolorat($im, $pixel->x, $pixel->y);
list($r, $g, $b, $a) = imagecolorsforindex($im, $index);
$rgb = imagecolorsforindex($im, $index);
$r = ($rgb >> 16) & 0xFF;
$g = ($rgb >> 8) & 0xFF;
$b = $rgb & 0xFF;
var_dump($r, $g, $b);
echo "RGB by shift: ".$r.", ".$g.", ".$b."<br/>";

$R = $rgb["red"];
$G = $rgb["green"];
$B = $rgb["blue"];
echo "RGB by hash access: ".$R.", ".$G.", ".$B."<br/>";
var_dump($r, $g, $b, $a);
$coor_tran = imagecolorsforindex($im, $rgb);
echo "Printing color_tran = ";
print_r($color_tran);
echo "<br/>";

echo "TEST: r(".$r.") g(".$g.") b(".$b.").<br/>";
?>
