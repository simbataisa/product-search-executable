<?php
	//require_once('SearchController.php');
	//require_once('ResultProcessor.php');
	define('ALLOWED_FILENAMES','jpg|jpeg|gif|png');
	define('IMAGE_DIR','./images/');
	require_once("dbconnection.php");
		if(!isset($_REQUEST['url']))
		$_REQUEST['url']="http://i165.photobucket.com/albums/u54/creexs/imahe-nasyon.jpg";

$message="OK";
	if(!preg_match('#^http://.*([^/]+\.('.ALLOWED_FILENAMES.'))$#',$_REQUEST['url'],$m)){
		$message = "Error: File type mismatched ";
	}
	if(!$img=file_get_contents($_REQUEST['url']))
	$message = "Error: File cant get ";
	if(!$f=fopen(IMAGE_DIR.'/upload.jpg','w'))
	$message = "Error: File open failed! ";
	chmod("./images/upload.jpg" ,0777);
	if(fwrite($f,$img)===FALSE)
	$message = "Error: File cant write ";
	fclose($f);
	
	
	$file = fopen("./images/exp.txt","w+"	);
	fwrite ($file, "images/upload.jpg\n");
    fclose($file);
    chmod("./images/exp.txt" ,0777);
	$last = exec("./extractFeatures ./images/exp.txt",$returnvar);
	
	echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
	//echo "<status><text>".$_REQUEST['url']."</text></status>";
	echo "<upload><item><url>Controller/images/upload.jpg</url> <feature>-1</feature><status>".$message."</status></item></upload>";
//	echo "text=".$_REQUEST['url'];
	$filename = "testout.txt";
	$handle = fopen($filename, 'w+');
	fwrite($handle,"text=".$_REQUEST['url']);
	
	/*
	if(is_array($argv)){
		$q=$argv[1];
		$page=(int)$argv[2];
		$length=(int)$argv[3];
		echo "----";
	}
	* 
	else{
	$q = "dress";
	//$q="dress";
	//if($_POST['page']!= null) $page = (int)$_POST['page']; else $page = 0;
	//if($_POST['pageLength']!= null) $length = (int)$_POST['pageLength']; else $length = 20;
	
	//if($page!=null && $length != null)
	//	$searcher->setResultRange($page*$length, $length);
	//else 
	$page = 0;
	$length=20;
		echo "--21212";
	//}
	echo "query = $q, page = $page, length=$length";
	$searcher = new SearchController();
	$searcher->setResultRange($page*$length,$length);
	$processor = new ResultProcessor();
	//$xmlCreator = new ResultCreator();
	
	$res = $searcher->search($q);
	$processor->process($res);
	//echo "testing";
$sql1 = "SELECT product_id FROM products p limit 0,10" ;

			$resPro = mysql_query($sql1);
			
	
	while($row = mysql_fetch_array($resPro)){
		echo $row['product_id'];
	
	}*/
	
	/*$count =0;
	$k=0;
	$feature ='';
	system("./readbin data.bin feat1.txt");
	
	$filename = "feat.txt";
	$handle = fopen($filename, 'r');
	$contents ='';
	$feature = fread($handle,filesize($filename));
	
	fclose($handle);
	
	echo $feature;*/
	
	/*
	while (!feof($handle)  && $count<299 ) {
  	
  	
  	
  	
  	
  	//$contents = unpack("d",fread($handle,8));
	if ($count >1){
	$feature .= " ";
	$feature .=$contents[1];
	}
	$count++;
		}
	
	echo  $feature . "</br>total ==".$count;
	
	*/
	
?>
 
