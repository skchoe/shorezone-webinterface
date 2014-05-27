	function createURL(shp_name, table_name_dst, table_name_dbf, zoom_start, zoom_end, lng, lat, zoom, rep, pickorviz, id, meta_names)
	{
		var json_meta_names = JSON.stringify(meta_names);
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
		url += "&id="+id;
		url += "&meta_names="+json_meta_names;
		//alert(url);

		return url;	
	}

	function getXMLHttp()
	{
		var xmlhttp;
		if (window.XMLHttpRequest)
 		{
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
		return xmlhttp;
	}

	function ajaxFunction(returnDivId, shp_name, table_name_dst, table_name_dbf, zoom_start, zoom_end, lng, lat, zoom, rep, pickorviz, id, meta_names)
	{
		var xmlhttp = getXMLHttp();

		var url = createURL(shp_name, table_name_dst, table_name_dbf, zoom_start, zoom_end, lng, lat, zoom, rep, pickorviz, id, meta_names)
		xmlhttp.onreadystatechange = function() {
			//document.getElementById(returnDivId).write("onreadystatechange");
			if(xmlhttp.readyState==4) {
				document.getElementById(returnDivId).innerHTML = xmlhttp.responseText;
			}
		}
		xmlhttp.open("GET", url, true);
		xmlhttp.send(null);
	}

	function clearResultAll()
	{
		var returnDivId = "resultSpanPoly";
		clearResult(returnDivId);
		returnDivId = "resultSpanLine";
		clearResult(returnDivId);
		returnDivId = "resultSpanPt";
		clearResult(returnDivId);
		returnDivId = "resultSpanLnend";
		clearResult(returnDivId);
	}

	function clearResult(returnDivId) 
	{
		var div = document.getElementById(returnDivId).innerHTML = "";
	}

	function ajaxShowNote(map, shp_tbl, returnDivId)
	{
		var gLatLngBounds = map.getBounds();
		var sw = gLatLngBounds.getSouthWest();
		var ne = gLatLngBounds.getNorthEast();
		var url = "ajax_loadgeometry.php?TableNameDst=" + shp_tbl + "&sw="+ sw.toUrlValue() + "&ne=" + ne.toUrlValue();

		var xmlhttp = getXMLHttp();

		xmlhttp.onreadystatechange = function() {
			if(xmlhttp.readyState==4) {
				document.getElementById(returnDivId).innerHTML = xmlhttp.responseText;
				//document.getElementById(returnDivId).innerHTML = "<b>test</b>";
			}
		}
		xmlhttp.open("GET", url, true);
		// Fire ajax call to get geometries from storage
		xmlhttp.send(null);
	}
/*
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
		drawNewMarker(setLat, setLon)

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
*/
