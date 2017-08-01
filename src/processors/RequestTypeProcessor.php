<?php

namespace alsvanzelf\debugtoolbar\processors;

class RequestTypeProcessor {
	public function __invoke(array $record) {
		$record['extra']['request_type'] = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? $_SERVER['HTTP_X_REQUESTED_WITH'] : null;
		
		return $record;
	}
}
