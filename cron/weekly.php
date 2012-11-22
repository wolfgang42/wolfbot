<?php
chdir(dirname(__FILE__)."/..");
foreach (array(
	'gallupgraph',
	'eswiki-graph'
		) as $task) {
	require("tasks/$task/$task.php");
}

