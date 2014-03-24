<!-- Original script taken from: http://www.gorissen.info/Pierre/maps/googleMapLocation.php -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
  $zoom_start = $_GET["zoom_start"];
  $zoom_end = $_GET["zoom_end"];
  $zoom_query = $_GET["zoom_query"];
  $rep = $_GET["rep"];
  $pickorviz = $_GET["pickorviz"];
?>

<html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
<HEAD>
  <style type="text/css">
  h1 {
    font-family:sans-serif; color:black; text-align: center; font-size:120%; }

  .tekst {
    font-family:sans-serif; color:green; font-size:100%;
  }

  .smalltekst {
    font-family:sans-serif; color:black; font-size:80%;
  }

  .coordInputTable {
    width:20px; 
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
</HEAD>

<BODY onload = "load_szline_data()">
  <TABLE>
    <INPUT type='hidden' name='Zoom' id="setZoom" value=""/>
    <TR> 
	<TD align="center">
	<H1><FONT COLOR='Teal'>Select Location, query for layers</FONT></H1>
	</TD>
	<TD>
	</TD>
    </TR>
    <TR>
	<TD colspan="2">
        <FONT COLOR='green'>Click on the map to mark a location and press "Find Object" for a list of ShoreZone layer element estimated to exist there.</FONT>
	</TD>
    </TR>
    <TR>
	<TD valign="top" rowspan="1">
	  <DIV id="map" style="width:800px; height:800px;"></DIV>
	</TD>
	<TD class="tekst" valign="top">
	<DIV id="geo">
		<FORM method="post" action="">
		<TABLE>
		<!--TABLE class="coordInputTable"-->
		<TR>
			<input type='hidden' name='Zoom' id="Zoom" value=""></td>
			<td> Latitude: <input type='text' name='Latitude' id="frmLat" style="width:165px;"></td>
		</TR>
		<TR>
			<td> Longitude: <input type='text' name='Longitude' id="frmLon" style="width:150px;"></td>
		</TR>
		<TR>
			<td><input type="button" name="Command" value="Find Object" style="width:120px;" onClick="computeExistence(this.form)"> <input type="button" name="Clear" value="Clear Result" style="width:120px;" onClick="clearResultAll()">
			</td>
		</TR>
		<TR>
			<TD rowspan="3">
	  		<p><i>Query results:</i></p>
	  		<div style="width: 3360px;" class="smalltekst" id="resultSpanPoly"> </div>
	  		<div style="width: 3360px;" class="smalltekst" id="resultSpanLine"></div>
	  		<div style="width: 3360px;" class="smalltekst" id="resultSpanPt"></div>
	  		<div style="width: 3360px;" class="smalltekst" id="resultSpanLnend"></div>
			</TD>
		</TR>
		</TABLE>
		</FORM>
	</DIV>
	</TD>
    </TR>
    <TR>
	<TD></TD>
    </TR>
    <TR>
	<TD valign="top">
	</TD>
    </TR>
    </TABLE>
</BODY>


<SCRIPT type="text/javascript" src="js/common.js"></SCRIPT>
<SCRIPT type="text/javascript">

	var topLat = 90.0;
	var westLng = -160.0;
	var eastLng = 160.0;
	var bottomLat = -50.0;

	//var centerLat = (topLat + bottomLat)/2.0;
	//var centerLng = (westLng + eastLng)/2.0;

	// Default location <West bound of I-70: junction to I-15>
	//var setLat = 38.570278;
	//var setLon = -112.605400;   

	// Default location <Olympic Mountain, WA>
	var setLat = 47.736306;
	var setLon = -123.4;//-122.361603;   
	var queryZoom = '<?php echo $zoom_query; ?>';   
	var initZoom = 8;   

	var centerLat = setLat;
	var centerLng = setLon;

	var map;
	var gMarkerArray = new Array();

	function computeExistence(form) 
	{
		var lng = form.Longitude.value;
		var lat = form.Latitude.value;

		drawNewMarker(lat, lng)

		var zoom_start = '<?php echo $zoom_start; ?>';
		var zoom_end = '<?php echo $zoom_end; ?>';
		var rep = '<?php echo $rep; ?>';
		var pickorviz = '<?php echo $pickorviz; ?>';

		var zoom = form.Zoom.value;
	
		// poly
		var shp_name = 'szpoly';
		var tbl_name_dst = 'szpoly_dst';
		var tbl_name_dbf = 'szpoly_dbf';
		var returnDivId = "resultSpanPoly";
		var id = "UNIT_ID";
		var meta_names = {"SHP_ID":"0","UNIT_ID":"3","DETH_Class":"7","GeoMapper":"9","Date":"11","BioMapper":"13","ShoreName":"35","BioUnit":"77","BioDesc":"78","A1":"87","B1":"91"}; 
		
		clearResult(returnDivId);
		ajaxFunction(returnDivId, shp_name, tbl_name_dst, tbl_name_dbf, 
			     zoom_start, zoom_end, lng, lat, zoom, rep, pickorviz,
			     id, meta_names);

		// line
		shp_name = 'szline';
		tbl_name_dst = 'szline_dst';
		tbl_name_dbf = 'szline_dbf';
		returnDivId = "resultSpanLine";
		id = "UNIT_ID";
		meta_names = {"SHP_ID":"0","UNIT_ID":"2","DETH_Class":"6","GeoMapper":"8","Date":"10","BioMapper":"12","ShoreName":"33","BioUnit":"76","BioDesc":"77","A1":"86","B1":"91"};

		clearResult(returnDivId);
		ajaxFunction(returnDivId, shp_name, tbl_name_dst, tbl_name_dbf, zoom_start, zoom_end, lng, lat, zoom, rep, pickorviz, id, meta_names);

		// pt
		shp_name = 'szpt';
		tbl_name_dst = 'szpt_dst';
		tbl_name_dbf = 'szpt_dbf';
		returnDivId = "resultSpanPt";
		id = "UNIT_ID";
		meta_names = {"SHP_ID":"0","UNIT_ID":"1","DETH_Class":"5","GeoMapper":"7","Date":"9","BioMapper":"11","ShoreName":"32","BioUnit":"58","BioDesc":"59","A1":"68","B1":"72"};
		clearResult(returnDivId);
		ajaxFunction(returnDivId, shp_name, tbl_name_dst, tbl_name_dbf, zoom_start, zoom_end, lng, lat, zoom, rep, pickorviz, id, meta_names);

		// lnend
		shp_name = 'szlnend';
		tbl_name_dst = 'szlnend_dst';
		tbl_name_dbf = 'szlnend_dbf';
		returnDivId = "resultSpanLnend";
		id = "SZLNEND_ID";
		meta_names = {"SHP_ID":"0","SZLNEND_ID":"1"};
		clearResult(returnDivId);
		ajaxFunction(returnDivId, shp_name, tbl_name_dst, tbl_name_dbf, zoom_start, zoom_end, lng, lat, zoom, rep, pickorviz, id, meta_names);
	}

	// argItems code taken from 
	// http://www.evolt.org/article/Javascript_to_Parse_URLs_in_the_Browser/17/14435/?format=print
	function argItems (theArgName) 
	{
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
	
	function drawNewMarker(lat, lng)
	{
		var gLatLng = new GLatLng(lat, lng);
		for(var i = 0 ; i < gMarkerArray.length ; i++)
		{
			var mkr = gMarkerArray[i];
			map.removeOverlay(mkr);
		}
		var message = "geotagged:\n\tLatitude=" + lat + "\n\tLongitude=" + lng + " "; 
		var messageRoboGEO = lat + ";" + lng + ""; 

		map.panTo(gLatLng);
		var markerOptions = { draggable: false, bouncy: false };
		var marker = new GMarker(gLatLng, markerOptions);

		gMarkerArray[gMarkerArray.length] = marker;
		map.addOverlay(marker);

		marker.openInfoWindowHtml(message);
		return true;
	}

	function drawPolyLine()
	{
		var polyline = new GPolyline([new GLatLng(48.994439, -122.760115), new GLatLng(48.989117, -122.773676)], "#00ff00", 2);
		map.addOverlay(polyline);
	}

	function placeMarker(setLat, setLon) 
	{
		var message = "geotagged geo:lat=" + setLat + " geo:lon=" + setLon + " "; 
		var messageRoboGEO = setLat + ";" + setLon + ""; 
	  
		document.getElementById("frmLat").value = setLat;
		document.getElementById("frmLon").value = setLon;
		document.getElementById("Zoom").value = queryZoom;
	  
		map = new GMap2(document.getElementById("map"));
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
		drawNewMarker(setLat, setLon);

		//line
		drawPolyLine();

		GEvent.addListener(map, 'click', function(overlay, point) 
		{
			if (overlay)  // click on bubble
			{
				map.removeOverlay(overlay);
			} 
			else if (point) 
			{
				var matchll = /\(([-.\d]*), ([-.\d]*)/.exec( point );
				if ( matchll ) 
				{ 
					var lat = parseFloat( matchll[1] );
					var lng = parseFloat( matchll[2] );
					lat = lat.toFixed(6);
					lng = lng.toFixed(6);
					var success = drawNewMarker(lat, lng);
					if(success)
					{
						document.getElementById("frmLat").value = lat;
						document.getElementById("frmLon").value = lng;
					} 
				}
				else 
				{ 
					var message = "<b>Error extracting info from</b>:" + point + ""; 
					var messagRoboGEO = message;
				}
			}
		});
	}


	if (argItems("lat") == '' || argItems("lon") == '') 
	{
		placeMarker(setLat, setLon);
	} 
	else 
	{
		var setLat = parseFloat( argItems("lat") );
		var setLon = parseFloat( argItems("lon") );
		setLat = setLat.toFixed(6);
		setLon = setLon.toFixed(6);
		placeMarker(setLat, setLon);
	}
 
	function load_szline_data()
	{
                var url = "run_load_geometry.php";
                url += "?ShapeName="+shp_name;
                url += "&TableNameDst="+table_name_dst;
                url += "&meta_names="+meta_names;

	}

</SCRIPT>


<!-- Start twatch code -->
<SCRIPT type="text/javascript">
document.write('<scr'+'ipt type="text/javascript" src="/Pierre/twatch/jslogger.php?ref='+( document["referrer"]==null?'':escape(document.referrer))+'&pg='+escape(window.location)+'&cparams=true"></scr'+'ipt>');
</SCRIPT>
<!-- End twatch code -->
</HTML>
