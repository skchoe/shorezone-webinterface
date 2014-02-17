<?php

require_once dirname(__FILE__)."/../mysql_utils.php"; 

class Upload_Files {

    var $temp_file_name_shp;
    var $file_name_shp;
    var $temp_file_name_dbf;
    var $file_name_dbf;
    var $upload_dir;
    var $upload_log_dir;
    var $max_file_size;
    var $banned_array;
    var $ext_array;
    
    /*
    * purpose: To validate permitted extensions
    * @param none
    * @reutrn boolean
    */
    
    function echoElements()
    {
      echo "Geometry(SHP): $this->file_name_shp - Upload complete</br>";
      echo "Meta data(DBF): $this->file_name_dbf - Upload complete</br>";
    }

    function checkFilenameMatch(){
      
      $seq_shp = explode(".", $this->file_name_shp);
      $seq_dbf = explode(".", $this->file_name_dbf);
      //print_r($seq_shp);
      //echo "</br> $this->file_name_shp </br>";
      //print_r($seq_dbf);
      //echo "</br> $this->file_name_dbf </br>";
      $shp_hdr = $seq_shp[0];
      $dbf_hdr = $seq_dbf[0];

      //echo "headers : $shp_hdr, $dbf_hdr </br>";
      if($shp_hdr == $dbf_hdr) echo "Dataname Test has passed! with header name: $shp_hdr</br>";
      else {
        echo "Dataname from geometry and metadata mismatch .. exit() </br>";
        exit;
      }
    }

    function validate_a_extension($file_name, $ext_array) 
    {
      $file_name = trim($file_name);
      $extension = strtolower(strrchr($file_name,"."));
      $ext_array = $ext_array;
      $ext_count = count($ext_array);
       
      if (!$file_name) { return false; } 
      else 
      {
         if (!$ext_array) { return true; } 
         else 
         {
             foreach ($ext_array as $value) 
             {
                 $first_char = substr($value,0,1);
                 if ($first_char <> ".") { $extensions[] = ".".strtolower($value); } 
                 else { $extensions[] = strtolower($value); }
             }
     
             foreach ($extensions as $value) 
             {
               if ($value == $extension) { 
                 echo "* Extension Check : $value </br>";
                 $valid_extension = "TRUE"; 
               }                
             }

             if ($valid_extension) { return true; } 
             else { return false; }
         }
      }
    }

    function validate_extension() 
    {
      $bshp = $this->validate_a_extension($this->file_name_shp, $this->ext_array);
      $bdbf = $this->validate_a_extension($this->file_name_dbf, $this->ext_array);
      $b = $bshp && $bdbf;
      return $b;
		}
		
		/*
		* purpose: To validate file size
		* @param none
		* @return boolean
		*/
    function validate_a_filesize($temp_file_name)
    {
       $temp_file_name = trim($temp_file_name);
       $max_file_size  = trim($this->max_file_size);

       $e = empty($temp_file_name);
       
       if (!empty($temp_file_name)) 
       {
         $size = filesize($temp_file_name);
         echo "* Size Check  - tmp_filename: $temp_file_name,  for  $size < $max_file_size</br>";
         if ($size > $max_file_size) return false;                                                        
         else return true;
       } 
       else return false;
    }
		
    function validate_size() 
    {
      $bsz_shp = $this->validate_a_filesize($this->temp_file_name_shp);
      $bsz_dbf = $this->validate_a_filesize($this->temp_file_name_dbf);

      $b = $bsz_shp && $bsz_dbf;
      return $b;
    }
    
    /*
		* purpose: Check if the file already exists or not
		* @param none
		* @return boolean
		*/
    
    function existing_a_file($file_name, $upload_dir) {
       $file_name  = trim($file_name);
       
       if ($upload_dir == "ERROR") { return true; } 
       else 
       {
           $file = $upload_dir . $file_name;
           if (file_exists($file)) { return true; } 
           else { return false; }
       }    
    }

