<?php

namespace alsvanzelf\debugtoolbar\models;

use alsvanzelf\debugtoolbar\models\Detail;

class PDORecordsDetail extends Detail {
	public $key = 'pdo_records';
	
	public $title = 'PDO records';
	
	public function allRecords() {
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
		
		return $allRecords;
	}
}
