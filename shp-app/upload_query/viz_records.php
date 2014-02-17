<!-- Original script taken from: http://www.gorissen.info/Pierre/maps/googleMapLocation.php -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
	function fetchMetaInfoFromTbl($dst_tbl, $ntt)  
	{
		mysql_query('SET CHARACTER SET utf8');
		$query = "SELECT xmin, xmax, ymin, ymax, numparts, numpoints
           		FROM ".$dst_tbl."
           		WHERE primary_key=".$ntt."
           		ORDER BY primary_key";

		//echo $query2."</br>";
		$result = mysql_query($query);
		if($result == FALSE) {
  			echo "The species is not available in DB: $dst_tbl, key: $ntt<br />";
  			return FALSE;
		}
		else 
		{
			for ($count=0; $count < mysql_numrows($result); $count++) {
				$xmin = mysql_result($result, $count, "xmin");
				$xmax = mysql_result($result, $count, "xmax");
				$ymin = mysql_result($result, $count, "ymin");
				$ymax = mysql_result($result, $count, "ymax");
				$nprt = mysql_result($result, $count, "numparts");
				$npnt = mysql_result($result, $count, "numpoints");
			}

			mysql_free_result($result);
			return array ($xmin, $xmax, $ymin, $ymax, $nprt, $npnt);
		}
	}

	$shp_name = $_GET["ShapeName"]; 
	$tbl_name_dst = $_GET["TableNameDst"];
	$tbl_name_dbf = $_GET["TableNameDbf"];
	$zoom_start = $_GET["zoom_start"];
	$zoom_end = $_GET["zoom_end"];

	$entity_folder = FALSE;
	if(array_key_exists('entity', $_GET))
		$entity_folder = $_GET["entity"];

	require_once(dirname(__FILE__)."/upload_utils.php");


	//echo "entityfolder: ".$entity_folder."</br>";
	// define dirs for all, selective, single or nothing
	if($entity_folder==FALSE)  {
		$dirTiles = dirname(__FILE__)."/../../../tiles/".$shp_name."_viz";	
		list($dirs, $files) = get_dirs_files($dirTiles);
	
		echo "dirTiles:".$dirTiles.";</br>";//, dirs: $dirs , files: $files </br>";
		echo "entity: ".$entity_folder.";</br>";
		echo "zooms: $zoom_start , $zoom_end </br>";
	
	}
	else
		$dirs = array($entity_folder);
	
	// TEST dirs

	require_once dirname(__FILE__)."/../../db2tile/db_credentials.php";
	//echo "name: $db_name, pwd:$db_pass, host:$db_host </br>";

	$conn = mysql_connect($db_host, $db_name, $db_pass)
	or die("Couldn't connect to my sql server!");

   	@mysql_select_db($database);

	// dst info from DB
	//echo "Existence of entity: $entity_folder </br>";

	$xn = -160.0;
	$xx = 160.0;
	$yn = -50.0;
	$yx = 50.0;
	$centerLng = ($xn + $xx) / 2.0;
	$centerLat = ($yn + $yx) / 2.0;

	if (substr($entity_folder, 0, 3) == "id_") {
		$prikey = substr($entity_folder, 3);
		//echo "prikey = $prikey </br>";

		list($xn, $xx, $yn, $yx, $npr, $npnt) = fetchMetaInfoFromTbl($tbl_name_dst, $prikey);
		$centerLng = ($xn + $xx) /2.0;
		$centerLat = ($yn + $yx) /2.0;
//	echo "center lat: $centerLat, enter lng: $centerLng <br/>";
	}
	mysql_close($conn);

?>