    function existing_file() {
      $bshp = $this->existing_a_file($this->file_name_shp, $this->get_upload_directory());
      $bdbf = $this->existing_a_file($this->file_name_dbf, $this->get_upload_directory());

      echo "* File pre-existence check: $this->file_name_shp - Exist? : $bshp </br>";
      echo "* File pre-existence check: $this->file_name_dbf - Exist? : $bdbf </br>";
      $b = $bshp && $bdbf;
      return $b;
    }
    
    /*
     * purpose: Gets the original file size
     * @param none
     * @return file size
     */
    function get_file_size_shp()
    {
      $this->get_file_size($this->temp_file_name_shp);
    }
    
    function get_file_size_dbf()
    {
      $this->get_file_size($this->temp_file_name_dbf);
    }

    function get_file_size($file_name) {
       $temp_file_name = trim($file_name);
       $kb = 1024;
       $mb = 1024 * $kb;
       $gb = 1024 * $mb;
       $tb = 1024 * $gb;
       
       if ($temp_file_name) 
       {
          $size = filesize($temp_file_name);
          if ($size < $kb) 
          {
             $file_size = "$size Bytes";
          }
          elseif ($size < $mb) 
          {
             $final = round($size/$kb,2);
             $file_size = "$final KB";
          }
          elseif ($size < $gb) 
          {
             $final = round($size/$mb,2);
             $file_size = "$final MB";
          }
          elseif($size < $tb) 
          {
             $final = round($size/$gb,2);
             $file_size = "$final GB";
          } 
          else 
          {
             $final = round($size/$tb,2);
             $file_size = "$final TB";
          }
       } 
       else 
       {
           $file_size = "ERROR: NO FILE PASSED TO get_file_size()";
       }
       return $file_size;
    }
    
    /*
		* purpose: Gets the maximum file size allowed by the script
		* @param none
		* @return maximum file size
		*/
    
    function get_max_size() {
       $max_file_size = trim($this->max_file_size);
       $kb = 1024;
       $mb = 1024 * $kb;
       $gb = 1024 * $mb;
       $tb = 1024 * $gb;
       
       if ($max_file_size) 
       {
          if ($max_file_size < $kb) 
          {
             $max_file_size = "max_file_size Bytes";
          }
          elseif ($max_file_size < $mb) 
          {
             $final = round($max_file_size/$kb,2);
             $max_file_size = "$final KB";
          }
          elseif ($max_file_size < $gb) 
          {
             $final = round($max_file_size/$mb,2);
             $max_file_size = "$final MB";
          }
          elseif($max_file_size < $tb) 
          {
             $final = round($max_file_size/$gb,2);
             $max_file_size = "$final GB";
          } 
          else 
          {
             $final = round($max_file_size/$tb,2);
             $max_file_size = "$final TB";
          }
       } 
       else 
       {
           $max_file_size = "ERROR: NO SIZE PARAMETER PASSED TO  get_max_size()";
       }
       return $max_file_size;
    }
    
    /*
		* purpose: Check if the user is banned or nor
		* @param none
		* @return boolean
		*/
    
    function validate_user() {
       $banned_array = $this->banned_array;
       $ip           = trim($_SERVER['REMOTE_ADDR']);
       $cpu          = gethostbyaddr($ip);
       $count        = count($banned_array);

       echo "* Access Check: Accessed host: $cpu with IP: $ip</br>";
       
       if ($count < 1) { return true; } 
       else 
       {
           foreach($banned_array as $key => $value) 
           {
               if ($value == $ip ."-". $cpu) { return false; } 
               else { return true;}
           }
       }
    }
    
    /*
		* purpose: Gets the upload directory
		* @param none
		* @return upload directory
		*/
    
    function get_upload_directory() {
       $upload_dir = trim($this->upload_dir);

       //echo "IN _get_upload_directory: w/ upload_dir: $upload_dir </br>";

       if ($upload_dir) 
       {
          $ud_len     = strlen($upload_dir);
          $last_slash = substr($upload_dir,$ud_len-1,1);
       
          if ($last_slash <> "/") 
          {
             $upload_dir = $upload_dir."/";
          } 
          else 
          {
             $upload_dir = $upload_dir;
          }
       
          $handle = @opendir($upload_dir);


           //echo "uploaddir == not false: $upload_dir</br>";
          if ($handle) 
          {
             $upload_dir = $upload_dir;
             closedir($handle);
          } 
          else 
          {
             $upload_dir = "ERROR";
          }
       } 
       else 
       {
           //echo "uploaddir == FALSE</br>";
           $upload_dir = "ERROR";
       }
       return $upload_dir;
    } 
    
