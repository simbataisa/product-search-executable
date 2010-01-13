<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

/**
 * Description of TextSearchResultProcessor
 *
 * @author Dao Tuan Anh
 */

require_once("XMLUtil.php");
require_once("dbconnection.php");

class TextSearchResultProcessor {

    private $_xmlWriter;
    private $_constants;

    public function __construct() {
        $this->_constants = new Constants();
        $this->_xmlWriter = new XMLUtil($this->_constants->xml_element_title_searchresult,
                $this->_constants->xml_element_type_products);
    }

    public function processError($input) {
        $totalNode = $this->_xmlWriter->createElement2($this->_constants->xml_element_total,$input);
        $this->_xmlWriter->appendChildToRootNode($totalNode);
        print $this->_xmlWriter->save();

    }
    
    public function process_result($resIds,$total,$searchTime,$firstPageReq,$finished) {
        $totalNode = $this->_xmlWriter->createElement2($this->_constants->xml_element_total,$total);
        $this->_xmlWriter->appendChildToRootNode($totalNode);
        $timeNode = $this->_xmlWriter->createElement2("searchTime", $searchTime);
        $this->_xmlWriter->appendChildToRootNode($timeNode);

        $firstPageNode = $this->_xmlWriter->createElement2("firstPage", $firstPageReq);
        $this->_xmlWriter->appendChildToRootNode($firstPageNode);
        $finishedNode = $this->_xmlWriter->createElement2("finished", $finished);
        $this->_xmlWriter->appendChildToRootNode($finishedNode);


        $idStr = implode(",",$resIds);
        //echo $idStr;
        //print $this->_xmlWriter->save();
        $productQuery= "SELECT p.product_id,p.asin,p.name,p.description,p.detail_page_url,
                        p.highest_retail_price,p.lowest_retail_price,p.highest_sale_price,
                        p.lowest_sale_price,p.num_views,p.category_id,p.avg_rating,
                        p.merchant_id,p.search_index
                        FROM products p WHERE product_id IN ($idStr)
                        ORDER BY Field(p.product_id,$idStr)";
        $productResultSet = mysql_query($productQuery);
        while($row = mysql_fetch_array($productResultSet)) {


                $this->_xmlWriter->resetElements();
                $this->_xmlWriter->addElement('product_id', $row['product_id']);
                $this->_xmlWriter->addElement('asin', $row['asin']);
                $this->_xmlWriter->addElement('name',$row['name']);
                $this->_xmlWriter->addElement('minRetail',$row['lowest_retail_price']);
                $this->_xmlWriter->addElement('maxRetail',$row['highest_retail_price']);
                $this->_xmlWriter->addElement('minSale',$row['lowest_sale_price']);
                $this->_xmlWriter->addElement('maxSale',$row['highest_sale_price']);
                $this->_xmlWriter->addElement('description',strip_tags($row['description']));
                $this->_xmlWriter->addElement('avg_rating',$row['avg_rating']);
                $this->_xmlWriter->addElement('url',$row['detail_page_url']);
                $this->_xmlWriter->addElement('num_views',$row['num_views']);
                $this->_xmlWriter->addElement('category_id',$row['category_id']);
                $this->_xmlWriter->addElement('merchant_id',$row["merchant_id"]);
                $this->_xmlWriter->addElement('merchant_id',$row["merchant_id"]);
                $this->_xmlWriter->addElement('search_index',$row["search_index"]);

                //get images info
                $imageQuery = "SELECT image_path, image_type FROM images WHERE product_asin = '".$row['asin']."'";
                $imageResultSet = mysql_query($imageQuery);
                while($imageRow = mysql_fetch_array($imageResultSet)) {
                    if($imageRow['image_type'] == "primary_large"){
                        $this->_xmlWriter->addElement('primaryImage',$imageRow['image_path']);
                    }else if($imageRow['image_type'] == "variant_large"){
                        $this->_xmlWriter->addElement('variantImage',$imageRow['image_path']);
                    }
                }
                $this->_xmlWriter->addItem();
                
        }
        print $this->_xmlWriter->save();
       
    }

