<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/
require_once("SearchResultProcessor.php");
require_once("dbconnection.php");
require_once("Constants.php");
header ("content-type: text/xml");

$stimer = explode(' ',microtime());
$stimer = $stimer[1] + $stimer[0];
session_start();

//Lsh has its own referencing number so i create a table to map them to our products_id in the database.
// The create statement is as below.
/*Create table test
(
   ID BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
   PRIMARY KEY (ID)
) as select product_id as pid from images where feature_set !=""  order by product_id */


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

if($option == "vsDragDrop" || $option == "vsButtonClick" || $option == "vsRefinement") {
    $vsResultProcessor->createVisualSearchXMLTitle();
    if($firstPageReq=="Y") {
        //Getting LSH index id
        $sqlQuery = "SELECT index_id FROM itable WHERE product_id ='$product_id'";
        $res = mysql_query($sqlQuery);
        if($r = mysql_fetch_array($res))
            $index_id= $r['index_id'];

        $index_id=$index_id;
        //
        $socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Could not create socket");
        $host = $constants->image_server_host;
        $port = $constants->image_server_port;
        // connect to server
        $result = 0;
        $result = socket_connect($socket, $host, $port);// or die("Could not connect to server\n");

        if(!$result) {
            $vsResultProcessor->processVSresult("-1");
            die;
        }
        socket_write($socket, $index_id, strlen($index_id)) or die("Could not send data to server\n");

        while (($recv = socket_read($socket, 30)) !=false)
            $data .=$recv;
        socket_close($socket);

        //
        $pos = strpos($data, ",");
        $arrayIndexId = array();
        if($pos) {
            $arrayIndexId = split(",", $data);
            $total = count($arrayIndexId)-1;
            $searchTime = $arrayIndexId[count($arrayIndexId)-1];
        }else {
            $total = 0;
            $searchTime = $data;
        }
        $searchTime = number_format(floatval($searchTime), 4);
        //var_dump($arrayIndexId);


        //Getting level_1_id category for the requested category id
        $cateLevel1Query = "SELECT level_1_id FROM test_sub_categories WHERE category_id = '$category'";

        $cateLevel1ResSet = mysql_query($cateLevel1Query);
        $level_1_id = "";
        while($r = mysql_fetch_array($cateLevel1ResSet)) {
            $level_1_id = $r['level_1_id'];
        }


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
        //var_dump($product_ids);
        //Set the session so that data can be retrieved faster for paging...
        $_SESSION['product_ids'] =$product_ids;

        //Getting product id for first page result
        $productIdToPrint = array();
        if(intval($total)>$pageLength) {
            for($counter = 0; $counter<intval($pageLength); $counter++) {
                $productIdToPrint[$counter] = $product_ids[$counter];
            }
        }else {
            for ($counter = 0; $counter < $total; $counter++) {
                $idsToPrint[$counter] = $product_ids[$counter];
            }
        }

        //var_dump($product_ids);
        //echo "Total : $total Search Time: $searchTime First Page Request: $firstPageReq Last Page: $isLastPage";
        $vsResultProcessor->process_result($productIdToPrint, $total, $searchTime, $firstPageReq, $isLastPage);
    }else {
        $product_ids = $_SESSION['product_ids'];
        $total = $_SESSION['total'];
        //Getting product id for first page result
        $productIdToPrint = array();
        for($counter = $startIndex; $counter<intval($stopIndex); $counter++) {
            $productIdToPrint[$counter] = $product_ids[$counter];
        }

        //var_dump($product_ids);
        //echo "Total : $total Search Time: $searchTime First Page Request: $firstPageReq Last Page: $isLastPage";
        $vsResultProcessor->process_result($productIdToPrint, $total, $searchTime, $firstPageReq, $isLastPage);
    }

}else if($option=="byColor") {
    $vsResultProcessor->createColorSearchXMLTitle();
    if($firstPageReq=="Y") {
        $product_ids = array();
        if(isset($_SESSION['product_ids'])) {
            $product_ids = $_SESSION['product_ids'];
        }
        $idStr = implode(",",$product_ids);
        //if ($color == -97)
        $idWithTheColorQuery="SELECT product_id,sqrt(power($red-R_value,2)+ power($green-G_value,2)+power($blue-B_value,2)) as dist
            FROM RGB WHERE product_id in (".$idStr.") ORDER BY dist";

        $idWithTheColorResSet = mysql_query($idWithTheColorQuery);

        $product_ids = array();
        $total = mysql_num_rows($idWithTheColorResSet);
        while($r1 = mysql_fetch_array($idWithTheColorResSet)) {
            array_push($product_ids, $r1['product_id']);
        }
        $_SESSION['product_ids'] = $product_ids;
        $_SESSION['total'] = $total;
        $searchTime = $_SESSION['time'];
        //
        $idsToPrint = array();
        if(intval($total)>$pageLength) {
            for ($counter = 0; $counter < $pageLength; $counter++) {
                $idsToPrint[$counter] = $product_ids[$counter];
            }
        }else {
            for ($counter = 0; $counter < $total; $counter++) {
                $idsToPrint[$counter] = $ids[$counter];
            }
        }
        $vsResultProcessor->process_result($idsToPrint,$total,$searchTime,$firstPageReq,$isLastPage);
    }else if($firstPageReq == "N") {
        $product_ids = array();
        if(isset($_SESSION['product_ids'])) {
            $product_ids = $_SESSION['product_ids'];
        }

        if(isset($_SESSION['total'])) {
            $total = $_SESSION['total'];
        }

        $idsToPrint = array();
        for ($counter = $startIndex; $counter < $stopIndex; $counter++) {
            $idsToPrint[$counter] = $product_ids[$counter];
        }
        //echo "-----------------------------------------------------------\n $startIndex $stopIndex";
        //var_dump($idsToPrint);
        $vsResultProcessor->process_result($idsToPrint,$total,0,$firstPageReq,$isLastPage);
    }
}else if($option == "refineSearchResult") {
    $vsResultProcessor->createVisualSearchXMLTitle();
    if($firstPageReq=="Y") {

        //Getting level_1_id category for the requested category id
        $cateLevel1Query = "SELECT level_1_id FROM test_sub_categories WHERE category_id = '$category'";

        $cateLevel1ResSet = mysql_query($cateLevel1Query);
        $level_1_id = "";
        while($r = mysql_fetch_array($cateLevel1ResSet)) {
            $level_1_id = $r['level_1_id'];
        }

        $product_ids = $_SESSION['product_ids'];
        $idStr = implode(",",$product_ids);

        //Getting refined product id realated to the catefory
        $productQuery ="SELECT distinct p.product_id as pid from products as p, test_sub_categories c
	where p.product_id IN (" .$idStr.") AND level_1_id = '".$level_1_id."'
        AND p.category_id=c.category_id  ORDER BY Field(product_id," .$idStr. ")";

        $productResSet= mysql_query($productQuery);
        $total = mysql_num_rows($productResSet);
        $_SESSION['total'] = $total;
        $product_ids = array();
        while($r = mysql_fetch_array($productResSet)) {
            array_push($product_ids,  $r['pid']);
        }
        //Set the session so that data can be retrieved faster for paging...
        $_SESSION['product_ids'] =$product_ids;

        //Getting product id for first page result
        $productIdToPrint = array();
        for($counter = 0; $counter<intval($pageLength); $counter++) {
            $productIdToPrint[$counter] = $product_ids[$counter];
        }

        //var_dump($product_ids);
        //echo "Total : $total Search Time: $searchTime First Page Request: $firstPageReq Last Page: $isLastPage";
        $vsResultProcessor->process_result($productIdToPrint, $total, $searchTime, $firstPageReq, $isLastPage);
    }
}












