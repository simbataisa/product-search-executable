<?php

//
// $Id: test.php 1712 2009-03-02 10:27:19Z shodan $
//

require ( "sphinxapi.php" );
require ( "SearchController.php");
//////////////////////
// parse command line
//////////////////////


$cl = new SphinxClient ();

$q = "";
$sql = "";
$mode = SPH_MATCH_ALL;
$host = "localhost";
$port = 3315;
$index = "category deltaCategory";
$groupby = "";
$groupsort = "@group desc";
$filter = "parent";
$filtervals = array(128);
$distinct = "";
$sortby = "";
$limit = 20;
$ranker = SPH_RANK_PROXIMITY_BM25;
$select = "";


////////////
// do query
////////////

$cl->SetServer ( $host, $port );
$cl->SetConnectTimeout ( 1 );
$cl->SetArrayResult ( true );
$cl->SetWeights ( array ( 100, 1 ) );
$cl->SetMatchMode ( $mode );
if ( count($filtervals) )	$cl->SetFilter ( $filter, $filtervals );
if ( $groupby )				$cl->SetGroupBy ( $groupby, SPH_GROUPBY_ATTR, $groupsort );
if ( $sortby )				$cl->SetSortMode ( SPH_SORT_EXTENDED, $sortby );
if ( $sortexpr )			$cl->SetSortMode ( SPH_SORT_EXPR, $sortexpr );
if ( $distinct )			$cl->SetGroupDistinct ( $distinct );
if ( $select )				$cl->SetSelect ( $select );
if ( $limit )				$cl->SetLimits ( 0, $limit, ( $limit>1000 ) ? $limit : 1000 );
$cl->SetRankingMode ( $ranker );
$res = $cl->Query ( $q, $index );

////////////////
// print me out
////////////////

if ( $res===false )
{
	print "Query failed: " . $cl->GetLastError() . ".\n";

} else
{
	if ( $cl->GetLastWarning() )
		print "WARNING: " . $cl->GetLastWarning() . "\n\n";

	print "Query '$q' retrieved $res[total] of $res[total_found] matches in $res[time] sec.\n";
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


echo "========================================================================\n";

$searcher = new SearchController();
$searcher->setIndex("category deltaCategory");
$searcher->reset();
$searcher->setFilters("parent", array(128));
$res = $searcher->search("");
if ( $res===false )
{
	print "Query failed: " . $cl->GetLastError() . ".\n";

} else
{
	if ( $cl->GetLastWarning() )
		print "WARNING: " . $cl->GetLastWarning() . "\n\n";

	print "Query '$q' retrieved $res[total] of $res[total_found] matches in $res[time] sec.\n";
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

//
// $Id: test.php 1712 2009-03-02 10:27:19Z shodan $
//

?>
