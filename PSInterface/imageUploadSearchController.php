<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

require_once("SearchResultProcessor.php");
require_once("dbconnection.php");
require_once("Constants.php");

$stimer = explode(' ',microtime());
$stimer = $stimer[1] + $stimer[0];
session_start();

if(isset($_REQUEST['option']))
    $option = $_REQUEST['option'];
//$id = "769";
else
    $option = "";

if(isset($_REQUEST['category']))
    $category = $_REQUEST['category'];
else
    $category = "";


if(isset($_REQUEST['product_id']))
    $product_id = $_REQUEST['product_id'];
//$id = "769";
else
    $product_id = "";

if(isset ($_REQUEST['pageLength'])) {
    $pageLength = $_REQUEST['pageLength'];
}else
    $pageLength = 20;

if(isset ($_REQUEST['startIndex'])) {
    $startIndex = $_REQUEST['startIndex'];
}else
    $startIndex = 0;

if(isset ($_REQUEST['stopIndex'])) {
    $stopIndex = $_REQUEST['stopIndex'];
}else
    $stopIndex = 0;

if(isset ($_REQUEST['firstPageReq'])) {
    $firstPageReq = $_REQUEST['firstPageReq'];
}else
    $firstPageReq = "";

if(isset ($_REQUEST['lastPage'])) {
    $isLastPage = $_REQUEST['lastPage'];
}else
    $isLastPage = "";

if(isset($_POST['red']))
    $red = $_POST['red'];
else
    $red = 0;
if(isset($_POST['green']))
    $green = $_POST['green'];
else
    $green = 0;
if(isset($_POST['blue']))
    $blue = $_POST['blue'];
else
    $blue = 0;

$constants = new Constants();
$vsResultProcessor = new SearchResultProcessor();

$data='';
$total=0;
$searchTime="";
$product_ids = array();
$feature = get_feature();
if($option == "imageUploadSearch"){
    $socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Could not create socket");
        $host = $constants->image_server_host;
        $port = $constants->image_server_port;
        // connect to server
        $result = socket_connect($socket, $host, $port);// or die("Could not connect to server\n");

        if(!$result) {
            $vsResultProcessor->processVSresult("-1");
            die;
        }
        socket_write($socket, $feature, strlen($feature)) or die("Could not send data to server\n");

        while (($recv = socket_read($socket, 30)) !=false)
            $data .=$recv;
        socket_close($socket);
        echo $data;
}
function get_feature() {
    $count =0;
    $feature ="";

    //1st method

    $filename = "./data.bin";
    //chmod($filename , 0777);
    $handle = fopen($filename, "rb");
    $contents = '';
    while (!feof($handle) && $count < 299) {
        $contents = unpack("d",fread($handle,8));
        if ($count >1) {
            $feature .= " ";
            $feature .= $contents[1];
            //$feature .= $contents[1];
        }
        $count++;
    }
    //second method
    /*
	system("./readbin data.bin feat.txt");

	$filename = "feat.txt";
	$handle = fopen($filename, 'r');

	$feature = fread($handle,filesize($filename));
	fclose($handle);
    */
    return $feature;
}
?>