    /*----------------------------Auto Suggestion-----------------------------*/
    public function createAutoSuggestXMLTitle(){
        $this->_xmlWriter = new XMLUtil($this->_constants->xml_element_title_autosuggesstion,
                $this->_constants->xml_element_type_products);
    }
    /*----------------------------Color Search -----------------------------*/
    public function createColorSearchXMLTitle(){
        $this->_xmlWriter = new XMLUtil($this->_constants->xml_element_title_cs_searchresult,
                $this->_constants->xml_element_type_products);
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

                //$total = $this->_xmlWriter->xml->createElement("total", count($resIds));
                //	$total = $this->_xmlWriter->root->appendChild($total);
                //$total = $this->_xmlWriter->xml->createElement("total_found", count($resIds));
                //$total = $this->_xmlWriter->root->appendChild($total);
                $time = $this->_xmlWriter->xml->createElement("searchTime", $Qtime);
                $time = $this->_xmlWriter->root->appendChild($time);


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
                $this->_xmlWriter->reset();
                $this->_xmlWriter->elements['db_id'] = $row['id'];
                $this->_xmlWriter->elements['id'] = $row['identifier'];
                $this->_xmlWriter->elements['name'] = $row['name'];
                $this->_xmlWriter->elements['description'] = $row['description'];
                $this->_xmlWriter->elements['url'] = $row['url'];
                $this->_xmlWriter->elements['maxRetail'] = $row['highest_retail_price'];
                $this->_xmlWriter->elements['minRetail'] = $row['lowest_retail_price'];
                $this->_xmlWriter->elements['maxSale'] = $row['highest_sale_price'];
                $this->_xmlWriter->elements['minSale'] = $row['lowest_sale_price'];
                $this->_xmlWriter->elements['numViews'] = $row['num_views'];
                $this->_xmlWriter->elements['category'] = $row['category_id'];
                $this->_xmlWriter->elements['rating'] = $row['average_rating'];
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
                            $this->_xmlWriter->elements['primaryImage'] = $baseURL.$row['image_path'];
                        else if($row['image_type'] =="variant_large") {
                            $this->_xmlWriter->elements['variantImage'][$c] = $baseURL.$row['image_path'];
                            $c++;
                        }
                        /*
							if(isset($row['feature_set']) && $row['feature_set']!=""){
								$this->_xmlWriter->elements['feature'] = $row['feature_set'];

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
                        $this->_xmlWriter->elements['merchant']=$row['name'];
                    }
                }
                $this->_xmlWriter->addItem();
            }

        }
        //$this->_xmlWriter->saveFile("dress.xml");

        //	echo $this->_xmlWriter->save2();


    }

    /*

	 		private function getSphxResults($res){
			foreach ($res["matches"] as $docinfo){
				$this->_xmlWriter->reset();
				$this->_xmlWriter->reset();
				$this->_xmlWriter->elements['db_id'] = $docinfo['id'];
				$this->_xmlWriter->elements['id'] = $docinfo['fields']['identifier'];
				$this->_xmlWriter->elements['name'] = $docinfo['fields']['name'];
				$this->_xmlWriter->elements['description'] = $docinfo['fields']['description'];
				$this->_xmlWriter->elements['url'] = $docinfo['fields']['url'];
				$this->_xmlWriter->elements['maxRetail'] = $docinfo['attrs']['highest_retail_price'];
				$this->_xmlWriter->elements['minRetail'] = $docinfo['attrs']['lowest_retail_price'];
				$this->_xmlWriter->elements['maxSale'] = $docinfo['attrs']['highest_sale_price'];
				$this->_xmlWriter->elements['minSale'] = $docinfo['attrs']['lowest_sale_price'];
				$this->_xmlWriter->elements['numViews'] = $docinfo['attrs']['num_views'];
				$this->_xmlWriter->elements['category'] = $docinfo['attrs']['category_id'];
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
							$this->_xmlWriter->elements['primaryImage'] = $baseURL.$row['image_path'];
						else if($row['image_type'] =="variant_large"){
							$this->_xmlWriter->elements['variantImage'][$c] = $baseURL.$row['image_path'];
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
						$this->_xmlWriter->elements['merchant']=$row['name'];
					}
				}
				$this->_xmlWriter->addItem();

			}
			echo $this->_xmlWriter->save2();

		}
	 *
    */
}
?>
