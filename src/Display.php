<?php

namespace alsvanzelf\debugtoolbar;

use alsvanzelf\debugtoolbar\Part;
use alsvanzelf\debugtoolbar\Value;

class Display {
	private $log;
	
	public function __construct($log) {
		$this->log = $log;
	}
	
	public function renderLog() {
		$parts = [
			$this->getRequestPart($this->log->extra),
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
		$values = [
			new Value('Request',  '<code>'.$logData->http_method.' '.$logData->url.'</code>'),
			new Value('Duration', round($logData->request_duration, 4).' ms', $featured=true),
			new Value('Memory',   $logData->memory_peak_usage.' (peak)', $featured=true),
			new Value('Git',      $logData->git->branch.' <code>'.substr($logData->git->commit, 0, 7).'</code>'),
		];
		
		return new Part('Request', ...$values);
	}
}
