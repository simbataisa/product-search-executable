<?php
$MAXIMUM_FILESIZE = 10240 * 10240; // 200KB
$MAXIMUM_FILE_COUNT = 10; // keep maximum 10 files on server
$IMAGE_PREFIX_DIR = "PSInterface/images/";

//chmod("./images/exp.txt" ,0777);
$file = fopen("./images/exp.txt","w+") or die ("Can't open file");
fwrite ($file, "Testing.................\n");
fclose($file);

$file = fopen("./images/exp.txt","r") or die ("Can't open file");
while (!feof($file)) {
        echo fread($file,8);

    }
?>
