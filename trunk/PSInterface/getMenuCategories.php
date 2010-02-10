<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
 require_once("CategoryMenuProcessor.php");
 header ("content-type: text/xml");

 $_categoryProcessor = new CategoryMenuProcessor();
 $_categoryProcessor->getCategories();
?>
