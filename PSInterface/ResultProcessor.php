<?php
require_once("XMLCreator.php");
require_once("dbconnection.php");
class ResultProcessor {

    private $xmlWriter;

    public function __construct() {
        $this->xmlWriter = new XMLCreator(XMLCreator::SEARCHRESULT);
    }

    public function processError($message) {
        $this->xmlWriter = new XMLCreator(XMLCreator::MESSAGE);
        $this->xmlWriter->add("error", $message);
        //$this->xmlWriter->saveFile("dress.xml");
        $this->xmlWriter->save2();

    }
    //=================NEW PROCESS METHOD==============================
    public function process_result($resIds,$tota) {

        $idStr = implode(",",$resIds);
        //$idStr ="338190";

        //echo $idStr;
        //	$sql1 = "SELECT * FROM products p ,images WHERE p.id=product_id AND p.id in (".$idStr.")";

        /*$sql1 = "SELECT p.product_id,p.asin,p.name,p.description,p.detail_page_url,p.highest_retail_price,
			p.lowest_retail_price,p.highest_sale_price,p.lowest_sale_price,p.num_views
				,p.category_id,p.avg_rating,image_type,image_path  
			FROM images i ,(select * from  products where product_id in ($idStr)) as p WHERE product_asin =p.asin
			 order by Field(p.product_id,$idStr)"	;
        */
        $sql1= "SELECT p.product_id,p.asin,p.name,p.description,p.detail_page_url,p.highest_retail_price,
			p.lowest_retail_price,p.highest_sale_price,p.lowest_sale_price,p.num_views
				,p.category_id,p.avg_rating,image_type,image_path  
			FROM images i ,products   as p WHERE product_id in ($idStr) and product_asin =p.asin
			 order by Field(p.product_id,$idStr)"	;

        $sql3 = "SELECT distinct(product_id) FROM images i ,products p WHERE product_asin =asin and p.product_id in ($idStr)" ;
        ///$sql2 = "SELECT image_type,image_path, product_id FROM images WHERE product_id in ($idStr)";
        //	echo $sql1;
        $resPro = mysql_query($sql1);
        $resImage = mysql_query($sql1);
        $resPage = mysql_query($sql3) or die ('Query Failed');
        $total_f =mysql_num_rows($resPage);

        //echo "done get image \n";
        $images = array();
        $count = 0;

        $t_found = $this->xmlWriter->xml->createElement("total_found", $total_f);
        $t_found  = $this->xmlWriter->root->appendChild($t_found);
        $total = $this->xmlWriter->xml->createElement("total",$tota);
        $total = $this->xmlWriter->root->appendChild($total);

        while($r = mysql_fetch_array($resImage)) {
            $images[$count][0] = $r['product_id'];

            //if( $r['image_type'] == "primary_large")
            //{
            $images[$count][1] = $r['image_type'];
            $images[$count][2] = $r['image_path'];
            //}
            //else if( $r['image_type'] == "variant_large")
            //{
            //$images[$count][1] = $r['image_type'];
            //$images[$count][2] = $r['image_path'];
            //}
            //else
            //{
            //$images[$count][1] = "primary_large";
            //$images[$count][2] = "test2.jpg";
            //}


            $count++;
        }


        //var_dump($images);
        $baseURL = "./imageNew1/";

        foreach ($resIds as $product) {

            while($row = mysql_fetch_array($resPro)) {
                if($row['product_id'] == $product) {
                    $this->xmlWriter->reset();
                    $this->xmlWriter->elements['db_id'] = $row['product_id'];
                    $this->xmlWriter->elements['id'] = $row['asin'];
                    $this->xmlWriter->elements['name'] = $row['name'];
                    $this->xmlWriter->elements['description'] = strip_tags($row['description']);
                    $this->xmlWriter->elements['url'] = $row['detail_page_url'];
                    $this->xmlWriter->elements['maxRetail'] = $row['highest_retail_price'];
                    $this->xmlWriter->elements['minRetail'] = $row['lowest_retail_price'];
                    $this->xmlWriter->elements['maxSale'] = $row['highest_sale_price'];
                    $this->xmlWriter->elements['minSale'] = $row['lowest_sale_price'];
                    $this->xmlWriter->elements['numViews'] = $row['num_views'];
                    $this->xmlWriter->elements['category'] = $row['category_id'];
                    $this->xmlWriter->elements['rating'] = $row['avg_rating'];
                    $this->xmlWriter->elements['merchant'] = "amazon";
                    //get images info



                    $c = 0;
                    $p=0;

                    foreach ($images as $image) {
                        if($image[0] == $row['product_id']) {
                            if($image[1] == "primary_large") {
                                $this->xmlWriter->elements['primaryImage'] = $baseURL.$image[2];
                                $p++;
                            }
                            else if($p == 0) {
                                $this->xmlWriter->elements['primaryImage'] = $baseURL.$image[2];

                            }
                            else if($image[1] == "variant_large") {
                                $this->xmlWriter->elements['variantImage'][$c] = $baseURL.$image[2];
                                $c++;
                            }



                        }
                    }




                    $this->xmlWriter->addItem();
                    mysql_data_seek($resPro, 0);
                    break;

                }
            }
            mysql_data_seek($resPro, 0);

        }
        $this->xmlWriter->save2();
        //$this->xmlWriter->saveFile("results.xml");


    }

