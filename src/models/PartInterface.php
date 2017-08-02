<?php

namespace alsvanzelf\debugtoolbar\models;

use Monolog\Logger;

/**
 * @todo add addMetric()
 */
interface PartInterface {
	public function __construct($logData);
	
	public static function track(Logger $logger);
	
	public function name();
	
	public function title();
	
	public function metrics();
	
	public function hasFeaturedMetrics();
	
	public function featuredMetrics();
	
	public function hasActiveAlerts();
	
	public function alertMetrics();
}
