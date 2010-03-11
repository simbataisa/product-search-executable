<?php
	require("sphinxapi.php");
	//require_once("ResultCreator.php");
	class SearchController{
		//$q = "";
	
		//private $mode = SPH_MATCH_ALL;
		//private $host = "localhost";
		//private $port = 3315;
		public $cl;
		private $index;  //control which index to search
		private $groupby; //group 
		private $groupsort;
		
		//private $filtervals;
		private $distinct;
		private $sortby;
		private $limit;
		private $offset;
		//private $ranker; = SPH_RANK_PROXIMITY_BM25;
		private $select;	
		
		public function __construct(){
			$this->cl = new SphinxClient();
	
		//	$this->index ="product delta";
			$this->index ="product";
			//$this->mode = SPH_MATCH_ALL;
			$this->cl->SetServer ( 'localhost', 3315 );     
			$this->cl->SetConnectTimeout ( 1 );          
			$this->cl->SetArrayResult ( true );
			$this->cl->SetWeights ( array ( 100, 1 ) );  //?
			$this->cl->SetMatchMode ( SPH_MATCH_ALL);
			$this->cl->SetRankingMode(SPH_RANK_PROXIMITY_BM25);
		}
		
		public function search($query){
			$res = $this->cl->query($query,$this->index);
			
			/* else
			{
				if ( $this->cl->GetLastWarning() )
					print "WARNING: " . $this->cl->GetLastWarning() . "\n\n";

				print "Query '$query' retrieved $res[total] of $res[total_found] matches in $res[time] sec.\n";
				print "Query stats:\n";
				if ( is_array($res["words"]) )
					foreach ( $res["words"] as $word => $info )
						print "    '$word' found $info[hits] times in $info[docs] documents\n";
				print "\n";
				
				if ( is_array($res["matches"]) )
				{
					$n = 1;
					print "Matches:\n";
					foreach ( $res["matches"] as $docinfo )
					{
						print "$n. doc_id=$docinfo[id], weight=$docinfo[weight]";
						foreach ( $res["attrs"] as $attrname => $attrtype )
						{
							$value = $docinfo["attrs"][$attrname];
							if ( $attrtype & SPH_ATTR_MULTI )
							{
								$value = "(" . join ( ",", $value ) .")";
							} else
							{
								if ( $attrtype==SPH_ATTR_TIMESTAMP )
									$value = date ( "Y-m-d H:i:s", $value );
							}
							print ", $attrname=$value";
						}
						print "\n";
						$n++;
					}
				}
				
			}
			*/
			return $res;
		}

		public function setResultRange($offSet, $lim){
			$this->limit = $lim;
			$this->offset = $offSet;
			$this->cl->SetLimits($offSet, $lim,1000);	
			
		}
		public function setFilters($f, $values){
		
			$this->cl->SetFilter($f,$values);				
		}
		
		public function reset(){
			$this->cl->ResetFilters();	
		}
		
		public function setIndex($index){
			$this->index = $index;
		}
		
		public function setSortMode($mode, $attr){
			if($mode == "des" && $attr == "rate")
				$this->cl->SetSortMode(SPH_SORT_ATTR_DESC, "avg_rating");
			else if($mode == "asc" && $attr == "rate")
				$this->cl->SetSortMode(SPH_SORT_ATTR_ASC, "avg_rating");
				//$mode = SPH_SORT_ATTR_ASC;
			else if($mode == "asc" && $attr == "price")
				$this->cl->SetSortMode(SPH_SORT_EXTENDED, "lowest_retail_price ASC, highest_retail_price ASC");
			else if($mode == "des" && $attr == "price")
				$this->cl->SetSortMode(SPH_SORT_EXTENDED, "lowest_retail_price DESC, highest_retail_price DESC");

		}
		
		public function setPriceRange($low, $up){
			$this->cl->SetFilterFloatRange("lowest_retail_price", $low, $up);
			$this->cl->SetFilterFloatRange("highest_retail_price", $low, $up);	
		}
		
		public function setRatingRange($low, $up){
			$this->cl->SetFilterFloatRange("avg_rating", $low, $up);
		}
	}

?>
