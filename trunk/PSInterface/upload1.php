<?php
$MAXIMUM_FILESIZE = 10240 * 10240; // 200KB
$MAXIMUM_FILE_COUNT = 10; // keep maximum 10 files on server
$IMAGE_PREFIX_DIR = "PSInterface/images/";

//chmod("./images/exp.txt" ,0777);
$file = fopen("./images/exp.txt","w+");
$doc = new DOMDocument();
$doc->formatOutput = true;


ini_set('display_errors',0);
$message = "<upload>
            <item>
                <url/>
                <feature>-1</feature>
                <status>ERROR</status>
            </item>
            </upload>";
//


echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
echo $message;





?>
