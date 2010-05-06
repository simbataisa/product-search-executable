<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

/**
 * Description of CategoryMenuProcessor
 *
 * @author Dao Tuan Anh
 */
require_once("XMLUtil.php");
require_once("dbconnection.php");
require_once("SearchController.php");
require_once("Constants.php");

class CategoryMenuProcessor {

    private $_xmlWriter;    
    private $_constants;

    public function CategoryMenuProcessor() {
        $this->_constants = new Constants();
        $this->_xmlWriter = new XMLUtil($this->_constants->xml_element_title_category,
               $this->_constants->xml_element_type_categories);

    }
    public function getCategories() {
        $query = "SELECT id, search_index FROM amazon WHERE crawled = 1";
        
        $res = mysql_query($query);
        while($r = mysql_fetch_array($res)){                       
            $attr = array("id"=>$r["id"], "label"=>$r["search_index"]);
            $category = $this->_xmlWriter->addNode($this->_constants->xml_element_type_categories_category, null, $attr);
            $category = $this->_xmlWriter->appendChildToTypeNode($category);
            $level1Query = "";
            if ($r["id"] == "35") { //Watches
                $level1Query = "SELECT category_id, name FROM test_sub_categories WHERE category_id IN (2039,2041,2049) ORDER BY name";
            }else if($r["id"] == "2"){ //Automotive
                 $level1Query = "SELECT category_id, name FROM test_sub_categories WHERE category_id IN (5325,5303,2664,3010,3676,5939,2930,2853,2787) ORDER BY name";
            }else if($r["id"] == "4"){ //Beauty
                 $level1Query = "SELECT category_id, name FROM test_sub_categories WHERE category_id IN (891,919,1025,872,884,973,1003) ORDER BY name";
            }else if($r["id"] == "12"){ //HealthPersonalCare
                 $level1Query = "SELECT category_id, name FROM test_sub_categories WHERE category_id IN (7988,7963,8414,9230) ORDER BY name";
            }else{
               $level1Query = "SELECT category_id, name FROM test_sub_categories WHERE amazon_id = ".$r["id"]." AND category_level = 1";
            }
            
            //echo $level1Query;
            $resLevel1 = mysql_query($level1Query);
            while($rowLevel1 = mysql_fetch_array($resLevel1)){
                $attrLevel1 = array("id"=>utf8_encode($rowLevel1["category_id"]), "label"=>utf8_encode($rowLevel1["name"]));
                $leve1node = $this->_xmlWriter->addNode($this->_constants->xml_element_type_categories_category_node, null, $attrLevel1);
                $leve1node = $this->_xmlWriter->addToNode($category,$leve1node);
                if($r["id"] == "1"){ //Apparel
                    $level2Query = "SELECT category_id, name FROM test_sub_categories WHERE amazon_id = ".$r["id"]." AND category_level = 2 AND parent = ".$rowLevel1["category_id"];
                    $resLevel2 = mysql_query($level2Query);
                    while($rowLevel2 = mysql_fetch_array($resLevel2)){
                        $attrLevel2 = array("id"=>utf8_encode($rowLevel2["category_id"]), "label"=>utf8_encode($rowLevel2["name"]));
                        $leve2node = $this->_xmlWriter->addNode("node_lvl_2", null, $attrLevel2);
                        $leve2node = $this->_xmlWriter->addToNode($leve1node,$leve2node);
                    }
                }
            }
            
        }
        print $this->_xmlWriter->save();

    }
}
?>
