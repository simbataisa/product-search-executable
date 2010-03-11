<?php
require_once('SearchController.php');
require_once('ResultProcessor.php');
require_once('CategoryProcessor.php');


session_start();

if(isset($_POST['query'])) 
    $query = $_POST['query'];
else 
    $query = "";


if(isset($_POST['page'])) 
    $page = (int)$_POST['page'];
else 
    $page = 0;

if(isset($_POST['pageLength'])) 
    $length = (int)$_POST['pageLength'];
else 
    $length = 30;

if(isset($_POST['category']))
    $category = $_POST['category'];
else 
    $category = "";

if(isset($_POST['sort']))
    $sort = $_POST['sort'];
else
    $sort = "des";

if(isset($_POST['by']))
    $by = $_POST['by'];
else
    $by = "";

if(isset($_POST['merchant']))
    $merchant = $_POST['merchant'];
else
    $merchant = "";

if(isset($_POST['upP']))
    $upP = $_POST['upP'];
else
    $upP = "";

if(isset($_POST['lowP']))
    $lowP = $_POST['lowP'];
else
    $lowP = "";

if(isset($_POST['upR']))
    $upR = $_POST['upR'];
else
    $upR = "";

if(isset($_POST['lowR']))
    $lowR = $_POST['lowR'];
else
    $lowR = "";
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
if(isset($_POST['session'])) 
    $session = $_POST['session'];
else 
    $session = 0;

$pid ="";

//$query = strip_tags($query);
$offset = $page * $length;
//$sort = "asc"; $by = "rate";
//$query = "girl";
//$query = "sunglasses"; 
//$by ="rate";
//echo "$query $by\n";

$searcher = new SearchController();
//$category = "123";
if($category != 0) {
    $cateMan = new CategoryProcessor();
    $category = (int)$category;

    $childCategories = $cateMan->getBottomChildren($category);

    $searcher->reset();
    $searcher->setFilters('category_id',$childCategories);
//			echo "filters";
}


//set Price filter

if($lowP!="" && $upP!="")
    $searcher->setPriceRange((float)$lowP,(float)$upP);
else if($lowP!="" && $upP=="")
    $searcher->setPriceRange((float)$lowP, 10000000);
else if($lowP=="" && $upP!="")
    $searcher->setPriceRange(0, (float)$upP);

//set Rating filter
if($lowR!="" && $upR!="")
    $searcher->setRatingRange((float)$lowR,(float)$upR);
else if($lowR!="" && $upR=="")
    $searcher->setRatingRange((float)$lowR, 10);
else if($lowR=="" && $upR!="")
    $searcher->setRatingRange(0, (float)$upR);


//set sorting mode
if($sort !="" && $sort !=null && $by!="") {

    $searcher->setSortMode($sort,$by);

}
if($color!=-1) {
    //set result page
    $searcher->setResultRange($page*$length,$length);

    //echo "start searching ... \n";
    $processor = new ResultProcessor();
    //if($page==0){ /// Implementing seesion to keep the data



    $res = $searcher->search($query);
    //	echo "finish searching ... \n";
    //$res = false;
    if($res === false)
        $processor->processError($searcher->cl->GetLastError());
    //echo "hellllllll\n";
    else if(!isset($res["matches"]))
        $processor->processError("No matches found");
    else {
        //$i=0;
        //var_dump($res["matches"]);
        //	$ids = array();
        //		foreach($res["matches"] as $docinfo){
        //			array_push($ids, $docinfo['id']);
        //echo $ids[$i];
        //$i++;
        //	}
        //echo "start process\n";

        //$_SESSION['items'] = $res;
        $processor->process($res);

    }
}

else {
    //echo "start searching ... \n";

    $processor = new ResultProcessor();

    $searcher->setResultRange(0,800);

    $res = $searcher->search($query);
    //	echo "finish searching ... \n";

    if($res === false)
        $processor->processError($searcher->cl->GetLastError());
    else if(!isset($res["matches"]))
        $processor->processError("No matches found");
    else {

        $ids = array();
        foreach($res["matches"] as $docinfo) {
            array_push($ids, $docinfo['id']);

        }
        //echo "start process\n";
        $d =implode(",",$ids);
        //	echo $d;

        $query="SELECT Product_id as pid,sqrt(power($red-R_value,2)+ power($green-G_value,2)+power($blue-B_value,2)) as dist from RGB where product_id in (".$d.") order by dist limit $offset,$length "	;

        $res1 = mysql_query($query);

        $pids="";
        $j=0;
        while($r1 = mysql_fetch_array($res1)) {
            $pid = $r1['pid'];
            if($j > 0) $pids .= " ";
            $pids .= $pid;

            $j++;

        }
        /*$_SESSION['rank'] = $pids;
		$query="SELECT Product_id as pid from products where product_id in (".$_SESSION['rank'].") limit $offset,$length "	;
		//	echo $query;
		$res1 = mysql_query($query);
	
		$pids="";
		$j=0;
		while($r1 = mysql_fetch_array($res1))
		{
			$pid = $r1['pid'];
			if($j > 0) $pids .= " ";
			$pids .= $pid;
		
			$j++;
		
		}*/




        //$_SESSION['items'] = $res;
        $processor->processVSresult($pids,$res['total'],$res['time']);
        //echo $res['total'];
    }

}


?>

