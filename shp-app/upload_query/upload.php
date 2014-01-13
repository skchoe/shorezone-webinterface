<html>
	<head>
		<title>Upload</title>
	  <link rel="stylesheet" href="upload.css" type="text/css">
	  <script language="JavaScript" src="json/json.js"></script>
	  <script language="JavaScript">
	  	
	  	var httpSubmit   = getHTTPObject();
			
			/*
			* Sends a request to the server
			* @param none
			* @return none
			*/
      function getFileInfo()
      {

         var uploaded = new Uploaded();
				 uploaded.filesName = '';
         var json_text = uploaded.toJSONString();

         httpSubmit.open("POST", "json_upload.php" , true);           
         httpSubmit.onreadystatechange = handleHttpSubmitResponse; 
         httpSubmit.send(json_text);

      }

      function Uploaded()
      {
      	this.filesName  = "";
      }            

      /*
      * Handles response sent from the server
      * @param none
      * @return none
      */

      function handleHttpSubmitResponse()
      {
         if (httpSubmit.readyState == 4)
         { 
             var uploaded = httpSubmit.responseText.parseJSON();
             
             if(uploaded.filesName == undefined)
             {
             		document.getElementById("showFile").innerHTML  =  "File not uploaded!";
             }
             else
             {
      			 		document.getElementById("showFile").innerHTML  =  uploaded.filesName;
      			 }

      			 document.getElementById("showFile").style.display = "block";
         }
      }

      function getHTTPObject() 
      {
         var xmlhttp;

         if (!xmlhttp) 
         {
            if(window.XMLHttpRequest) 
            {
               try 
               {
                  xmlhttp = new XMLHttpRequest();
               }
               catch(e) 
               {
                  xmlhttp = false;
               }

            }
            else if(window.ActiveXObject) // branch for IE/Windows ActiveX version
            {
               try 
               {
                  xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
               }
               catch(e)
               {
                  try
                  {
                     xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
                  }
                  catch(e)
                  {
                     xmlhttp = false;
                  }
               }
            }
            return xmlhttp;
         }
      }
      
	    window.onload = function()
	    {
	    	setInterval("getFileInfo()",1000);
	    }
      
	  </script>
	</head>
<body>
<iframe src="upload.html" style="border-width:1; border-style:solid; border-color:000000; width:300; height:70;">
</iframe><br>
<div class="style1" id="showFile" name="showFile" style="display: none; padding:8px 8px 8px 8px;"></div>
</body>
</html>