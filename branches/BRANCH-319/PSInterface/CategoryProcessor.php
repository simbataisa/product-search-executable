<?php
	require_once("XMLCreator.php");
	require_once("dbconnection.php");
	require_once("SearchController.php");
	class CategoryProcessor{
		
		private $xmlWriter;
		
		public function __construct(){
			$this->xmlWriter = new XMLCreator(XMLCreator::CATEGORY);			
		}
		
		public function getCategories(){
			$sql = "SELECT name FROM test_sub_categories WHERE category_level = 1";
			$res = mysql_query($sql);
			while($r = mysql_fetch_array($res)){
				$name = $r['name'];
				$this->xmlWriter->add('category',$name);			
			}
			
			//$this->xmlWriter->saveFile("../Model/categories.xml");
			echo $this->xmlWriter->save2();
		}
		
		public function getCategoryByName($name){
			$sql = "SELECT category_id FROM test_sub_categories WHERE name = '$name'";
			$res = mysql_query($sql);
			if($r= mysql_fetch_array($res))
				return $r['category_id'];
		}
		/*
		public function getCategoryHierarchy(){
			$sql = "SELECT id, name FROM categories WHERE level <= 2";
		}
		*/
		public function exploreCategory($id){
			if($id==-1)
				$sql = "SELECT category_id, name FROM test_sub_categories WHERE category_level = 1";
			else
				$sql = "SELECT category_id, name FROM test_sub_categories WHERE parent = $id";
			$res = mysql_query($sql);
			while($r = mysql_fetch_array($res)){
				//var_dump($r);
				$attr = array("id"=>$r["category_id"], "label"=>$r["name"]);
				$this->xmlWriter->addNode("node",null, $attr);	
			}
			
			if($id != -1){
				$sql = "SELECT count(*) FROM products WHERE category_id = $id";
				if($r = mysql_query($sql)){
					$res = mysql_fetch_array($r);

					$this->xmlWriter->add("total_products", $res[0]);
				}
				else{
					//echo mysql_error();
				}
				//$this->xmlWriter->add("parent_id", $id);
			}
			//$this->xmlWriter->saveFile("../Model/categories2.xml");
			echo $this->xmlWriter->save2();
		}
		
		public function remove($id){
			$sql = "DELETE FROM test_sub_categories WHERE category_id = $id";
			$xml = new XMLCreator(XMLCreator::MESSAGE);
			if($r = mysql_query($sql)){
				
				$xml->add("message","category removed"); 	
			}
			else{
				$xml->add("message", "Error: " + mysql_error());
				
			}	
			echo $xml->save2();			
		}
		
		public function update($id, $name){
			$sql = "UPDATE test_sub_categories SET name = '".$name."' WHERE category_id  = $id";
			$xml = new XMLCreator(XMLCreator::MESSAGE);
			if($r = mysql_query($sql)){
				$xml->add("message","category removed"); 	
			}
			else
				$xml->add("message", "Error: " + mysql_error());
				
			echo $xml->save2();				
		}
		
		public function addCategory($name, $parent){
			$sql = "INSERT INTO test_sub_categories (name, parent) VALUES('".$name."',$parent )";
			$xml = new XMLCreator(XMLCreator::MESSAGE);
			if($r = mysql_query($sql)){
				$xml->add("message","category successfully added"); 	
			}
			else
				$xml->add("message", "Error: " + mysql_error());
				
			echo $xml->save2();
		}
		
		public function get2LevelCategories(){
		
			$sql = "SELECT category_id, name FROM test_sub_categories WHERE category_level = 1 and  category_id in (1,235,424,487,3412) ";
		
			$res = mysql_query($sql);
			while($r = mysql_fetch_array($res)){
			//	var_dump($r);
				$attr = array("id"=>$r["category_id"], "label"=>$r["name"]);
				$cur = $this->xmlWriter->addNode("node", null, $attr);	
				$sql = "SELECT category_id, name FROM test_sub_categories WHERE parent = ".$r["category_id"]." limit 20";
				$result = mysql_query($sql);
				while($row = mysql_fetch_array($result)){
					$attr = array("id"=>utf8_encode($row["category_id"]), "label"=>utf8_encode($row["name"]));
					//echo utf8_decode($row['category_id'])." ".utf8_decode($row["name"])."</br>";
					
					$this->xmlWriter->addToNode($cur , "node", null, $attr);						
				}
			}
			
			
			echo $this->xmlWriter->save2();		
			//echo $this->xmlWriter->saveFile("dd.xml");
		}
		
		public function getBottomChildren($id){
			$child_ids = array();
			$searcher = new SearchController();
			$searcher->setIndex("category");
			$this->getChildren($id, $child_ids, $searcher);
			return $child_ids;			
		}
		private function getChildren($id, &$child_ids, $searcher){
			$searcher->reset();
		
			$searcher->setFilters("parent", array($id));
			$res = $searcher->search("");
		
			if($res === false){
				echo $searcher->cl->GetLastError();
			}
			else{
				echo $searcher->cl->GetLastWarning();
				if(isset($res["matches"])){
					foreach ($res["matches"] as $docinfo){
						$this->getChildren($docinfo['id'], $child_ids, $searcher);
					}
				}
				else{
				
					array_push($child_ids, (int)$id);		
							
				}
			} 
		}
		
		
	}
?>
