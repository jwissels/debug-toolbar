<?php

namespace alsvanzelf\debugtoolbar;

use alsvanzelf\debugtoolbar\models\Detail;
use alsvanzelf\debugtoolbar\parts\PDOPart;
use alsvanzelf\debugtoolbar\parts\RequestPart;

class Display {
	private $log;
	
	private $options = [
		'collapse' => false,
	];
	
	public function __construct($log, array $options=[]) {
		$this->log     = $log;
		$this->options = array_merge($this->options, $options);
	}
	
	public function render() {
		$parts = [
			new RequestPart($this->log->extra),
			new PDOPart($this->log->extra),
		];
		
		$options = [
			'partials' => [
				'detail'  => file_get_contents(__DIR__.'/templates/detail.html'),
				'sidebar' => file_get_contents(__DIR__.'/templates/sidebar.html'),
			],
		];
		$template = file_get_contents(__DIR__.'/templates/display.html');
		$data     = [
			'collapse' => $this->options['collapse'],
			'log'      => $this->log,
			'parts'    => $parts,
		];
		
		$mustache = new \Mustache_Engine($options);
		$rendered = $mustache->render($template, $data);
		
		return $rendered;
	}
	
	public function renderDetail($detailRequest) {
		list($partName, $detailKey, $detailMode) = explode('|', $detailRequest);
		
		$className = '\alsvanzelf\debugtoolbar\parts\\'.$partName.'Part';
		$part      = new $className($this->log->extra);
		$detail    = new Detail($detailKey, $detailMode);
		$data     = $part->detail($detail);
		$template = file_get_contents(__DIR__.'/templates/'.strtolower($partName).'/'.$detail->key.'.html');
		
		$mustache = new \Mustache_Engine();
		$rendered = $mustache->render($template, $data);
		
		return $rendered;
	}
}
