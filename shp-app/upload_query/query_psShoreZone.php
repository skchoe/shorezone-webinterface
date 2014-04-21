<!-- Original script taken from: http://www.gorissen.info/Pierre/maps/googleMapLocation.php -->
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<?php
  $zoom_start = $_GET["zoom_start"];
  $zoom_end = $_GET["zoom_end"];
  $zoom_query = $_GET["zoom_query"];
  $rep = $_GET["rep"];
  $pickorviz = $_GET["pickorviz"];
?>

<HTML xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml">
<HEAD>
  <META http-equiv="Content-Type" content="text/html; charset=utf-8">
  <STYLE type="text/css">
  h1 {
    font-family:sans-serif; color:black; text-align: center; font-size:120%; }

  .tekst {
    font-family:sans-serif; color:green; font-size:100%;
  }

  .smalltekst {
    font-family:sans-serif; color:black; font-size:80%;
  }
  .labels {
	width:110px;
	text-align:right; 
	float:left;
	margin-right:10px;
  }
  </STYLE>

  <STYLE type="text/css">
    v\:* {
	behavior:url(#default#VML);
    }
  </STYLE>
  <!--style type="text/css"> 
	input{ 
	text-align:right; 
	} 
  </style-->

  <!-- Google Map v2 -->
  <!--script src="http://maps.google.com/maps?file=api&v=2&key=AIzaSyCDHLSbvam3dECsm9rcno6KIy6h9ynb98U"type="text/javascript"></script-->
  <!-- Google Map v3 -->
  <SCRIPT type="text/javascript" src="//maps.googleapis.com/maps/api/js?v=3&key=AIzaSyCDHLSbvam3dECsm9rcno6KIy6h9ynb98U&sensor=false"></SCRIPT>
  <TITLE>Query for Shape-layer</TITLE>
</HEAD>

<BODY onload = "load_szline_data()">
  <TABLE>
    <TR><TD></TD><TD></TD></TR>
    <!--INPUT type='hidden' name='Zoom' id="setZoom" value=""/-->
    <TR> 
	<TD align="left">
		<H1><FONT COLOR='Teal'>Select Location, query for layers</FONT></H1>
	</TD>
	<TD></TD>
    </TR>
    <TR>
	<TD aligh="left" colspan="2">
        	<FONT COLOR='green'>Click on the map to mark a location and press "Find Object" for a list of ShoreZone layer element estimated to exist there.</FONT>
	</TD>
    </TR>
    <TR>
	<TD valign="top" rowspan="2">
	  <DIV id="map" style="width:800px; height:700px;"></DIV>
	</TD>
	<TD class="tekst" valign="top">
	  <DIV id="geo">
		<FORM method="post" action="">
		<TABLE>
		<TR>
			<input type='hidden' name='Zoom' id="Zoom" value="">
			<TD><DIV class=labels><LABEL for="Latitude">Latitude:</LABEL></DIV>
			    <DIV class=inputs><INPUT type='text' name='Latitude' id="frmLat" style="width:120px; text-align:right"></DIV>
			    <DIV class=labels><LABEL for="Longitude">Longitude:</LABEL></DIV>
			    <DIV class=inputs><INPUT type='text' name='Longitude' id="frmLon" style="width:120px; text-align:right"></DIV>
			</TD>
		</TR>
		<TR>
			<TD><input type="button" name="Command" value="Find Object" style="width:120px;" 
				onClick="computeExistence(this.form)"> 
				<input type="button" name="Clear" value="Clear Result" style="width:120px;" 
				onClick="clearResultAll()">
			</TD>
		</TR>
		<TR>
			<TD rowspan="3">
	  		<p><i>Query results:</i></p>
	  		<DIV style="width: 3360px;" class="smalltekst" id="resultSpanPoly"> </DIV>
	  		<DIV style="width: 3360px;" class="smalltekst" id="resultSpanLine"></DIV>
	  		<DIV style="width: 3360px;" class="smalltekst" id="resultSpanPt"></DIV>
	  		<DIV style="width: 3360px;" class="smalltekst" id="resultSpanLnend"></DIV>
			</TD>
		</TR>
		</TABLE>
		</FORM>
	  </DIV>
	</TD>
    </TR>
    <TR>
	<TD class="tekst"><INPUT type="checkbox" name="viewGeometryPolyLine" id="viewGeometryPolyLine">&nbsp;View Poly Lines
	</TD>
    </TR>
    <TR>
	<TD colspan="2"><DIV style="width: 3360px;" class="smalltekst" id="note"></DIV></TD>
    </TR>
    </TABLE>
</BODY>


<SCRIPT type="text/javascript" src="js/common.js"></SCRIPT>
<SCRIPT type="text/javascript">
/*
Use : new google.maps. in place of G...

GLatLngBounds() --> google.maps.LatLngBounds()
GlatLng --> google.maps.LatLng
GPoint --> google.maps.Point
Event.addListener --> google.maps.event.addListener
map.getInfoWindow().getPoint --> google.maps.getPosition()
markers[i].getPoint() --> markers[i].getPosition()
closeInfoWindow() --> map.InforWindow.Close();
map.getBoundsZoomLevel(bounds) --> map.fitBounds(bounds)
markers[i].setImage --> .setIcon
map.InfoWindow.close() --> create a function to close
find in maps for objects --> $('#id')[0] or $('#id').get(0) or document.getElementbyId

http://www.mywebexperiences.com/2013/03/05/migrate-google-maps-from-v2-to-v3/
http://www.absoluteweb.net/google-map-api-v3/
*/
	var topLat = 90.0;
	var westLng = -160.0;
	var eastLng = 160.0;
	var bottomLat = -50.0;

	//var centerLat = (topLat + bottomLat)/2.0;
	//var centerLng = (westLng + eastLng)/2.0;

	// Default location <West bound of I-70: junction to I-15>
	//var setLat = 38.570278;
	//var setLng = -112.605400;   

	// Default location <Olympic Mountain, WA>
	var setLat = 47.736306;
	var setLng = -123.4;//-122.361603;   
	var queryZoom = '<?php echo $zoom_query; ?>';   
	var initZoom = 8;   

	var centerLat = setLat;
	var centerLng = setLng;

	var map;
	var gMarkerArray = new Array();

	function computeExistence(form) 
	{
		var lng = form.Longitude.value;
		var lat = form.Latitude.value;

		drawNewMarker(new google.maps.LatLng(lat, lng));

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
	
	function drawNewMarker(gLatLng)
	{
		map.panTo(gLatLng);

		var lat = gLatLng.lat();
		var lng = gLatLng.lng();	
		lat = lat.toFixed(6);
		lng = lng.toFixed(6);
		//var gLatLng = new GLatLng(lat, lng);

		for(var i = 0 ; i < gMarkerArray.length ; i++)
		{
			var mkr = gMarkerArray[i];
			//map.removeOverlay(mkr);
			mkr.setMap(null);
		}

		//var markerOptions = { draggable: false, bouncy: false };
		//var marker = new GMarker(gLatLng, markerOptions);
		var marker = new google.maps.Marker({
			position: gLatLng, 
			map: map,
			draggable: false,
			bouncy: false});
		//	markerOptions);

		gMarkerArray[gMarkerArray.length] = marker;
		//map.addOverlay(marker);
		marker.setMap(map);

		var messageRoboGEO = "<TABLE><TR><TD align=\"center\" colspan=\"2\"><b>Location</b></TD></TR><TR><TD align=\"right\">Latitude:</TD><TD align=\"right\">"+lat+"</TD></TR><TR><TD align=\"right\">Longitude:</TD><TD align=\"right\">"+lng+"</TD></TR></TABLE>";
		var infoWindow = new google.maps.InfoWindow({
				content: messageRoboGEO
			});
		infoWindow.open(map, marker);

		//marker.openInfoWindowHtml(message);
		return true;
	}

	function drawPolyLine()
	{
/*
		//var polyLine = new GPolyline([new GLatLng(48.994439, -122.760115), new GLatLng(48.989117, -122.773676)], "#00ff00", 2);
		var polyLine = new google.maps.Polyline([new google.maps.LatLng(48.994439, -122.760115), new google.maps.LatLng(48.989117, -122.773676)], "#00ff00", 2);
		//map.addOverlay(polyLine);
		polyLine.setMap(map);
*/
	}

	function placeMarker(setLat, setLng) 
	{
		var message = "geotagged geo:lat=" + setLat + " geo:lon=" + setLng + " "; 
		var messageRoboGEO = setLat + ";" + setLng + ""; 
	  
		document.getElementById("frmLat").value = setLat;
		document.getElementById("frmLon").value = setLng;
		document.getElementById("Zoom").value = queryZoom;
	  
		//var point = new GLatLng(centerLat, centerLng);
		var point = new google.maps.LatLng(centerLat, centerLng);
		var zoomStart = <?php echo $zoom_start ?>;
		var zoomEnd = <?php echo $zoom_end ?>;

		var mapOptions = {
			center: point,
			zoomControl: true,
			panControl: true,
			rotateControl: true,
			scaleControl: true,
			scrollwheel: true,
			mapTypeControl: true,
			overviewMapControl: true,
			overviewMpaControlOptions: {opened:true},
			draggableCursor: 'crosshair',
			//streetViewControl: true,
			zoom : initZoom,
			minZoom :zoomStart,
			maxZoom :zoomEnd,
			mapTypeControlOptions: 	{ 
				mapTypeIds: [	google.maps.MapTypeId.ROADMAP, 
						google.maps.MapTypeId.TERRAIN, 
						google.maps.MapTypeId.SATELLITE, 
						google.maps.MapTypeId.HYBRID],
				style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR},
			mapTypeId: google.maps.MapTypeId.TERRAIN };

		//map = new GMap2(document.getElementById("map"));
		map = new google.maps.Map(document.getElementById("map"), mapOptions);

//google.maps.MapTypeId.TERRAIN, google.maps.MapTypeId.SATELLITE, google.maps.MapTypeId.HYBRID
		//map.addMapType(G_PHYSICAL_MAP);
		//map.setMapType(G_PHYSICAL_MAP);
		
		//var mt = map.getMapTypes();
		//for(var i=0; i<mt.length ; i++) {
 		//	mt[i].getMinimumResolution = function () {return <?php echo $zoom_start ?>;}
 		//	mt[i].getMaximumResolution = function () {return <?php echo $zoom_end ?>;}
		//}

		//map.addControl(new GLargeMapControl()); // added
		//map.addControl(new GMapTypeControl()); // added

		//overview
		//map.addControl(new GOverviewMapControl(null));

		//scale control
		//map.addControl(new GScaleControl(), new GControlPosition(G_ANCHOR_BOTTOM_LEFT, new GSize(80, 3)));
		//map.addControl(new google.maps.ScaleControl(), new google.maps.ControlPosition(G_ANCHOR_BOTTOM_LEFT, new google.maps.Size(80, 3)));

		var threshold = 2.0;
		//var min_pos = new GLatLng(westLng-threshold, bottomLat-threshold, true);
		var min_pos = new google.maps.LatLng(westLng-threshold, bottomLat-threshold, true);
		//var max_pos = new GLatLng(eastLng+threshold, topLat+threshold, true);
		var max_pos = new google.maps.LatLng(eastLng+threshold, topLat+threshold, true);
		//var bound = new GLatLngBounds(min_pos, max_pos);
		var bound = new google.maps.LatLngBounds(min_pos, max_pos);

 		//map.setCenter(point, initZoom); v2

		// Drawing initial marker when loading the page
		//var gLatLng = new google.maps.LatLng(setLat, setLng);
		//drawNewMarker(gLatLng);

		//line
		drawPolyLine();

		google.maps.event.addListener(	map, 
						'click', 
						function(event) 
						{ 
							var gLatLng = event.latLng;
							var success = drawNewMarker(gLatLng);
							if(success)
							{
								document.getElementById("frmLat").value = gLatLng.lat().toFixed(6);
								document.getElementById("frmLon").value = gLatLng.lng().toFixed(6);
							}
						});
		
		google.maps.event.addListener(	map,
						'zoom_changed',
						function()
						{
						});

		
		google.maps.event.addListener(	map,
						'dragend',
						function(event)
						{
							ajaxShowNote(map, "szline_dst", "note");
						});
/*

		GEvent.addListener(map, 'click', function(overlay, point) 
		{
			if (overlay)  // click on bubble
			{
				//map.removeOverlay(overlay);
				overlay.setMap(null);
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
					alert(messageRoboGEO);
				}
			}
		});
*/
	}

	function load_szline_data()
	{
		alert("Welcome!");
/*
                var url = "ajax_loadgeometry.php";
                url += "?ShapeName="+shp_name;
                url += "&TableNameDst="+table_name_dst;
                url += "&meta_names="+meta_names;
*/
	}


	if (argItems("lat") == '' || argItems("lon") == '') 
	{
		setLat = setLat.toFixed(6);
		setLng = setLng.toFixed(6);
		placeMarker(setLat, setLng);
	} 
	else 
	{
		var y = parseFloat( argItems("lat") );
		var x = parseFloat( argItems("lon") );
		setLat = y.toFixed(6);
		setLng = x.toFixed(6);
		placeMarker(setLat, setLng);
	}
 
</SCRIPT>


<!-- Start twatch code -->
<SCRIPT type="text/javascript">
document.write('<scr'+'ipt type="text/javascript" src="/Pierre/twatch/jslogger.php?ref='+( document["referrer"]==null?'':escape(document.referrer))+'&pg='+escape(window.location)+'&cparams=true"></scr'+'ipt>');
</SCRIPT>
<!-- End twatch code -->
</HTML>
