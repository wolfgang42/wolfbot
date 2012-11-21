<?php
define('BOT_USERNAME',"WolfBot");
chdir(dirname(__FILE__)."/..");
require_once("includes/errors.php");
// TODO log exceptions for emailing
abstract class Task {
	abstract public function __toString();
	abstract public function run(WolfBot $wolfbot);
}
class WolfBot {
	public function getWiki($wiki,$autologin=true) {
		if (preg_match('/^(.*\.wikipedia.org|.*\.wikimedia.org)/',$wiki,$match)) {
			if ($autologin) {
				require('password.php'); # This file declares a variable $password, so the password isn't uploaded to git
				return $this->getWikiByUrl('http://'.$match[1].'/w/api.php',BOT_USERNAME,$password);
			} else {
				return $this->getWikiByUrl('http://'.$match[1].'/w/api.php');
			}
		} else {
			throw new Exception("No wiki with that shortcut");
		}
	}
	public function getWikiByUrl($url, $username=null, $password=null) {
		static $wikis=array();
		if (isset($wikis[$url])) return $wikis[$url];
		require_once('lib/botclasses.php');
		$wikis[$url]=new wikipedia($url); // TODO set user-agent
		//$wikis[$url]->quiet=true;
		if ($username != null && $password != null) $wikis[$url]->login($username,$password);
		return $wikis[$url];
	}
	public function runTask(Task $task) {
		$wikipedia=$this->getWiki('en.wikipedia.org');
		try {
			// TODO anacron
			// Check global shutoff
			if (!$wikipedia->nobots('User:'.BOT_USERNAME.'/Global Shutoff',BOT_USERNAME)) die ('Global shutoff activated!');

			if (!$wikipedia->nobots("User:".BOT_USERNAME."/shutoff/".(string)$task,BOT_USERNAME)) {
				throw new Exception("\"".(string)$task."\" shutoff activated!\n");
			}

			$task->run($this);
		} catch (Exception $e) {
			// TODO log
			echo "\nException in task $task: ".$e->getMessage()." in file ".$e->getFile().":".$e->getLine()."\n";
		}
	}
}
$wolfbot=new WolfBot();

