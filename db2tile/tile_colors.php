<?php

function RGBtoHSV(array $rgb) 
{
	list($R,$G,$B) = $rgb;
	//1
	$V = max($R,$G,$B);
	//2
	$X = min($R,$G,$B);
	//3
	$S = ($V-$X)/$V;
	if ($S == 0)
		throw new Exception("Hue is undefined");
	//4
	$r = ($V-$R)/($V-$X);
	$g = ($V-$G)/($V-$X);
	$b = ($V-$B)/($V-$X);
	//5
	if ($R == $V)
		$H = $G==$X?(5+$b):(1-$g);
	elseif ($G == $V)
		$H = $B==$X?(1+$r):(3-$b);
	else
		$H = $R==$X?(3+$g):(5-$r);
	//6
	$H /= 6;
	return array($H, $S, $V);
}

function HSVtoRGB(array $hsv) 
{
	list($H,$S,$V) = $hsv;
	//1
	$H *= 6;
	//2
	$I = floor($H);
	$F = $H - $I;
	//3
	$M = $V * (1 - $S);
	$N = $V * (1 - $S * $F);
	$K = $V * (1 - $S * (1 - $F));
	//4
	switch ($I) {
		case 0:
			list($R,$G,$B) = array($V,$K,$M);
			break;
		case 1:
			list($R,$G,$B) = array($N,$V,$M);
			break;
		case 2:
			list($R,$G,$B) = array($M,$V,$K);
			break;
		case 3:
			list($R,$G,$B) = array($M,$N,$V);
			break;
		case 4:
			list($R,$G,$B) = array($K,$M,$V);
			break;
		case 5:
		case 6: //for when $H=1 is given
			list($R,$G,$B) = array($V,$M,$N);
			break;
	}
	return array($R, $G, $B);
}

// TRUE / FALSE
function imagelinethick($image, $x1, $y1, $x2, $y2, $color, $thick = 1)
{
    /* this way it works well only for orthogonal lines
    imagesetthickness($image, $thick);
    return imageline($image, $x1, $y1, $x2, $y2, $color);
    */
    if ($thick == 1) {
        return imageline($image, $x1, $y1, $x2, $y2, $color);
    }
    $t = $thick / 2 - 0.5;
    if ($x1 == $x2 || $y1 == $y2) {
        return imagefilledrectangle($image, round(min($x1, $x2) - $t), round(min($y1, $y2) - $t), round(max($x1, $x2) + $t), round(max($y1, $y2) + $t), $color);
    }
    $k = ($y2 - $y1) / ($x2 - $x1); //y = kx + q
    $a = $t / sqrt(1 + pow($k, 2));
    $points = array(
        round($x1 - (1+$k)*$a), round($y1 + (1-$k)*$a),
        round($x1 - (1-$k)*$a), round($y1 - (1+$k)*$a),
        round($x2 + (1+$k)*$a), round($y2 - (1-$k)*$a),
        round($x2 + (1-$k)*$a), round($y2 + (1+$k)*$a),
    );
    imagefilledpolygon($image, $points, 4, $color);
    return imagepolygon($image, $points, 4, $color);
}

function generatergbcolors($num_colors, $bshuff)
{
  $maxrand = mt_getrandmax();
  $width = 360 / $num_colors;
  $colorarray = array();
  for ($i = 0; $i < 360; $i += $width) 
  {
	$hue = $i / 360;
	$rand = mt_rand(0, $maxrand);
	$saturation = 0.5 * (1 + $rand / $maxrand); // min = 0.5 max = 1
	$value = .9;
	list($r,$g,$b) = HSVtoRGB(array($hue, $saturation, $value));
	$r = (int) ($r*255);
	$g = (int) ($g*255); 
	$b = (int) ($b*255);
	$color = array("r"=>$r, "g"=> $g, "b"=>$b);
	//echo "$i th ";
	array_push($colorarray, $color);
  }
  if($bshuff)
    shuffle($colorarray);

  return $colorarray;
}

function rgbtophpcolor($image, $r, $g, $b)
{
  $c = imagecolorallocate($image, $r, $g, $b);
  return $c;
}

function generatephpcolors($image, $num_colors, $bshuff)
{
  $width = 360 / $num_colors;
  $rgbarray = generatergbcolors($num_colors, $bshuff);

  $colorarray = array();
  for ($i = 0; $i < $num_colors; $i ++) 
  {
	$rgb = array_pop($rgbarray);
	$c = rgbtophpcolor($image, $rgb["r"], $rgb["g"], $rgb["b"]);

	array_push($colorarray, $c);
  }
  return $colorarray;
}

function rgb2html($r, $g=-1, $b=-1)
{
    if (is_array($r) && sizeof($r) == 3)
        list($r, $g, $b) = $r;

    $r = intval($r); $g = intval($g);
    $b = intval($b);

    $r = dechex($r<0?0:($r>255?255:$r));
    $g = dechex($g<0?0:($g>255?255:$g));
    $b = dechex($b<0?0:($b>255?255:$b));

    $color = (strlen($r) < 2?'0':'').$r;
    $color .= (strlen($g) < 2?'0':'').$g;
    $color .= (strlen($b) < 2?'0':'').$b;
    return '#'.$color;
}

function html2rgb($color)
{
    if ($color[0] == '#')
        $color = substr($color, 1);

    if (strlen($color) == 6)
        list($r, $g, $b) = array($color[0].$color[1],
                                 $color[2].$color[3],
                                 $color[4].$color[5]);
    elseif (strlen($color) == 3)
        list($r, $g, $b) = array($color[0].$color[0], $color[1].$color[1], $color[2].$color[2]);
    else
        return false;

    $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);

    return array($r, $g, $b);
}

function echocolor($rgbs)
{
  echo "R: ".$rgbs["r"].", G: ".$rgbs["g"].", B: ".$rgbs["b"]."</br>";
}

?>
