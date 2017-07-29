<?php

namespace alsvanzelf\debugtoolbar;

use alsvanzelf\debugtoolbar\Value;

class Part {
	public $key = '';
	
	public $title = '';
	
	public $values = [];
	
	public function __construct($title, Value ...$values) {
		$this->key      = preg_replace('{[^a-z0-9-]}', '-', strtolower($title));
		$this->title    = $title;
		$this->values   = $values;
	}
	
	public function featuredValues() {
		$featuredValues = [];
		
		foreach ($this->values as $value) {
			if ($value->featured === false) {
				continue;
			}
			
			$featuredValues[] = $value;
		}
		
		return $featuredValues;
	}
}
