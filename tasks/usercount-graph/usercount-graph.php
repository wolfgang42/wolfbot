<?php
require_once(dirname(__FILE__)."/../../includes/task.php");
require_once('lib/SVGGraph/SVGGraph.php');
class UsercountGraph extends Task {
	function __toString() {
		return "usercount-graph";
	}
	function run(WolfBot $wolfbot) {
		$wolfbot->checkShutoff('usercount-graph');
		$userCount=array();
		foreach(array(
				// TODO it's called es-nl, but it has a lot more than that
				array('es','nl')) as $langList) {
			$values=array();
			foreach ($langList as $lang) {
				if (!isset($userCount[$lang]))
					$userCount[$lang]=$wolfbot->getWiki($lang.".wikipedia.org",false)->statistics('users');
				$values[$lang]=$userCount[$lang];
			}
			$graph = new SVGGraph(count($langList)*200, 300, array (
				/*'marker_size' => 0,
				'force_assoc' => true, // Doesn't change graph any, but makes rendering a lot faster
				'axis_max_h'  => 365*4, // 4 years
				'grid_division_h' => 365, // 1 date per year
				'grid_division_v' => 10,*/
				//'title'       => "Barack Obama's Presidential Job Approval Ratings, 2009-2012 (Gallup Poll)",
				'label_v'     => "Users",
				'axis_font_size'    => 10,
				'label_font_size'   => 12,
				'bar_space' => 80
			));
			$graph->values($values);
			$filename='User count '.join($langList,"-").'.svg';
			file_put_contents($filename,$graph->Fetch('BarGraph'));
			//$wolfbot->getWiki('en.wikipedia.org')->upload($filename,$filename,'Automated update by bot');
		}
	}
}

$wolfbot->runTask(new UsercountGraph());
