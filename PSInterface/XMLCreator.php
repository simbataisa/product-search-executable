<?php
class XMLCreator {
    public $xml;
    public $root;
    public $type;
    public $elements;

    const SEARCHRESULT = 'search results';
    const CATEGORY = 'product categories';
    const ACCOUNT = 'user account';
    const MESSAGE = 'message';
    const MERCHANT = 'merchant';

    function __construct($type) {

        $this->elements = array();

        $this->xml = new DomDocument('1.0', 'UTF-8');
        $this->xml->formatOutput = true;

        $this->root =  $this->xml->createElement('results');
        $this->root =  $this->xml->appendChild($this->root);
        $title = $this->xml->createElement('title', $type);
        $title = $this->root->appendChild($title);
        if($type == self::SEARCHRESULT)
            $this->type = $this->xml->createElement('products');
        else if($type==self::CATEGORY)
            $this->type = $this->xml->createElement('categories');
        else if($type== self::ACCOUNT)
            $this->type = $this->xml->createElement('account');
        else if($type == self::MESSAGE)
            $this->type = $this->xml->createElement('messages');
        else if($type == self::MERCHANT)
            $this->type = $this->xml->createElement('merchants');
        $this->type = $this->root->appendChild($this->type);
        //print_r($this->xml);

    }
    public function addItem() {
        //$this->xml = new DomDocument('1.0', 'UTF-8');
        //echo "in test item";
        $itemEle = $this->xml->createElement('item');
        $itemEle = $this->type->appendChild($itemEle);
        //$field = $this->xml->createElement($name, $value);
        //$field = $itemEle->appendChild($field);

        $keys = array_keys($this->elements);
        foreach($keys as $key) {
            if($key == "variantImage") {
                foreach($this->elements["variantImage"] as $img) {
                    $field = $this->xml->createElement($key, $img);
                    $field = $itemEle->appendChild($field);
                }
            }
            else {
                $field = $this->xml->createElement($key);
                $field->appendChild($this->xml->createTextNode($this->elements[$key]));
                $field = $itemEle->appendChild($field);
            }
        }

        //var_dump($this->xml);
        //var_dump($this->xml->saveXML());
    }
    public function add($nodeName, $category) {
        $cat = $this->xml->createElement($nodeName);
        $cat->appendChild($this->xml->createTextNode($category));
        $cat = $this->type->appendChild($cat);
    }

    public function addNode($nodeName, $value, $attr) {
        if($value==null)
            $node = $this->xml->createElement($nodeName);
        else {
            $node = $this->xml->createElement($nodeName);
            $node->appendChild($this->xml->createTextNode($value));
        }
        foreach ($attr as $key => $val) {
            $attribute = new DOMAttr($key, $val);
            //$attribute->value = $val;
            $node->setAttributeNode($attribute);
        }
        $node = $this->type->appendChild($node);
        return $node;
    }

    public function addToNode($target, $child, $value, $attr) {
        if($value==null)
            $node = $this->xml->createElement($child);
        else
            $node = $this->xml->createElement($child, $value);

        foreach ($attr as $key => $val) {
            $attribute = new DOMAttr($key, $val);
            //$attribute->value = $val;
            $node->setAttributeNode($attribute);
        }
        $node = $target->appendChild($node);
    }

    public function reset() {
        unset($this->elements);
    }

    public function save() {
        //$this->xml->save("results.xml");
        print $this->xml->saveXML();
        //$this->xml->save("results.xml");
    }

    public function save2() {
        $result ="";
        foreach ($this->xml->childNodes as $node) {
            $result.= $this->xml->saveXML($node);
        }
        echo $result;
    }
    
    public function saveFile($file) {
        echo $this->xml->save($file);
    }

    public function setMessage($mess, $type) {
        $message = $this->xml->createElement("message");
        $message = $this->xml->appendChild($message);
        $mesType = $this->xml->createElement($type, $mess);
        $mesType = $message->appendChild($mesType);
    }


}
?>
