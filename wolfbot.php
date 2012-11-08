<?php
define('BOT_VERS',"0.1");
header('Content-Type: text/plain');

require_once("errors.php");
// TODO log exceptions for emailing
$username="WolfBot";
require('password.php'); # This file declares a variable $password, so the password isn't uploaded to git

require_once('lib/botclasses.php');
$wikipedia=new wikipedia(); // TODO set user-agent
$wikipedia->login($username,$password);

$activeTasks = array( 
	'gallupgraph'
);

foreach ($activeTasks as $task) {
	// TODO allow shutoff by checking with user page
	// TODO anacron
	try {
		doTask($task);
	} catch (ErrorException $e) {
		// TODO log
		echo "\nException in task $task: ".$e->getMessage()." in file ".$e->getFile().":".$e->getLine()."\n";
	}
}

function doTask($task) {
	global $wikipedia;
	require("tasks/$task/$task.php");
}

die(); // Stupid hack to keep my web server from crashing
