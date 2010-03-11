<?php 
require_once("ResultProcessor.php");
require_once("dbconnection.php");

$stimer = explode(' ',microtime());
$stimer = $stimer[1] + $stimer[0];


//Lsh has its own referencing number so i create a table to map them to our products_id in the database.
// The create statement is as below.
/*Create table test 
(
   ID BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
   PRIMARY KEY (ID)
) as select product_id as pid from images where feature_set !=""  order by product_id */



if(isset($_POST['id']))
    $id = $_POST['id'];
//$id = "769";
else 
    $id = "";

if(isset($_POST['category']))
    $category = $_POST['category'];
else
    $category = "";

if(isset($_POST['page'])) 
    $page = $_POST['page'];
else 
    $page = 0;
if(isset($_POST['session'])) 
    $session = $_POST['session'];
else 
    $session = 1;
if(isset($_POST['pageLength'])) 
    $length = $_POST['pageLength'];
else 
    $length = 30;

if(isset($_POST['feature'])) 
    $feature = $_POST['feature'];
else 
    $feature = 0;
if(isset($_POST['reranking'])) 
    $reranking = $_POST['reranking'];
else 
    $reranking = 0;
if(isset($_POST['color'])) 
    $color = $_POST['color'];
else 
    $color = 0;
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
if(isset($_POST['sort']))
    $sort = $_POST['sort'];
else
    $sort = "des";

if(isset($_POST['by']))
    $by = $_POST['by'];
else
    $by = "";

$pid ="";

$offset = $page * $length;
//$ID = $r['feature_set'];	
//------------------------------------	
//ni_set('display_warning',0);
error_reporting(0);
//------------------------------------
//Session start
session_start();



$host = "localhost";
$data='';
$pids='';
$port = 9000;
$Rpro = new ResultProcessor();

if($feature == 0 ) {

    $sql = "select index_id as ID from itable as t where product_id ='$id'";
    $res = mysql_query($sql);
    if($r = mysql_fetch_array($res))
        $ID= $r['ID'];
    //	echo $ID;
    $ID=$ID;

}

else
    $ID = get_feature();


//start socket communication here, for every new request page

if($session == 0) {

    $socket = socket_create(AF_INET, SOCK_STREAM, 0) or die("Could not create socket");

    // connect to server
    $result = socket_connect($socket, $host, $port);// or die("Could not connect to server\n");

    if(!$result) {
        $Rpro->processVSresult("-1");
        die;
    }


    socket_write($socket, $ID, strlen($ID)) or die("Could not send data to server\n");


    while (($recv = socket_read($socket, 30)) !=false)
        $data .=$recv;
    socket_close($socket);

    $_SESSION['items'] =$data;

}
else
    $data = $_SESSION['items'];




$j = 0;
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
$data = "";


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

