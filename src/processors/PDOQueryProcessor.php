<?php

namespace alsvanzelf\debugtoolbar\processors;

use alsvanzelf\debugtoolbar\Log;

class PDOQueryProcessor {
	public function __invoke(array $record) {
		$record['extra']['pdo_queries'] = Log::$trackedPDOQueries;
		
		return $record;
	}
}
