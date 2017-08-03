<?php

namespace alsvanzelf\debugtoolbar\parts;

use alsvanzelf\debugtoolbar\models\Detail;
use alsvanzelf\debugtoolbar\models\Metric;
use alsvanzelf\debugtoolbar\models\PartAbstract;
use alsvanzelf\debugtoolbar\models\PartInterface;
use alsvanzelf\debugtoolbar\processors\RequestTimeProcessor;
use alsvanzelf\debugtoolbar\processors\RequestTypeProcessor;
use Monolog\Logger;
use Monolog\Processor\GitProcessor;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\ProcessIdProcessor;
use Monolog\Processor\WebProcessor;

/**
 * @todo add process() (store a processed version for easier display and re-usage between methods)
 */
class RequestPart extends PartAbstract implements PartInterface {
	const DURATION_ALERT = 0.5;
	
	public function name() {
		return 'Request';
	}
	
	public function title() {
		return 'Request';
	}
	
	public static function track(Logger $logger) {
		$logger->pushProcessor(new GitProcessor());
		$logger->pushProcessor(new MemoryPeakUsageProcessor());
		$logger->pushProcessor(new MemoryUsageProcessor());
		$logger->pushProcessor(new RequestTimeProcessor());
		$logger->pushProcessor(new RequestTypeProcessor());
		$logger->pushProcessor(new WebProcessor());
	}
	
	public function metrics() {
		$durationAlert = ($this->logData->request_duration > self::DURATION_ALERT) ? '> '.self::DURATION_ALERT.' s' : null;
		$memoryAlert   = (self::stringToBytes($this->logData->memory_peak_usage) > self::stringToBytes(ini_get('memory_limit')) / 4) ? '> 25% of set memory limit ('.ini_get('memory_limit').')' : null;
		$gitAlert      = (strpos($this->logData->git->branch, 'HEAD detached at') !== false) ? 'HEAD detached' : null;
		
		switch ($this->logData->http_method) {
			case 'GET':    $requestMethod = $this->logData->http_method.' ';                                      break;
			case 'POST':   $requestMethod = '<span class="text-primary">'.$this->logData->http_method.'</span> '; break;
			case 'PUT':
			case 'PATCH':  $requestMethod = '<span class="text-info">'   .$this->logData->http_method.'</span> '; break;
			case 'DELETE': $requestMethod = '<span class="text-danger">' .$this->logData->http_method.'</span> '; break;
			default:       $requestMethod = '<code>'                     .$this->logData->http_method.'</code> '; break;
		}
		
		$requestType = '';
		if ($this->logData->request_type !== null) {
			$requestType .= ' <span class="label label-default">'.$this->logData->request_type.'</span>';
		}
		
		$memoryDetail = new Detail($key='memory', $mode=Detail::MODE_INLINE);
		
		$metrics = [
			new Metric('Request',  $requestMethod.$this->logData->url.$requestType),
			new Metric('Duration', round($this->logData->request_duration, 3).' s', $featured=true, $durationAlert),
			new Metric('Memory',   $this->logData->memory_peak_usage.' (peak)', $featured=true, $memoryAlert, $memoryDetail),
			new Metric('Git',      $this->logData->git->branch.' <code>'.substr($this->logData->git->commit, 0, 7).'</code>', $featured=false, $gitAlert),
		];
		
		return $metrics;
	}
	
	public function detail(Detail $detail) {
		switch ($detail->key) {
			case 'memory': return $this->detailMemory();
			default:       throw new Exception('unknown detail key');
		}
	}
	
	private function detailMemory() {
		$template = file_get_contents(__DIR__.'/../templates/details/request_memory.html');
		$data     = [
			'memoryCurrentMetric' => $this->metricMemoryCurrent(),
			'memoryPeakMetric'    => $this->metricMemoryPeak($detail=true),
		];
		
		$mustache = new \Mustache_Engine();
		$rendered = $mustache->render($template, $data);
		
		return $rendered;
	}
	
	private function metricMemoryCurrent() {
		$alert  = (self::stringToBytes($this->logData->memory_usage) > self::stringToBytes(ini_get('memory_limit')) / 4) ? '> 25% of set memory limit ('.ini_get('memory_limit').')' : null;
		$metric = new Metric('During <code>Log::track</code>', $this->logData->memory_usage, $featured=false, $alert);
		
		return $metric;
	}
	
	private function metricMemoryPeak($detail=false) {
		$title    = ($detail) ? 'Peak' : 'Memory';
		$value    = ($detail) ? $this->logData->memory_peak_usage : $this->logData->memory_peak_usage.' (peak)';
		$featured = true;
		$alert    = (self::stringToBytes($this->logData->memory_peak_usage) > self::stringToBytes(ini_get('memory_limit')) / 4) ? '> 25% of set memory limit ('.ini_get('memory_limit').')' : null;
		$detail   = new Detail($key='memory', $mode=Detail::MODE_INLINE);
		
		$metric = new Metric($title, $value, $featured, $alert, $detail);
		
		return $metric;
	}
	
	private static function stringToBytes($byteString) {
		preg_match('{(?<bytes>[0-9]+) ?(?<unit>[A-Z])}', $byteString, $match);
		$bytes = (int) $match['bytes'];
		
		if (empty($match['unit'])) {
			return $bytes;
		}
		
		$units    = ['K'=>1, 'M'=>2, 'G'=>3, 'T'=>4];
		$exponent = $units[$match['unit']];
		$bytes    = $bytes * pow(1024, $exponent);
		
		return $bytes;
	}
}
