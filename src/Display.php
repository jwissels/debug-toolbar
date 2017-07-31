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
}
