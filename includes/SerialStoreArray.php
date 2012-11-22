<?php
class SerialStoreArray {
	protected $task,$variable,$file,$data=array();
	public function __construct($task,$variable,$default,$file=null) {
		$this->task=$task;
		$this->variable=$variable;
		if ($file==null) {
			$this->file='tasks/'.$this->task."/".$this->variable.".serialize";
		} else {
			$this->file=$file;
		}
		if (file_exists($this->file)) {
			$this->data=unserialize(file_get_contents($this->file));
		} else {
			$this->data=$default;
		}
	}
	public function __isset($variable) {
		return isset($this->data[$variable]);
	}
	public function __get($variable) {
		if (isset($this->data[$variable])) {
			return $this->data[$variable];
		} else {
			throw new Exception("Undefined SerialStore variable: $variable");
		}
	}
	public function __set($variable,$data) {
		$this->data[$variable]=$data;
		file_put_contents($this->file,serialize($this->data));
	}
	public function getData() {
		return $this->data;
	}
}
