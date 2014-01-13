<?php

// RGB to color name: lower case with underbar for space
// http://web.njit.edu/~kevin/rgb.txt.html
function get_color_allocate($image, $color_name)
{
  $col = 0;

  //echo "get_color_alloc: ".$color_name."</br>";

  switch ($color_name){
  case "white":
    $col = imagecolorallocate($image,255,255,255);
    break;
  case "darkRed":
    $col = imagecolorallocate($image,125,0,0);
    break;
  case "red":
    $col = imagecolorallocate($image,250,0,0);
    break;
  case "darkGreen":
    $col = imagecolorallocate($image,0,150,0);
    break;
  case "green":
    $col = imagecolorallocate($image,0,250,0);
    break;
  case "darkBlue":
    $col = imagecolorallocate($image,0,0,150);
    break;
  case "slate_blue":
    $col = imagecolorallocate($image,0,127,250);
    break;
  case "blue":
    $col = imagecolorallocate($image,0,0,250);
    break;
  case "orange":
    $col = imagecolorallocate($image,250,150,0);
    break;
  case "white":
    $col = imagecolorallocate($image, 255, 255, 255);
    break;
  case "black":
    $col = imagecolorallocate($image,0,0,0);
    break;
  default:
    echo "get_color_allocate : ".$color_name." undefined</br>";
    $col = FALSE;
    break;
  }

  return $col;
}

?>
