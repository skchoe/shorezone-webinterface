<?php
set_time_limit(3600*24*3);
ini_set('memory_limit', -1);

class Shape2Wkt {

  public static $GEOTYPE_MULTIPOLYGON = "MULTIPOLYGON";
  public static $GEOTYPE_MULTILINESTRING = "MULTILINESTRING";
  public static $GEOTYPE_MULTIPOINT = "MULTIPOINT";
  public static $GEOMODEL_THICKNESS = 805; // an half mile in meter--804.672

  public $geotype = "Multipolygon";

  function __construct($geotype)
  {
    $this->geotype = $geotype;
  }

  //this->geotype: MultiPolygon, MultiLineString, MultiPoint
  public function convert ($numparts, $part_array) 
  {
    //echo "<br>------------------------------------------------</br>---WktUtil GEO Type: ".$this->geotype.", numparts: ".$numparts."</br>";
    //Dump the information
    //shp_data["numparts"] = number of parts (int);
    //shp_data["numpoints"] =  same as aobv points (double);
    //shp_data["parts"][i]["points"] = array of index of vertices where parts start;
    //where i is part index

    //print_r($part_array);
    //echo "</br>";
    $geo_string = "";
    $thisgeotype = $this->geotype;

    if($thisgeotype == self::$GEOTYPE_MULTIPOLYGON)
      $geo_string = 'MULTIPOLYGON(';
    else if($thisgeotype == self::$GEOTYPE_MULTILINESTRING)
      $geo_string = 'MULTILINESTRING(';
    else if($thisgeotype == self::$GEOTYPE_MULTIPOINT)
      $geo_string = 'MULTIPOINT(';
    else
      $geo_string = '(';

    if($thisgeotype==self::$GEOTYPE_MULTIPOLYGON 
       || $thisgeotype==self::$GEOTYPE_MULTILINESTRING
       || $thisgeotype==self::$GEOTYPE_MULTIPOINT)
    {
      for($i=0; $i<$numparts; $i++) 
      {
        //echo "PART "+$i+" started</br>";
        $part_string = ''; // part separator
        if (is_array ($part_array[$i]["points"])) 
        {
          $point_array = $part_array[$i]["points"];
          $point_count = count ($point_array);
          //echo "$i th part w/ points size = ".$point_count."<br />";

          if($thisgeotype==self::$GEOTYPE_MULTIPOLYGON)
          {
            if($i==0) $part_string = '((';
            else $part_string = ',((';
          } 
          else if($thisgeotype==self::$GEOTYPE_MULTILINESTRING)
          {
            if($i==0) $part_string = '(';
            else $part_string = ',(';
          }
          else if($thisgeotype==self::$GEOTYPE_MULTIPOINT)
          {
            if($i==0) $part_string = '';
            else $part_string = ',';
          }
          $init_x = 0.0; // for winding up the polyline
          $init_y = 0.0;
          for($j=0;$j < $point_count ; $j++) 
          {
            $x = $point_array[$j]["x"];
            $y = $point_array[$j]["y"];
            if($j==0) {
              $init_x = $x;
              $init_y = $y;
            }
            else
              $part_string .= ',';

            $part_string .=' '.$x.' '.$y;
          }
          if($thisgeotype == self::$GEOTYPE_MULTIPOLYGON)
          {
            $part_string .=', '.$init_x.' '.$init_y.'))';
          }
          else if($thisgeotype == self::$GEOTYPE_MULTILINESTRING)
          {
            $part_string .=')';
          }
          else if($thisgeotype==self::$GEOTYPE_MULTIPOINT)
          {
            $part_string .='';
          }
        }
        $geo_string .= $part_string;
      }
    }
    else
    {
      echo "--No geotype selected </br>";
    }
    $geo_string .= ')';
  
/*
    if($thisgeotype == self::$GEOTYPE_MULTIPOLYGON)
      echo "Multipolygon WKT = ".$geo_string." <br/>";	
    else if($thisgeotype == self::$GEOTYPE_MULTILINESTRING)
      echo "MultiLineString WKT = ".$geo_string." <br/>";	
    else if($thisgeotype == self::$GEOTYPE_MULTIPOINT)
      echo "MultiPoint WKT = ".$geo_string." <br/>";	
    else
      echo "<br>Geometry is failed to be parsed</br>";
*/
    return $geo_string;
  }
}


class Wkt2PhpPolygon {
  
  public static  $GEOTYPE_MULTIPOLYGON = "MULTIPOLYGON";
  public static  $GEOTYPE_MULTILINESTRING = "MULTILINESTRING";
  public static  $GEOTYPE_MULTIPOINT = "MULTIPOINT";

  public function getGeoType($wktgeom_text)
  {
    $matches = array();
    preg_match('/^(\w+)\(.*\)$/', $wktgeom_text, $matches);
    //echo "Match in getGeotype: ".$matches[1]."</br>";
    return $matches[1];
  }

  /* the convert()'s input arg. is obtained by the output $res_poly of following code
   * $res_mp = mysql_query("SELECT ASTEXT(polygons) FROM ".$species_dst_tbl);
   * $cnt1 = mysql_numrows($res_mp);
   * $res_poly = mysql_result($res_mp, 0, "ASTEXT(polygons)");
   */

