<!-- Original script taken from: http://www.gorissen.info/Pierre/maps/googleMapLocation.php -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
  <head>
	<style type="text/css">
	<!--
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
	-->	
	</style>
	<style type="text/css">
	v\:* {
		behavior:url(#default#VML);
	}
	</style>
    	<script src="http://maps.google.com/maps?file=api&v=2&key=AIzaSyCDHLSbvam3dECsm9rcno6KIy6h9ynb98U" type="text/javascript"></script>

	<title>Find animal living here!</title>
  </head>
  <body>
	<h1>Select Location, query for animals</h1>
	<div style="width: 200px;" class="tekst"><b></b></div>
	<div id="map" style="width: 800px;height: 600px;position: absolute;left:20px;"></div>
	<div id="geo" style="width: 800px;position: absolute;left: 20px;top: 650px;" class="tekst">
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
			<td> Zoom:</td>
			<td><input type='text' name='Zoom' id="frmZoom"></td>
		</tr>
		<tr>
			<td><input type="button" name="Command" value="Find Animals"
			onClick="computeSpeciesExistence(this.form, 0)"></td>
			<!--
		</tr>
		<tr>
			<td>Result: </td>
			<td><input type="text" name="result" id="val"></td>
		</tr>
		<tr>
			-->
			<td><input type="button" name="Clear" value="Clear Result"
			onClick="clearResult(this.form)"></td>
		</tr>

		<tr>
			<td><input type="button" name="Amphibians" value="Amphibians"
			onClick="computeSpeciesExistence(this.form, 1)"></td>
			<td><input type="button" name="Birds" value="Birds"
			onClick="computeSpeciesExistence(this.form, 2)"></td>
		</tr>

		<tr>
			<td><input type="button" name="Mammals" value="Mammals"
			onClick="computeSpeciesExistence(this.form, 3)"></td>
			<td><input type="button" name="Reptiles" value="Reptiles"
			onClick="computeSpeciesExistence(this.form, 4)"></td>
		</tr>
		</table>
	</form><br />
	</div>

	<div style="width: 600px; position: absolute; left: 300px; top: 650px;" class="smalltekst" id="resultSpan">
	<p><i>Qeury results:</i></p>
	<!--
        <textarea rows="7" cols="60" name="result" id="val">
	</textarea>
	<p><span id="resultSpan"></span></p>
	-->
        <br />
	</div>


    <script type="text/javascript">
    //<![CDATA[


	var topLat = 42.0;
	var westLng = -120.0;
	var eastLng = -102.0;
	var bottomLat = 31.0;

	var centerLat = (topLat + bottomLat)/2.0;
	var centerLng = (westLng + eastLng)/2.0;

	var setLat = 38.570278;
	var setLon = -112.605400;   
	var setZoom = 11;   

	function ajaxFunction(lng, lat, zoom, kind)
	{
	  var xmlhttp;
	  if (window.XMLHttpRequest)
 	  {
	    // code for IE7+, Firefox, Chrome, Opera, Safari
	    xmlhttp=new XMLHttpRequest();
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

	  var url = "runQueryZ11.php";
	  url += "?lng="+lng;
	  url += "&lat="+lat;
	  url += "&zoom="+zoom;
	  url += "&kind="+kind;
	  xmlhttp.onreadystatechange = function()
	  {
	    if(xmlhttp.readyState==4)
	    {
		//document.getElementById("val").value = xmlhttp.responseText;
		document.getElementById("resultSpan").innerHTML = xmlhttp.responseText;
	    }
	  }
	  xmlhttp.open("GET", url, true);
	  xmlhttp.send(null);
	}

	function clearResult(frm) 
	{
		//document.getElementById("val").value = "";
		document.getElementById("resultSpan").innerHTML = "";
	}



	// kind == 0 : all species
	// kind -- 1 : Amphibians
	// kind -- 2 : Birds
	// kind -- 3 : Mammals
	// kind -- 4 : Reptiles
	function computeSpeciesExistence(frm, kind) {

	  clearResult(frm);

	  var lng = frm.Longitude.value;
	  var lat = frm.Latitude.value;
	  var zoom = frm.Zoom.value;
	  var sum = 0;
	
	/*
	  sum = parseFloat(lng) + parseFloat(lat);
	  frm.result.value = sum ;
	  frm.result.innerHTML = sum ;
	  document.getElementById("val").value = sum;
	*/
	// TEST AJAX : get sum value from SERVER.
	  ajaxFunction(lng, lat, zoom, kind);
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
		document.getElementById("frmZoom").value = setZoom;
	  
		var map = new GMap(document.getElementById("map"));
		map.addMapType(G_PHYSICAL_MAP);
		map.setMapType(G_PHYSICAL_MAP);
		
		var mt = map.getMapTypes();
		for(var i=0; i<mt.length ; i++) {
 			mt[i].getMinimumResolution = function () {return 3;}
 			mt[i].getMaximumResolution = function () {return 13;}
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
		var initZoom = map.getBoundsZoomLevel(bound);

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
