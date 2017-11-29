<?php

namespace alsvanzelf\debugtoolbar;

use alsvanzelf\debugtoolbar\models\Detail;
use alsvanzelf\debugtoolbar\parts\PDOPart;
use alsvanzelf\debugtoolbar\parts\RequestPart;
use alsvanzelf\debugtoolbar\parts\TwigPart;

class Display {
	private $log;
	
	private $options = [
		'collapse'  => false,
		'distUrl'   => '/dist/',
		'vendorUrl' => null,
	];
	
	public function __construct($log, array $options=[]) {
		$this->log     = $log;
		$this->options = array_merge($this->options, $options);
	}
	
	public function render() {
		$parts = [
			new RequestPart($this->log->extra->request, isset($this->options['request']) ? $this->options['request'] : []),
			new PDOPart($this->log->extra->pdo,         isset($this->options['pdo'])     ? $this->options['pdo']     : []),
			new TwigPart($this->log->extra->twig,       isset($this->options['twig'])    ? $this->options['twig']    : []),
		];
		
		$options = [
			'partials' => [
				'detail'  => file_get_contents(__DIR__.'/templates/detail.html'),
				'sidebar' => file_get_contents(__DIR__.'/templates/sidebar.html'),
			],
		];
		$template = file_get_contents(__DIR__.'/templates/display.html');
		$data     = [
			'collapse'  => $this->options['collapse'],
			'distUrl'   => $this->options['distUrl'],
			'vendorUrl' => $this->options['vendorUrl'],
			'log'       => $this->log,
			'datetime'  => $this->log->datetime->toDateTime()->format('Y-m-d H:i:s'),
			'parts'     => $parts,
		];
		
		$mustache = new \Mustache_Engine($options);
		$rendered = $mustache->render($template, $data);
		
		return $rendered;
	}
	
	public function renderDetail($detailRequest) {
		list($partName, $detailKey, $detailMode) = explode('|', $detailRequest);
		
		$partKey  = strtolower($partName);
		$partData = $this->log->extra->{$partKey};
		$options  = isset($this->options[$partKey]) ? $this->options[$partKey] : [];
		$detail   = new Detail($detailKey, $detailMode);
		
		$className = '\alsvanzelf\debugtoolbar\parts\\'.$partName.'Part';
		$part      = new $className($partData, $options);
		
		$template = file_get_contents(__DIR__.'/templates/'.$partKey.'/'.$detail->key.'.html');
		$data     = $part->detail($detail);
		$data['distUrl']   = $this->options['distUrl'];
		$data['vendorUrl'] = $this->options['vendorUrl'];
		
		$mustache = new \Mustache_Engine();
		$rendered = $mustache->render($template, $data);
		
		return $rendered;
	}
}
