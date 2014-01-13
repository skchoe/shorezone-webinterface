<!-- Original script taken from: http://www.gorissen.info/Pierre/maps/googleMapLocation.php -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
  $zoom_start = $_GET["zoom_start"];
  $zoom_end = $_GET["zoom_end"];
  $rep = $_GET["rep"];
  $pickorviz = $_GET["pickorviz"];
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

	<style type="text/css">
	v\:* {
		behavior:url(#default#VML);
	}
	</style>

	<!-- Google Map v2 -->
  <script src="http://maps.google.com/maps?file=api
              &v=2
              &key=AIzaSyCDHLSbvam3dECsm9rcno6KIy6h9ynb98U" 
          type="text/javascript">
  </script>

	<title>Query for Shape-layer</title>

  </head>

<?php
  $shp_name = $_GET["ShapeName"]; 
  $tbl_name_dst = $_GET["TableNameDst"];
  $tbl_name_dbf = $_GET["TableNameDbf"];

  //die ("Data Passing $shp_name , $tbl_name_dbf </br>");
  $sps;
  $sp;
  if ($shp_name == "amphanura") {
    $sps = "frogs and toads";
    $sp = "frog and toad";
  }
  else if ($shp_name == "amphcaudata") {
    $sps = "salamanders";
    $sp = "salamander";
  }
  else if ($shp_name == "amphgymnophiona") {
    $sps = "caecilians";
    $sp = "caecilian";
  }
  else {
    $sps = "objects";
    $sp = "object";
  }

?>



  <body>
	<h1>Select Location, query for layers</h1>
	<!-- <div style="width: 200px;" class="tekst"><b></b></div> -->
	<div id="map" style="width: 1200px;height: 450px;position: absolute;left:20px;"></div>
	<div id="geo" style="width: 310px;position: absolute;left: 20px;top: 500px;" class="tekst">

	<form method="post" action="">
		<table>
		<tr>
			<td> Latitude:</td>
			<td><input type='text' name='Latitude' id="frmLat"></td>
		</tr>
		<tr>
			<td> Longitude:</td>
			<td><input type='text' name='Longitude' id="frmLon"></td>
		</tr>
		<tr>
			<td> Accuracy zoom level:</td>
			<td><input type='label' name='Zoom' id="frmZoom"></td>
		</tr>
		<tr>
			<td><input type="button" name="Command" value="Find Object" 
			onClick="computeExistence(this.form)"></td>
			<td><input type="button" name="Clear" value="Clear Result"
			onClick="clearResult(this.form)"></td>
		</tr>
		</table>
	</form><br />
	</div>

        <div id="desc" style="width: 310px;position: absolute;left: 20px;top: 630px;" >
  Click on the map to mark a location and press "Find Object" for a list of <?php echo $sps; ?> estimated to occur there. <!--This software uses IUCN <?php echo $sp; ?> distribution maps, and was created by Seung-Keol Choe and Jeff Rice. --> </div>

	<!---
	<div style="width: 960px; position: absolute; left: 350px; top: 500px;" class="smalltekst" id="progressbar">
	<p><i>progress bar here:</i></p>
	</div>
	--->
	<!---<div style="width: 960px; position: absolute; left: 320px; top: 500px;" class="smalltekst" id="resultSpan">
        -->
	<div style="width: 3360px; position: absolute; left: 330px; top: 510px;" class="smalltekst" id="resultSpan">
	<p><i>Query results:</i></p>
	<br />
	</div>

<script type="text/javascript">
    //<![CDATA[
	var topLat = 90.0;
	var westLng = -160.0;
	var eastLng = 160.0;
	var bottomLat = -50.0;

	//var centerLat = (topLat + bottomLat)/2.0;
	//var centerLng = (westLng + eastLng)/2.0;

	// Default location <West bound of I-70: junction to I-15>
	//var setLat = 38.570278;
	//var setLon = -112.605400;   

	// Default location <Puget Sound Area>
	var setLat = 47.736306;
	var setLon = -122.361603;   
	var queryZoom = 18;   
	var initZoom = 8;   

	var centerLat = setLat;
	var centerLng = setLon;

	function ajaxFunction(shp_name, table_name_dst, table_name_dbf, zoom_start, zoom_end, lng, lat, zoom, rep, pickorviz)
	{
		var xmlhttp;
		
		if (window.XMLHttpRequest)
 		{
			// code for IE7+, Firefox, Chrome, Opera, Safari
			//document.getElementById("resultSpan").write("Before xmlhttprequest");
			xmlhttp=new XMLHttpRequest();
			//document.getElementById("resultSpan").write("After xmlhttprequest");
 		}
		else if (window.ActiveXObject)
 		{
			// code for IE6, IE5
			xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
 		}
		else
 		{
			alert("Your browser does not support XMLHTTP!");
		}

		var url = "run_query_gen.php";
		url += "?ShapeName="+shp_name;
		url += "&TableNameDst="+table_name_dst;
		url += "&TableNameDbf="+table_name_dbf;
		url += "&ZoomStart="+zoom_start;
		url += "&ZoomEnd="+zoom_end;
		url += "&lng="+lng;
		url += "&lat="+lat;
		url += "&zoom="+zoom;
		url += "&rep="+rep;
		url += "&pickorviz="+pickorviz;
		//alert(url);

		//document.getElementById("resultSpan").write("url ready");
		xmlhttp.onreadystatechange = function() {
			//document.getElementById("resultSpan").write("onreadystatechange");
			if(xmlhttp.readyState==4) {
				document.getElementById("resultSpan").innerHTML = xmlhttp.responseText;
			}
		}
		//document.getElementById("resultSpan").write("before url open");
		xmlhttp.open("GET", url, true);
		//document.getElementById("resultSpan").write("after url open, before send");
		xmlhttp.send(null);
		//document.getElementById("resultSpan").write("after url send");
	}

	function clearResult(frm) 
	{
		document.getElementById("resultSpan").innerHTML = "";
	}



	function computeExistence(frm) {

		var shp_name = '<?php echo $shp_name; ?>';
		var tbl_name_dst = '<?php echo $tbl_name_dst; ?>';
		var tbl_name_dbf = '<?php echo $tbl_name_dbf; ?>';
		var zoom_start = '<?php echo $zoom_start; ?>';
		var zoom_end = '<?php echo $zoom_end; ?>';
		var rep = '<?php echo $rep; ?>';
		var pickorviz = '<?php echo $pickorviz; ?>';

		clearResult(frm);

		var lng = frm.Longitude.value;
		var lat = frm.Latitude.value;
		var zoom = frm.Zoom.value;
		var sum = 0;
	
		ajaxFunction(shp_name, tbl_name_dst, tbl_name_dbf, zoom_start, zoom_end, lng, lat, zoom, rep, pickorviz);
	}

	// argItems code taken from 
	// http://www.evolt.org/article/Javascript_to_Parse_URLs_in_the_Browser/17/14435/?format=print
	function argItems (theArgName) {
		sArgs = location.search.slice(1).split('&');
    		r = '';
    		for (var i = 0; i < sArgs.length; i++) {
        		if (sArgs[i].slice(0,sArgs[i].indexOf('=')) == theArgName) {
            			r = sArgs[i].slice(sArgs[i].indexOf('=')+1);
            			break;
        		}
    		}
    	return (r.length > 0 ? unescape(r).split(',') : '')
	}
	
	function placeMarker(setLat, setLon) {
	
		var message = "geotagged geo:lat=" + setLat + " geo:lon=" + setLon + " "; 
		var messageRoboGEO = setLat + ";" + setLon + ""; 
	  

		document.getElementById("frmLat").value = setLat;
		document.getElementById("frmLon").value = setLon;
		//document.getElementById("frmZoom").value = queryZoom;
	  
		var map = new GMap(document.getElementById("map"));
		map.addMapType(G_PHYSICAL_MAP);
		map.setMapType(G_PHYSICAL_MAP);
		
		var mt = map.getMapTypes();
		for(var i=0; i<mt.length ; i++) {
 			mt[i].getMinimumResolution = function () {return <?php echo $zoom_start ?>;}
 			mt[i].getMaximumResolution = function () {return <?php echo $zoom_end ?>;}
		}

		map.addControl(new GLargeMapControl()); // added
		map.addControl(new GMapTypeControl()); // added

		//overview
		map.addControl(new GOverviewMapControl(null));

		//scale control
		map.addControl(new GScaleControl(), 
				new GControlPosition(G_ANCHOR_BOTTOM_LEFT, new GSize(80, 3)));

		var point = new GLatLng(centerLat, centerLng);
		var threshold = 2.0;
		var min_pos = new GLatLng(westLng-threshold, bottomLat-threshold, true);
		var max_pos = new GLatLng(eastLng+threshold, topLat+threshold, true);
		var bound = new GLatLngBounds(min_pos, max_pos);

 		map.setCenter(point, initZoom);

		//marker
		var point = new GPoint(setLon, setLat);
		var marker = new GMarker(point);
		map.addOverlay(marker);

		GEvent.addListener(map, 'click', function(overlay, point) {
			if (overlay) {
				map.removeOverlay(overlay);
			} else if (point) {
				map.recenterOrPanToLatLng(point);
				var marker = new GMarker(point);
				map.addOverlay(marker);
				var matchll = /\(([-.\d]*), ([-.\d]*)/.exec( point );
				if ( matchll ) { 
					var lat = parseFloat( matchll[1] );
					var lon = parseFloat( matchll[2] );
					lat = lat.toFixed(6);
					lon = lon.toFixed(6);
					var message = "geotagged geo:lat=" + lat + " geo:lon=" + lon + " "; 
					var messageRoboGEO = lat + ";" + lon + ""; 
				} else { 
					var message = "<b>Error extracting info from</b>:" + point + ""; 
					var messagRoboGEO = message;
				}

				marker.openInfoWindowHtml(message);

				document.getElementById("frmLat").value = lat;
				document.getElementById("frmLon").value = lon;

			}
		});
	}


	if (argItems("lat") == '' || argItems("lon") == '') {
		placeMarker(setLat, setLon);
	} else {
		var setLat = parseFloat( argItems("lat") );
		var setLon = parseFloat( argItems("lon") );
		setLat = setLat.toFixed(6);
		setLon = setLon.toFixed(6);
		placeMarker(setLat, setLon);
    }

    //]]>

</script>



<!-- Start twatch code -->
<script type="text/javascript">
<!--
//<![CDATA[
document.write('<scr'+'ipt type="text/javascript" src="/Pierre/twatch/jslogger.php?ref='+( document["referrer"]==null?'':escape(document.referrer))+'&pg='+escape(window.location)+'&cparams=true"></scr'+'ipt>');
//]]>
//-->
</script>
<!-- End twatch code -->



</body>
</html>
