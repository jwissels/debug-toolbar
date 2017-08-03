<?php

namespace alsvanzelf\debugtoolbar\parts;

use alsvanzelf\debugtoolbar\models\Detail;
use alsvanzelf\debugtoolbar\models\Metric;
use alsvanzelf\debugtoolbar\models\PartAbstract;
use alsvanzelf\debugtoolbar\models\PartInterface;
use alsvanzelf\debugtoolbar\processors\PDOQueryProcessor;
use Monolog\Logger;

/**
 * @todo add process() (store a processed version for easier display and re-usage between methods)
 */
class PDOPart extends PartAbstract implements PartInterface {
	public function name() {
		return 'PDO';
	}
	
	public function title() {
		return 'PDO';
	}
	
	public static function track(Logger $logger) {
		$logger->pushProcessor(new PDOQueryProcessor());
	}
	
	public function metrics() {
		$allCount     = count($this->logData->pdo_queries);
		
		$similarKeys    = [];
		$similarQueries = [];
		$similarHighest = 0;
		$equalKeys      = [];
		$equalQueries   = [];
		$equalHighest   = 0;
		foreach ($this->logData->pdo_queries as $queryData) {
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
			$detail = new Detail('records');
		}
		
		$metrics = [
			new Metric('All',     $allCount.' queries', $featured=true, $alert=null, $detail),
			new Metric('Similar', $similarValue, $featured=false, $similarAlert),
			new Metric('Equal',   $equalValue, $featured=false, $equalAlert),
		];
		
		return $metrics;
	}
	
	public function detail($detailKey, $detailMode) {
		switch ($detailKey) {
			case 'records': return $this->detailRecords();
			default:        throw new Exception('unknown detail key');
		}
	}
	
	private function detailRecords() {
		$allRecords = json_decode(json_encode($this->logData->pdo_queries), true);
		
		/**
		 * - duct-tape formatting for queries
		 * - prepare binds for mustache
		 * 
		 * @todo use a better formatter, i.e. https://github.com/zeroturnaround/sql-formatter
		 */
		foreach ($allRecords as &$record) {
			if (strpos($record['query'], "\n") === false) {
				$record['query'] = preg_replace('{\s(FROM|JOIN|WHERE|AND|OR|ORDER BY|GROUP BY|LIMIT)(\s)}', "\n$1$2", $record['query']);
			}
			
			$record['hasBinds'] = (!empty($record['binds']));
			if ($record['hasBinds'] === false) {
				continue;
			}
			
			array_walk($record['binds'], function(&$value, $key) {
				$value = ['key' => $key, 'value' => var_export($value, true)];
			});
			
			$record['binds'] = array_values((array) $record['binds']);
		}
		
		$template = file_get_contents(__DIR__.'/../templates/details/pdo_records.html');
		$data     = [
			'allRecords' => $allRecords,
		];
		
		$mustache = new \Mustache_Engine();
		$rendered = $mustache->render($template, $data);
		
		return $rendered;
	}
}
