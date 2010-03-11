<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

/**
 * Description of KeywordProcessor
 *
 * @author Dao Tuan Anh
 */
require_once("XMLUtil.php");
require_once("dbconnection.php");
require_once("Constants.php");

class KeywordProcessor {
    private $_xmlWriter;
    private $_constants;

    public function KeywordProcessor() {
        $this->_constants = new Constants();
        $this->_xmlWriter = new XMLUtil($this->_constants->xml_element_title_keywordgeneration,
                $this->_constants->xml_element_type_keywords);

    }
    public function generateKeyword($key_word) {
        $keywordQuery = "SELECT keyword, search_result, popularity
                     FROM popular_keyword WHERE keyword LIKE '". $key_word ."%'
                     ORDER BY popularity DESC LIMIT 15";
        $keywordResSet = mysql_query($keywordQuery);

        while($r = mysql_fetch_array($keywordResSet)) {
            $this->_xmlWriter->resetElements();
            $this->_xmlWriter->addElement('name', $r["keyword"]);
            $this->_xmlWriter->addElement('hex', $r['search_result']);
            //$attr = array("name"=>$r["keyword"], "search_result"=>$r["search_result"]);
            //var_dump($r["keyword"]);

            //$keyword = $this->_xmlWriter->addNode($this->_constants->xml_element_type_keywords_keyword, null, $attr);
            //$keyword = $this->_xmlWriter->appendChildToTypeNode($keyword);
            $this->_xmlWriter->addItem1("keyword");
        }
        
        print $this->_xmlWriter->save();
        

    }
}
?>
