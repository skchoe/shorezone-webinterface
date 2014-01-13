<?php
   // include upload class
   require_once "upload_class.php";
	 

   $upload_class  = new Upload_Files;
   $upload_class -> temp_file_name    = trim($_FILES['userfile']['tmp_name']);
   $upload_class -> file_name         = trim(strtolower($_FILES['userfile']['name']));
   // change your upload directory if needed
   $upload_class -> upload_dir        = getcwd()."/upload/";
   // change your upload logs directory if needed
   $upload_class -> upload_log_dir    = getcwd()."/upload/upload_logs/";
   // maximum size is 5MB, consult your php.ini for maximum file size
   // updated 5MB * 1000
   $upload_class -> max_file_size     = 5242880000;
   // banned user array
   $upload_class -> banned_array      = array("");
   // permitted file extension array
   //$upload_class -> ext_array         = array(".doc",".jpeg",".jpg",".bmp",".png",".gif",".dbf",".shp");
   $upload_class -> ext_array         = array(".dbf",".shp");
 
 	 // validate extension
   $valid_ext     = $upload_class->validate_extension();
   // validate size
   $valid_size    = $upload_class->validate_size();
   // validate user
   $valid_user    = $upload_class->validate_user();
   // get maximum file size
   $max_size      = $upload_class->get_max_size();
   // get file size
   $file_size     = $upload_class->get_file_size();
   // check if the file already exists
   $file_exists   = $upload_class->existing_file();

	 // return path
	 $extra = "<a href='JavaScript:history.go(-1);'>Back</a>";
	 
	 // start checking
   if (!$valid_ext) 
   {
       $result = "The file extension is invalid, please try again! ".$extra;
   }
   elseif (!$valid_size) 
   {
       $result = "The file size is invalid, please try again! The maximum file size is: $max_size and your file was: $file_size ".$extra;
   }
   elseif (!$valid_user) 
   {
       $result = "You have been banned from uploading to this server. ".$extra;
   }
   elseif ($file_exists) 
   {
       $result = "This file already exists on the server, please try again. ".$extra;
   } 
   else 
   {
   	
       $upload_file = $upload_class->upload_file_with_validation();
       if (!$upload_file) 
       {
           $result = "Your file could not be uploaded! ".$extra;
       } 
       else 
       {
           $result = "Your file has been successfully uploaded to the server. ".$extra;
       }
   } 
   // end of checking
   
 
	 echo $result;
?>
