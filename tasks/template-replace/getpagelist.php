<?php
# This is a one-time-run task.
require_once(dirname(__FILE__)."/../../includes/task.php");
require_once('includes/SerialStoreArray.php');
class TemplateReplaceGetPageList extends Task {
	function __toString() {
		return "template-replace/GetPageList";
	}
	function run(WolfBot $wolfbot) {
		require(dirname(__FILE__)."/control.php");
		$wikipedia=$wolfbot->getWiki('en.wikipedia.org');
		$checkedPages=new SerialStoreArray('template-replace',$replaceInfo['name'].'.checkedPages',array());
		$embedList=$wikipedia->query('?action=query&list=embeddedin&eititle='.urlencode($replaceInfo['template']).'&eilimit=max&format=php');
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
					if (!isset($revision['*'])) continue; // Revision was hidden
					if (preg_match($replaceInfo['regex'],$revision['*'],$reMatch)) {
						$theRevision=$revision;
					} else {
						break;
					}
				}
				unset($revision);
				if ($theRevision === false) {echo "WARN: can't find template in \"$title\"\n";continue;}
				$checkedPages->$title=array($theRevision['user'],$theRevision['timestamp']);
				echo $theRevision['user']."\t".$theRevision['timestamp']."\n";
			}
			echo "*** Continuing...\n";
			if (isset($embedList['query-continue']['embeddedin']['eicontinue'])) $embedList=$wikipedia->query('?action=query&list=embeddedin&eicontinue='.$embedList['query-continue']['embeddedin']['eicontinue'].'&eilimit=max&format=php');
		}
	}
}
$wolfbot->runTask(new TemplateReplaceGetPageList());
