<?php

namespace alsvanzelf\debugtoolbar;

class Value {
	public $title = '';
	
	public $value = '';
	
	public $featured = false;
	
	public function __construct($title, $value, $featured=false) {
		$this->title    = $title;
		$this->value    = $value;
		$this->featured = $featured;
	}
}