    /*
		* purpose: Gets the upload logs directory
		* @param none
		* @return upload log directory
		*/
    
    function get_upload_log_directory() {
       $upload_log_dir = trim($this->upload_log_dir);
       if ($upload_log_dir) 
       {
           $ud_len     = strlen($upload_log_dir);
           $last_slash = substr($upload_log_dir, $ud_len-1, 1);
           if ($last_slash <> "/") 
           {
              $upload_log_dir = $upload_log_dir."/";
           } 
           else 
           {
              $upload_log_dir = $upload_log_dir;
           }
           $handle = @opendir($upload_log_dir);
           if ($handle) 
           {
              $upload_log_dir = $upload_log_dir;
              closedir($handle);
           } 
           else 
           {
              $upload_log_dir = "ERROR";
           }
       } 
       else 
       {
           $upload_log_dir = "ERROR";
       }
       return $upload_log_dir;
    }
    
    /*
		* purpose: Upload a file without validation
		* @param none
		* @return boolean
		*/
    
    function upload_file_no_validation() 
    {
       $temp_file_name_shp = trim($this->temp_file_name_shp);
       $file_name_shp      = trim(strtolower($this->file_name_shp));
       $temp_file_name_dbf = trim($this->temp_file_name_dbf);
       $file_name_dbf      = trim(strtolower($this->file_name_dbf));
       $upload_dir     = $this->get_upload_directory();
       $upload_log_dir = $this->get_upload_log_directory();
       $file_size_shp      = $this->get_file_size_shp();
       $file_size_dbf      = $this->get_file_size_dbf();
       $ip             = trim($_SERVER['REMOTE_ADDR']);
       $cpu            = gethostbyaddr($ip);
       $m              = date("m");
       $d              = date("d");
       $y              = date("Y");
       $date           = date("m/d/Y");
       $time           = date("h:i:s A");


       if (($upload_dir == "ERROR") OR ($upload_log_dir == "ERROR")) 
       { return false; } 
       else 
       {
         $bchk_shp = check_upload($temp_file_name_shp, $file_name_shp, $file_size_shp, $upload_dir, $upload_log_dir, $ip, $y, $m, $d, $date, $time);
         $bchk_dbf = check_upload($temp_file_name_dbf, $file_name_dbf, $file_size_dbf, $upload_dir, $upload_log_dir, $ip, $y, $m, $d, $date, $time);
         $b = $bchk_shp && $bchk_dbf;
         return $b;
       }
    }

    function check_upload($temp_file_name, $file_name, $file_size, $upload_dir, $upload_log_dir, $ip, $y, $m, $d, $date, $time)
    {
        if (is_uploaded_file($temp_file_name)) 
        {
          if (move_uploaded_file($temp_file_name, $upload_dir . $file_name)) 
            {
                 echo "---------true------</br>";
                 $log = $upload_log_dir.$y."_".$m."_".$d.".txt";
                 $fp  = fopen($log,"a+");
                 fwrite($fp,"$ip-$cpu | $file_name | $file_size | $date | $time");
                 fclose($fp);
                 return true;
             } 
             else { echo "-----------------------false     ---------</br>";
                   return false;    }
         } 
         else { return false; }
    }

  function my_move_to_uploaded_file($temp_file_name, $file_name, $file_size, $upload_dir, $upload_log_dir, $y, $m, $d, $date, $time)
  {
      if (is_uploaded_file($temp_file_name)) 
      {
        if (move_uploaded_file($temp_file_name, $upload_dir . $file_name)) 
        {
          $log = $upload_log_dir.$y."_".$m."_".$d.".txt";
          $fp  = fopen($log,"a+");
          fwrite($fp,"$ip-$cpu | $file_name | $file_size | $date | $time\n");
          fclose($fp);
          return true;
        } 
        else { return false; }
      } 
      else { return false; }
    }


