<?php
//Include File
require_once('SphinxSearchManager.php');
require_once("dbconnection.php");
require_once("KeywordProcessor.php");

session_start();
$sphinxSearchManger = new SphinxSearchManager();




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



if(isset ($_REQUEST['key_word'])) {
    $key_word = $_REQUEST['key_word'];
}else
    $key_word = "";


$total = "";
//------------------------------------------------------------------------------
if($option == "updateKeyword") {
    $sphinxSearchManger->setIndex("product");    
//$res = $sphinxSearchManger->search("(@name $key_word) | (@description $key_word)");
    $sphinxSearchManger->setResultRange(0,1000,1000);

    $res = $sphinxSearchManger->search("(@name $key_word)");
//Getting total result first

    if(intval($res["total"]) > 0) {
        $total = $res["total"];
        $keywordQuery = "SELECT id, keyword, hashed_keyword, popularity, category_id, search_result
                    FROM popular_keyword WHERE keyword = '". trim($key_word) ."'";
        $keywordResSet = mysql_query($keywordQuery);
        $existingKeyword = mysql_num_rows($keywordResSet);
        //echo $existingKeyword;
        if(intval($existingKeyword) == 1) {
            //Updating the table

            $keywordQuery = "UPDATE popular_keyword SET popularity = popularity+1,
                        search_result = $total WHERE keyword = '". trim($key_word) ."'";
            $keywordResSet = mysql_query($keywordQuery);
            //echo $keywordResSet;
        }else {
            //Insert new keyword

            $keywordQuery = "INSERT INTO popular_keyword (keyword, popularity, search_result)
                        VALUES('".$key_word."', 1, $total)";
            $keywordResSet = mysql_query($keywordQuery);
        }

    }else {

    }
}else if($option == "generateKeyword") {
    header ("content-type: text/xml");
    $keywordProcessor = new KeywordProcessor();
    $keywordProcessor->generateKeyword($key_word);
}

?>
