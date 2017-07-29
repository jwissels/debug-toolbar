<?php

namespace alsvanzelf\debugtoolbar\processors;

class RequestTimeProcessor {
	public function __invoke(array $record) {
		$record['extra']['request_duration'] = (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']);
		
		return $record;
	}
}
