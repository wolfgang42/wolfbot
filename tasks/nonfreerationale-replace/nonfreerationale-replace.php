<?php
if (!defined('BOT_VERS')) die("Cannot use task alone.");
# This is a one-time-run task.

require('SerialStoreArray.php');

$wikipedia->quiet=true;

$checkedPages=new SerialStoreArray('nonfreerationale-replace','checkedPages',array());

$embedList=$wikipedia->query('?action=query&list=embeddedin&eititle=Template:Non-free_media_rationale&eilimit=max&format=php');
$firstLoop=true;$i=0;
while ($firstLoop||isset($embedList['query-continue']['embeddedin']['eicontinue'])) {
$firstLoop=false;
	foreach ($embedList['query']['embeddedin'] as $page) {
		$i++;
		$title=$page['title'];
		if (isset($checkedPages->$title)) continue;
		if (($i%5)==0) sleep(1); // Sleep politely for 1 second every 5 requests
		echo "$i\t$title\t";
		// Error correction
		$try=true;
		while ($try) {
			$resp = $wikipedia->query('?action=query&format=php&prop=revisions&titles='.urlencode($title).'&rvprop=user|content|timestamp&rvlimit=max');
			if ($resp == null) {
				echo "Error, retrying";
				sleep(5); // In case something goes wrong, don't deluge wikipedia's servers
				echo "...\n";
			} else {
				$try=false;
			}
		}
		$resp=$resp['query']['pages'];
		$resp=array_pop($resp);
		$theRevision=false;
		foreach ($resp['revisions'] as $revNo=>$revision) {
			if (preg_match('/\{\{[Nn]on-free media rationale/',$revision['*'])) {
				$theRevision=$revision;
			} else {
				break;
			}
		}
		if ($theRevision === false) {echo "WARN: can't find template in \"$title\"\n";continue;}
		$checkedPages->$title=array($revision['user'],$revision['timestamp']);
		echo $revision['user']."\t".$revision['timestamp']."\n";
		/*$pageContents=$wikipedia->getpage($title);
		$newPageContents=preg_replace('/\{\{[Nn]on-free media rationale/','{{Non-free use rationale',$pageContents);
		if ($newPageContents != $pageContents) {
			echo "* [[$title]] OK\n";
			echo $newPageContents;die();
		} else {
			echo "* [[$title]] Error\n";
		}*/
	}
	echo "*** Continuing...\n";
	if (isset($embedList['query-continue']['embeddedin']['eicontinue'])) $embedList=$wikipedia->query('?action=query&list=embeddedin&eicontinue='.$embedList['query-continue']['embeddedin']['eicontinue'].'&eilimit=max&format=php');
}

