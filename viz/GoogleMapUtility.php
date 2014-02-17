<?php

class GoogleMapUtility {
    const TILE_SIZE = 256;

    public static function fromXYToLatLng($point,$zoom) {
        $scale = (1 << ($zoom)) * GoogleMapUtility::TILE_SIZE;
        
        return new Point(
            (int) ($normalised->x * $scale),
            (int)($normalised->y * $scale)
        );
    
        return new Point(
            $pixelCoords->x % GoogleMapUtility::TILE_SIZE, 
            $pixelCoords->y % GoogleMapUtility::TILE_SIZE
        );
    }
    
    public static function fromMercatorCoords($point) 
    {
        echo "From-Mercator in : ".$point->x.", ".$point->y."</br>"; 
        $point->x *= 360; 
        $point->y = rad2deg(atan(sinh($point->y))*M_PI);
        echo "From-Mercator out : ".$point->x.", ".$point->y."</br>"; 
        return $point;
    }
    
    public static function getPixelOffsetInTile($lat,$lng,$zoom) 
    {
	  $pixelCoords = GoogleMapUtility::toZoomedPixelCoords($lat, $lng, $zoom);
	  return new Point($pixelCoords->x % GoogleMapUtility::TILE_SIZE, 
					   $pixelCoords->y % GoogleMapUtility::TILE_SIZE);
    }

    public static function getTileRect($x,$y,$zoom) 
    {
	  $tilesAtThisZoom = 1 << $zoom;
	  $lngWidth = 360.0 / $tilesAtThisZoom;
	  $lng = -180 + ($x * $lngWidth);

	  $latHeightMerc = 1.0 / $tilesAtThisZoom;
	  $topLatMerc = $y * $latHeightMerc;
	  $bottomLatMerc = $topLatMerc + $latHeightMerc;

	  $bottomLat = (180 / M_PI) * ((2 * atan(exp(M_PI * 
												 (1 - (2 * $bottomLatMerc))))) - (M_PI / 2));
	  $topLat = (180 / M_PI) * ((2 * atan(exp(M_PI * 
											  (1 - (2 * $topLatMerc))))) - (M_PI / 2));

	  $latHeight = $topLat - $bottomLat;
	  
	  return new Boundary($lng, $bottomLat, $lngWidth, $latHeight);
    }

    public static function toMercatorCoords($lat, $lng) 
    {
	  if ($lng > 180) {
		$lng -= 360;
	  }

	  $lng /= 360;
	  $lat = asinh(tan(deg2rad($lat)))/M_PI/2;
	  return new Point($lng, $lat);
    }

    public static function toNormalisedMercatorCoords($point) 
    {
	  $point->x += 0.5;
	  $point->y = abs($point->y-0.5);
	  return $point;
    }

    public static function toTileXY($lat, $lng, $zoom) {
        $normalised = GoogleMapUtility::toNormalisedMercatorCoords(
            GoogleMapUtility::toMercatorCoords($lat, $lng)
        );
        $scale = 1 << ($zoom);
        return new Point((int)($normalised->x * $scale), (int)($normalised->y * $scale));
    }

    public static function toZoomedPixelCoords($lat, $lng, $zoom) {
        $normalised = GoogleMapUtility::toNormalisedMercatorCoords(
            GoogleMapUtility::toMercatorCoords($lat, $lng)
        );
        $scale = (1 << ($zoom)) * GoogleMapUtility::TILE_SIZE;
        return new Point(
            (int)($normalised->x * $scale), 
            (int)($normalised->y * $scale)
        );
    }

    // http://gis.stackexchange.com/questions/66247/what-is-the-formula-for-calculating-world-coordinates-for-a-given-latlng-in-goog
    function fromLatLngToPoint($lat, $lng) 
    {
      $x = ($lng + 180) / 360 * GoogleMapUtility::TILE_SIZE;
      $y = ((1 - log(tan($lat * M_PI / 180) + 1 / cos($lat * M_PI / 180)) / M_PI) / 2 * pow(2, 0)) * GoogleMapUtility::TILE_SIZE;
      return new Point($x, $y);
    }

    function fromPointToLatLng($point) 
    {
      $lng = $point->x / GoogleMapUtility::TILE_SIZE * 360 - 180;
      $n = M_PI - 2 * M_PI * $point->y / GoogleMapUtility::TILE_SIZE;
      $lat = (180 / M_PI * atan(0.5 * (exp($n) - exp(-$n))));
      return new Point($lng, $lat);
    }

    //https://github.com/sminnee/silverstripe-gis/blob/master/thirdparty/GoogleMapUtility.php
	static function originShift() {
		return 2 * pi() * 6378137 / 2;
	}

	static function initialResolution() {
		return 2 * pi() * 6378137 / self::$TILE_SIZE;
	}

	static function latLonToMeters($lat, $lng) {
		$mx = $lng * self::originShift() / 180;
		$my = log( tan((90 + $lat) * pi() / 360.0 )) / (pi() / 180);
		$my = $my * self::originShift() / 180;
		return new Point($mx, $my);
	}

	static function metersToLatLon($mx, $my) {
		$lng = ($mx / self::originShift()) * 180.0;
		$lat = ($my / self::originShift()) * 180.0;

		$lat = 180 / pi() * (2 * atan( exp( $lat * pi() / 180.0)) - pi() / 2.0);
		return new Point($lat, $lng);
	}

	static function metersToTile($mx, $my, $zoom) {
		$p = self::metersToPixels($mx, $my, $zoom);
		return self::pixelsToTile($p->x, $p->y);
	}

	static function metersToPixels($mx, $my, $zoom) {
		$res = self::resolution($zoom);
		$px = ($mx + self::originShift()) / $res;
		$py = ($my + self::originShift()) / $res;
		return new Point($px, $py);
	}

	static function pixelsToMeters($px, $py, $zoom) {
		$res = self::resolution($zoom);
		$mx = $px * $res - self::originShift();
		$my = $py * $res - self::originShift();
		return new Point($mx, $my);
	}

	static function pixelsToTile($px, $py) {
		$tx = (int)ceil( $px / (float)self::$TILE_SIZE ) - 1;
		$ty = (int)ceil( $py / (float)self::$TILE_SIZE ) - 1;
		return new Point($tx, $ty);
	}

	static function resolution($zoom) {
		return self::initialResolution() / pow(2, $zoom);
	}


        //http://wiki.openstreetmap.org/wiki/Mercator
	function lon2x($lon) { return deg2rad($lon) * 6378137.0; }
	function lat2y($lat) { return log(tan(M_PI_4 + deg2rad($lat) / 2.0)) * 6378137.0; }
	function x2lon($x) { return rad2deg($x / 6378137.0); }
	function y2lat($y) { return rad2deg(2.0 * atan(exp($y / 6378137.0)) - M_PI_2); }
}

class Point {
     public $x,$y;
     function __construct($x,$y) {
          $this->x = $x;
          $this->y = $y;
     }

     function __toString() {
          return "({$this->x},{$this->y})";
     }
}

class Boundary {
     public $x,$y,$width,$height;
     function __construct($x,$y,$width,$height) {
          $this->x = $x;
          $this->y = $y;
          $this->width = $width;
          $this->height = $height;
     }
     function __toString() {
          return "({$this->x},{$this->y},{$this->width},{$this->height})";
     }
}

?>