    //=================RESTRICT THE NUMBER OF TIME ACCESS DB===========
    public function process($res) {


        if ( $res!== false ) {

            if ( isset($res["matches"]) && is_array($res["matches"]) ) {

                //$total = $this->xmlWriter->xml->createElement("total", $res['total']);
                //$total = $this->xmlWriter->root->appendChild($total);
                //$total = $this->xmlWriter->xml->createElement("total_found", $res['total_found']);
                //$total = $this->xmlWriter->root->appendChild($total);
                $time = $this->xmlWriter->xml->createElement("searchTime", $res['time']);
                $time = $this->xmlWriter->root->appendChild($time);

                $ids = array();
                foreach($res["matches"] as $docinfo) {
                    array_push($ids, $docinfo['id']);
                }

                //	$this->processError("No Match Found" . $idss);
                $this->process_result($ids,$res['total']);
                //echo "ee".$ids;
            }

        }


    }
    public function test_process($res) {


        if ( $res!== false ) {

            if ( isset($res["matches"]) && is_array($res["matches"]) ) {

                //$total = $this->xmlWriter->xml->createElement("total", $res['total']);
                //$total = $this->xmlWriter->root->appendChild($total);
                //$total = $this->xmlWriter->xml->createElement("total_found", $res['total_found']);
                //$total = $this->xmlWriter->root->appendChild($total);
                $time = $this->xmlWriter->xml->createElement("searchTime", $res['time']);
                //$time = $this->xmlWriter->root->appendChild($time);

                echo $time;
            }

        }


    }

    //=======================VISUAL SEARCH=============================
    public function processVSresult($res,$total,$Qtime) {
        //$resIds = preg_split("/\s/",$res);
        if($res == -1) {
            $this->processError("Image Server not Started " .$res);
        }
        else {
            $resIds = explode(" ",trim($res));
            //echo count($resIds);
            //echo $total;



            if($resIds && $total!=0) {
                //$this->processError("No Match Found1 =" .$res);

                //$total = $this->xmlWriter->xml->createElement("total", count($resIds));
                //	$total = $this->xmlWriter->root->appendChild($total);
                //$total = $this->xmlWriter->xml->createElement("total_found", count($resIds));
                //$total = $this->xmlWriter->root->appendChild($total);
                $time = $this->xmlWriter->xml->createElement("searchTime", $Qtime);
                $time = $this->xmlWriter->root->appendChild($time);


                $this->process_result($resIds,$total);
                //	$this->processError("No Match Found" .count($resIds));
                //$this->processError("No Match Found" .$res);

            }


            else
                $this->processError("No Match Found");
        }

    }




