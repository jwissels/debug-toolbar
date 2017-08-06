<?php

namespace alsvanzelf\debugtoolbar\models;

/**
 * @todo add addMetric()
 */
interface PartInterface {
	public function __construct($logData, $options=[]);
	
	public static function track();
	
	public function name();
	
	public function title();
	
	public function metrics();
	
	public function isAvailable();
	
	public function hasFeaturedMetrics();
	
	public function featuredMetrics();
	
	public function hasActiveAlerts();
	
	public function alertMetrics();
}