<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
  <head>
  <style type="text/css">
  h1 {
    font-family:sans-serif;
    color:black;
    text-align: center;
    font-size:120%;
  }

  .tekst {
    font-family:sans-serif;
    color:green;
    font-size:100%;
  }

  .smalltekst {
    font-family:sans-serif;
    color:black;
    font-size:80%;
  }
  </style>

	<!--style type="text/css">
	v\:* {
		behavior:url(#default#VML);
	}
	</style-->

	<!-- Google Map v2 -->
  <script src="http://maps.google.com/maps?file=api
              &v=2
              &key=AIzaSyCDHLSbvam3dECsm9rcno6KIy6h9ynb98U" 
          type="text/javascript">
  </script>

	<title>Shape Records Visualization</title>

  </head>



  <body>
	<h1>Shape File: Overlay Visualization of Entities</h1>

	<script type="text/javascript">
		var shp_name = '<?php echo $shp_name; ?>';//"tm_wgs84_sans_antarctica";
		var tbl_name_dbf = '<?php echo $tbl_name_dbf; ?>';//"tm_wgs84_sans_antarctica_dbf";
		var shp_name = '<?php echo $shp_name; ?>';
		var idDirArray = <?php echo json_encode($dirs); ?>;
		//document.write(idDirArray);
		//document.write("ShapeName: "+shp_name+", Number of records:"+idDirArray.length);
	</script>

	<div style="width: 200px;" class="tekst"><b></b></div>
	<div id="map" style="width: 1280px;height: 580px;position: absolute;left:20px;"></div>



<script type="text/javascript">
    //<![CDATA[

	// either false or string/
	var entity_folder = '<?php echo $entity_folder; ?>';

	// center pt 
	var topLat = '<?php echo $yx; ?>';
	var westLng = '<?php echo $xn; ?>';
	var eastLng = '<?php echo $xx; ?>';
	var bottomLat = '<?php echo $yn; ?>';

	var centerLat = '<?php echo  $centerLat; ?>';
	var centerLng = '<?php echo  $centerLng; ?>';

	var setLat = 38.570278;
	var setLon = -112.605400;   
	var setZoom = 8;   

	function load() {
	
		var map = new GMap2(document.getElementById("map"));
		map.addMapType(G_PHYSICAL_MAP);
		map.setMapType(G_PHYSICAL_MAP);
		
		var mt = map.getMapTypes();
		for(var i=0; i<mt.length ; i++) {
 			mt[i].getMinimumResolution = function () {return '<?php echo $zoom_start ?>';}
 			mt[i].getMaximumResolution = function () {return '<?php echo $zoom_end ?>';}
		}

		map.addControl(new GLargeMapControl()); // added
		map.addControl(new GMapTypeControl()); // added
		map.addControl(new GOverviewMapControl(null));
		map.addControl(new GScaleControl(), 
				new GControlPosition(G_ANCHOR_BOTTOM_LEFT, new GSize(80, 3)));

		var point = new GLatLng(centerLat, centerLng);
		var threshold = 2.0;
		var min_pos = new GLatLng(westLng-threshold, bottomLat-threshold, true);
		var max_pos = new GLatLng(eastLng+threshold, topLat+threshold, true);
		var bound = new GLatLngBounds(min_pos, max_pos);
		var initZoom = 2;

		if(entity_folder == "") initZoom = 2;
		else	{
			initZoom = map.getBoundsZoomLevel(bound);
			if(initZoom > 0) initZoom--; // just get zoomed out level.
		}

 		map.setCenter(point, initZoom);

		if(entity_folder != "")
			map.addOverlay(new GMarker(point));


		// defining new maptype
		var copyright = new GCopyright(1,
                new GLatLngBounds(new GLatLng(-90, -180),
                new GLatLng(90, 180)),
                0,
                "2014 Rice, Choe");

		var copyrightCollection = new GCopyrightCollection("&#xA9");
		copyrightCollection.addCopyright(copyright);

		var urlFunc = function(shpName, dirName) {
			return function(tile, zoom) {
				var tileUrl = "tileServer.php?ShapeName="+shpName+"&IdDirName="+dirName+"&x="+tile.x+"&y="+tile.y+"&zoom="+zoom;
                return tileUrl;
            };
		};

		var trueFunc = function() {return true;};
		var numFunc = function(num) { return function() { return num;} };

		var idDirArray = <?php echo json_encode($dirs); ?>;


		var cnt;
		for(cnt=0; cnt < idDirArray.length ; cnt++) 
		{
			var idDir = idDirArray[cnt];
   	 		var tilelayer = new GTileLayer(copyrightCollection, 4, 5);

   	 		tilelayer.getTileUrl = urlFunc (shp_name, idDir);
   	 		tilelayer.isPng = trueFunc;
			tilelayer.getOpacity = numFunc(0.333);

   	 		var myTileLayer = new GTileLayerOverlay(tilelayer);
   	 		map.addOverlay(myTileLayer);
		}

	}

window.onload=load;
window.onunload=GUnload;
</script>


	<div id="geo" style="width: 300px;position: absolute; left: 20px; top:660px;" class="tekst">
		<form method="post" action"">

<?php

/* instantiated previously
	$shp_name = $_GET["ShapeName"]; 
	$tbl_name_dst = $_GET["TableNameDst"];
	$tbl_name_dbf = $_GET["TableNameDbf"];
	$zoom_start = $_GET["zoom_start"];
	$zoom_end = $_GET["zoom_end"];

	$entity_folder = $_GET["entity"];
*/

        require_once dirname(__FILE__)."/upload_utils.php";

        $conn = mysql_connect($db_host, $db_name, $db_pass);
        //or die("Couldn't select db - $tbl_name_dbf");

        $db_list = mysql_list_dbs($conn);
        @mysql_select_db($database);

	echo "Tbl name: $tbl_name_dbf at db: $database </br>";

	echo "bbx: x-[$xn, $xx], y-[$yn, $yx] </br>";

        $b_tbl_exst = table_exist($database, $tbl_name_dbf)
        or die("tble $tbl_name_dbf doesn't exist");

        echo_tilelink_table($shp_name, $tbl_name_dst, $tbl_name_dbf, $zoom_start, $zoom_end);
	mysql_close($conn);

?>
		</form>
 test page
	</div>



</body>
</html>
