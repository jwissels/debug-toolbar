<?php

namespace alsvanzelf\debugtoolbar;

class Display {
	private $log;
	
	public function __construct($log) {
		$this->log = $log;
	}
	
	public function renderLog() {
		$template = file_get_contents(__DIR__.'/templates/detail.html');
		$data     = [
			'log' => $this->log,
		];
		
		$mustache = new \Mustache_Engine();
		$rendered = $mustache->render($template, $data);
		
		return $rendered;
	}
}
