<?php
	require_once("AccountManager.php");

	if(isset($_POST['user']))
		$user = $_POST['user'];
	if(isset($_POST['pass']))
		$pass = $_POST['pass'];
		
	$manager = new AccountManager();
	$manager->login($user,$pass);
	//$sql = "SELECT password, admin FROM accounts WHERE user = '$user'";
	/*
	$res = mysql_query($sql);
	if($res){
		$data = mysql_fetch_array($res);
		if($pass == $data['password'])	
			echo "success";
		else 
			echo "
	}
	else 
		echo "fail";*/
?>
