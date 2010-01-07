<?php
	require_once("dbconnection.php");
	require_once("XMLCreator.php");
	$sql = "SELECT name, id FROM merchants";
	$xml = new XMLCreator(XMLCreator::MERCHANT);
	$res = mysql_query($sql);
	if($r = mysql_fetch_array($res)){
		$attr = array("id"=>$r['id']);
		$xml->addNode('merchant', $r['name'], $attr);
		
	}
	echo $xml->save2();
	//$xml->saveFile("../Model/test.xml");

?>
