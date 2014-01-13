<?php
set_time_limit(3600*24*3);
ini_set('memory_limit', -1);

class Shape2PhpPolygon {

// a record in shape file -> wkt string rep.
public function convert ($numparts, $part_array) {

  $array_parts = array();
  for($i=0; $i<$numparts; $i++) {

	  if (is_array ($part_array[$i]["points"])) {
	    $point_array = $part_array[$i]["points"];
	    $point_count = count ($point_array);

	    $init_x = 0.0; // for winding up the polyline
	    $init_y = 0.0;

      $outputPart = array();
	    for($j=0;$j < $point_count ; $j++) {
  
  		  $x = $point_array[$j]["x"];
  		  $y = $point_array[$j]["y"];
  		  if($j==0) {
  		    $init_x = $x;
  		    $init_y = $y;
  		  }

        $outputPart[] = $x;
        $outputPart[] = $y;
	    }
	    $outputPart[] = $init_x;
	    $outputPart[] = $init_y;

      $array_parts[] = $outputPart;
	  }
	  else echo "Shape2PhpPolygon.convert(), ".$part_array[$i]["points"]."isn't array</br>";
  }

  /*
  echo "Multipolygon Geo = ";
  print_r($array_parts);
  echo "<br/>";	
  flush();
  ob_flush();
   */
  
  return $array_parts;
}
}
?>
