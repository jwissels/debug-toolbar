<?php

namespace alsvanzelf\debugtoolbar\models;

use alsvanzelf\debugtoolbar\models\Detail;

class Metric {
	public $title = '';
	
	public $value = '';
	
	public $featured = false;
	
	public $alert = null;
	
	public $detail = null;
	
	public function __construct($title, $value, $featured = false, $alert=null, Detail $detail=null) {
		$this->title    = $title;
		$this->value    = $value;
		$this->featured = $featured;
		$this->alert    = $alert;
		$this->detail   = $detail;
	}
	
	public function isAvailable() {
		return ($this->value !== null);
	}
}
