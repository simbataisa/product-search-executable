<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

/**
 * Description of XMLUtil
 *
 * @author Dao Tuan Anh
 */
require_once("dbconnection.php");
require_once("Constants.php");

class XMLUtil {


    private $_constants;
    private $_xml;
    private $_root;
    private $_type;
    private $_elements;

    function XMLUtil($type, $childNode) {
        $this->_constants  = new Constants();

        $this->_elements = array();
        $this->_xml = new DomDocument('1.0', 'UTF-8');
        $this->_xml->formatOutput = true;

        $this->_root =  $this->_xml->createElement('results');
        $this->_root =  $this->_xml->appendChild($this->_root);
        $title = $this->_xml->createElement($this->_constants->xml_element_title,$type);
        $title = $this->_root->appendChild($title);

        $this->_type = $this->_xml->createElement($childNode);
        $this->_type = $this->_root->appendChild($this->_type);
    }

    public function createElement2(/*String*/$name,/*String*/$value) {
        return $this->_xml->createElement($name,$value);
    }
    public function createElement1(/*String*/$name) {
        return $this->_xml->createElement($name);
    }
    public function appendChildToRootNode(/*XML element*/$e) {
        return $this->_root->appendChild($e);
    }
    public function appendChildToTypeNode(/*XML Element*/$e) {
        return $this->_type->appendChild($e);
    }
    public function addNode($nodeName, $value, $attr) {
        if($value==null)
            $node = $this->_xml->createElement($nodeName);
        else {
            $node = $this->_xml->createElement($nodeName);
            $node->appendChild($this->_xml->createTextNode($value));
        }
        foreach ($attr as $key => $val) {
            $attribute = new DOMAttr($key, $val);
            //$attribute->value = $val;
            $node->setAttributeNode($attribute);
        }
        return $node;
    }

    public function addToNode($target, $childNode) {
        return $target->appendChild($childNode);
    }
    public function addItem() {
        //$this->xml = new DomDocument('1.0', 'UTF-8');
        //echo "in test item";
        $itemEle = $this->_xml->createElement('item');
        $itemEle = $this->_type->appendChild($itemEle);
        //$field = $this->xml->createElement($name, $value);
        //$field = $itemEle->appendChild($field);

        $keys = array_keys($this->_elements);
        foreach($keys as $key) {
            if($key == "variantImage") {
                if(is_array($this->_elements["variantImage"])) {
                    foreach($this->_elements["variantImage"] as $img) {
                        $field = $this->createElement2($key, $img);
                        $field = $itemEle->appendChild($field);
                    }
                }else{
                    $field = $this->createElement2($key, $this->_elements["variantImage"]);
                    $field = $itemEle->appendChild($field);
                }


            }
            else {
                $field = $this->createElement1($key);
                $field->appendChild($this->_xml->createTextNode($this->_elements[$key]));
                $field = $itemEle->appendChild($field);
            }
        }

        //var_dump($this->xml);
        //var_dump($this->xml->saveXML());
    }
    public function save() {
        return $this->_xml->saveXML();
    }
    public function addElement($key, $value) {
        $this->_elements[$key] = $value;
    }
    public function resetElements() {
        unset($this->_elements);
    }
}
?>
