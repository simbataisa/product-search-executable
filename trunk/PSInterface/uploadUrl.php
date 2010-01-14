<?php
//handles the url upload
define('ALLOWED_FILENAMES','jpg|jpeg|gif|png');
define('IMAGE_DIR','./images/');
require_once("dbconnection.php");
if(!isset($_REQUEST['url']))
    $_REQUEST['url']="http://i165.photobucket.com/albums/u54/creexs/imahe-nasyon.jpg";

$message="OK";
if(!preg_match('#^http://.*([^/]+\.('.ALLOWED_FILENAMES.'))$#',$_REQUEST['url'],$m)) {
    $message = "Error: File type mismatched ";
}
if(!$img=file_get_contents($_REQUEST['url']))
    $message = "Error: File cant get ";
if(!$f=fopen(IMAGE_DIR.'/upload.jpg','w'))
    $message = "Error: File open failed! ";
chmod("./images/upload.jpg" ,0777);
if(fwrite($f,$img)===FALSE)
    $message = "Error: File cant write ";
fclose($f);


$file = fopen("./images/exp.txt","w+"	);
fwrite ($file, "images/upload.jpg\n");
fclose($file);
chmod("./images/exp.txt" ,0777);
$last = exec("./extractFeatures ./images/exp.txt",$returnvar);

echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
echo "<upload><item><url>Controller/images/upload.jpg</url> <feature>-1</feature><status>".$message."</status></item></upload>";

$filename = "testout.txt";
$handle = fopen($filename, 'w+');
fwrite($handle,"text=".$_REQUEST['url']);



?>

