<?php
require_once("sphinxapi.php");
class SphinxSearchManager {

    public $cl;
    private $_sphinxIndex;  //control which index to search
    private $groupby; //group
    private $groupsort;

    //private $filtervals;
    private $distinct;
    private $sortby;
    private $limit;
    private $offset;
    //private $ranker; = SPH_RANK_PROXIMITY_BM25;
    private $select;

    function SphinxSearchManager() {
        $this->cl = new SphinxClient();

        $this->_sphinxIndex ="products";

        $this->cl->SetServer ( 'localhost', 3315 );
        $this->cl->SetConnectTimeout ( 1 );
        $this->cl->SetArrayResult ( true );
        $this->cl->SetWeights ( array ( 100, 1 ) );  //?
        $this->cl->SetMatchMode (SPH_MATCH_EXTENDED);
        $this->cl->SetRankingMode(SPH_RANK_PROXIMITY_BM25);
    }

    public function search($query) {
        $res = $this->cl->query($query,$this->_sphinxIndex);        
        return $res;
    }

    public function setResultRange($offSet, $lim, $max) {
        $this->limit = $lim;
        $this->offset = $offSet;
        $this->cl->SetLimits($offSet, $lim, $max);

    }
    public function setFilter($field, $values) {

        $this->cl->SetFilter($field,$values);
    }

    public function reset() {
        $this->cl->ResetFilters();
    }

    public function setIndex($index) {
        $this->_sphinxIndex = $index;
    }

    public function setSortMode($mode, $attr) {
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

    public function setPriceRange($low, $up) {
        $this->cl->SetFilterFloatRange("lowest_retail_price", $low, $up);
        $this->cl->SetFilterFloatRange("highest_retail_price", $low, $up);
    }

    public function setRatingRange($low, $up) {
        $this->cl->SetFilterFloatRange("avg_rating", $low, $up);
    }
}

?>