  /* Output of convert() is a php-array of each complex polygon
   * each polygon is represented by array(...,x(i),y(i),x(i+1),y(i+1),...)
   * where x,y's are all pixel coordinates.

   * the conversion from geo -> pixel is done by Utility function
   */
  public function convert ($multigeom_text) 
  {
	//echo "ORIGIANL = ".$multigeom_text."<br/>";
	$orglen = strlen($multigeom_text);

 	$thisgeotype = $this->getGeoType($multigeom_text);
	$headlen = $taillen = 0;
	if($thisgeotype == self::$GEOTYPE_MULTIPOLYGON)
	{
	  $headlen = strlen("MULTIPOLYGON(((");
	  $taillen = strlen(")))");
	}
	else if($thisgeotype == self::$GEOTYPE_MULTILINESTRING)
	{
	  $headlen = strlen("MULTILINESTRING((");
	  $taillen = strlen("))");
	}
	else if($thisgeotype == self::$GEOTYPE_MULTIPOINT)
	{
	  $headlen = strlen("MULTIPOINT(");
	  $taillen = strlen(")");
	}
	else
	{
	  echo "TESTING FAIL: Cannot recognize Geometry from DB</br>";
	}

	$bodylen = $orglen-$headlen-$taillen;

	$strip_mp_paran = substr($multigeom_text, -$bodylen-$taillen, $bodylen);
	
	$stringParts = $numparts = 0;
	if($thisgeotype == self::$GEOTYPE_MULTIPOLYGON)
	{
	  $stringParts = explode(")),((", $strip_mp_paran);
	}
	else if($thisgeotype == self::$GEOTYPE_MULTILINESTRING)
	{
	  $stringParts = explode("),(", $strip_mp_paran);
	}
	else if($thisgeotype == self::$GEOTYPE_MULTIPOINT)
	{
	  $stringParts = explode(",", $strip_mp_paran);
	}
	$numparts = sizeof($stringParts);

	$arrayParts = array();
	for($i=0; $i<$numparts ;$i++) {
	  $outputPart = array();
	  
	  $inputPart = $stringParts[$i];
	  $points = explode(",", $inputPart);
	  $numpoints = sizeof($points);
	  for($j=0; $j<$numpoints; $j++) {
		$inputPoint = $points[$j];
		$xy = explode(" ", $inputPoint);
		//echo "X,Y[".$i.":".$j."] = ".$xy[0].", ".$xy[1]."<br/>";
		$outputPart[] = $xy[0];
		$outputPart[] = $xy[1];
	  }
	  
	  $arrayParts[] = $outputPart;
	}
	return $arrayParts;
  }
  
  /* Special function for debuging position of points
   * an array is output having x,y,x,y,......
   */
  public function convert2simplePointArray ($multigeom_text) {
	
	//echo "ORIGIANL = ".$multigeom_text."<br/>";
	$orglen = strlen($multigeom_text);
	$headlen = strlen("MULTIPOLYGON(((");
	$taillen = strlen(")))");
	$bodylen = $orglen-$headlen-$taillen;

	$strip_mp_paran = substr($multigeom_text, -$bodylen-$taillen, $bodylen);
	
	$stringParts = explode(")),((", $strip_mp_paran);
	//	echo "poly = ".$strip_mp_paran."<br/>";
	$numparts = sizeof($stringParts);

	$arrayPoints = array();
	for($i=0; $i<$numparts ;$i++) {
	  
	  $inputPart = $stringParts[$i];
	  $points = explode(",", $inputPart);
	  $numpoints = sizeof($points);
	  for($j=0; $j<$numpoints; $j++) {
		$inputPoint = $points[$j];
		$xy = explode(" ", $inputPoint);
		//echo "X,Y[".$i.":".$j."] = ".$xy[0].", ".$xy[1]."<br/>";
		$arrayPoints[] = $xy[0];
		$arrayPoints[] = $xy[1];
	  }
	}
	return $arrayPoints;
  }

  private function multiPartsString($arrayParts)
  {
	$jsonString = "";
        for($i=0;$i<count($arrayParts);$i++)
        {
		$part = $arrayParts[$i];

		$eachPartString ='[';
		for($j=0;$j<count($part) / 2;$j+=2)
 		{
			$k = $j+1;
			$x = $part[$j];
			$y = $part[$k];
			if($j==0) $eachPartString = $eachPartString.'['.$x.','.$y.']';
			else $eachPartString = $eachPartString.',['.$x.','.$y.']';
		}
		$eachPartString .=']';

		if($i==0) $jsonString = $jsonString.$eachPartString;
		else $jsonString = $jsonString.','.$eachPartString;
        }
	$jsonString .= ']}'; 

    	return $jsonString;
  }

  public function convert2GeoJSON ($multigeom_text) 
  {
    $geoType = $this->getGeoType($multigeom_text);
    $shp2wkt = new Shape2Wkt($geoType);

    if($multigeom_text != null && 0 < count($multigeom_text))
    {
      $jsonString = "";
      if($geoType == Shape2Wkt::$GEOTYPE_MULTIPOLYGON)
      {
	$jsonString = '{"type":"MultiPolygon","coordinates":[';
        $jsonString .= $this->multiPartsString($multigeom_text);
      }
      else if($geoType== Shape2Wkt::$GEOTYPE_MULTILINESTRING)
      {
	$jsonString = '{"type":"MultiLineString",\n"coordinates":[\n';
        $jsonString .= $this->multiPartsString($multigeom_text);
      }
      else if($geoType == Shape2Wkt::$GEOTYPE_MULTIPOINT)
      {
	$jsonString .= '{"type":"MultiPoint","coordinates":[';
        $jsonString .= $this->multiPartsString($multigeom_text);
      }
      else
      {
      }
      return $geoType;
      //return $jsonString;
    }
    else
    {
      return null;
    }
  }
}
?>
