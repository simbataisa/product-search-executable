<?php
	require_once("CategoryProcessor.php");
	
	$processor = new CategoryProcessor();
	if(isset($_POST['opt'])&&$_POST['opt']!=null)
		$opt = $_POST['opt'];
		
		$opt = "get2level";
	if(isset($_POST['id'])&& $_POST['id']!=null)
		$id = $_POST['id'];
	else
		$id = -1;

	if($opt == "check")	{
		$processor->exploreCategory($id);
	}
	else if($opt=="remove"){
		//echo "here";
		$processor->remove($id);
	}
	else if($opt=="add"){
		if(isset($_POST['name']))
			$name = $_POST['name'];
		if(isset($_POST['parent']))
			$parent = $_POST['parent'];
		$processor->addCategory($name, $parent);
	}
	else if($opt=="change"){
		
	}
	else if($opt == "get"){
		$processor->getCategories();	
	}
	
	else if($opt == "get2level"){
		//echo $opt;	
		$processor->get2LevelCategories();		
	}
	/*
	else
		$processor->getCategories();
	*/
	
?>