    private function getResults($ids) {

        foreach ( $ids as $docid ) {
            //echo "for id = ".$docid."get info \n";
            $sql = "SELECT * FROM products where id = $docid";
            $r = mysql_query($sql);
            if(!$r) {
                //die("Invalid query : ".mysql_error());
            }
            else {

                $row = mysql_fetch_array($r);
                $this->xmlWriter->reset();
                $this->xmlWriter->elements['db_id'] = $row['id'];
                $this->xmlWriter->elements['id'] = $row['identifier'];
                $this->xmlWriter->elements['name'] = $row['name'];
                $this->xmlWriter->elements['description'] = $row['description'];
                $this->xmlWriter->elements['url'] = $row['url'];
                $this->xmlWriter->elements['maxRetail'] = $row['highest_retail_price'];
                $this->xmlWriter->elements['minRetail'] = $row['lowest_retail_price'];
                $this->xmlWriter->elements['maxSale'] = $row['highest_sale_price'];
                $this->xmlWriter->elements['minSale'] = $row['lowest_sale_price'];
                $this->xmlWriter->elements['numViews'] = $row['num_views'];
                $this->xmlWriter->elements['category'] = $row['category_id'];
                $this->xmlWriter->elements['rating'] = $row['average_rating'];
                $merchant_id = $row['merchant_id'];
                //get images && get feature set
                $sql = "SELECT image_type,image_path FROM images WHERE product_id = $docid";
                $r = mysql_query($sql);
                if(!$r) {
                    //die("Invalid query : ".mysql_error());
                }
                else {
                    $c = 0;

                    while($row = @mysql_fetch_array($r)) {
                        $baseURL = "./images/";
                        if($row['image_type']=="primary_large")
                            $this->xmlWriter->elements['primaryImage'] = $baseURL.$row['image_path'];
                        else if($row['image_type'] =="variant_large") {
                            $this->xmlWriter->elements['variantImage'][$c] = $baseURL.$row['image_path'];
                            $c++;
                        }
                        /*
							if(isset($row['feature_set']) && $row['feature_set']!=""){
								$this->xmlWriter->elements['feature'] = $row['feature_set'];
								
							}
                        */
                    }
                }


                if($merchant_id!=null && $merchant_id!="") {
                    $sql = "SELECT name FROM merchants where id = ".$merchant_id;
                    $r = mysql_query($sql);
                    if(!$r) {
                        die("Invalid query 1: ".mysql_error());
                    }
                    else {
                        $row=mysql_fetch_array($r);
                        $this->xmlWriter->elements['merchant']=$row['name'];
                    }
                }
                $this->xmlWriter->addItem();
            }

        }
        //$this->xmlWriter->saveFile("dress.xml");

        //	echo $this->xmlWriter->save2();


    }

    /*
	 
	 		private function getSphxResults($res){
			foreach ($res["matches"] as $docinfo){
				$this->xmlWriter->reset();
				$this->xmlWriter->reset();
				$this->xmlWriter->elements['db_id'] = $docinfo['id'];
				$this->xmlWriter->elements['id'] = $docinfo['fields']['identifier']; 
				$this->xmlWriter->elements['name'] = $docinfo['fields']['name'];
				$this->xmlWriter->elements['description'] = $docinfo['fields']['description'];
				$this->xmlWriter->elements['url'] = $docinfo['fields']['url'];
				$this->xmlWriter->elements['maxRetail'] = $docinfo['attrs']['highest_retail_price'];
				$this->xmlWriter->elements['minRetail'] = $docinfo['attrs']['lowest_retail_price'];
				$this->xmlWriter->elements['maxSale'] = $docinfo['attrs']['highest_sale_price'];
				$this->xmlWriter->elements['minSale'] = $docinfo['attrs']['lowest_sale_price'];
				$this->xmlWriter->elements['numViews'] = $docinfo['attrs']['num_views'];
				$this->xmlWriter->elements['category'] = $docinfo['attrs']['category_id'];
				$merchant_id = $docinfo['attrs']['merchant_id'];				
				$sql = "SELECT image_type,image_path FROM images WHERE product_id = ".$docinfo['id'];
				$r = mysql_query($sql);
				if(!$r){
							//die("Invalid query : ".mysql_error());
				}
				else{
					$c = 0;
					
					while($row = @mysql_fetch_array($r)){
						$baseURL = "./images/";
						if($row['image_type']=="primary_large")
							$this->xmlWriter->elements['primaryImage'] = $baseURL.$row['image_path'];
						else if($row['image_type'] =="variant_large"){
							$this->xmlWriter->elements['variantImage'][$c] = $baseURL.$row['image_path'];
								$c++;
						}
	
					}
				}
	
						
				if($merchant_id!=null && $merchant_id!=""){
					$sql = "SELECT name FROM merchants where id = ".$merchant_id;
					$r = mysql_query($sql);
					if(!$r){
						die("Invalid query 1: ".mysql_error());
					}
					else{
						$row=mysql_fetch_array($r);
						$this->xmlWriter->elements['merchant']=$row['name'];				
					}						
				}
				$this->xmlWriter->addItem();
							
			}
			echo $this->xmlWriter->save2();
			
		}
	 * 
    */
}

?>
