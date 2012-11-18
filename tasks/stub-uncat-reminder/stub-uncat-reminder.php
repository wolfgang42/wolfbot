<?php
if (!defined('BOT_VERS')) die("Cannot use task alone.");
$wikipedia->quiet=true;

$checkedPages=unserialize(file_get_contents('tasks/stub-uncat-reminder/checkedPages.serialize'));
$stubbers=unserialize(file_get_contents('tasks/stub-uncat-reminder/stubbers.serialize'));

foreach ($wikipedia->categorymembers('Category:Stubs') as $stub) {
	if (isset($checkedPages[$stub])) continue; // Already checked this one
	echo "$stub\n";
	$resp = $wikipedia->query('?action=query&format=php&prop=revisions&titles='.urlencode($stub).'&rvprop=user|content&rvlimit=max');
	$resp=$resp['query']['pages'];
	$resp=array_pop($resp);
	$stubber=false;
	foreach ($resp['revisions'] as $revNo=>$revision) {
		if (preg_match('/\{\{[Ss]tub(\|[^}]+)?\}\}/',$revision['*'])) {
			$stubber=$revision['user'];
		} else {
			break;
		}
	}
	if ($stubber === false) {echo ("WARN: can't find stub in \"$stub\"\n");continue;}
	$checkedPages[$stub]=time();
	if (!isset($stubbers[$stubber])) $stubbers[$stubber]=array();
	$stubbers[$stubber][]=$stub;
	file_put_contents('tasks/stub-uncat-reminder/checkedPages.serialize',serialize($checkedPages));
	file_put_contents('tasks/stub-uncat-reminder/stubbers.serialize',serialize($stubbers));
}

$tooMany=10;
$list = "This is a list of editors who have created $tooMany or more uncategorized stubs.\n";
$summary=array();
foreach ($stubbers as $stubber=>$stubs) {
	$summary[$stubber]=count($stubs);
	if (count($stubs) < $tooMany) continue; // Skip
	$list .= "* [[User:$stubber|]] ([[User_talk:$stubber|talk]] - [[Special:Contributions/$stubber|contribs]]): ".count($stubs)."\n";
	foreach ($stubs as $stub) {
		$list .= "** [[$stub]]\n";
	}
}
echo $list;
arsort($summary);
var_dump($summary);
