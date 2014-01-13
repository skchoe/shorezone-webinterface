<?php
set_time_limit(3600*24*3);
function copyFilesToTargetFolder($sourceF, $ext, $minSizeByte, $targetF)
{
    $sdh = @opendir($sourceF);

	while(false !== ($targetFile = readdir($sdh)))
	{
		$ext = end(explode('.', $targetFile));
		$sizeB = filesize("$sourceF/$targetFile");

		if(('png'==$ext)&&($minSizeByte <= $sizeB)) {

	        //echo "File:$targetFile, size:$sizeB bytes : copy   $sourceF/$targetFile ----->$targetF<br/>";

			$string =  system("cp $sourceF/$targetFile $targetF");
			system("chmod 777 $targetF/$targetFile");
		}
	}
}

function getSubDirectory($path = '.', $subfoldername, $dest_folder, $level = 1)
{
		$count = 0;
		$ignore = array('.', '..');
		$dh = @opendir($path);

		while(false !== ($targetFolderID = readdir($dh)))
		{
				if(!in_array($targetFolderID, $ignore))
				{
						$spaces = str_repeat('&nbsp;', ($level * 5));
						$count++;
						
						if(is_dir($path.'/'.$targetFolderID))
						{
								echo "$spaces<a href='$path/$targetFolderID/index.php'>$targetFolderID</a><br />";
								echo "$path/$targetFolderID/$subfoldername<br/>";

								if(file_exists("$path/$targetFolderID/$subfoldername"))
								{
										//echo "dest_folder = ".$dest_folder."<br/>";
										echo "$count -  targetfolder = ".$targetFolderID."<br/>";

										if(!file_exists("$dest_folder/$targetFolderID"))
										{
												$result = system("mkdir $dest_folder/$targetFolderID");

												echo "$spaces new folder made: $result<br/>";
										}
										else
												echo "$spaces folder exists<br/>";

										copyFilesToTargetFolder("$path/$targetFolderID/$subfoldername", 'png', 170, "$dest_folder/$targetFolderID");
										//NOTE that the file of size 169 byte is empty file.
								}
								else {
										echo "$count - subfolder: $subfoldername doesn't exist: $targetFolderID <br/>";
								}
						}
				}
		}
		closedir($dh);
}
/*
echo "---begin-test<br/>";

$exfolder = 'aa';
if(!file_exists("./$exfolder"))
		echo $exfolder." folder not exists<br/>";
else
		echo $exfolder." folder exis<br/>";

$testfolder = "test-folder";

system("mkdir $testfolder");
system("cp ../tiles/id_-3/10/*.png $testfolder");
system("rm -rf $testfolder");
echo "---end-test<br/>";
*/

$posTilesFolder = './tiles';
$targetZoomFolder = '11';
$destFolder = '/tmp/tiles_11';

getSubDirectory($posTilesFolder, $targetZoomFolder, $destFolder);

?>
