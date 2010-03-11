<?php
require_once("dbconnection.php");
header ("content-type: text/xml");
if(isset ($_REQUEST['option'])) {
    $option = $_REQUEST['option'];
}else
    $option = "";

if(isset($_REQUEST['product_id']))
    $product_id = $_REQUEST['product_id'];
else
    $product_id = "";

if(isset ($_REQUEST['rating'])) {
    $rating = $_REQUEST['rating'];
}else
    $rating = 0;

if($option == "doRating") {
    if($rating != 0) {
        $avg_ratingQuery = "SELECT avg_rating FROM products WHERE product_id = $product_id";
        $avg_ratingResSet = mysql_query($avg_ratingQuery);
        
        if($avg_ratingResSet!=null) {
            $r1 = mysql_fetch_array($avg_ratingResSet);
            var_dump($r1);
            $dbRate = floatval($r1['avg_rating']);
            if($dbRate<=0){
                $dbRate = floatval($rating);
            }else{
                $dbRate = ($dbRate+floatval($rating))/2;
            }
            //Do update product rating
            $avg_ratingQuery = "UPDATE products SET avg_rating = $dbRate WHERE product_id = $product_id";
            echo mysql_query($avg_ratingQuery);
            echo $dbRate;
        }
    }

}

?>
