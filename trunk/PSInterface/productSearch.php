<?php
//Include File
require_once ('SphinxSearchManager.php');
require_once('TextSearchResultProcessor.php');

session_start();
$sphinxSearchManger = new SphinxSearchManager();
$resultProcessor = new TextSearchResultProcessor();

/* 
 * Getting request parameters
*/
if(isset ($_REQUEST['option'])) {
    $option = $_REQUEST['option'];
}else
    $option = "";

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

$total = "";
//------------------------------------------------------------------------------


$sphinxSearchManger->setIndex("product");

if($option == "byKeyword") {
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
                $_SESSION['ids'] = $ids;
                $idsToPrint = array();
                for ($counter = 0; $counter < $pageLength; $counter++) {
                    $idsToPrint[$counter] = $ids[$counter];
                }
                //var_dump($idsToPrint);
                $total = $res['total'];
                $_SESSION['total'] = $total;
                $searchTime = $res['time'];
                $resultProcessor->process_result($idsToPrint,$total,$searchTime,$firstPageReq,$isLastPage);
            }
            //var_dump($res);
        }
    }else if($firstPageReq=="N") {
        $ids = array();
        if(isset($_SESSION['ids'])){           
            $ids = $_SESSION['ids'];           
        }

        if(isset($_SESSION['total'])){           
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

}else if($option == "autoSuggestion"){
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
}
?>
