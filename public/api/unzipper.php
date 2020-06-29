<?php
/**
 * File to help unzip
 */
 $filename = isset($_GET["file_name"]) ? trim($_GET["file_name"]) : '';
 if(empty($filename)){
    echo "Please parse file name like this: ?file_name=file.zip";
    exit();
 }
$unzip = new ZipArchive;
$out = $unzip->open($filename);
if ($out === TRUE) {
  $unzip->extractTo(getcwd());
  $unzip->close();
  echo 'File unzipped';
} else {
  echo 'Something went wrong';
}
?>