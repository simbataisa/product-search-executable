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


//------------------------------------------------------------------------------

$sphinxSearchManger->setResultRange(0,500,500);
$sphinxSearchManger->setIndex("products");

if($option == "byKeyword") {    
    $res = $sphinxSearchManger->search("(@name $key_word) | (@description $key_word)");
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
            $idsToPrint = array();
            for ($counter = 0; $counter < $pageLength; $counter++) {
                $idsToPrint[$counter] = $ids[$counter];
            }
            //var_dump($idsToPrint);
            $total = $res['total'];
            $searchTime = $res['time'];
            $resultProcessor->process_result($idsToPrint,$total,$searchTime,$firstPageReq,$isLastPage);
        }
        //var_dump($res);
    }
}
?>
