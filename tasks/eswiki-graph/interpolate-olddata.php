<?php
// Using the data from the chart at http://en.wikipedia.org/wiki/Enciclopedia_Libre_Universal_en_Espa%C3%B1ol,
// produces a week-by-week (estimated) count of the number of articles, using linear interpolation.

$eswikiData=array();
$libreData=array();
$lastWeek=false;
foreach (explode("\n",
"11 March 2002	3036	1300
2 April 2002	5972	1300
1 May 2002	7540	1400
27 May 2002	7837	1500
13 June 2002	8125	1500
13 July 2002	8519	1500
4 August 2002	8658	1500
31 August 2002	8996	1500
3 October 2002	9362	1600
26 October 2002	10258	1900
17 June 2003	14250	3700
8 July 2003	14428	3700
2 August 2003	14662	4600
6 September 2003	15116	5900
2 November 2003	15572	8900
6 December 2003	16039	11000
20 March 2004	19688	19000
6 November 2004	25038	33573
25 September 2005	28709	66984
27 February 2006	30455	97568
9 April 2006	30776	108012
3 July 2006	31980	130939
1 October 2006	33367	157061
3 January 2007	34342	186022
1 April 2007	34551	218690
1 July 2007	35703	248775
2 October 2007	36860	283630
1 January 2008	38012	315611
2 April 2008	39478	348217
4 July 2008	40424	376664
3 October 2008	41096	403280
1 January 2009	41852	430159
1 April 2009	42366	458301
1 July 2009	42533	488905
1 October 2009	43035	516333
1 January 2010	44156	546487
1 April 2010	44592	581486
1 July 2010	45170	615232
1 October 2010	45947	653596
1 January 2011	46374	692451
2 April 2011	46775	745694
3 July 2011	47150	785900
2 October 2011	47315	832243
1 January 2012	47638	856400
2 April 2012	47797	880230
2 July 2012	47937	900861
2 October 2012	48082	924293")
as $line) {
	$array=explode("\t",$line);
	$thisWeek=round(strtotime($array[0])/(60*60*24*7));
	$thisEswiki=$array[1];
	$thisLibre=$array[2];
	// Interpolate data between last week and this week
	if ($lastWeek !== false) {
		for ($prevWeek = $lastWeek+1; $prevWeek < $thisWeek; $prevWeek++) {
			$prevEswiki=interpolate($prevWeek,$lastWeek,$lastEswiki,$thisWeek,$thisEswiki);
			$prevLibre=interpolate($prevWeek,$lastWeek,$lastLibre,$thisWeek,$thisLibre);
			addData($prevWeek,$prevEswiki,$prevLibre);
		}
	}
	$lastWeek=$thisWeek;
	$lastEswiki=$thisEswiki;
	$lastLibre=$thisLibre;
	addData($thisWeek,$thisEswiki,$thisLibre);
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
