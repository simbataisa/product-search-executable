<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of VisualSearchResultProcessor
 *
 * @author Dao Tuan Anh
 */

require_once("XMLUtil.php");
require_once("dbconnection.php");

class VisualSearchResultProcessor {
    private $_xmlWriter;
    private $_constants;

    public function __construct() {
        $this->_constants = new Constants();
        $this->_xmlWriter = new XMLUtil($this->_constants->xml_element_title_vs_searchresult,
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
                        FROM products p WHERE product_id in ($idStr)
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
     /*----------------------------Color Search -----------------------------*/
    public function createColorSearchXMLTitle(){
        $this->_xmlWriter = new XMLUtil($this->_constants->xml_element_title_cs_searchresult,
                $this->_constants->xml_element_type_products);
    }
}
?>
