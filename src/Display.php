<?php

namespace alsvanzelf\debugtoolbar;

use alsvanzelf\debugtoolbar\models\Detail;
use alsvanzelf\debugtoolbar\models\PDORecordsDetail;
use alsvanzelf\debugtoolbar\models\Metric;
use alsvanzelf\debugtoolbar\parts\RequestPart;

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
	
	public function render() {
		$parts = [
			new RequestPart($this->log->extra),
			#$this->getRequestPart($this->log->extra),
			#$this->getPDOPart($this->log->extra),
		];
		$details = [
			#new PDORecordsDetail($this->log->extra),
		];
		
		$options = [
			'partials' => [
				'detail'  => file_get_contents(__DIR__.'/templates/detail.html'),
				'sidebar' => file_get_contents(__DIR__.'/templates/sidebar.html'),
			],
		];
		$template = file_get_contents(__DIR__.'/templates/display.html');
		$data     = [
			'collapse' => $this->options['collapse'],
			'log'      => $this->log,
			'parts'    => $parts,
			'details'  => $details,
		];
		
		$mustache = new \Mustache_Engine($options);
		$rendered = $mustache->render($template, $data);
		
		return $rendered;
	}
	
	public function renderDetail($detailRequest) {
		list($partName, $detailKey, $detailMode) = explode('|', $detailRequest);
		
		$className = '\alsvanzelf\debugtoolbar\parts\\'.$partName.'Part';
		$part      = new $className($this->log->extra);
		
		return $part->detail($detailKey, $detailMode);
		
		
		
		$details = [
			'pdo_records' => new PDORecordsDetail($this->log->extra),
		];
		$detail = $details[$detailKey];
		
		return $detail->render();
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
		$allCount     = count($logData->pdo_queries);
		
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
		
		$similarValue = null;
		$similarAlert = null;
		if ($similarCount > 0) {
			if ($similarHighest > 5) {
				$similarAlert = '> 5 times';
			}
			elseif ($similarCount > 2) {
				$similarAlert = '> 2 queries';
			}
			
			$similarValue = $similarCount.' queries, at most '.$similarHighest.' times';
		}
		
		$equalValue = null;
		$equalAlert = null;
		if ($equalCount > 0) {
			if ($equalHighest > 2) {
				$equalAlert = '> 2 times';
			}
			elseif ($equalCount > 2) {
				$equalAlert = '> 2 queries';
			}
			
			$equalValue = $equalCount.' queries, at most '.$equalHighest.' times';
		}
		
		$detail = null;
		if ($allCount) {
			$detail = new PDORecordsDetail($logData);
		}
		
		$metrics = [
			new Metric('All',     $allCount.' queries', $featured=true, $alert=null, $detail),
			new Metric('Similar', $similarValue, $featured=false, $similarAlert),
			new Metric('Equal',   $equalValue, $featured=false, $equalAlert),
		];
		
		return new Part('PDO', ...$metrics);
	}
}
