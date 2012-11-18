<?php
if (!defined('BOT_VERS')) die("Cannot use task alone.");

 // TODO multiple pages (Currently only checking 1st page of result)
if (preg_match_all('/\|\-
\| [0-9]+
\| \[\[(.*)\]\]
\| \[\[:Category:([^|]*)|.*\]\]/',$wikipedia->getpage('Wikipedia:Database reports/Stubs included directly in stub categories/1'), $matches)) {
$wikipedia->quiet=true;
	foreach ($matches[1] as $key=>$value) {
		$page=str_replace("_"," ",$value);
		$stubCat=str_replace("_"," ",$matches[2][$key]);
		if ($page=="" && $stubCat=="") continue; // TODO where are all these blank bits coming from?
		$stubTpl=getStubTemplateFromCategory($stubCat);
		if ($stubTpl == false) continue; // No template found
		// TODO watch exclusion tag
		$pageContents=$wikipedia->getpage($page);
		if (preg_match('/stub\]\]/',$pageContents)) {
			echo "\n$page\t$stubCat\n";
		}
		// TODO keep track of who added the category so we can tell repeat offenders not to
	}
} else {
	throw new Exception("Couldn't parse report page");
}

function getStubTemplateFromCategory($category) {
	global $wikipedia;
	static $cache = array('Stubs'=>'stub');
	// TODO look up the category then extract the stub name
	if (!isset($cache[$category])) {
		$catPage=$wikipedia->getpage('Category:'.$category);
		if (preg_match('/\{\{((Regional) )?Stub Category\s*\|(.+\|)?\s*newstub\=([^\}\|]+)/is',$catPage,$match)) {
			$cache[$category]=$match[4];
		} else if (preg_match('/\{\{Parent-only Stub Category/is',$catPage)) {
			// TODO mark the page somehow
			echo "WARN: parent-only stub category \"$category\" requested\n";
			$cache[$category]=false;
		} else {
			echo "WARN: Can't get stub template for category \"$category\"\n";
			$cache[$category]=false;
		}
	}
	return $cache[$category];
}

