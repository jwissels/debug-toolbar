<?php

namespace alsvanzelf\debugtoolbar\models;

class Detail {
	public $key = '';
	
	public $title = '';
	
	protected $logData = [];
	
	public function __construct($logData) {
		$this->logData = $logData;
	}
	
	public function render() {
		$template = file_get_contents(__DIR__.'/../templates/details/'.$this->key.'.html');
		$data     = [
			'detail' => $this,
		];
		
		$mustache = new \Mustache_Engine();
		$rendered = $mustache->render($template, $data);
		
		return $rendered;
	}
}
