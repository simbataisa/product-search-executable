<?php
//Include File
require_once ('SphinxSearchManager.php');
require_once('SearchResultProcessor.php');
header ("content-type: text/xml");

session_start();
$sphinxSearchManger = new SphinxSearchManager();
$resultProcessor = new SearchResultProcessor();


/* 
 * Getting request parameters
*/
if(isset ($_REQUEST['option'])) {
    $option = $_REQUEST['option'];
}else
    $option = "";

if(isset($_REQUEST['category']))
    $category = $_REQUEST['category'];
else
    $category = "";

if(isset ($_REQUEST['pageLength'])) {
    $pageLength = $_REQUEST['pageLength'];
}else
    $pageLength = 20;

if(isset ($_REQUEST['search_index'])) {
    $search_index = $_REQUEST['search_index'];
}else
    $search_index = "";

if(isset ($_REQUEST['key_word'])) {
    $key_word = $_REQUEST['key_word'];
}else
    $key_word = "";

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

if(isset ($_REQUEST['colorRequest'])) {
    $colorRequest = $_REQUEST['colorRequest'];
}else
    $colorRequest = 0;

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

$total = "";
//------------------------------------------------------------------------------


$sphinxSearchManger->setIndex("product");

if($option == "byKeyword") {
    $resultProcessor->createTextSearchXMLTitle();
    //$res = $sphinxSearchManger->search("(@name $key_word) | (@description $key_word)");
    $sphinxSearchManger->setResultRange(0,500,500);
    if($firstPageReq=="Y") {
        $res = $sphinxSearchManger->search("(@name $key_word)");
        //Getting total result first

        if($res === false) {
            $resultProcessor->processError($sphinxSearchManger->cl->GetLastError());
        }
        else if(!isset($res["matches"])) {
            //No match found
            $resultProcessor->processError("0");
        }
        else {
            if (is_array($res["matches"]) ) {
                $ids = array();


                foreach($res["matches"] as $docinfo) {
                    array_push($ids, $docinfo['id']);
                }

                //
                $idStr = implode(",",$ids);
                if($search_index == "All Categories") {
                    $productIdQuery = "SELECT product_id FROM products WHERE product_id IN (".$idStr.")
                        ORDER BY Field(product_id," .$idStr. ")";
                }else
                    $productIdQuery = "SELECT product_id FROM products WHERE search_index = '" . $search_index . "'
                    AND product_id IN (".$idStr.") ORDER BY Field(product_id," .$idStr. ")";
                $productResSet = mysql_query($productIdQuery);
                $total = mysql_num_rows($productResSet);
                $ids = array();
                if(intval($total)>0) {
                    while($r = mysql_fetch_array($productResSet)) {
                        array_push($ids, $r['product_id']);
                    }
                    //var_dump($ids);
                    $_SESSION['ids'] = $ids;
                    $idsToPrint = array();
                    //$total = $res['total'];
                    if(intval($total)>$pageLength) {
                        for ($counter = 0; $counter < $pageLength; $counter++) {
                            $idsToPrint[$counter] = $ids[$counter];
                        }
                    }else {
                        for ($counter = 0; $counter < $total; $counter++) {
                            $idsToPrint[$counter] = $ids[$counter];
                        }
                    }
                    $_SESSION['total'] = $total;
                    $searchTime = $res['time'];
                    $_SESSION['time'] = $searchTime;
                    $resultProcessor->process_result($idsToPrint,$total,$searchTime,$firstPageReq,$isLastPage);
                }else {
                    $total = 0;
                    $resultProcessor->processError($total);
                }


                //var_dump($idsToPrint);


            }
            //var_dump($res);
        }
    }else if($firstPageReq=="N") {
        $ids = array();
        if(isset($_SESSION['ids'])) {
            $ids = $_SESSION['ids'];
        }

        if(isset($_SESSION['total'])) {
            $total = $_SESSION['total'];
        }



        $idsToPrint = array();
        for ($counter = $startIndex; $counter < $stopIndex; $counter++) {
            $idsToPrint[$counter] = $ids[$counter];
        }
        //echo "-----------------------------------------------------------\n $startIndex $stopIndex";
        //var_dump($idsToPrint);
        $resultProcessor->process_result($idsToPrint,$total,0,$firstPageReq,$isLastPage);
    }

}else if($option == "autoSuggestion") {
    $sphinxSearchManger->setResultRange(intval($startIndex),intval($stopIndex),500);
    $res = $sphinxSearchManger->search("(@name $key_word)");
    //Getting total result first
    $resultProcessor->createAutoSuggestXMLTitle();
    if($res === false) {
        $resultProcessor->processError($sphinxSearchManger->cl->GetLastError());
    }
    else if(!isset($res["matches"])) {
        //No match found
        $resultProcessor->processError("0");
    }
    else {
        if (is_array($res["matches"]) ) {
            $ids = array();


            foreach($res["matches"] as $docinfo) {
                array_push($ids, $docinfo['id']);
            }
            $total = $res['total'];
            $_SESSION['total'] = $total;
            $searchTime = $res['time'];
            $resultProcessor->process_result($ids,$total,$searchTime,$firstPageReq,$isLastPage);
        }
        //var_dump($res);
    }
}else if($option=="byColor") {
    $resultProcessor->createColorSearchXMLTitle();
    if($firstPageReq=="Y") {
        $ids = array();
        if(isset($_SESSION['ids'])) {
            $ids = $_SESSION['ids'];
        }
        $idStr = implode(",",$ids);
        //if ($color == -97)
        $idWithTheColorQuery="SELECT product_id,sqrt(power($red-R_value,2)+ power($green-G_value,2)+power($blue-B_value,2)) as dist
            FROM RGB WHERE product_id in (".$idStr.") ORDER BY dist";

        $idWithTheColorResSet = mysql_query($idWithTheColorQuery);

        $ids = array();
        $total = mysql_num_rows($idWithTheColorResSet);
        while($r1 = mysql_fetch_array($idWithTheColorResSet)) {
            array_push($ids, $r1['product_id']);
        }
        $_SESSION['$ids'] = $ids;
        $_SESSION['total'] = $total;
        $searchTime = $_SESSION['time'];
        //
        $idsToPrint = array();
        if(intval($total)>$pageLength) {
            for ($counter = 0; $counter < $pageLength; $counter++) {
                $idsToPrint[$counter] = $ids[$counter];
            }
        }else {
            for ($counter = 0; $counter < $total; $counter++) {
                $idsToPrint[$counter] = $ids[$counter];
            }
        }
        $resultProcessor->process_result($idsToPrint,$total,$searchTime,$firstPageReq,$isLastPage);
    }else if($firstPageReq == "N") {
        $ids = array();
        if(isset($_SESSION['ids'])) {
            $ids = $_SESSION['ids'];
        }

        if(isset($_SESSION['total'])) {
            $total = $_SESSION['total'];
        }

        $idsToPrint = array();
        for ($counter = $startIndex; $counter < $stopIndex; $counter++) {
            $idsToPrint[$counter] = $ids[$counter];
        }
        //echo "-----------------------------------------------------------\n $startIndex $stopIndex";
        //var_dump($idsToPrint);
        $resultProcessor->process_result($idsToPrint,$total,0,$firstPageReq,$isLastPage);
    }
}else if($option == "refineSearchResult") {
    $resultProcessor->createTextSearchXMLTitle();
    if($firstPageReq=="Y") {
        $ids = array();
        if(isset($_SESSION['ids'])) {
            $ids = $_SESSION['ids'];
        }
        $idStr = implode(",",$ids);
        //if ($color == -97)
        $cateQuery = "SELECT level_1_id FROM test_sub_categories WHERE category_id = '$category'";
        $cateResSet = mysql_query($cateQuery);
        $level_1_id = "";
        while($r1 = mysql_fetch_array($cateResSet)) {
            $level_1_id = $r1['level_1_id'];
        }
        //echo $level_1_id;
        //echo $idStr;
        $productQuery ="SELECT distinct p.product_id as pid from products as p, test_sub_categories c
	where p.product_id IN (" .$idStr.") AND level_1_id = '".$level_1_id."'
        AND p.category_id=c.category_id  ORDER BY Field(product_id," .$idStr. ")";

        $productResSet = mysql_query($productQuery);

        $ids = array();
        $total = mysql_num_rows($productResSet);
        while($r1 = mysql_fetch_array($productResSet)) {
            array_push($ids, $r1['pid']);
        }
        $_SESSION['$ids'] = $ids;
        $_SESSION['total'] = $total;
        $searchTime = $_SESSION['time'];
        //
        $idsToPrint = array();
        if(intval($total)>$pageLength) {
            for ($counter = 0; $counter < $pageLength; $counter++) {
                $idsToPrint[$counter] = $ids[$counter];
            }
        }else {
            for ($counter = 0; $counter < $total; $counter++) {
                $idsToPrint[$counter] = $ids[$counter];
            }
        }
        $resultProcessor->process_result($idsToPrint,$total,$searchTime,$firstPageReq,$isLastPage);
    }
} else if($option == "frontPageItems") {
    $productIdQuery = "SELECT product_id FROM products WHERE asin IN ('B002FX6XA6','B002EDRMTI','B002MG4ER0'
                        ,'B001PLBTDA','B002UAPK30','B002E14Q76','B001PDIOJA','B002P73SBE','B00127O4B6'
                        ,'BOO1PDM4J6','B002DQBXMS','B002FX8INQ')";
    $productResSet = mysql_query($productIdQuery);
    $total = mysql_num_rows($productResSet);
    $ids = array();
    if(intval($total)>0) {
        while($r = mysql_fetch_array($productResSet)) {
            array_push($ids, $r['product_id']);
        }
        //var_dump($ids);
        $_SESSION['ids'] = $ids;
        $idsToPrint = array();
        //$total = $res['total'];
        if(intval($total)>$pageLength) {
            for ($counter = 0; $counter < $pageLength; $counter++) {
                $idsToPrint[$counter] = $ids[$counter];
            }
        }else {
            for ($counter = 0; $counter < $total; $counter++) {
                $idsToPrint[$counter] = $ids[$counter];
            }
        }
        $resultProcessor->process_result($idsToPrint,$total,"0","Y","Y");
    }else {
        $total = 0;
        $resultProcessor->processError($total);
    }
}
?>
