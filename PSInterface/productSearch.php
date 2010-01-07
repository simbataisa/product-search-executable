<?php
//Include File
require_once ('SphinxSearchManager.php');
require_once('ResultProcessor.php');

session_start();
$sphinxSearchManger = new  SphinxSearchManager();
$resultProcessor = new ResultProcessor();

/* 
 * Getting request parameters
*/
if(isset ($_POST['opt'])) {
    $option = $_POST['opt'];
}else
    $option = "";

if(isset ($_POST['pageLength'])) {
    $pageLength = $_POST['pageLength'];
}else
    $pageLength = 20;

if(isset ($_POST['search_index'])) {
    $search_index = $_POST['search_index'];
}else
    $search_index = "";

if(isset ($_POST['key_word'])) {
    $key_word = $_POST['key_word'];
}else
    $key_word = "";

if(isset ($_POST['startIndex'])) {
    $startIndex = $_POST['startIndex'];
}else
    $startIndex = 0;

if(isset ($_POST['stopIndex'])) {
    $stopIndex = $_POST['stopIndex'];
}else
    $stopIndex = 0;

if(isset ($_POST['firstPageReq'])) {
    $firstPageReq = $_POST['firstPageReq'];
}else
    $firstPageReq = "";

//------------------------------------------------------------------------------
if(strcmp ($option, "byKeyword") == 0) {
    //Getting total result first
    //$total =
}

$sphinxSearchManger->setResultRange(0,60);
$sphinxSearchManger->setIndex("products");
$res = $sphinxSearchManger->search($key_word);
if($res === false) {
    $resultProcessor->processError($sphinxSearchManger->cl->GetLastError());
}
else if(!isset($res["matches"])) {
    $resultProcessor->processError("No matches found");
}
else {
    //$resultProcessor->process($res);
    //$resultProcessor->test_process($res);
    $resultProcessor->process($res);

    if (is_array($res["matches"]) ) {

        //$total = $this->xmlWriter->xml->createElement("total", $res['total']);
        //$total = $this->xmlWriter->root->appendChild($total);
        //$total = $this->xmlWriter->xml->createElement("total_found", $res['total_found']);
        //$total = $this->xmlWriter->root->appendChild($total);
        //$time = $this->xmlWriter->xml->createElement("searchTime", $res['time']);
        //$time = $this->xmlWriter->root->appendChild($time);

        $ids = array();
        foreach($res["matches"] as $docinfo) {
            array_push($ids, $docinfo['id']);
        }

        //	$this->processError("No Match Found" . $idss);
        //$this->process_result($ids,$res['total']);
        //echo "ee".$ids;
         echo $res['total_found'];
    }

}
?>
