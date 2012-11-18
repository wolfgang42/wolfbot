<?php

list($libreData,$eswikiData)=unserialize(file_get_contents(dirname(__FILE__)."/entrycount.serialize"));

# Get the latest data
$esWiki=new wikipedia('http://es.wikipedia.org/w/api.php');
$resp=$esWiki->query('?action=query&meta=siteinfo&siprop=statistics&format=php');
$thisEswiki=$resp['query']['statistics']['articles'];
unset($esWiki);
$libre=new wikipedia('http://enciclopedia.us.es/api.php');
$resp=$libre->query('?action=query&meta=siteinfo&siprop=statistics&format=php');
$thisLibre=$resp['query']['statistics']['articles'];
unset($libre);
unset($resp);
# Interpolate if necessary
# TODO some sort of a shared library between this file and interpolate-olddata.php ?
$thisWeek = round(time()/(60*60*24*7));
$lastWeek = round(strtotime(array_pop(array_keys($libreData)))/(60*60*24*7));
$lastEswiki=array_pop(array_keys($eswikiData));
$lastLibre=array_pop(array_keys($libreData));
for ($prevWeek = $lastWeek+1; $prevWeek < $thisWeek; $prevWeek++) {
	$prevEswiki=interpolate($prevWeek,$lastWeek,$lastEswiki,$thisWeek,$thisEswiki);
	$prevLibre=interpolate($prevWeek,$lastWeek,$lastLibre,$thisWeek,$thisLibre);
	addData($prevWeek,$prevEswiki,$prevLibre);
}
file_put_contents(dirname(__FILE__)."/entrycount.serialize",serialize(array($eswikiData,$libreData)));

function addData($week,$eswiki,$libre) {
	global $eswikiData,$libreData;
	$date=date("d M Y",$week*60*60*24*7);
	$eswikiData[$date]=intval($eswiki);
	$libreData[$date]=intval($libre);
}

function interpolate($x,$x0,$y0,$x1,$y1) {
	return $y0+(($y1-$y0)*(($x-$x0)/($x1-$x0)));
}

require_once 'lib/SVGGraph/SVGGraph.php';
$graph = new SVGGraph(800, 500, array (
	'marker_size' => 0,
	'force_assoc' => true, // Doesn't change graph any, but makes rendering a lot faster
	'grid_division_h' => 52, // 4 dates per year
	'axis_text_angle_h' => -90,
	'title'       => "Approximate number of articles in Spanish Wikipedia vs Enciclopedia Libre",
	'label_v'     => "Approx. article count"
	// TODO legend
));
$graph->Values(array($libreData,$eswikiData));
$graph->colours = array('blue','red');
file_put_contents('eswiki.svg',$graph->Fetch('MultiLineGraph'));
#$wikipedia->upload('Enciclopedia Libre and Spanish Wikipedia article count.svg','eswiki.svg','Automated update by bot');