/*$j = 0;
$data = trim($data);
$e = explode(",",$data);
//to remove the last array, which is the amount of time taken.
for($i = 0 ; $i <count($e)-1 ; $i++) {
    if($i>0)
        $d.=",";
    $d .=$e[$i];
}


//handles visual query of products in the database
if($feature ==0 ) {
    // All Query1 are to provide the total number of results only.
    $query1 ="select distinct p.product_id as pid from products as p,itable t, test_sub_categories c
	where p.category_id=c.category_id and p.product_id = t.product_id and
	level_1_id = ( SELECT level_1_id from test_sub_categories where category_id = '$category' ) and
	index_id in (" .$d.") order by Field(index_id," .$d. ") ";

}



//handles uploaded query , images not in the database
else if($feature!=0) {
    // to find out the top numbers of products that belongs to the same parent category in order to apply the category filter
    $query2= "select nid, count(num) as cnum from  (select c.level_1_id as nid ,c.level_1_id as num from products as p ,test_sub_categories c, itable t
 	where p.product_id = t.product_id and p.category_id=c.category_id and p.product_id =t.product_id  and index_id in ($d) limit 50)
 			as t group by num order by cnum desc limit 1";
    //echo $query2;
    if($category == 0) {
        $res1 = mysql_query($query2);
        while($r = mysql_fetch_array($res1)) {
            $nid = $r['nid'];
            $num = $r['cnum'];
            if($num > 25)
                $category =$nid;
            else
                $category = 0;

        }
    }
}

if($feature ==0 ) {
    //Actual paging query are handle by all "$query"
    $query ="select p.product_id as pid from products as p,itable t, test_sub_categories c where p.category_id=c.category_id and p.product_id = t.product_id and
	level_1_id = ( SELECT level_1_id from test_sub_categories where category_id = '$category' ) and index_id in (" .$d.") order by Field(index_id," .$d. ") limit $offset , $length";
}
else if($feature!=0) {

    if($category==0) {
        //Actual paging query are handle by all "$query"
        $query ="select p.product_id as pid from products as p,itable as t where p.product_id=t.product_id and index_id in (" .$d.") order by Field(index_id," .$d. ") limit $offset , $length";

        //To provide the total numbers of products found
        $query1 ="select distinct p.product_id as pid from products as p,itable as t
	 where p.product_id=t.product_id  and index_id in (" .$d.") order by Field(index_id," .$d. ") ";
    }
    else {
        $query ="select p.product_id as pid from products as p,test_sub_categories as c,itable as t
		where p.product_id=t.product_id  and p.category_id = c.category_id and c.level_1_id = $category and index_id in (" .$d.") order by Field(index_id," .$d. ") limit $offset , $length";

        $query1 ="select distinct p.product_id as pid from products as p,test_sub_categories as c,itable as t
		where p.product_id=t.product_id and p.category_id = c.category_id and c.level_1_id = $category and index_id in (" .$d.") order by Field(index_id," .$d. ") ";



    }
}

// color dun have to use the order by field query
if($color!=0)
    $res = mysql_query($query1);
else
    $res = mysql_query($query);

//fecthing of queries
$j=0;
$total = mysql_num_rows(mysql_query($query1));
while($r = mysql_fetch_array($res)) {
    $pid = $r['pid'];
    if($j > 0) $pids .= " ";
    $pids .= $pid;

    $j++;

}

//implement the coloring ranking using euclidean distance
if($color!=0) {
    $e1 = explode(" ",$pids);
    $d =implode(",",$e1);
    //if ($color == -97)
    $query="SELECT Product_id as pid,sqrt(power($red-R_value,2)+ power($green-G_value,2)+power($blue-B_value,2)) as dist from RGB where product_id in (".$d.") order by dist limit $offset , $length"	;

    $res1 = mysql_query($query);

    $pids="";
    $j=0;
    while($r1 = mysql_fetch_array($res1)) {
        $pid = $r1['pid'];
        if($j > 0) $pids .= " ";
        $pids .= $pid;

        $j++;

    }
}


//pass the results to the processor for parsing into XML format
$etimer = explode(' ',microtime());
$etimer = $etimer[1] + $etimer[0];
$Rpro->processVSresult($pids,$total,round($etimer-$stimer,5));



$pids ="";
$data = "";*/


function get_feature() {


    $count =0;
    $feature ="";

    //1st method

    //$filename = "./data.bin";
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
