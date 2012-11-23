<?php
chdir(dirname(__FILE__)."/..");
foreach (array(
	'requestedmoves'
		) as $task) {
	require("tasks/$task/$task.php");
}

