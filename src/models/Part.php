<?php

namespace alsvanzelf\debugtoolbar\models;

use alsvanzelf\debugtoolbar\models\Metric;

class Part {
	public $key = '';
	
	public $title = '';
	
	public $metrics = [];
	
	public function __construct($title, Metric ...$metrics) {
		$this->key     = preg_replace('{[^a-z0-9-]}', '-', strtolower($title));
		$this->title   = $title;
		$this->metrics = $metrics;
	}
	
	public function featuredMetrics() {
		$featuredMetrics = [];
		
		foreach ($this->metrics as $metric) {
			if ($metric->featured === false) {
				continue;
			}
			
			$featuredMetrics[] = $metric;
		}
		
		return $featuredMetrics;
	}
	
	public function alertMetrics() {
		$alertMetrics = [];
		
		foreach ($this->metrics as $metric) {
			if ($metric->alert === null) {
				continue;
			}
			
			$alertMetrics[] = $metric;
		}
		
		return $alertMetrics;
	}
	
	public function hasAlertMetrics() {
		foreach ($this->metrics as $metric) {
			if ($metric->alert !== null) {
				return true;
			}
		}
		
		return false;
	}
}
