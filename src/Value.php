<?php

namespace alsvanzelf\debugtoolbar;

class Value {
	public $title = '';
	
	public $value = '';
	
	public $featured = false;
	
	public $alertLevel = null;
	
	public function __construct($title, $value, $featured=false, $alertLevel=null) {
		$this->title      = $title;
		$this->value      = $value;
		$this->featured   = $featured;
		$this->alertLevel = $alertLevel;
	}
}
