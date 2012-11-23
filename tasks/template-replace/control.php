<?php
# WARNING about regular expressions: The Pipe (|) character is special
# Templates with parameters will cause problems if it's not escaped!
/*$replaceInfo=array(
	'name' => 'bpn-replace',
	'template' => 'Template:BPN',
	'regex' => '/\{\{[Bb]PN\|([0-9]+)\}\}/',
	'replace' => '{{Authority control|BPN=$1}}',
	'editsummary' => 'Replaced obsolete {{BPN}} with {{Authority control}}'
);
*/
$replaceInfo=array(
	'name' => 'nonfreerationale-replace',
	'template' => 'Template:Non-free media rationale',
	'regex' => '/\{\{[Nn]on-free media rationale/',
	'replace' => '{{Non-free use rationale',
	'editsummary' => 'Replaced old {{Non-free media rationale}} with {{Non-free use rationale}}'
);
