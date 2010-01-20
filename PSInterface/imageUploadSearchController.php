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

if(isset($_REQUEST['search_index']))
    $search_index = $_REQUEST['search_index'];
else
    $search_index = "";


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
$imageUploadResultProcessor = new SearchResultProcessor();

$data='';
$total=0;
$searchTime="";
$product_ids = array();
$feature = get_feature();

if($option == "imageUploadSearch"){
    $imageUploadResultProcessor->createUploadSearchXMLTitle();
    if($firstPageReq=="Y"){
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

        //
        $pos = strpos($data, ",");
        $arrayIndexId = array();
        if($pos) {
            $arrayIndexId = split(",", $data);
            $searchTime = $arrayIndexId[count($arrayIndexId)-1];
        }else {
            $total = 0;
            $searchTime = $data;
        }
        $searchTime = number_format(floatval($searchTime), 4);
        //var_dump($arrayIndexId);
        $first10ids = array();
        for($counter = 0; $counter < 10; $counter++){
            $first10ids[$counter] = $arrayIndexId[$counter];
        }
        $index_id_string = implode(",",$first10ids);
        //Finding the most suitable category
        /*$cateLevel1Query = "SELECT level_1_id, count(*) as total FROM test_sub_categories
            WHERE category_id IN (SELECT category_id FROM products
            WHERE product_id IN (SELECT product_id FROM itable WHERE index_id IN (".$index_id_string.")))
            GROUP BY level_1_id ORDER BY total DESC";*/
        //Getting the level 1 category id
       /* $cateLevel1Query = "SELECT level_1_id FROM test_sub_categories
            WHERE category_id = (SELECT category_id FROM products
            WHERE product_id = (SELECT product_id FROM itable WHERE index_id = $arrayIndexId[0]))";*/
        $cateLevel1Query = "SELECT product_id FROM itable WHERE index_id IN (".$index_id_string.")";
        $cateLevel1ResSet = mysql_query($cateLevel1Query);
        $level_1_id = "";
        /*while($r = mysql_fetch_array($cateLevel1ResSet)) {
            $level_1_id = $r['level_1_id'];
        }*/
        while($r = mysql_fetch_array($cateLevel1ResSet)) {
            $level_1_id = $r['product_id'];
        }
        echo $level_1_id;

        //Getting index id for first page result
        array_pop($arrayIndexId);
        $index_id_string = implode(",",$arrayIndexId);
        
        //Getting actual product id realated to the catefory
        $productQuery ="SELECT distinct p.product_id as pid from products as p,itable t, test_sub_categories c
	where t.index_id IN (" .$index_id_string.") AND level_1_id = $level_1_id
        AND p.category_id=c.category_id AND
        p.product_id = t.product_id  ORDER BY Field(index_id," .$index_id_string. ")";

        $productResSet= mysql_query($productQuery);
        $total = mysql_num_rows($productResSet);
        $_SESSION['total'] = $total;
        $product_ids = array();
        while($r = mysql_fetch_array($productResSet)) {
            array_push($product_ids,  $r['pid']);
        }

        //echo $total;
        //Set the session so that data can be retrieved faster for paging...
        $_SESSION['product_ids'] =$product_ids;

        //Getting product id for first page result
        $productIdToPrint = array();
        for($counter = 0; $counter<intval($pageLength); $counter++) {
            $productIdToPrint[$counter] = $product_ids[$counter];
        }

        //var_dump($product_ids);
        //echo "Total : $total Search Time: $searchTime First Page Request: $firstPageReq Last Page: $isLastPage";
        $imageUploadResultProcessor->process_result($productIdToPrint, $total, $searchTime, $firstPageReq, $isLastPage);
    }else{
        $product_ids = $_SESSION['product_ids'];
        $total = $_SESSION['total'];
        //Getting product id for first page result
        $productIdToPrint = array();
        for($counter = $startIndex; $counter<intval($stopIndex); $counter++) {
            $productIdToPrint[$counter] = $product_ids[$counter];
        }

        //var_dump($product_ids);
        //echo "Total : $total Search Time: $searchTime First Page Request: $firstPageReq Last Page: $isLastPage";
        $imageUploadResultProcessor->process_result($productIdToPrint, $total, $searchTime, $firstPageReq, $isLastPage);
    }
    
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
