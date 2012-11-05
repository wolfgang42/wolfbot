<?php
if (!defined('BOT_VERS')) die("Cannot use task alone.");
$csv = fopen('http://www.gallup.com/viz/v1/csv/8386b935-9a6b-4a07-ae74-78e02ac52871/POLLFLEXCHARTVIZ/OBAMAJOBAPPR113980.aspx','r');
if ($csv === FALSE) {
	die ("ERROR opening stream: fail!");
} else {
	$row=0;
	$approve=array();
	$undecided=array();
	$disapprove=array();
    while (($data = fgetcsv($csv, 1000, ",")) !== FALSE) {
		$row++;
    	if ($row <= 4) continue; // Skip over headers
    	if (strncmp($data[0], "---", 3) == 0) break; // The footer starts with --------- etc
       	$date=midrange($data[0]);
       	$approve[$date]=intval($data[1]);
       	$undecided[$date]=(100-($data[1]+$data[2]));
		$disapprove[$date]=intval($data[2]);
    }
    fclose($csv);
    // Now graph
    require_once 'lib/SVGGraph/SVGGraph.php';
	$graph = new SVGGraph(800, 500, array (
		'marker_size' => 0,
		'force_assoc' => true, // Doesn't change graph any, but makes rendering a lot faster
		'axis_max_h'  => 365*4, // 4 years
		'grid_division_h' => 365, // 1 date per year
		'grid_division_v' => 10,
		'title'       => "Barack Obama's Presidential Job Approval Ratings, 2009-2012 (Gallup Poll)",
		'label_v'     => "Approval Rating (%)",
		'axis_font_size'    => 15,
		'label_font_size'   => 15
	));
	$graph->Values(array($approve, $undecided, $disapprove));
	$graph->colours = array('green','yellow','red');
	file_put_contents('/tmp/gallup.svg',$graph->Fetch('StackedLineGraph'));
	$wikipedia->upload('Barack Obama\'s Presidential Job Approval Ratings, 2009-2012 (Gallup Poll).svg','/tmp/gallup.svg','Automated update by bot');
}

function midrange($range) {
	list ($part1, $part2) = explode('-',$range);
	$part1=explode('/',$part1);$part2=explode('/',$part2);
	$year2=array_pop($part2); // Last one in array 2 is always year
	if (count($part1) > 2) {
		$year1=array_pop($part1);
	} else {
		$year1=$year2;
	}
	if (count($part1) != 2) die ("Wrong number of chunks for \$part1!");
	$month1=array_shift($part1);
	$day1=array_shift($part1);
	$day2=array_pop($part2);
	if (count($part2) == 0) {
		$month2=$month1;
	} else if (count($part2) == 1) {
		$month2=array_pop($part2);
	} else {
		die ("Wrong number of chunks for \$part2!");
	}
	$date1=strtotime("$year1-$month1-$day1");
	$date2=strtotime("$year2-$month2-$day2");
	$mid=($date1+$date2)/2;
	return date('Y-m-d',$mid);
}
