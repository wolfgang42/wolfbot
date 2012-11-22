<?php
header('Content-Type: text/plain');

foreach (array(
	'gallupgraph',
	'eswiki-graph'
		) as $task) {
	require("tasks/$task/$task.php");
}

