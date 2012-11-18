<?php

#TODO get the latest data

list($eswikiData,$libreData)=unserialize(file_get_contents(dirname(__FILE__)."/entrycount.serialize"));
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
$graph->Values(array($eswikiData,$libreData));
$graph->colours = array('blue','red');
file_put_contents('eswiki.svg',$graph->Fetch('MultiLineGraph'));
#TODO upload
