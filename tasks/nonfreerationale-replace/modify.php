<?php if (!defined('BOT_VERS')) die("Cannot use task alone.");
# This is a one-time-run task.
require('SerialStoreArray.php');

$checkedPages=new SerialStoreArray('nonfreerationale-replace','checkedPages',array());
$changedPages=new SerialStoreArray('nonfreerationale-replace','changedPages',array());

$i=0;
foreach ($checkedPages->getData() as $page => $changeInfo) {
	$i++;
	if (isset($changedPages->$page)) continue; # already did this one
	$pageContents=$wikipedia->getpage($page);
	if ($wikipedia->nobots($page,'WolfBot',$pageContents)) {
		$newPageContents=preg_replace('/\{\{[Nn]on-free media rationale/','{{Non-free use rationale',$pageContents);
		if ($newPageContents != $pageContents) {
			#TODO uncomment this next to actually run the bot
			#$wikipedia->edit($page,$newPageContents,'Replaced old {{Non-free media rationale}} with {{Non-free use rationale}}');
			$changedPages->$page=time();
			echo "* [[$page]] OK\n";
			echo $newPageContents;die();
			sleep (10); # Be polite
		} else {
			echo "* [[$page]] Error: Null edit\n";
		}
	} else {
		echo " * [[$page]] Error: Denied\n";
	}
}
