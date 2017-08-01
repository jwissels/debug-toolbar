<?php

namespace alsvanzelf\debugtoolbar\models;

class Value {
	public $title = '';
	
	public $value = '';
	
	public $featured = false;
	
	public $alert = null;
	
	public function __construct($title, $value, $featured = false, $alert=null) {
		$this->title    = $title;
		$this->value    = $value;
		$this->featured = $featured;
		$this->alert    = $alert;
	}
}
