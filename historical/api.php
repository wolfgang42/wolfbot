<?php
die('API deprecated'); // I found botclasses.php
if (!defined('BOT_VERS')) die("Cannot use API alone.");
class API {
	public $username = "BeepBot";
	// Note: when this file is uploaded to Wikipedia, the password will be replaced with **********
	// public $password = ""; // Email & password removed because this file is deprecated
	// public $email    = "";
	public $wikiroot = "http://en.wikipedia.org/wiki/";
	public $wikiapi  = "http://en.wikipedia.org/w/api.php";
	
	protected $snoopy;
	protected $editToken;
	
	public function __construct() {
		require_once('lib/Snoopy/Snoopy.class.php');
		$this->snoopy=new Snoopy();
		$this->snoopy->agent=$this->username."/".BOT_VERS." (http://en.wikipedia.org/wiki/User:".$this->username.'/; '.$this->email.")";
		$this->snoopy->set_submit_multipart();
		$this->login();
	}
	protected function login() {
		// TODO SUL login to other wikis?
		# Login via api.php
		$login_vars['action'] = "login";
		$login_vars['lgname'] = $this->username;
		$login_vars['lgpassword'] = $this->password;
		## First part
		$response = $this->post($login_vars);
		$login_vars['lgtoken'] = $response['login']['token'];
		$this->snoopy->cookies["wiki_session"] = $response['login']['sessionid']; // You may have to change 'wiki_session' to something else on your Wiki
		## Second part, now that we have the token
		$this->post($login_vars);
		
		// Get an edit token too
		$r=$this->get(array('action'=>'tokens','type'=>'edit'));
		$this->editToken=$r['tokens']['edittoken'];
	}
	
	/** Send an API call to the wiki via POST
	 */
	public function post($data) {
		$data['format'] = "php";
		$this->snoopy->submit($this->wikiapi,$data);
		return $this->parseSnoopyResults();
	}
	public function get($data) {
		// TODO cache
		$data['format'] = "php";
		$data['maxlag'] = 5;
		$get="";$first=true;
		foreach($data as $key=>$val) {
			if ($first) {
				$first=false;
			} else {
				$get .= "&";
			}
			$get .= urlencode($key).'='.urlencode($val);
		}
		$retry=true;
		while ($retry) {
			$this->snoopy->fetch($this->wikiapi."?$get");
			$retry=false;
			foreach($this->snoopy->headers as $header) { // Make sure it didn't fail on DB lag
                if (preg_match("/X-Database-Lag:/", $header)) { // Oop, it did
					sleep(5);
					$retry=true;
				}
			}
		}
		return $this->parseSnoopyResults();
	}
	public function editPage($title,$text,$summary,$minor=false) {
		// AssertEdit?
		$data=array(
			'action'=>'edit',
			'title'=>$title,
			'text' =>$text,
			'summary' => $summary,
			'bot' => true,
			'md5' => md5($text),
			'token' => $this->editToken
		);
		if ($minor) {
			$data['minor']=1;
		} else {
			$data['notminor']=1;
		}
		$this->post($data);
		sleep(15); // Be polite
	}
	public function uploadFile($filename, $contents, $comment) {
		$data=array(
			'action'=>'upload',
			'filename' => $filename,
			'file' =>$contents,
			'comment' => $comment,
			'ignorewarnings'  => '1',
			'token' => $this->editToken
		);
		$this->post($data);
	}
	
	protected function parseSnoopyResults() {
		// Set cookies (currently ignores domain & expiry)
		$result=unserialize(trim($this->snoopy->results));
		var_dump($result);
        foreach($this->snoopy->headers as $header)
                if(preg_match("/Set-Cookie: ([^=]*)=([^;]*)/", $header, $matches))
                        $this->snoopy->cookies[$matches[1]] = $matches[2];
		// TODO check for errors
		foreach($this->snoopy->headers as $header) // Make sure it didn't fail
            if (preg_match("/MediaWiki-API-Error:/", $header))  // Oop, it did
				die ("API returned error");
		if (isset($result['edit']['result']) && $result['edit']['result']=="Failure")
			die ("Edit failure!");
		flush();
		return $result; // Apparently whitespace can sneak in
	}
}
