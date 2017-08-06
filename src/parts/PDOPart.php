<?php

namespace alsvanzelf\debugtoolbar\parts;

use alsvanzelf\debugtoolbar\models\Detail;
use alsvanzelf\debugtoolbar\models\Metric;
use alsvanzelf\debugtoolbar\models\PartAbstract;
use alsvanzelf\debugtoolbar\models\PartInterface;

/**
 * @todo add process() (store a processed version for easier display and re-usage between methods)
 */
class PDOPart extends PartAbstract implements PartInterface {
	public static $trackedQueries = [];
	
	private $nonBootstrapQueries = [];
	
	public function name() {
		return 'PDO';
	}
	
	public function title() {
		return 'PDO';
	}
	
	/**
	 * @note should be called after executing a statement
	 *       this allows new features to get most debug information from the statement object
	 * 
	 * @param  \PDOStatement $statement
	 * @param  array         $binds     needed as currently PDOStatement's debugDumpParams() doesn't include bind values
	 *                                  @see https://bugs.php.net/bug.php?id=52384
	 *                                  @see https://github.com/php/php-src/pull/1999
	 */
	public static function trackExecutedStatement(\PDOStatement $statement, array $binds=[]) {
		self::trackQuery($statement->queryString, $binds);
	}
	
	/**
	 * @note prefer to use trackExecutedStatement() is a statement is available
	 * 
	 * @param  string $queryString
	 * @param  array  $binds
	 */
	public static function trackQuery($queryString, array $binds=[]) {
		self::$trackedQueries[] = [
			'query' => $queryString,
			'binds' => $binds,
		];
	}
	
	public static function track() {
		if (empty(self::$trackedQueries)) {
			return null;
		}
		
		return [
			'queries' => self::$trackedQueries,
		];
	}
	
	public function metrics() {
		$similarKeys    = [];
		$similarQueries = [];
		$similarHighest = 0;
		$equalKeys      = [];
		$equalQueries   = [];
		$equalHighest   = 0;
		foreach ($this->nonBootstrapQueries() as $queryData) {
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
			$equalKeys[$equalKey]     = $queryData;
		}
		$allCount     = count($this->nonBootstrapQueries());
		$similarCount = count($similarQueries);
		$equalCount   = count($equalQueries);
		
		$allAlert = null;
		if (isset($this->options['alert_level_all_queries']) && $allCount > $this->options['alert_level_all_queries']) {
			$allAlert = '> '.$this->options['alert_level_all_queries'].' queries';
		}
		
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
			new Metric('All',     $allCount.' queries', $featured=true, $allAlert, $detail),
			new Metric('Similar', $similarValue, $featured=false, $similarAlert),
			new Metric('Equal',   $equalValue, $featured=false, $equalAlert),
		];
		
		return $metrics;
	}
	
	public function detail(Detail $detail) {
		/**
		 * - duct-tape formatting for queries
		 * - prepare binds for mustache
		 * 
		 * @todo use a better formatter, i.e. https://github.com/zeroturnaround/sql-formatter
		 */
		foreach ($this->nonBootstrapQueries() as &$record) {
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
		
		$data = [
			'allRecords' => $this->nonBootstrapQueries(),
		];
		
		return $data;
	}
	
	private function nonBootstrapQueries() {
		if (empty($this->nonBootstrapQueries)) {
			if (isset($this->options['bootstrap_query_hashes']) && !empty($this->logData->queries)) {
				foreach ($this->logData->queries as $queryData) {
					$queryKey = md5($queryData['query']);
					
					if (isset($this->options['bootstrap_query_hashes'][$queryKey])) {
						// only remove it once
						// i.e. a user get by id should be allowed after a session check
						unset($this->options['bootstrap_query_hashes'][$queryKey]);
						
						continue;
					}
					
					$this->nonBootstrapQueries[] = $queryData;
				}
			}
			else {
				$this->nonBootstrapQueries = $this->logData->queries;
			}
		}
		
		return $this->nonBootstrapQueries;
	}
}
