<?php
/**
 * File to easily zip all files and folders to be ready for plugin install
 */
// Get real path for our folder
$rootPath = realpath(__DIR__);
$zip_name = 'wv-admin.zip';
$folder_path = '';
//first delete incase theres an old zip file
unlink($zip_name);
// Initialize archive object
$zip = new ZipArchive();
$zip->open($zip_name, ZipArchive::CREATE | ZipArchive::OVERWRITE);

// Create recursive directory iterator
/** @var SplFileInfo[] $files */
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($rootPath),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($files as $name => $file)
{
    // Skip directories (they would be added automatically)
    if (!$file->isDir())
    {
        // Get real and relative path for current file
        $filePath = $file->getRealPath();
		$filePath = str_replace('\\','/',$filePath);//solved the repeated value of files with back slash :)
		//ignore .git
		if(strpos($filePath,'.git') !== false || strpos($filePath,'~') !== false || strpos($filePath,'.zip') !== false){
			continue;
		}
		 $relativePath = $folder_path.substr($filePath, strlen($rootPath) + 1);
		$relativePath = str_replace('\\','/',$relativePath);//replace back slash with forward slash,for proper directory
		// Add current file to archive
        $zip->addFile($filePath, $relativePath);
    }
}
// Zip archive will be created only after closing object
$zip->close();
echo "zipped\n";

//now remove some stuff inside the zip file, no better way
if($zip->open($zip_name)){
	$files_to_delete = ['zipper_file.php','README.md','test.php','composer.lock'];
	for($i = 0; $i < count($files_to_delete); $i++){
		echo "Deleting: ".$files_to_delete[$i]."...\n";
		var_dump($zip->deleteName($folder_path.$files_to_delete[$i]));//delete this current file too
	}
	$zip->close();
	echo "necessary stuff deleted";
}
else{
	echo 'couldnt delete necessary stuff';
}
