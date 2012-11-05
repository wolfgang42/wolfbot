<?php
// TODO request deletion for files which no longer exist?

if (!defined('BOT_VERS')) die("Cannot use task alone.");
// Note: timestamps file is not uploaded with the rest of the source
// If it were it would cause an endless loop
global $timestamps,$directories;
$timestamps=unserialize(file_get_contents('tasks/sourceupload/timestamps.serialize'));
$directories=unserialize(file_get_contents('tasks/sourceupload/directories.serialize'));
uploadsource_checkDir('.');

function uploadsource_checkDir($dir) {
	if ($handle = opendir($dir)) {
		$files=array();
		while (false !== ($file = readdir($handle))) {
		    if ($file != "." && $file != "..") {
		    	if ($dir != '.') {
		    		$entry="$dir/$file";
		    	} else {
		    		$entry=$file;
		    	}
		    	$files[]=$file;
		        if (is_dir($entry)) {
		        	uploadsource_checkDir($entry);
		        } else {
		        	uploadsource_checkUploadFile($entry);
		        }
		    }
		}
		closedir($handle);
		global $directories;
		asort($files);
		if (!isset($directories[$dir]) || $directories[$dir] != $files) uploadsource_uploadDirIndex($dir,$files);
	}
}
function uploadSource_uploadDirIndex($dir,$files) {
	if ($dir==".") {
		$dir = "";
	} else {
		$dir = "/$dir";
	}
	global $username,$directories,$wikipedia;
	$index="";
	foreach($files as $file) $index .= "* [[User:".$username."/source$dir/$file|$file".
			(is_dir(".$dir/$file")?"/":"")."]]\n";
	$wikipedia->edit('User:'.$username."/source".$dir,"$index","Updated index to reflect active version");
	$directories[$dir]=$files;
	file_put_contents('tasks/sourceupload/directories.serialize',serialize($directories)); // Save so script can be stopped
}
function uploadsource_checkUploadFile($file) {
	global $timestamps;
	if ($file == "tasks/sourceupload/timestamps.serialize") return; // Bad--don't upload
	if ($file == "tasks/sourceupload/directories.serialize") return; // Bad--don't upload
	if (!isset($timestamps[$file]) || filemtime($file) != $timestamps[$file]) uploadsource_uploadFile($file);
}
function uploadSource_uploadFile($file) {
	$extension=array_pop(explode('.',$file));
	// The wiki syntax below must have an interruption in the middle so it's not
	// interpreted by MediaWiki (specifically the GeSHi plugin) when the source is uploaded.
	if ($extension=='php' || $extension=='ini') {
		$begin='<'.'syntaxhighlight lang="'.$extension.'">';
		$end='</'.'syntaxhighlight>';
	} else {
		$begin='<'.'pre>';
		$end='</'.'pre>';
	}
	global $wikipedia,$username,$password;
	$contents=file_get_contents($file);
	// TODO escape wiki markup!
	if ($file == "beepbot.php") $contents=preg_replace("/".$password."/","*********",$contents); // Hide password
	$wikipedia->edit('User:'.$username."/source/".$file,"$begin\n$contents\n$end","Updated source to reflect active version");
	global $timestamps;
	$timestamps[$file]=filemtime($file);
	file_put_contents('tasks/sourceupload/timestamps.serialize',serialize($timestamps)); // Save so script can be stopped
}

