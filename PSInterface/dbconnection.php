<?php
$host = 'localhost';
$user = 'root';
$pass = '123';
$db = 'psnew1';
$conn = mysql_connect($host,$user,$pass);
if(!$conn)
    die("not connected : ".mysql_error());
mysql_select_db($db, $conn);
/*
$host = 'localhost';
$user = 'root';
$pass = '123456';
$db = 'product_search';
$conn = mysql_connect($host,$user,$pass);
if(!$conn)
    die("not connected : ".mysql_error());
mysql_select_db($db, $conn);*/
?>
