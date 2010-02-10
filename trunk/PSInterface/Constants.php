<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of publicants
 *
 * @author Dao Tuan Anh
 */
class Constants {

    public $xml_element_title = 'title';
    public $xml_element_title_searchresult = 'search results';
    public $xml_element_title_vs_searchresult = 'vssearch results';
    public $xml_element_title_cs_searchresult = 'cssearch results';
    public $xml_element_title_us_searchresult = 'ussearch results';
    public $xml_element_title_category = 'product categories';
    public $xml_element_title_account = 'user account';
    public $xml_element_title_autosuggesstion = 'search suggestion';
    public $xml_element_title_keywordgeneration = 'keyword generation';


    public $xml_element_type_categories = 'categories';
    public $xml_element_type_categories_category = 'category';
    public $xml_element_type_categories_category_node = 'node';

    public $xml_element_type_products = 'products';
    public $xml_element_type_product = 'item';

    public $xml_element_type_keywords = 'keywords';
    public $xml_element_type_keywords_keyword = 'keyword';

    public $xml_element_iscomplete = 'isComplete';
    public $xml_element_total = 'total';


    public $image_server_host = "localhost";
    public $image_server_port = 9000;

    function Constants(){
        
    }
}
?>
