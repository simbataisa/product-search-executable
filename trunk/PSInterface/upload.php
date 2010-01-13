<?php
require_once("XMLCreator.php");
$MAXIMUM_FILESIZE = 10240 * 10240; // 200KB
$MAXIMUM_FILE_COUNT = 10; // keep maximum 10 files on server
$IMAGE_PREFIX_DIR = "PSInterface/images/";

//chmod("./images/exp.txt" ,0777);

$doc = new DOMDocument();
$doc->formatOutput = true;

$r = $doc->createElement( "upload" );
$doc->appendChild( $r );

ini_set('display_errors',0);
$message = "<upload>
            <item>
                <url>".$IMAGE_PREFIX_DIR.$_FILES['Filedata']['name']."</url>
                <feature>-1</feature>
                <status>ERROR</status>
            </item>
            </upload>";

if ($_FILES['Filedata']['size'] <= $MAXIMUM_FILESIZE) {
    move_uploaded_file($_FILES['Filedata']['tmp_name'], "./temporary/".$_FILES['Filedata']['name']);


    $type = exif_imagetype("./temporary/".$_FILES['Filedata']['name']);
    echo "Opening file...\n";
    $file = fopen("./images/exp.txt","w+") or die("can't open file");
    if ($type == 1 || $type == 2 || $type == 3) {
        rename("./temporary/".$_FILES['Filedata']['name'], "./images/".$_FILES['Filedata']['name']);
        chmod("./images/".$_FILES['Filedata']['name'] ,777);
        fwrite ($file, "images/".$_FILES['Filedata']['name']."\n");
        fclose($file);
        $fh = fopen("./images/exp.txt", 'r') or die("can't open file");
        $theData = fread($fh, 5);
        fclose($fh);
        echo $theData;

        //$last = exec("./extractFeatures ./images/exp.txt",$returnvar);

        $image_url->$IMAGE_PREFIX_DIR.$_FILES['Filedata']['name'];
        $message = "<upload><item><url>".$image_url."</url> <feature>-1</feature><status>OK</status></item></upload>";
    }
    else {
        unlink("./temporary/".$_FILES['Filedata']['name']);
    }
}
//
$directory = opendir('./images/');
$files = array();
while ($file = readdir($directory)) {
    array_push($files, array('./images/'.$file, filectime('./images/'.$file)));
}
usort($files, sorter);
if (count($files) > $MAXIMUM_FILE_COUNT) {
    $files_to_delete = array_splice($files, 0, count($files) - $MAXIMUM_FILE_COUNT);
    for ($i = 0; $i < count($files_to_delete); $i++) {
        unlink($files_to_delete[$i][0]);
    }
}

echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
echo $message;


closedir($directory);

function sorter($a, $b) {
    if ($a[1] == $b[1]) {
        return 0;
    }
    else {
        return ($a[1] < $b[1]) ? -1 : 1;
    }
}


/*function get_feature()
{
	
	$count =0;
	$feature ="";
	$filename = "./data.bin";
	chmod($filename , 0777);
	$handle = fopen($filename, "rb");
	$contents = '';
	while (!feof($handle) && $count < 299) {
  	$contents = unpack("d",fread($handle,8));
	if ($count >1){
	$feature .= " ";
	$feature .= round($contents[1],9);
	//$feature .= $contents[1];
	}
	$count++;
		}
		/*
	system("./readbin data.bin feat.txt");
	
	$filename = "feat.txt";
	$handle = fopen($filename, 'r');
	$contents ='';
	$feature = fread($handle,filesize($filename));
	fclose($handle);
		
	return $feature;
	
	
}*/

?>
