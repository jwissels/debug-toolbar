<?php

namespace alsvanzelf\debugtoolbar;

use alsvanzelf\debugtoolbar\Part;
use alsvanzelf\debugtoolbar\Value;

class Display {
	private $log;
	
	private $options = [
		'request' => ['duration_alert'=>0.5],
	];
	
	public function __construct($log, array $options=[]) {
		$this->log     = $log;
		$this->options = array_merge($this->options, $options);
	}
	
	public function renderLog() {
		$parts = [
			$this->getRequestPart($this->log->extra),
			$this->getPDOPart($this->log->extra),
		];
		
		$template = file_get_contents(__DIR__.'/templates/detail.html');
		$data     = [
			'log'   => $this->log,
			'parts' => $parts,
		];
		
		$mustache = new \Mustache_Engine();
		$rendered = $mustache->render($template, $data);
		
		return $rendered;
	}
	
	private function getRequestPart($logData) {
		$durationAlert = ($logData->request_duration > $this->options['request']['duration_alert']) ? $this->options['request']['duration_alert'].' s' : null;
		$gitAlert      = (strpos($logData->git->branch, 'HEAD detached at') !== false) ? 'HEAD detached' : null;
		
		$values = [
			new Value('Request',  '<code>'.$logData->http_method.' '.$logData->url.'</code>'),
			new Value('Duration', round($logData->request_duration, 4).' s', $featured=true, $durationAlert),
			new Value('Memory',   $logData->memory_peak_usage.' (peak)', $featured=true),
			new Value('Git',      $logData->git->branch.' <code>'.substr($logData->git->commit, 0, 7).'</code>', $featured=false, $gitAlert),
		];
		
		return new Part('Request', ...$values);
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
		
		$values = [
			new Value('All',     count($logData->pdo_queries).' queries', $featured=true),
			new Value('Similar', $similarCount.' queries, at most '.$similarHighest.' times', $featured=false, $similarAlert),
			new Value('Equal',   $equalCount.' queries, at most '.$equalHighest.' times', $featured=false, $equalAlert),
		];
		
		return new Part('PDO', ...$values);
	}
}
