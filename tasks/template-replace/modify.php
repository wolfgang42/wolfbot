<?php
# This is a one-time-run task.
require_once(dirname(__FILE__)."/../../includes/task.php");
require_once('includes/SerialStoreArray.php');
class TemplateReplaceModify extends Task {
	function __toString() {
		return "template-replace/Modify";
	}
	function run(WolfBot $wolfbot) {
		$wikipedia=$wolfbot->getWiki('en.wikipedia.org');
		require(dirname(__FILE__)."/control.php");
		$checkedPages=new SerialStoreArray('template-replace',$replaceInfo['name'].'.checkedPages',array());
		$changedPages=new SerialStoreArray('template-replace',$replaceInfo['name'].'.changedPages',array());

		$i=0;
		foreach ($checkedPages->getData() as $page => $changeInfo) {
			if (isset($changedPages->$page)) continue; # already did this one
			if (($i % 6)==0) $wolfbot->checkShutoff('template-replace'); // 6 edits = ~1 minute
			$i++;
			echo "* $i ";
			if ($i > 50) {echo "Reached 50 edits!"; return;} // Approved for 50 edits only
			$pageContents=$wikipedia->getpage($page);
			if ($wikipedia->nobots($page,BOT_USERNAME,$pageContents)) {
				$newPageContents=preg_replace($replaceInfo['regex'],$replaceInfo['replace'],$pageContents);
				if ($newPageContents != $pageContents) {
					#TODO uncomment this next to actually run the bot
					$wikipedia->edit($page,$newPageContents,$replaceInfo['editsummary']);
					$changedPages->$page=time();
					echo "[[$page]] OK\n";
					sleep (10); # Be polite
				} else {
					echo "[[$page]] Error: Null edit\n";
				}
			} else {
				echo "[[$page]] Error: Denied\n";
			}
		}
	}
}
$wolfbot->runTask(new TemplateReplaceModify());
