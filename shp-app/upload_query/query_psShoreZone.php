<!-- Original script taken from: http://www.gorissen.info/Pierre/maps/googleMapLocation.php -->
<!--DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"-->
<!DOCTYPE HTML>
<?php
  $zoom_start = $_GET["zoom_start"];
  $zoom_end = $_GET["zoom_end"];
  $zoom_query = $_GET["zoom_query"];
  $rep = $_GET["rep"];
  $pickorviz = $_GET["pickorviz"];
?>

<HTML><!--xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml"-->
<HEAD>
  <!--META http-equiv="Content-Type" content="text/html; charset=utf-8"-->
  <META charset="utf-8">
  <META content="text/html">
  <META http-equiv="Content-Type">
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

  <!-- Google Map v3 -->
  <SCRIPT type="text/javascript" src="//maps.googleapis.com/maps/api/js?v=3&key=AIzaSyCDHLSbvam3dECsm9rcno6KIy6h9ynb98U&sensor=false"></SCRIPT>
  <TITLE>Query for Shape-layer with HTML5</TITLE>
</HEAD>

<BODY onload = "load_szline_data()">
  <TABLE>
    <TR><TD></TD><TD></TD></TR>
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
	<TD colspan="2"><DIV style="width: 3360px;" class="smalltekst" id="note"></DIV></TD>
    </TR>
    </TABLE>
</BODY>


<SCRIPT type="text/javascript" src="js/common.js"></SCRIPT>
<SCRIPT type="text/javascript">
	var topLat = 90.0;
	var westLng = -160.0;
	var eastLng = 160.0;
	var bottomLat = -50.0;

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

		for(var i = 0 ; i < gMarkerArray.length ; i++)
		{
			var mkr = gMarkerArray[i];
			mkr.setMap(null);
		}

		var marker = new google.maps.Marker({
			position: gLatLng, 
			map: map,
			draggable: false,
			bouncy: false});

		gMarkerArray[gMarkerArray.length] = marker;
		marker.setMap(map);

		var messageRoboGEO = "<TABLE><TR><TD align=\"center\" colspan=\"2\"><b>Location</b></TD></TR><TR><TD align=\"right\">Latitude:</TD><TD align=\"right\">"+lat+"</TD></TR><TR><TD align=\"right\">Longitude:</TD><TD align=\"right\">"+lng+"</TD></TR></TABLE>";
		var infoWindow = new google.maps.InfoWindow({
				content: messageRoboGEO
			});
		infoWindow.open(map, marker);

		return true;
	}

	function drawPolyLine()
	{
	}

	function placeMarker(setLat, setLng) 
	{
		var message = "geotagged geo:lat=" + setLat + " geo:lon=" + setLng + " "; 
		var messageRoboGEO = setLat + ";" + setLng + ""; 
	  
		document.getElementById("frmLat").value = setLat;
		document.getElementById("frmLon").value = setLng;
		document.getElementById("Zoom").value = queryZoom;
	  
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

		map = new google.maps.Map(document.getElementById("map"), mapOptions);


		var threshold = 2.0;
		var min_pos = new google.maps.LatLng(westLng-threshold, bottomLat-threshold, true);
		var max_pos = new google.maps.LatLng(eastLng+threshold, topLat+threshold, true);
		var bound = new google.maps.LatLngBounds(min_pos, max_pos);

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
						});
	}

	function load_szline_data()
	{
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
