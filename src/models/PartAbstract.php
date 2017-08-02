<?php

namespace alsvanzelf\debugtoolbar\models;

/**
 * @todo add addMetric()
 */
abstract class PartAbstract {
	public function __construct($logData) {
		$this->logData = $logData;
	}
	
	public function hasFeaturedMetrics() {
		foreach ($this->metrics() as $metric) {
			if ($metric->featured === true) {
				return true;
			}
		}
		
		return false;
	}
	
	public function featuredMetrics() {
		$featuredMetrics = [];
		
		foreach ($this->metrics() as $metric) {
			if ($metric->featured === false) {
				continue;
			}
			
			$featuredMetrics[] = $metric;
		}
		
		return $featuredMetrics;
	}
	
	public function hasActiveAlerts() {
		foreach ($this->metrics() as $metric) {
			if ($metric->alert !== null) {
				return true;
			}
		}
		
		return false;
	}
	
	public function alertMetrics() {
		$alertMetrics = [];
		
		foreach ($this->metrics() as $metric) {
			if ($metric->alert === null) {
				continue;
			}
			
			$alertMetrics[] = $metric;
		}
		
		return $alertMetrics;
	}
}
