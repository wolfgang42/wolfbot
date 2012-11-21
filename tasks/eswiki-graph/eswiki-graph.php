<?php
require_once(dirname(__FILE__)."/../../includes/task.php");
class EswikiGraph extends Task {
	private $libreData,$eswikiData;
	function __toString() {
		return "eswiki-graph";
	}
	function run(WolfBot $wolfbot) {
		$wolfbot->checkShutoff('eswiki-graph');
		# TODO use the array serializer class
		list($this->eswikiData,$this->libreData)=unserialize(file_get_contents(dirname(__FILE__)."/entrycount.serialize"));

		# Get the latest data
		$thisEswiki=$wolfbot->getWiki('es.wikipedia.org',false)->statistics('articles');
		$thisLibre=$wolfbot->getWikiByUrl('http://enciclopedia.us.es/api.php')->statistics('articles');
		# Interpolate if necessary
		# TODO some sort of a shared library between this file and interpolate-olddata.php ?
		$thisWeek = round(time()/(60*60*24*7));
		$lastWeek = round(strtotime(array_pop(array_keys($this->libreData)))/(60*60*24*7));
		$lastEswiki=array_pop(array_values($this->eswikiData));
		$lastLibre=array_pop(array_values($this->libreData));
		for ($prevWeek = $lastWeek+1; $prevWeek < $thisWeek; $prevWeek++) {
			$prevEswiki=$this->interpolate($prevWeek,$lastWeek,$lastEswiki,$thisWeek,$thisEswiki);
			$prevLibre=$this->interpolate($prevWeek,$lastWeek,$lastLibre,$thisWeek,$thisLibre);
			$this->addData($prevWeek,$prevEswiki,$prevLibre);
		}
		file_put_contents(dirname(__FILE__)."/entrycount.serialize",serialize(array($this->eswikiData,$this->libreData)));

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
		$graph->Values(array($this->libreData,$this->eswikiData));
		$graph->colours = array('blue','red');
		file_put_contents('eswiki.svg',$graph->Fetch('MultiLineGraph'));
		#$wolfbot->getWiki('en.wikipedia.org')->upload('Enciclopedia Libre and Spanish Wikipedia article count.svg','eswiki.svg','Automated update by bot');
	}
	protected function addData($week,$eswiki,$libre) {
		$date=date("d M Y",$week*60*60*24*7);
		$this->eswikiData[$date]=intval($eswiki);
		$this->libreData[$date]=intval($libre);
	}

	protected function interpolate($x,$x0,$y0,$x1,$y1) {
		return $y0+(($y1-$y0)*(($x-$x0)/($x1-$x0)));
	}
}

$wolfbot->runTask(new EswikiGraph);
