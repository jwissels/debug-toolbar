<?php

namespace alsvanzelf\debugtoolbar;

use alsvanzelf\debugtoolbar\models\Part;
use alsvanzelf\debugtoolbar\models\Metric;

class Display {
	private $log;
	
	private $options = [
		'collapse' => false,
		'request'  => ['duration_alert'=>0.5],
	];
	
	public function __construct($log, array $options=[]) {
		$this->log     = $log;
		$this->options = array_merge($this->options, $options);
	}
	
	public function renderSidebar() {
		$parts = [
			$this->getRequestPart($this->log->extra),
			$this->getPDOPart($this->log->extra),
		];
		
		$template = file_get_contents(__DIR__.'/templates/sidebar.html');
		$data     = [
			'collapse' => $this->options['collapse'],
			'log'      => $this->log,
			'parts'    => $parts,
		];
		
		$mustache = new \Mustache_Engine();
		$rendered = $mustache->render($template, $data);
		
		return $rendered;
	}
	
	private function getRequestPart($logData) {
		$durationAlert = ($logData->request_duration > $this->options['request']['duration_alert']) ? '> '.$this->options['request']['duration_alert'].' s' : null;
		$memoryAlert   = (self::stringToBytes($logData->memory_peak_usage) > self::stringToBytes(ini_get('memory_limit')) / 4) ? '> 25% of set memory limit ('.ini_get('memory_limit').')' : null;
		$gitAlert      = (strpos($logData->git->branch, 'HEAD detached at') !== false) ? 'HEAD detached' : null;
		
		switch ($logData->http_method) {
			case 'GET':    $requestMethod = $logData->http_method.' ';                                      break;
			case 'POST':   $requestMethod = '<span class="text-primary">'.$logData->http_method.'</span> '; break;
			case 'PUT':
			case 'PATCH':  $requestMethod = '<span class="text-info">'   .$logData->http_method.'</span> '; break;
			case 'DELETE': $requestMethod = '<span class="text-danger">' .$logData->http_method.'</span> '; break;
			default:       $requestMethod = '<code>'                     .$logData->http_method.'</code> '; break;
		}
		
		$requestType = '';
		if ($logData->request_type !== null) {
			$requestType .= ' <span class="label label-default">'.$logData->request_type.'</span>';
		}
		
		$metrics = [
			new Metric('Request',  $requestMethod.$logData->url.$requestType),
			new Metric('Duration', round($logData->request_duration, 3).' s', $featured=true, $durationAlert),
			new Metric('Memory',   $logData->memory_peak_usage.' (peak)', $featured=true, $memoryAlert),
			new Metric('Git',      $logData->git->branch.' <code>'.substr($logData->git->commit, 0, 7).'</code>', $featured=false, $gitAlert),
		];
		
		return new Part('Request', ...$metrics);
	}
	
	private function getPDOPart($logData) {
		$similarKeys    = [];
		$similarQueries = [];
		$similarHighest = 0;
		$equalKeys      = [];
		$equalQueries   = [];
		$equalHighest   = 0;
		foreach ($logData->pdo_queries as $queryData) {
			$similarKey = md5($queryData['query']);
			$equalKey   = md5($queryData['query'].serialize($queryData['binds']));
			
			if (isset($similarKeys[$similarKey])) {
				if (isset($similarQueries[$similarKey]) === false) {
					$similarQueries[$similarKey] = ['queries'=>[$similarKeys[$similarKey]], 'count'=>1];
				}
				$similarQueries[$similarKey]['queries'][] = $queryData;
				$similarQueries[$similarKey]['count']++;
				$similarHighest = max($similarHighest, $similarQueries[$similarKey]['count']);
			}
			
			if (isset($equalKeys[$equalKey])) {
				if (isset($equalQueries[$equalKey]) === false) {
					$equalQueries[$equalKey] = ['query'=>$queryData, 'count'=>1];
				}
				$equalQueries[$equalKey]['count']++;
				$equalHighest = max($equalHighest, $equalQueries[$equalKey]['count']);
			}
			
			$similarKeys[$similarKey] = $queryData;
			$equalKeys[$equalKey]   = $queryData;
		}
		$similarCount = count($similarQueries);
		$equalCount   = count($equalQueries);
		
		$similarAlert   = null;
		if ($similarHighest > 5) {
			$similarAlert = '> 5 times';
		}
		elseif ($similarCount > 2) {
			$similarAlert = '> 2 queries';
		}
		
		$equalAlert   = null;
		if ($equalHighest > 2) {
			$equalAlert = '> 2 times';
		}
		elseif ($equalCount > 2) {
			$equalAlert = '> 2 queries';
		}
		
		$metrics = [
			new Metric('All',     count($logData->pdo_queries).' queries', $featured=true),
			new Metric('Similar', $similarCount.' queries, at most '.$similarHighest.' times', $featured=false, $similarAlert),
			new Metric('Equal',   $equalCount.' queries, at most '.$equalHighest.' times', $featured=false, $equalAlert),
		];
		
		return new Part('PDO', ...$metrics);
	}
	
	public static function stringToBytes($byteString) {
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