    /*
		* purpose: Upload a file with validation
		* @param none
		* @return boolean
		*/
    
    function upload_file_with_validation() {

       date_default_timezone_set('America/Denver');
       $temp_file_name_shp = trim($this->temp_file_name_shp);
       $file_name_shp      = trim(strtolower($this->file_name_shp));
       $temp_file_name_dbf = trim($this->temp_file_name_dbf);
       $file_name_dbf      = trim(strtolower($this->file_name_dbf));
       $upload_dir     = $this->get_upload_directory();
       $upload_log_dir = $this->get_upload_log_directory();
       $file_size_shp      = $this->get_file_size_shp();
       $file_size_dbf      = $this->get_file_size_dbf();
       $ip             = trim($_SERVER['REMOTE_ADDR']);
       $cpu            = gethostbyaddr($ip);
       $m              = date("m");
       $d              = date("d");
       $y              = date("Y");
       $date           = date("m/d/Y");
       $time           = date("h:i:s A");
       $existing_file  = $this->existing_file();        
       $valid_user     = $this->validate_user();        
       $valid_size     = $this->validate_size();        
       $valid_ext      = $this->validate_extension();   

       
       echo "_______________cwd_____________________:".getcwd()."</br>";
       echo "_______________uploaddir_______________:".$upload_dir."</br>";
       echo "_______________filename_shp________________:".$file_name_shp."</br>";
       echo "_______________filename_dbf________________:".$file_name_dbf."</br>";
       echo "_______________tmpfilename_shp_____________:".$temp_file_name_shp."</br>";
       echo "_______________tmpfilename_dbf_____________:".$temp_file_name_dbf."</br>";
       echo "_______________filesize_shp_____________:".$file_size_shp."</br>";
       echo "_______________filesize_dbf_____________:".$file_size_dbf."</br>";
       
       
       if (($upload_dir == "ERROR") OR ($upload_log_dir == "ERROR")) { return false; }
       elseif ((((!$valid_user) OR (!$valid_size) OR (!$valid_ext) OR ($existing_file)))) { return false; } 
       else 
       {
         echo "*****************   temp_file_name to be checked: $temp_file_name , uploaded_dir: $upload_dir, filename: $file_name_shp.</br>";
         $bmv_shp = $this->my_move_to_uploaded_file($temp_file_name_shp, $file_name_shp, $file_size_shp, $upload_dir, $upload_log_dir, $y, $m, $d, $date, $time);
         if($bmv_shp) echo "SHP file move tmp to upload worked</br>";
         $bmv_dbf = $this->my_move_to_uploaded_file($temp_file_name_dbf, $file_name_dbf, $file_size_dbf, $upload_dir, $upload_log_dir, $y, $m, $d, $date, $time);
         if($bmv_dbf) echo "DBF file move tmp to upload worked</br>";
         return $bmv_shp && $bmv_dbf;
       }
    } 
    function validate_bbx($arr_bbx)
    {
      $axmin = abs($arr_bbx[0]);
      $axmax = abs($arr_bbx[1]);
      $aymin = abs($arr_bbx[2]);
      $aymax = abs($arr_bbx[3]);

      echo "($axmin , $axmax) , ($aymin, $aymax) </br>";

/*
      if ($axmin > 180.0){ return FALSE; }
      elseif ($axmax > 180.0){ return FALSE; }
      elseif ($aymin > 90.0){ return FALSE; }
      elseif ($aymax > 90.0){ return FALSE; }
      else{ return TRUE; }
*/
      return TRUE;
    }

    function get_shp_path()
    {
      $filename = $this->upload_dir.$this->file_name_shp;
      return $filename;
    }

    function get_shp_name()
    {
      $filename = $this->get_shp_path();
      $shp_name = extract_shp_name($filename);

      return $shp_name;
    }

}   
?>
