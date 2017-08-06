<?php

namespace alsvanzelf\debugtoolbar\models;

/**
 * @todo add addMetric()
 */
abstract class PartAbstract {
	protected $logData;
	
	protected $options;
	
	public function __construct($logData, $options=[]) {
		$this->logData = $logData;
		$this->options = $options;
	}
	
	public function isAvailable() {
		if ($this->logData === null) {
			return false;
		}
		
		foreach ($this->metrics() as $metric) {
			if ($metric->isAvailable()) {
				return true;
			}
		}
		
		return false;
	}
	
	public function hasFeaturedMetrics() {
		foreach ($this->metrics() as $metric) {
			if ($metric->featured === true && $metric->isAvailable()) {
				return true;
			}
		}
		
		return false;
	}
	
	public function featuredMetrics() {
		$featuredMetrics = [];
		
		foreach ($this->metrics() as $metric) {
			if ($metric->featured === false || $metric->isAvailable() === false) {
				continue;
			}
			
			$featuredMetrics[] = $metric;
		}
		
		return $featuredMetrics;
	}
	
	public function hasActiveAlerts() {
		foreach ($this->metrics() as $metric) {
			if ($metric->alert !== null && $metric->isAvailable()) {
				return true;
			}
		}
		
		return false;
	}
	
	public function alertMetrics() {
		$alertMetrics = [];
		
		foreach ($this->metrics() as $metric) {
			if ($metric->alert === null && $metric->isAvailable()) {
				continue;
			}
			
			$alertMetrics[] = $metric;
		}
		
		return $alertMetrics;
	}
}
