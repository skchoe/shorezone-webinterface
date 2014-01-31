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

//$numseg == 4 -> rombus
//           6 -> hexagon
// r(th) = lenx * leny / sqrt(pow (leny*cos(th), 2) + pow(lenx*sin(th), 2));
// output : array (x1, y1, x2, y2, ..., xnumseg, ynumseg);
function ellipse($centerx, $centery, $lenx, $leny, $numseg)
{
  $resultpolyline = array();

  if($numseg < 4) $numseg = 4;
  
  for($i=0 ; $i < $numseg ; $i ++)
  {
    $theta = 2*M_PI/$numseg * $i;
    $ucoordx = cos($theta);
    $ucoordy = sin($theta);
    $xf = $lenx * $ucoordx;
    $yf = $leny * $ucoordy;
    $r = $lenx * $leny / sqrt(pow($xf, 2) + pow($yf, 2));

    array_push($resultpolyline, $centerx + $r * $ucoordx); 
    array_push($resultpolyline, $centery + $r * $ucoordy); 
  }

  return $resultpolyline;
}

// output array(x1,y1,x2,y2,x3,y3,x4,y4);
function offsetrectangle($x1, $y1, $thick1, $x2, $y2, $thick2)
{
  $r1 = $thick1 / 2;
  $r2 = $thick2 / 2;
  if($x1==$x2 && $y1==$y2)
  {
    return array();
  }
  else if($x1==$x2 && $y1!=$y2)
  {
    $miny = min($y1, $y2);
    $maxy = max($y1, $y2);
    return array($x1-$r1, $maxy, $x2-$r2, $miny, $x2+$r2, $miny, $x1+$r1, $maxy);
  }
  else if($x1!=$x2 && $y1==$y2)
  {
    $minx = min($x1, $x2);
    $maxx = max($x1, $x2);
    return array($minx, $y1+$r1, $minx, $y1-$r1, $maxx, $y2-$r2, $maxx, $y2+$r1);
  }
  else
  {
    $orgslope = ($y1-$y2) / ($x1-$x2);
    $orthoslope = -1 / $orgslope;
    $dx1 = $r1 / sqrt(1 + $orthoslope * $orthoslope);
    $dy1 = $r1 * $orthoslope / sqrt(1 + $orthoslope * $orthoslope);
    $dx2 = $r2 / sqrt(1 + $orthoslope * $orthoslope);
    $dy2 = $r2 * $orthoslope / sqrt(1 + $orthoslope * $orthoslope);
    if($x2 < $x1 && $y2 < $y1)
    {
      return array($x1-$dx1, $y1+$dy1, $x2-$dx2, $y2+$dy2, $x2+$dx2, $y2-$dy2, $x1+$dx1, $y1-$dy1);
    }
    else if($x2 < $x1 && $y1 < $y2)
    {
      return array($x1+$dx1, $y1+$dy1, $x2-$dx2, $y2+$dy2, $x2+$dx2, $y2-$dy2, $x1-$dx1, $y1-$dy1);
    }
    else if($x1 < $x2 && $y2 < $y1)
    {
      return array($x2+$dx2, $y2+$dy2, $x1+$dx1, $y1+$dy1, $x1-$dx1, $y1-$dy1, $x2-$dx2, $y2-$dy2);
    }
    else if($x1 < $x2 && $y1 < $y2)
    {
      return array($x2-$dx2, $y2+$dy2, $x1-$dx1, $y1+$dy1, $x1+$dx1, $y1-$dy1, $x2+$dx2, $y2-$dy2);
    }
  }
}

// TRUE / FALSE
function imagelinethick2($image, $x1, $y1, $x2, $y2, $color, $thick1 = 1, $thick2 = 1)
{
    if ($thick1 == $thick2) 
    {
      return imagelinethick($image, $x1, $y1, $x2, $y2, $color, $thick1);
    }
    if ($x1 == $x2 || $y1 == $y2) 
    {
	$t = round(($thick1 + $thick2) / 2) - 0.5;
        return imagefilledrectangle($image, round(min($x1, $x2) - $t), round(min($y1, $y2) - $t), round(max($x1, $x2) + $t), round(max($y1, $y2) + $t), $color);
    }
    $k = ($y2 - $y1) / ($x2 - $x1); //y = kx + q
    $div = sqrt(1 + pow($k, 2));
    $t1 = $thick1 / 2 - 0.5;
    $t2 = $thick2 / 2 - 0.5;
    $a1 = $t1 / $div;
    $a2 = $t2 / $div;

    $points = array(
        round($x1 - (1+$k)*$a1), round($y1 + (1-$k)*$a1),
        round($x1 - (1-$k)*$a1), round($y1 - (1+$k)*$a1),
        round($x2 + (1+$k)*$a2), round($y2 - (1-$k)*$a2),
        round($x2 + (1-$k)*$a2), round($y2 + (1+$k)*$a2),
    );
    imagefilledpolygon($image, $points, 4, $color);
    return imagepolygon($image, $points, 4, $color);
}

function imagelinethick($image, $x1, $y1, $x2, $y2, $color, $thick = 1)
{
    /* this way it works well only for orthogonal lines
    imagesetthickness($image, $thick);
    return imageline($image, $x1, $y1, $x2, $y2, $color);
    */
    if ($thick == 1) 
    {
        return imageline($image, $x1, $y1, $x2, $y2, $color);
    }
    $t = $thick / 2 - 0.5;
    if ($x1 == $x2 || $y1 == $y2) 
    {
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
