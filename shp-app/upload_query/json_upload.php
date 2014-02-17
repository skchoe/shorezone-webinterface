<?php
			
		require_once dirname(__FILE__)."/json/json.php";
		require_once dirname(__FILE__)."/upload_class.php";
		        	  
		$json = new Services_JSON();
    	  
    $uploaded = $GLOBALS['HTTP_RAW_POST_DATA'];
    $uploaded = $json->decode($uploaded);
		
		$i = 1;
		if ($handle = opendir('upload/')) 
		{
    	while (false !== ($file = readdir($handle))) 
    	{ 
    		if (is_file("upload/$file")) 
    		{ 
    			  	if($i == 1)
    			  	{
    			  		$mainfile = "File(s) uploaded: <br>";
    			  	}
        			$mainfile .= ($i<10)? "0".$i : $i;
        			$mainfile .= ". <a href='upload/$file'>".$file."</a><br>";
        			$i++;
    		} 
    	}
    	
    	closedir($handle); 
		}
		else
		{
			$mainfile = "empty";
		}
		
		$uploaded->filesName = $mainfile;
		$uploaded = $json->encode($uploaded);

    echo $uploaded;
        
?>
